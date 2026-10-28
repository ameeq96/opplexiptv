#!/usr/bin/env python3
"""Version validated daily articles in Git before importing or publishing. Dry-run by default."""

from __future__ import annotations

import argparse
import contextlib
import dataclasses
import datetime as dt
import functools
import hashlib
import json
import os
from pathlib import Path
import re
import stat
import subprocess
import sys
import time
import urllib.request
import urllib.parse


SITES = {
    "main": ("opplexiptv.com", "Opplex IPTV"),
    "trex": ("trexiptv8k.store", "Trex IPTV 8K"),
    "opplex": ("opplexiptv.store", "Opplex IPTV Store"),
    "geo": ("geoiptv.store", "Geo IPTV"),
    "5g": ("5gliveiptv.store", "5G Live IPTV"),
    "iptvsubscription": ("iptvsubscription.shop", "IPTV Subscription"),
    "getiptv": ("getiptv.store", "Get IPTV"),
}
SHA = re.compile(r"(?:[a-f0-9]{40}|[a-f0-9]{64})\Z")
SHA256 = re.compile(r"[a-f0-9]{64}\Z")
PKT = dt.timezone(dt.timedelta(hours=5), "Asia/Karachi")
MAX_ARTICLE = 2 * 1024**2
SSH_HOST = "opplex-iptv"
SERVER_MAIN = Path("/var/www/opplexiptv.com/current")
SERVER_DAILY = "/usr/local/libexec/opplex-daily-blog-git"
SERVER_PUBLISHER = "/usr/local/libexec/opplex-blog-publish"
SOURCE_PREFIX = "resources/content/blog-publications"
RUNTIME_UNTRACKED = ("public/screenshots/", "public/uploads/blogs/", "storage/public-from-archive/")
MAX_BASELINE = 2 * 1024**2
PHASES = {"preflight", "final_content", "git_stage", "git_commit", "git_push", "live_pull_import_publish",
          "live_preflight", "live_pull", "import", "publish", "api_budget_wait"}


class WorkflowError(RuntimeError):
    def __init__(self, code, progress=None):
        self.code = code if re.fullmatch(r"[a-z0-9_:-]{1,100}", code) else "daily_workflow_failed"
        self.progress = safe_progress(progress)
        super().__init__(self.code)


def safe_progress(value):
    if not isinstance(value, dict):
        return {}
    result = {}
    if isinstance(value.get("phase"), str) and value["phase"] in PHASES:
        result["phase"] = value["phase"]
    if isinstance(value.get("commit"), str) and SHA.fullmatch(value["commit"]):
        result["commit"] = value["commit"]
    for key in ("git_changes_possible", "live_changes_possible", "outcome_unknown"):
        if type(value.get(key)) is bool:
            result[key] = value[key]
    if isinstance(value.get("published_sites"), list):
        result["published_sites"] = list(dict.fromkeys(site for site in value["published_sites"] if isinstance(site, str) and site in SITES))
    return result


def preserve_progress(method):
    @functools.wraps(method)
    def run(self, *args, **kwargs):
        self.progress = {"phase": "preflight", "git_changes_possible": False,
                         "live_changes_possible": False, "outcome_unknown": False, "published_sites": []}
        try:
            return method(self, *args, **kwargs)
        except WorkflowError as error:
            remote = error.progress
            progress = safe_progress(self.progress)
            for key in ("git_changes_possible", "live_changes_possible", "outcome_unknown"):
                progress[key] = progress.get(key, False) or remote.get(key, False)
            progress["published_sites"] = list(dict.fromkeys(progress.get("published_sites", []) + remote.get("published_sites", [])))
            if "phase" in remote:
                progress["phase"] = remote["phase"]
            error.progress = progress
            raise
        except (OSError, ValueError, TypeError, KeyError) as error:
            raise WorkflowError("daily_workflow_failed", self.progress) from error
    return run


def source_path(site, date):
    if site not in SITES or not re.fullmatch(r"\d{4}-\d{2}-\d{2}", date):
        raise WorkflowError("invalid_site_or_date")
    return f"{SOURCE_PREFIX}/{site}/{date}.json"


def validate_day(date, now):
    try:
        parsed = dt.date.fromisoformat(date)
    except (ValueError, TypeError) as exc:
        raise WorkflowError("invalid_publication_day") from exc
    if parsed.isoformat() != date or date != now.astimezone(PKT).date().isoformat():
        raise WorkflowError("publication_day_is_not_current")


def as_bytes(value):
    return value.encode("utf-8") if isinstance(value, str) else value


def no_links(path):
    if path.is_symlink() or getattr(path, "is_junction", lambda: False)():
        raise WorkflowError("unsafe_source_path")


def beneath(root, relative):
    no_links(root)
    path = root
    for part in Path(relative).parts:
        if part in ("..", "."):
            raise WorkflowError("unsafe_source_path")
        path /= part
        no_links(path)
    if not path.resolve().is_relative_to(root.resolve()):
        raise WorkflowError("unsafe_source_path")
    return path


@contextlib.contextmanager
def workflow_lock(path):
    no_links(path.parent)
    path.parent.mkdir(mode=0o700, parents=True, exist_ok=True)
    no_links(path)
    with open(path, "a+b") as handle:
        if os.name == "nt":
            import msvcrt
            if handle.tell() == 0:
                handle.write(b"0")
                handle.flush()
            handle.seek(0)
            try:
                msvcrt.locking(handle.fileno(), msvcrt.LK_NBLCK, 1)
            except OSError as exc:
                raise WorkflowError("daily_workflow_locked") from exc
            try:
                yield
            finally:
                handle.seek(0)
                msvcrt.locking(handle.fileno(), msvcrt.LK_UNLCK, 1)
        else:
            import fcntl
            try:
                fcntl.flock(handle.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
            except BlockingIOError as exc:
                raise WorkflowError("daily_workflow_locked") from exc
            try:
                yield
            finally:
                fcntl.flock(handle.fileno(), fcntl.LOCK_UN)


def public_available(domain):
    try:
        request = urllib.request.Request("https://" + domain + "/", headers={"User-Agent": "OpplexDailyBlog/1.0"})
        with urllib.request.urlopen(request, timeout=20) as response:
            final = urllib.parse.urlsplit(response.geturl())
            return response.status == 200 and final.scheme == "https" and final.hostname == domain and not final.username and not final.password
    except (OSError, ValueError):
        return False


@dataclasses.dataclass(frozen=True)
class Layout:
    repo: Path = Path(__file__).resolve().parents[1]
    private: Path = Path(__file__).resolve().parents[1] / "storage/app/private/blog-writing"
    server: Path = SERVER_MAIN
    server_lock: Path = Path("/var/lib/opplex-blog-publishing/locks/daily-git.lock")
    baseline: Path = Path("/var/lib/opplex-blog-publishing/live-dirty-baseline.json")


class Commands:
    def __init__(self, *, execute=subprocess.run):
        self.execute = execute

    def call(self, argv, *, cwd=None, data=None, timeout=120, success_codes=(0,), env=None):
        try:
            options = {"cwd": cwd, "input": data, "capture_output": True, "timeout": timeout, "check": False}
            if env is not None:
                options["env"] = env
            result = self.execute(argv, **options)
        except (OSError, subprocess.SubprocessError) as exc:
            raise WorkflowError("command_failed_or_outcome_unknown") from exc
        if result.returncode not in success_codes:
            raise WorkflowError("command_failed_or_outcome_unknown")
        return as_bytes(result.stdout or b"")

    def protocol(self, argv, *, cwd=None, data=None, timeout=180, success_codes=(0,)):
        try:
            result = self.execute(argv, cwd=cwd, input=data, capture_output=True, timeout=timeout, check=False)
            value = json.loads(as_bytes(result.stdout or b""))
        except (OSError, subprocess.SubprocessError) as exc:
            raise WorkflowError("command_failed_or_outcome_unknown") from exc
        except (ValueError, TypeError) as exc:
            raise WorkflowError("invalid_command_response") from exc
        if not isinstance(value, dict) or result.returncode not in success_codes or value.get("ok") is not True or not isinstance(value.get("data"), dict):
            raise WorkflowError("workflow_check_failed", value.get("progress") if isinstance(value, dict) else None)
        return value["data"]

    def git(self, root, *arguments):
        environment = os.environ.copy()
        count = environment.get("GIT_CONFIG_COUNT", "0")
        if not re.fullmatch(r"[0-9]{1,2}", count) or int(count) > 32:
            raise WorkflowError("unsafe_git_environment")
        count = str(int(count))
        environment.update({"GIT_TERMINAL_PROMPT": "0", "GCM_INTERACTIVE": "never",
                            "GIT_CONFIG_COUNT": str(int(count) + 1),
                            "GIT_CONFIG_KEY_" + count: "credential.username", "GIT_CONFIG_VALUE_" + count: "ameeq96"})
        return self.call(["git", "-c", "core.quotepath=false", *arguments], cwd=root, env=environment)

    def repository(self, root):
        no_links(root)
        actual = self.git(root, "rev-parse", "--show-toplevel").decode("utf-8").strip()
        if Path(actual).resolve() != root.resolve():
            raise WorkflowError("wrong_repository_root")
        branch = self.git(root, "symbolic-ref", "--short", "HEAD").decode().strip()
        if branch not in ("main", "master"):
            raise WorkflowError("unexpected_repository_branch")
        for direction in ((), ("--push",)):
            remotes = self.git(root, "remote", "get-url", "--all", *direction, "origin").decode().splitlines()
            if not remotes:
                raise WorkflowError("unexpected_repository_remote")
            for remote in remotes:
                if remote == "git@github.com:ameeq96/opplexiptv.git":
                    continue
                url = urllib.parse.urlsplit(remote)
                if url.scheme != "https" or url.hostname != "github.com" or url.path != "/ameeq96/opplexiptv.git" or url.query or url.fragment or url.port not in (None, 443):
                    raise WorkflowError("unexpected_repository_remote")
        return branch

    def clean(self, root, allowed=(), runtime=()):
        entries = self.git(root, "status", "--porcelain=v1", "-z", "--untracked-files=all").decode("utf-8").split("\0")
        for entry in filter(None, entries):
            if entry[:3] == " M " and entry[3:] in runtime:
                continue
            if len(entry) < 4 or entry[:3] not in ("?? ", "A  ") or entry[3:] not in allowed:
                raise WorkflowError("unrelated_repository_changes")

    def head(self, root):
        revision = self.git(root, "rev-parse", "HEAD").decode().strip()
        if not SHA.fullmatch(revision):
            raise WorkflowError("invalid_git_revision")
        return revision


class DailyWorkflow(Commands):
    def __init__(self, layout=None, *, execute=subprocess.run, availability=public_available,
                 now=None, lock_factory=workflow_lock):
        super().__init__(execute=execute)
        self.layout = layout or Layout()
        self.availability = availability
        self.now = now or (lambda: dt.datetime.now(dt.timezone.utc))
        self.lock_factory = lock_factory

    def plan(self, sites, date):
        validate_day(date, self.now())
        if not sites or len(set(sites)) != len(sites):
            raise WorkflowError("invalid_site_selection")
        paths = [source_path(site, date) for site in sites]
        return {"dry_run": True, "mutations_performed": False, "date": date, "sources": paths,
                "steps": ["private_drafts", "read_only_preflight", "final_content_commit", "git_push",
                          "live_fast_forward_pull", "exact_source_check", "import", "build_verify_publish", "notify"]}

    @staticmethod
    def _cross_article_checks(articles):
        seen = []
        for article in articles:
            row = article["translations"]["en"]
            body = " ".join(" ".join([section["heading"], *section["paragraphs"], *section["items"]]) for section in row["sections"]).casefold()
            for _, name in SITES.values():
                body = body.replace(name.casefold(), " ")
            words = re.findall(r"[^\W_]+", body, re.UNICODE)
            grams = {tuple(words[index:index + 5]) for index in range(max(0, len(words) - 4))}
            topic = " ".join(article["topic"].split()).casefold()
            for other_topic, other_grams in seen:
                smallest = min(len(grams), len(other_grams))
                if topic == other_topic or (smallest and len(grams & other_grams) / smallest >= 0.72):
                    raise WorkflowError("daily_articles_are_too_similar")
            seen.append((topic, grams))

    @preserve_progress
    def publish(self, sites, date):
        self.plan(sites, date)
        layout = self.layout
        dated = beneath(layout.private, date)
        with self.lock_factory(dated / ".publish.lock"):
            allowed = [source_path(site, date) for site in sites]
            branch = self.repository(layout.repo)
            self.clean(layout.repo, allowed)
            initial_head = self.head(layout.repo)
            ready, skipped, articles = [], [], []
            for site in sites:
                if not self.availability(SITES[site][0]):
                    skipped.append({"site_key": site, "reason": "public_unavailable"})
                    continue
                draft = beneath(dated, site + ".json")
                if not draft.is_file() or draft.stat().st_size > MAX_ARTICLE:
                    raise WorkflowError("missing_or_oversized_private_draft")
                raw = draft.read_bytes().replace(b"\r\n", b"\n")
                article = json.loads(raw)
                if not isinstance(article, dict) or article.get("site_key") != site or article.get("publishing_date") != date:
                    raise WorkflowError("draft_identity_mismatch")
                result = self.protocol(["ssh", "-o", "BatchMode=yes", "-o", "ConnectTimeout=20", SSH_HOST,
                    "sudo", "-n", "-u", "www-data", "--", "/usr/bin/php", str(layout.server / "artisan"),
                    "blogs:publishing", "preflight", "--site=" + site, "--date=" + date, "--json"], data=raw)
                source_hash = hashlib.sha256(raw).hexdigest()
                if (result.get("valid") is not True or result.get("site_key") != site or result.get("publishing_date") != date
                    or result.get("source_sha256") != source_hash or not SHA256.fullmatch(str(result.get("fingerprint", "")))):
                    raise WorkflowError("preflight_identity_mismatch")
                ready.append({"site_key": site, "path": source_path(site, date), "sha256": source_hash,
                              "fingerprint": result["fingerprint"], "raw": raw})
                articles.append(article)
            self._cross_article_checks(articles)
            if not ready:
                return {"dry_run": False, "date": date, "published": [], "skipped": skipped}
            if self.head(layout.repo) != initial_head:
                raise WorkflowError("local_head_changed_during_preflight")
            self.clean(layout.repo, allowed)
            self.git(layout.repo, "fetch", "--no-tags", "origin", branch)
            remote_head = self.git(layout.repo, "rev-parse", "refs/remotes/origin/" + branch).decode().strip()
            if not SHA.fullmatch(remote_head):
                raise WorkflowError("invalid_remote_revision")
            self.git(layout.repo, "merge-base", "--is-ancestor", remote_head, initial_head)
            pending = set(filter(None, self.git(layout.repo, "diff", "--name-only", "-z", remote_head, initial_head).decode().split("\0")))
            ready_paths = {entry["path"] for entry in ready}
            if not pending.issubset(ready_paths):
                raise WorkflowError("unrelated_unpushed_commits")
            for entry in ready:
                self.progress.update(phase="final_content", git_changes_possible=True)
                path = beneath(layout.repo, entry["path"])
                path.parent.mkdir(mode=0o755, parents=True, exist_ok=True)
                if path.exists():
                    if not path.is_file() or path.read_bytes() != entry["raw"]:
                        raise WorkflowError("final_source_already_exists_with_different_content")
                else:
                    with open(path, "xb") as handle:
                        handle.write(entry["raw"])
                        handle.flush()
                        os.fsync(handle.fileno())
            self.progress.update(phase="git_stage", outcome_unknown=True)
            self.git(layout.repo, "add", "--", *sorted(ready_paths))
            staged = set(filter(None, self.git(layout.repo, "diff", "--cached", "--name-only", "-z").decode().split("\0")))
            if not staged.issubset(ready_paths) or self.head(layout.repo) != initial_head:
                raise WorkflowError("staged_content_or_head_changed")
            for entry in ready:
                blob = self.git(layout.repo, "show", ":" + entry["path"])
                if hashlib.sha256(blob).hexdigest() != entry["sha256"]:
                    raise WorkflowError("git_filter_changed_final_content")
            if staged:
                self.progress.update(phase="git_commit")
                self.git(layout.repo, "commit", "-m", "Publish daily IPTV articles for " + date, "--", *sorted(staged))
            commit = self.head(layout.repo)
            self.progress.update(commit=commit, phase="git_push")
            self.clean(layout.repo)
            committed_paths = set(filter(None, self.git(layout.repo, "diff", "--name-only", "-z", initial_head, commit).decode().split("\0")))
            if not committed_paths.issubset(ready_paths):
                raise WorkflowError("unexpected_paths_in_content_commit")
            for entry in ready:
                if hashlib.sha256(self.git(layout.repo, "show", commit + ":" + entry["path"])).hexdigest() != entry["sha256"]:
                    raise WorkflowError("committed_content_mismatch")
            self.progress.update(phase="git_push", outcome_unknown=True)
            self.git(layout.repo, "push", "origin", commit + ":refs/heads/" + branch)
            self.progress.update(outcome_unknown=False)
            if self.head(layout.repo) != commit:
                raise WorkflowError("local_head_changed_after_push")
            manifest = {"version": 1, "date": date, "commit": commit, "branch": branch,
                        "articles": [{key: value for key, value in entry.items() if key != "raw"} for entry in ready]}
            self.progress.update(phase="live_pull_import_publish", live_changes_possible=True, outcome_unknown=True)
            result = self.protocol(["ssh", "-o", "BatchMode=yes", "-o", "ConnectTimeout=20", SSH_HOST,
                "sudo", "-n", "/usr/bin/python3", SERVER_DAILY, "--server", "--publish", "--date=" + date],
                data=json.dumps(manifest, separators=(",", ":")).encode(), timeout=10800, success_codes=(0, 3))
            if result.get("commit") != commit or result.get("date") != date:
                raise WorkflowError("remote_publication_identity_mismatch")
            publications = result.get("published")
            if not isinstance(publications, list) or len(publications) != len(ready) or any(not isinstance(item, dict) or item.get("published") is not True for item in publications) or {item.get("site_key") for item in publications} != {item["site_key"] for item in ready}:
                raise WorkflowError("remote_publication_identity_mismatch")
            result["skipped"] = skipped
            return result


class GitReceiver(Commands):
    def __init__(self, layout=None, *, execute=subprocess.run, now=None, lock_factory=workflow_lock, pause=time.sleep):
        super().__init__(execute=execute)
        self.layout = layout or Layout()
        self.now = now or (lambda: dt.datetime.now(dt.timezone.utc))
        self.lock_factory = lock_factory
        self.pause = pause

    def _baseline_bytes(self):
        path = self.layout.baseline
        try:
            for parent in reversed(path.parents):
                no_links(parent)
            no_links(path)
            metadata = path.stat()
            if not stat.S_ISREG(metadata.st_mode) or metadata.st_size > MAX_BASELINE:
                raise WorkflowError("invalid_live_baseline")
            if os.name == "posix" and (metadata.st_uid != 0 or stat.S_IMODE(metadata.st_mode) != 0o600):
                raise WorkflowError("unsafe_live_baseline_permissions")
            with open(path, "rb") as handle:
                raw = handle.read(MAX_BASELINE + 1)
            if len(raw) > MAX_BASELINE:
                raise WorkflowError("invalid_live_baseline")
            return raw
        except OSError as error:
            raise WorkflowError("live_baseline_unavailable") from error

    @staticmethod
    def _baseline_path(path):
        if not isinstance(path, str) or not path or len(path) > 1024 or any(character in path for character in ("\\", ":", "\0")):
            raise WorkflowError("invalid_live_baseline")
        if any(part in ("", ".", "..", ".git") for part in path.split("/")) or path == "public/sitemap.xml" or path == SOURCE_PREFIX or path.startswith(SOURCE_PREFIX + "/"):
            raise WorkflowError("invalid_live_baseline")
        if any(path == prefix.rstrip("/") or path.startswith(prefix) for prefix in RUNTIME_UNTRACKED):
            raise WorkflowError("invalid_live_baseline")

    def _load_live_baseline(self, root):
        raw = self._baseline_bytes()
        try:
            document = json.loads(raw)
        except (ValueError, TypeError) as error:
            raise WorkflowError("invalid_live_baseline") from error
        if (not isinstance(document, dict) or set(document) != {"version", "root", "repository", "base_commit", "entries"}
            or type(document["version"]) is not int or document["version"] != 1 or document["root"] != str(root)
            or document["repository"] != "ameeq96/opplexiptv" or not isinstance(document["base_commit"], str)
            or not SHA.fullmatch(document["base_commit"]) or not isinstance(document["entries"], list) or len(document["entries"]) > 1000):
            raise WorkflowError("invalid_live_baseline")
        entries = {}
        for entry in document["entries"]:
            if not isinstance(entry, dict):
                raise WorkflowError("invalid_live_baseline")
            path = entry.get("path")
            self._baseline_path(path)
            if path in entries:
                raise WorkflowError("invalid_live_baseline")
            if entry.get("status") == " M":
                if set(entry) != {"path", "status", "sha256"} or not isinstance(entry["sha256"], str) or not SHA256.fullmatch(entry["sha256"]):
                    raise WorkflowError("invalid_live_baseline")
            elif entry.get("status") == " D":
                if set(entry) != {"path", "status", "missing"} or entry["missing"] is not True:
                    raise WorkflowError("invalid_live_baseline")
            else:
                raise WorkflowError("invalid_live_baseline")
            entries[path] = entry
        self.git(root, "merge-base", "--is-ancestor", document["base_commit"], self.head(root))
        return {"entries": entries, "sha256": hashlib.sha256(raw).hexdigest()}

    def _check_live_baseline(self, root, baseline):
        if hashlib.sha256(self._baseline_bytes()).hexdigest() != baseline["sha256"]:
            raise WorkflowError("live_baseline_changed")
        for prefix in RUNTIME_UNTRACKED:
            directory = beneath(root, prefix.rstrip("/"))
            if directory.exists() and not directory.is_dir():
                raise WorkflowError("unsafe_runtime_upload_path")
            if directory.is_dir():
                for child in directory.rglob("*"):
                    no_links(child)
        raw = self.git(root, "status", "--porcelain=v1", "-z", "--untracked-files=all").decode("utf-8")
        observed = set()
        for entry in filter(None, raw.split("\0")):
            if len(entry) < 4 or entry[2] != " ":
                raise WorkflowError("unrelated_repository_changes")
            status, path = entry[:2], entry[3:]
            if status == " M" and path == "public/sitemap.xml":
                file = beneath(root, path)
                if not file.is_file():
                    raise WorkflowError("unrelated_repository_changes")
                continue
            if status == "??" and any(path.startswith(prefix) for prefix in RUNTIME_UNTRACKED):
                file = beneath(root, path)
                if not file.is_file():
                    raise WorkflowError("unsafe_runtime_upload_path")
                continue
            expected = baseline["entries"].get(path)
            if expected is None or expected["status"] != status or path in observed:
                raise WorkflowError("unrelated_repository_changes")
            file = beneath(root, path)
            if status == " D":
                if file.exists():
                    raise WorkflowError("live_baseline_content_changed")
            elif not file.is_file() or hashlib.sha256(file.read_bytes()).hexdigest() != expected["sha256"]:
                raise WorkflowError("live_baseline_content_changed")
            observed.add(path)
        if observed != set(baseline["entries"]):
            raise WorkflowError("live_baseline_status_changed")

    @preserve_progress
    def receive(self, manifest):
        if not isinstance(manifest, dict) or set(manifest) != {"version", "date", "commit", "branch", "articles"} or type(manifest["version"]) is not int or manifest["version"] != 1:
            raise WorkflowError("invalid_publication_manifest")
        date, commit, branch, articles = (manifest[key] for key in ("date", "commit", "branch", "articles"))
        validate_day(date, self.now())
        if not isinstance(commit, str) or not SHA.fullmatch(commit) or branch not in ("main", "master") or not isinstance(articles, list) or not 1 <= len(articles) <= 7:
            raise WorkflowError("invalid_publication_manifest")
        sites = set()
        for item in articles:
            if not isinstance(item, dict) or set(item) != {"site_key", "path", "sha256", "fingerprint"}:
                raise WorkflowError("invalid_article_manifest")
            site = item["site_key"]
            if site in sites or item["path"] != source_path(site, date) or not isinstance(item["sha256"], str) or not SHA256.fullmatch(item["sha256"]) or not isinstance(item["fingerprint"], str) or not SHA256.fullmatch(item["fingerprint"]):
                raise WorkflowError("invalid_article_manifest")
            sites.add(site)
        root = self.layout.server.resolve()
        self.progress.update(commit=commit, phase="live_preflight")
        with self.lock_factory(self.layout.server_lock):
            if self.repository(root) != branch:
                raise WorkflowError("server_branch_mismatch")
            baseline = self._load_live_baseline(root)
            self._check_live_baseline(root, baseline)
            previous = self.head(root)
            sitemap = beneath(root, "public/sitemap.xml")
            previous_sitemap = hashlib.sha256(sitemap.read_bytes()).hexdigest() if sitemap.is_file() else None
            self.git(root, "fetch", "--no-tags", "origin", branch)
            remote_head = self.git(root, "rev-parse", "refs/remotes/origin/" + branch).decode().strip()
            if remote_head != commit:
                raise WorkflowError("remote_head_is_not_expected_commit")
            self.git(root, "merge-base", "--is-ancestor", previous, commit)
            changed = set(filter(None, self.git(root, "diff", "--name-only", "-z", previous, commit).decode().split("\0")))
            if not changed.issubset({item["path"] for item in articles}):
                raise WorkflowError("unexpected_paths_in_live_pull")
            self.progress.update(phase="live_pull", git_changes_possible=True, outcome_unknown=True)
            self.git(root, "pull", "--ff-only", "origin", commit)
            if self.head(root) != commit:
                raise WorkflowError("live_pull_revision_mismatch")
            self._check_live_baseline(root, baseline)
            after_sitemap = hashlib.sha256(sitemap.read_bytes()).hexdigest() if sitemap.is_file() else None
            if after_sitemap != previous_sitemap:
                raise WorkflowError("live_pull_changed_existing_sitemap")
            self.progress.update(outcome_unknown=False)
            drafts = []
            for item in articles:
                raw = self.git(root, "show", commit + ":" + item["path"])
                if len(raw) > MAX_ARTICLE or hashlib.sha256(raw).hexdigest() != item["sha256"]:
                    raise WorkflowError("committed_source_hash_mismatch")
                tracked = beneath(root, item["path"])
                if not tracked.is_file() or tracked.read_bytes() != raw:
                    raise WorkflowError("working_content_differs_from_commit")
                result = self.protocol(["/usr/sbin/runuser", "-u", "www-data", "--", "/usr/bin/php", str(root / "artisan"),
                    "blogs:publishing", "preflight", "--site=" + item["site_key"], "--date=" + date, "--json"], data=raw)
                if result.get("valid") is not True or result.get("site_key") != item["site_key"] or result.get("publishing_date") != date or result.get("source_sha256") != item["sha256"] or result.get("fingerprint") != item["fingerprint"]:
                    raise WorkflowError("live_preflight_changed")
                drafts.append((item, raw))
            published = []
            previous_next_build = False
            for item, raw in drafts:
                if item["site_key"] != "main" and previous_next_build:
                    self.progress.update(phase="api_budget_wait")
                    for seconds in (30, 30, 5):
                        self.pause(seconds)
                if self.layout.server.resolve() != root or self.head(root) != commit:
                    raise WorkflowError("live_source_changed_during_publication")
                self._check_live_baseline(root, baseline)
                site = item["site_key"]
                options = ["--source-commit=" + commit, "--source-sha256=" + item["sha256"], "--source-fingerprint=" + item["fingerprint"]]
                self.progress.update(phase="import", live_changes_possible=True, outcome_unknown=True)
                result = self.protocol(["/usr/sbin/runuser", "-u", "www-data", "--", "/usr/bin/php", str(root / "artisan"),
                    "blogs:publishing", "import", "--site=" + site, "--date=" + date, *options, "--json"], data=raw)
                run = result.get("run")
                if not isinstance(run, dict) or not isinstance(run.get("id"), int) or isinstance(run["id"], bool) or run["id"] <= 0:
                    raise WorkflowError("invalid_import_response")
                if run.get("git_source") != {"commit": commit, "path": item["path"], "sha256": item["sha256"], "fingerprint": item["fingerprint"]}:
                    raise WorkflowError("import_source_provenance_mismatch")
                self.progress.update(phase="publish", outcome_unknown=True)
                release = self.protocol(["/usr/bin/python3", SERVER_PUBLISHER, "--site=" + site, "--run=" + str(run["id"]), *options, "--publish"], timeout=1800, success_codes=(0, 3))
                if release.get("site_key") != site or release.get("run_id") != run["id"]:
                    raise WorkflowError("published_result_identity_mismatch")
                if release.get("published") is not True:
                    raise WorkflowError("publication_acknowledgement_missing")
                published.append(release)
                previous_next_build = previous_next_build or site != "main"
                self.progress.update(published_sites=[item["site_key"] for item in published], outcome_unknown=False)
            return {"dry_run": False, "date": date, "commit": commit, "published": published}


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--date", default=dt.datetime.now(PKT).date().isoformat())
    parser.add_argument("--site", action="append", choices=tuple(SITES))
    parser.add_argument("--server", action="store_true")
    mode = parser.add_mutually_exclusive_group()
    mode.add_argument("--publish", action="store_true")
    mode.add_argument("--dry-run", action="store_true")
    args = parser.parse_args(argv)
    try:
        if args.server and args.publish:
            if os.name != "posix" or not hasattr(os, "geteuid") or os.geteuid() != 0:
                raise WorkflowError("server_mode_requires_privileged_posix")
            raw = sys.stdin.buffer.read(65537)
            if len(raw) > 65536:
                raise WorkflowError("manifest_too_large")
            manifest = json.loads(raw)
            if not isinstance(manifest, dict) or manifest.get("date") != args.date or args.site:
                raise WorkflowError("manifest_argument_mismatch")
            result = GitReceiver().receive(manifest)
        else:
            workflow = DailyWorkflow()
            sites = args.site or list(SITES)
            result = workflow.publish(sites, args.date) if args.publish else workflow.plan(sites, args.date)
        print(json.dumps({"ok": True, "data": result}, ensure_ascii=False))
        if result.get("dry_run"):
            return 0
        return 0 if result.get("published") and not result.get("skipped") and all(item.get("published") and item.get("notifications_complete") for item in result["published"]) else 3
    except (WorkflowError, OSError, ValueError, TypeError, KeyError) as exc:
        code = exc.code if isinstance(exc, WorkflowError) else "daily_workflow_failed"
        result = {"ok": False, "error_code": code}
        if isinstance(exc, WorkflowError) and exc.progress:
            result["progress"] = exc.progress
        print(json.dumps(result))
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
