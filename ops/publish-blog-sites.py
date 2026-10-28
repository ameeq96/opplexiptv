#!/usr/bin/env python3
"""Publish a validated blog run on a fixed site; dry-run unless --publish is given."""

from __future__ import annotations

import argparse
import dataclasses
import datetime as dt
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import signal
import socket
import subprocess
import sys
import tarfile
import urllib.parse
import urllib.request
import uuid


LOCALES = ("en", "es", "fr", "it", "nl", "pt", "ru", "ur", "ar", "hi")
DOMAINS = {
    "main": "opplexiptv.com",
    "trex": "trexiptv8k.store",
    "opplex": "opplexiptv.store",
    "geo": "geoiptv.store",
    "5g": "5gliveiptv.store",
    "iptvsubscription": "iptvsubscription.shop",
    "getiptv": "getiptv.store",
}
SHA256 = re.compile(r"[a-f0-9]{64}\Z")
GIT_COMMIT = re.compile(r"(?:[a-f0-9]{40}|[a-f0-9]{64})\Z")
SLUG = re.compile(r"[a-z0-9]+(?:-[a-z0-9]+)*\Z")
PUBLISHED = {"published", "published_partial", "published_notified", "complete", "completed"}
MIN_FREE_BYTES = 2 * 1024**3
MAX_SNAPSHOT_BYTES = 64 * 1024**2


class PublishError(RuntimeError):
    def __init__(self, code: str, phase: str = "publish") -> None:
        super().__init__(code)
        self.code = code if re.fullmatch(r"[a-z0-9_:-]{1,100}", code) else "publishing_failed"
        self.phase = phase


@dataclasses.dataclass(frozen=True)
class Layout:
    www: Path = Path("/var/www")
    main: Path = Path("/var/www/opplexiptv.com/current")
    data: Path = Path("/var/lib/opplex-blog-publishing")
    backups: Path = Path("/var/backups/opplex-blog-publishing")


@dataclasses.dataclass(frozen=True)
class PublicState:
    target: Path
    was_directory: bool
    fingerprint: str = ""


def private_directory(path: Path, root: Path) -> None:
    if root.is_symlink() or not path.is_relative_to(root):
        raise PublishError("unsafe_private_directory", "filesystem")
    root.mkdir(mode=0o700, parents=True, exist_ok=True)
    current = root
    for part in path.relative_to(root).parts:
        current /= part
        if current.is_symlink():
            raise PublishError("unsafe_private_directory", "filesystem")
        current.mkdir(mode=0o700, exist_ok=True)


def tree_revision(root: Path) -> str:
    digest = hashlib.sha256()
    for path in sorted(root.rglob("*")):
        digest.update(str(path.relative_to(root)).encode("utf-8") + b"\0")
        if path.is_symlink():
            digest.update(b"link:" + os.readlink(path).encode("utf-8"))
        elif path.is_file():
            digest.update(b"file:" + hashlib.sha256(path.read_bytes()).digest())
        else:
            digest.update(b"directory")
    return digest.hexdigest()


def run_command(arguments, *, capture_output=False, timeout=None, check=False, **options):
    if capture_output:
        options.update(stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    process = subprocess.Popen(arguments, start_new_session=os.name == "posix", **options)
    try:
        stdout, stderr = process.communicate(timeout=timeout)
    except subprocess.TimeoutExpired as exc:
        if os.name == "posix":
            os.killpg(process.pid, signal.SIGTERM)
        else:
            process.terminate()
        try:
            stdout, stderr = process.communicate(timeout=5)
        except subprocess.TimeoutExpired:
            if os.name == "posix":
                os.killpg(process.pid, signal.SIGKILL)
            else:
                process.kill()
            stdout, stderr = process.communicate()
        raise subprocess.TimeoutExpired(arguments, timeout, output=stdout, stderr=stderr) from exc
    result = subprocess.CompletedProcess(arguments, process.returncode, stdout, stderr)
    if check and result.returncode:
        raise subprocess.CalledProcessError(result.returncode, arguments, output=stdout, stderr=stderr)
    return result


class SiteLock:
    def __init__(self, path: Path) -> None:
        self.path = path
        self.handle = None

    def __enter__(self):
        try:
            import fcntl
        except ImportError as exc:
            raise PublishError("unsupported_lock_platform", "lock") from exc
        private_directory(self.path.parent, self.path.parent.parent)
        if self.path.is_symlink():
            raise PublishError("unsafe_lock_path", "lock")
        flags = os.O_CREAT | os.O_RDWR | getattr(os, "O_NOFOLLOW", 0)
        self.handle = os.fdopen(os.open(self.path, flags, 0o600), "a+")
        try:
            fcntl.flock(self.handle.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError as exc:
            self.handle.close()
            self.handle = None
            raise PublishError("site_locked", "lock") from exc
        return self

    def __exit__(self, *_):
        if self.handle is not None:
            self.handle.close()


def positive_id(value: str) -> int:
    if not re.fullmatch(r"[1-9][0-9]{0,17}", value):
        raise argparse.ArgumentTypeError("run must be a positive integer")
    return int(value)


def public_available(domain: str) -> bool:
    try:
        socket.getaddrinfo(domain, 443)
        request = urllib.request.Request("https://" + domain + "/", headers={"User-Agent": "OpplexBlogPublisher/1.0"})
        with urllib.request.urlopen(request, timeout=20) as response:
            final = urllib.parse.urlsplit(response.geturl())
            return response.status == 200 and final.scheme == "https" and final.hostname == domain and not final.username and not final.password
    except (OSError, ValueError):
        return False


def private_json(path: Path, value: dict, *, exclusive: bool = False) -> None:
    raw = json.dumps(value, ensure_ascii=False, sort_keys=True, separators=(",", ":")).encode("utf-8")
    if len(raw) > MAX_SNAPSHOT_BYTES:
        raise PublishError("snapshot_too_large", "snapshot")
    path.parent.mkdir(mode=0o700, parents=True, exist_ok=True)
    if path.is_symlink():
        raise PublishError("unsafe_private_path", "snapshot")
    if exclusive and path.exists():
        if not path.is_file() or path.read_bytes() != raw:
            raise PublishError("existing_snapshot_mismatch", "snapshot")
        return
    temporary = path.with_name(path.name + "." + uuid.uuid4().hex + ".tmp")
    descriptor = os.open(temporary, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    with os.fdopen(descriptor, "wb") as handle:
        handle.write(raw)
        handle.flush()
        os.fsync(handle.fileno())
    os.replace(temporary, path)


class Publisher:
    def __init__(self, layout: Layout | None = None, *, execute=run_command,
                 availability=public_available, lock_factory=SiteLock, event=None) -> None:
        self.layout = layout or Layout()
        self.execute = execute
        self.availability = availability
        self.lock_factory = lock_factory
        self.event = event or (lambda value: None)

    def plan(self, site: str, run: int, initialize: bool = False, *, source_commit=None,
             source_sha256=None, source_fingerprint=None) -> dict:
        self._arguments(site, run)
        source = self._source_expectation(source_commit, source_sha256, source_fingerprint)
        return {
            "site_key": site, "domain": DOMAINS[site], "run_id": run, "dry_run": True,
            "initialize_releases": initialize, "mutations_performed": False,
            "steps": ["lock", "status"] + (["verify_git_source"] if source else [])
            + ["public_availability", "private_snapshot"]
            + ([] if site == "main" else ["backup", "clean_source_check", "build", "verify", "atomic_release"])
            + ["activate_and_verify", "notify"],
        }

    @staticmethod
    def _arguments(site: str, run: int) -> None:
        if site not in DOMAINS or isinstance(run, bool) or not isinstance(run, int) or run <= 0:
            raise PublishError("invalid_site_or_run", "arguments")

    @staticmethod
    def _source_expectation(commit, sha256, fingerprint) -> dict | None:
        if all(value is None for value in (commit, sha256, fingerprint)):
            return None
        if not isinstance(commit, str) or not GIT_COMMIT.fullmatch(commit) or not all(
            isinstance(value, str) and SHA256.fullmatch(value) for value in (sha256, fingerprint)
        ):
            raise PublishError("invalid_git_source_expectation", "arguments")
        return {"commit": commit, "sha256": sha256, "fingerprint": fingerprint}

    def _environment(self) -> dict:
        env = {key: os.environ[key] for key in ("HOME", "LANG", "TZ", "TMPDIR") if key in os.environ}
        env.update({"PATH": "/usr/local/bin:/usr/bin:/bin", "APP_ENV": "production"})
        return env

    def _php(self, action: str, site: str, run: int, **options) -> dict:
        if action not in {"status", "snapshot", "activate", "rollback", "block", "notify"}:
            raise PublishError("invalid_php_action", "protocol")
        arguments = ["/usr/sbin/runuser", "-u", "www-data", "--", "/usr/bin/php",
                     str(self.layout.main / "artisan"), "blogs:publishing", action,
                     "--site=" + site, "--run=" + str(run), "--json"]
        for key, value in options.items():
            if key not in {"revision", "snapshot-revision", "reason", "source-commit", "source-sha256", "source-fingerprint"}:
                raise PublishError("invalid_php_option", "protocol")
            if key.startswith("source-") and action != "activate":
                raise PublishError("invalid_php_option", "protocol")
            expression = GIT_COMMIT if key == "source-commit" else SHA256
            if key != "reason" and not expression.fullmatch(str(value)):
                raise PublishError("invalid_revision", "protocol")
            if key == "reason" and value != "public-unavailable":
                raise PublishError("invalid_block_reason", "protocol")
            arguments.append("--" + key + "=" + str(value))
        try:
            result = self.execute(arguments, capture_output=True, text=True, timeout=180,
                                  check=False, env=self._environment())
            response = json.loads(result.stdout)
        except (OSError, subprocess.SubprocessError, ValueError, TypeError) as exc:
            raise PublishError("php_protocol_failed", action) from exc
        if not isinstance(response, dict) or result.returncode != 0 or response.get("ok") is not True:
            code = response.get("error_code", "php_action_failed") if isinstance(response, dict) else "php_action_failed"
            raise PublishError(str(code), action)
        if not isinstance(response.get("data"), dict):
            raise PublishError("php_protocol_failed", action)
        return response["data"]

    def _snapshot(self, site: str, run: int, status: dict) -> tuple[dict, dict]:
        snapshot = self._php("snapshot", site, run)
        if snapshot.get("version") != 1 or snapshot.get("site_key") != site or snapshot.get("domain") != DOMAINS[site]:
            raise PublishError("snapshot_identity_mismatch", "snapshot")
        if snapshot.get("locales") != list(LOCALES) or not SHA256.fullmatch(str(snapshot.get("revision", ""))):
            raise PublishError("invalid_snapshot", "snapshot")
        if not isinstance(snapshot.get("labels"), dict) or set(snapshot["labels"]) != set(LOCALES):
            raise PublishError("invalid_snapshot_labels", "snapshot")
        posts = snapshot.get("posts")
        if not isinstance(posts, list) or len(posts) > 10000:
            raise PublishError("invalid_snapshot_posts", "snapshot")
        ids, slugs, candidates = set(), set(), []
        blog_id = status["run"].get("blog_id")
        for post in posts:
            if not isinstance(post, dict):
                raise PublishError("invalid_snapshot_post", "snapshot")
            post_id, slug = post.get("id"), post.get("slug")
            if isinstance(post_id, bool) or not isinstance(post_id, int) or post_id <= 0 or post_id in ids:
                raise PublishError("invalid_snapshot_post_id", "snapshot")
            if not isinstance(slug, str) or len(slug) > 180 or not SLUG.fullmatch(slug) or slug in slugs:
                raise PublishError("invalid_snapshot_slug", "snapshot")
            if not SHA256.fullmatch(str(post.get("revision", ""))):
                raise PublishError("invalid_snapshot_post_revision", "snapshot")
            translations = post.get("translations")
            if not isinstance(translations, dict) or set(translations) != set(LOCALES):
                raise PublishError("incomplete_translations", "snapshot")
            for translation in translations.values():
                if not isinstance(translation, dict) or not all(isinstance(translation.get(key), str) and translation[key].strip()
                    for key in ("title", "excerpt", "seo_title", "seo_description")) or not isinstance(translation.get("sections"), list) or not translation["sections"]:
                    raise PublishError("invalid_snapshot_translation", "snapshot")
            for key in ("published_at", "updated_at"):
                try:
                    date = dt.datetime.fromisoformat(post[key].replace("Z", "+00:00"))
                    if date.tzinfo is None:
                        raise ValueError("timezone required")
                except (KeyError, AttributeError, TypeError, ValueError) as exc:
                    raise PublishError("invalid_snapshot_date", "snapshot") from exc
            author = post.get("author", {})
            if not isinstance(author, dict) or author.get("type") != "Organization" or not isinstance(author.get("name"), str) or not author["name"].strip():
                raise PublishError("invalid_snapshot_author", "snapshot")
            ids.add(post_id)
            slugs.add(slug)
            if post_id == blog_id:
                candidates.append(post)
        if len(candidates) != 1:
            raise PublishError("candidate_not_in_snapshot", "snapshot")
        return snapshot, candidates[0]

    def _status(self, site: str, run: int) -> dict:
        status = self._php("status", site, run)
        current_run = status.get("run")
        if status.get("site_key") != site or not isinstance(current_run, dict) or current_run.get("id") != run:
            raise PublishError("run_identity_mismatch", "status")
        if status.get("enabled") is not True:
            raise PublishError("site_paused", "status")
        return status

    def _verify_git_source(self, site: str, status: dict, expected: dict | None, *, expected_checkout=None) -> str | None:
        if expected is None:
            return
        date = status["run"].get("publishing_date")
        if not isinstance(date, str) or not re.fullmatch(r"[0-9]{4}-[0-9]{2}-[0-9]{2}", date):
            raise PublishError("git_source_day_mismatch", "source")
        today = dt.datetime.now(dt.timezone(dt.timedelta(hours=5))).date().isoformat()
        if date != today:
            raise PublishError("git_source_day_mismatch", "source")
        path = "resources/content/blog-publications/" + site + "/" + date + ".json"
        if status["run"].get("git_source") != {**expected, "path": path}:
            raise PublishError("git_source_mismatch", "source")
        try:
            head = self.execute(["/usr/bin/git", "rev-parse", "HEAD"], cwd=self.layout.main,
                                capture_output=True, text=True, timeout=30, check=False, env=self._environment())
            if head.returncode or not isinstance(head.stdout, str) or not GIT_COMMIT.fullmatch(head.stdout.strip()):
                raise PublishError("git_source_commit_mismatch", "source")
            checkout = head.stdout.strip()
            if expected_checkout is not None and checkout != expected_checkout:
                raise PublishError("git_source_checkout_changed", "source")
            ancestor = self.execute(["/usr/bin/git", "merge-base", "--is-ancestor", expected["commit"], checkout],
                                    cwd=self.layout.main, capture_output=True, timeout=30, check=False, env=self._environment())
            if ancestor.returncode:
                raise PublishError("git_source_not_ancestor", "source")
            for revision in (expected["commit"], checkout):
                blob = self.execute(["/usr/bin/git", "show", revision + ":" + path], cwd=self.layout.main,
                                    capture_output=True, timeout=30, check=False, env=self._environment())
                if blob.returncode or not isinstance(blob.stdout, bytes) or len(blob.stdout) > 2 * 1024**2:
                    raise PublishError("git_source_blob_unavailable", "source")
                if hashlib.sha256(blob.stdout).hexdigest() != expected["sha256"]:
                    raise PublishError("git_source_blob_mismatch", "source")
            return checkout
        except (OSError, subprocess.SubprocessError) as exc:
            raise PublishError("git_source_verification_failed", "source") from exc

    @staticmethod
    def _eligibility(status: dict) -> dict:
        eligible = status.get("eligibility")
        if not isinstance(eligible, dict):
            raise PublishError("eligibility_missing", "preflight")
        if eligible.get("eligible") is not True:
            raise PublishError(str(eligible.get("error_code", "run_not_eligible")), "preflight")
        if not SHA256.fullmatch(str(eligible.get("revision", ""))) or not SHA256.fullmatch(str(eligible.get("snapshot_revision", ""))):
            raise PublishError("eligibility_revision_missing", "preflight")
        if eligible["revision"] != status["run"].get("revision"):
            raise PublishError("eligibility_revision_mismatch", "preflight")
        return eligible

    @staticmethod
    def _match_eligibility(eligible: dict, revision: str, snapshot_revision: str) -> None:
        if eligible["revision"] != revision:
            raise PublishError("eligibility_revision_mismatch", "preflight")
        if eligible["snapshot_revision"] != snapshot_revision:
            raise PublishError("eligibility_snapshot_mismatch", "preflight")

    def _run(self, arguments: list[str], *, cwd: Path, log: Path, phase: str, env=None) -> None:
        descriptor = os.open(log, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
        try:
            with os.fdopen(descriptor, "wb") as handle:
                result = self.execute(arguments, cwd=cwd, env=env or self._environment(),
                                      stdout=handle, stderr=subprocess.STDOUT, timeout=2400, check=False)
        except (OSError, subprocess.SubprocessError) as exc:
            raise PublishError(phase + "_failed", phase) from exc
        if result.returncode:
            raise PublishError(phase + "_failed", phase)

    def _public_state(self, base: Path, initialize: bool) -> PublicState:
        public, releases = base / "public", base / "releases"
        if public.is_symlink():
            try:
                target = public.resolve(strict=True)
                allowed = releases.resolve()
            except OSError as exc:
                raise PublishError("invalid_public_target", "public") from exc
            if not target.is_dir() or not target.is_relative_to(allowed):
                raise PublishError("unsafe_public_target", "public")
            return PublicState(target, False, tree_revision(target))
        if not public.is_dir():
            raise PublishError("missing_public_directory", "public")
        if not initialize:
            raise PublishError("physical_public_requires_initialization", "public")
        return PublicState(public, True, tree_revision(public))

    @staticmethod
    def _generated_paths(repo: Path) -> None:
        if not repo.is_dir() or repo.is_symlink():
            raise PublishError("unsafe_repository_path", "source")
        for relative in (".next", ".next/cache", ".next/dev", ".next/dev/cache", "out", "data", "data/.generated"):
            path = repo / relative
            if path.is_symlink() or (path.exists() and not path.resolve().is_relative_to(repo.resolve())):
                raise PublishError("unsafe_generated_path", "source")

    @staticmethod
    def _reject_symlinks(root: Path) -> None:
        if root.is_symlink() or any(path.is_symlink() for path in root.rglob("*")):
            raise PublishError("unexpected_export_symlink", "stage")

    def _stage(self, base: Path, exported: Path, old: PublicState, run: int, revision: str) -> Path:
        if not exported.is_dir():
            raise PublishError("missing_export", "stage")
        self._reject_symlinks(exported)
        releases = base / "releases"
        if releases.is_symlink():
            raise PublishError("unsafe_releases_directory", "stage")
        releases.mkdir(mode=0o755, exist_ok=True)
        release = releases / ("blog-" + str(run) + "-" + revision[:16] + "-" + uuid.uuid4().hex[:8])
        shutil.copytree(exported, release)
        old_assets = old.target / "_next" / "static"
        if old_assets.is_dir():
            self._reject_symlinks(old_assets)
            for path in old_assets.rglob("*"):
                if path.is_file():
                    destination = release / "_next" / "static" / path.relative_to(old_assets)
                    if not destination.exists():
                        destination.parent.mkdir(parents=True, exist_ok=True)
                        shutil.copy2(path, destination)
        for path in old.target.iterdir():
            if path.is_file() and (re.fullmatch(r"google[a-f0-9]{16}\.html", path.name) or path.name in {"BingSiteAuth.xml", "indexnow-key.txt"}):
                if path.is_symlink():
                    raise PublishError("unsafe_ownership_file", "stage")
                destination = release / path.name
                if destination.exists():
                    if destination.read_bytes() != path.read_bytes():
                        raise PublishError("ownership_marker_changed", "stage")
                else:
                    shutil.copy2(path, destination)
        return release

    @staticmethod
    def _link(base: Path, target: Path) -> None:
        temporary = base / (".public-blog-" + uuid.uuid4().hex)
        os.symlink(target, temporary, target_is_directory=True)
        try:
            os.replace(temporary, base / "public")
        except BaseException:
            if temporary.is_symlink():
                temporary.unlink()
            raise

    def _switch(self, base: Path, old: PublicState, release: Path, run: int) -> PublicState:
        public = base / "public"
        if old.fingerprint and tree_revision(old.target) != old.fingerprint:
            raise PublishError("concurrent_deployment", "switch")
        if old.was_directory:
            if public.is_symlink() or public != old.target or not public.is_dir():
                raise PublishError("concurrent_deployment", "switch")
            legacy = base / "releases" / ("legacy-before-" + str(run) + "-" + uuid.uuid4().hex[:8])
            os.replace(public, legacy)
            try:
                self._link(base, release)
            except BaseException:
                if not public.exists() and not public.is_symlink():
                    os.replace(legacy, public)
                raise
            return PublicState(legacy, True, old.fingerprint)
        if not public.is_symlink() or public.resolve() != old.target:
            raise PublishError("concurrent_deployment", "switch")
        self._link(base, release)
        return old

    def _restore(self, base: Path, old: PublicState, owned_release: Path) -> None:
        public = base / "public"
        if not public.is_symlink() or public.resolve() != owned_release.resolve():
            raise PublishError("rollback_concurrent_deployment", "rollback")
        if old.fingerprint and tree_revision(old.target) != old.fingerprint:
            raise PublishError("rollback_old_release_changed", "rollback")
        if old.was_directory:
            public.unlink()
            os.replace(old.target, public)
        else:
            self._link(base, old.target)

    def _notify(self, site: str, run: int, *, already_published: bool = False) -> dict:
        result = {"site_key": site, "domain": DOMAINS[site], "run_id": run,
                  "dry_run": False, "published": True, "already_published": already_published}
        try:
            response = self._php("notify", site, run)
            result["notifications_complete"] = response.get("complete") is True
            summaries = []
            deliveries = response.get("deliveries", [])
            if not isinstance(deliveries, list):
                deliveries = []
                result["notifications_complete"] = False
            for delivery in deliveries[:10]:
                if isinstance(delivery, dict):
                    summary = {}
                    for key in ("channel", "status", "error_code"):
                        value = delivery.get(key)
                        if isinstance(value, str):
                            summary[key] = value if re.fullmatch(r"[a-z0-9_:-]{1,100}", value) else "delivery_detail_withheld"
                    value = delivery.get("http_status")
                    if isinstance(value, int) and not isinstance(value, bool) and 100 <= value <= 599:
                        summary["http_status"] = value
                    summaries.append(summary)
            result["deliveries"] = summaries
        except PublishError as exc:
            result.update({"notifications_complete": False, "error_code": exc.code})
        return result

    def publish(self, site: str, run: int, *, initialize: bool = False, source_commit=None,
                source_sha256=None, source_fingerprint=None) -> dict:
        self._arguments(site, run)
        source = self._source_expectation(source_commit, source_sha256, source_fingerprint)
        if self.layout.data.is_symlink() or self.layout.backups.is_symlink():
            raise PublishError("unsafe_private_directory", "lock")
        with self.lock_factory(self.layout.data / "locks" / (site + ".lock")):
            self.event({"site_key": site, "run_id": run, "phase": "status"})
            status = self._status(site, run)
            checkout = self._verify_git_source(site, status, source)
            current_run = status["run"]
            if current_run.get("status") in PUBLISHED:
                return self._notify(site, run, already_published=True)
            eligible = self._eligibility(status)
            if not self.availability(DOMAINS[site]):
                self._php("block", site, run, reason="public-unavailable")
                return {"site_key": site, "domain": DOMAINS[site], "run_id": run,
                        "dry_run": False, "published": False, "blocked": "public-unavailable"}
            snapshot, candidate = self._snapshot(site, run, status)
            self._match_eligibility(eligible, candidate["revision"], snapshot["revision"])
            private = self.layout.data / "releases" / site / str(run)
            private_directory(private, self.layout.data)
            snapshot_path = private / ("snapshot-" + snapshot["revision"] + ".json")
            private_json(snapshot_path, snapshot, exclusive=True)
            options = {"revision": candidate["revision"], "snapshot-revision": snapshot["revision"]}
            if source:
                options.update({"source-commit": source["commit"], "source-sha256": source["sha256"],
                                "source-fingerprint": source["fingerprint"]})
            if site == "main":
                if source:
                    latest = self._status(site, run)
                    self._match_eligibility(self._eligibility(latest), candidate["revision"], snapshot["revision"])
                    self._verify_git_source(site, latest, source, expected_checkout=checkout)
                try:
                    self._php("activate", site, run, **options)
                except PublishError:
                    self._php("rollback", site, run, revision=candidate["revision"])
                    raise
                return self._notify(site, run)
            base, repo = self.layout.www / DOMAINS[site], self.layout.www / DOMAINS[site] / "repo"
            old = self._public_state(base, initialize)
            self._generated_paths(repo)
            source_status = self.execute(["/usr/bin/git", "status", "--porcelain=v1"], cwd=repo,
                                         capture_output=True, text=True, timeout=30, check=False, env=self._environment())
            if source_status.returncode or source_status.stdout.strip():
                raise PublishError("source_repository_not_clean", "source")
            if shutil.disk_usage(base).free < MIN_FREE_BYTES:
                raise PublishError("insufficient_free_space", "backup")
            attempt = self.layout.backups / site / ("run-" + str(run) + "-" + uuid.uuid4().hex)
            private_directory(attempt, self.layout.backups)
            with tarfile.open(attempt / "previous-public.tar.gz", "x:gz") as archive:
                archive.add(old.target, arcname="public")
            for label, relative in (("production", ".next/cache/fetch-cache"), ("development", ".next/dev/cache/fetch-cache")):
                cache = repo / relative
                if cache.exists():
                    if cache.is_symlink() or not cache.resolve().is_relative_to(repo.resolve()):
                        raise PublishError("unsafe_cache_path", "backup")
                    shutil.move(str(cache), str(attempt / (label + "-fetch-cache")))
            env = self._environment()
            env.update({"NEXT_TELEMETRY_DISABLED": "1", "NODE_ENV": "production",
                        "BLOG_BUILD_SNAPSHOT_PATH": str(snapshot_path), "LANDING_API_ORIGIN": "https://opplexiptv.com",
                        "NEXT_PUBLIC_LANDING_API_ORIGIN": "https://opplexiptv.com"})
            for phase in ("build", "verify"):
                self.event({"site_key": site, "run_id": run, "phase": phase})
                self._run(["/usr/bin/npm", "run", phase], cwd=repo, log=attempt / (phase + ".log"), phase=phase, env=env)
            source_after = self.execute(["/usr/bin/git", "status", "--porcelain=v1"], cwd=repo,
                                       capture_output=True, text=True, timeout=30, check=False, env=self._environment())
            if source_after.returncode or source_after.stdout.strip():
                raise PublishError("source_repository_changed_during_build", "source")
            release = self._stage(base, repo / "out", old, run, candidate["revision"])
            manifest = {"site_key": site, "run_id": run, "post_revision": candidate["revision"],
                        "snapshot_revision": snapshot["revision"], "previous_public": str(old.target),
                        "release": str(release), "state": "verified"}
            private_json(attempt / "manifest.json", manifest)
            latest = self._status(site, run)
            self._match_eligibility(self._eligibility(latest), candidate["revision"], snapshot["revision"])
            self._verify_git_source(site, latest, source, expected_checkout=checkout)
            old = self._switch(base, old, release, run)
            try:
                self._php("activate", site, run, **options)
            except PublishError as activation_error:
                try:
                    self._restore(base, old, release)
                    self._php("rollback", site, run, revision=candidate["revision"])
                except (PublishError, OSError) as rollback_error:
                    manifest["state"] = "rollback_needs_attention"
                    private_json(attempt / "manifest.json", manifest)
                    raise PublishError("activation_failed_rollback_incomplete", "rollback") from rollback_error
                manifest["state"] = "rolled_back"
                private_json(attempt / "manifest.json", manifest)
                raise activation_error
            manifest["state"] = "published"
            private_json(attempt / "manifest.json", manifest)
            return self._notify(site, run)


def main(argv=None) -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--site", required=True, choices=tuple(DOMAINS))
    parser.add_argument("--run", required=True, type=positive_id)
    mode = parser.add_mutually_exclusive_group()
    mode.add_argument("--publish", action="store_true")
    mode.add_argument("--dry-run", action="store_true")
    parser.add_argument("--initialize-releases", action="store_true")
    parser.add_argument("--source-commit")
    parser.add_argument("--source-sha256")
    parser.add_argument("--source-fingerprint")
    arguments = parser.parse_args(argv)
    publisher = Publisher(event=lambda value: print(json.dumps(value), file=sys.stderr, flush=True))
    source = {"source_commit": arguments.source_commit, "source_sha256": arguments.source_sha256,
              "source_fingerprint": arguments.source_fingerprint}
    try:
        if not arguments.publish:
            result = publisher.plan(arguments.site, arguments.run, arguments.initialize_releases, **source)
        else:
            if os.name != "posix" or not hasattr(os, "geteuid") or os.geteuid() != 0:
                raise PublishError("publish_requires_privileged_server", "arguments")
            result = publisher.publish(arguments.site, arguments.run, initialize=arguments.initialize_releases, **source)
        print(json.dumps({"ok": True, "data": result}, ensure_ascii=False))
        return 0 if result.get("dry_run") or (result.get("published") and result.get("notifications_complete")) else 3
    except (PublishError, OSError, ValueError) as exc:
        code = exc.code if isinstance(exc, PublishError) else "publishing_failed"
        phase = exc.phase if isinstance(exc, PublishError) else "filesystem"
        print(json.dumps({"ok": False, "error_code": code, "phase": phase}))
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
