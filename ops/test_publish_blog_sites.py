"""Local-only publisher tests: temp directories and mocked processes/network."""

import contextlib
import copy
import importlib.util
import io
import json
import os
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
from unittest import mock


MODULE_PATH = Path(__file__).with_name("publish-blog-sites.py")
SPEC = importlib.util.spec_from_file_location("blog_site_publisher", MODULE_PATH)
publisher = importlib.util.module_from_spec(SPEC)
sys.modules[SPEC.name] = publisher
SPEC.loader.exec_module(publisher)


class FakeLock:
    held = set()

    def __init__(self, path):
        self.path = str(path)

    def __enter__(self):
        if self.path in self.held:
            raise publisher.PublishError("site_locked", "lock")
        self.held.add(self.path)
        return self

    def __exit__(self, *_):
        self.held.remove(self.path)


def snapshot(site="getiptv", blog_id=41):
    translation = {
        "title": "A useful device check", "excerpt": "A practical starting point.",
        "seo_title": "A useful device check", "seo_description": "Check the device before changing its player.",
        "sections": [{"heading": "Check the starting point", "paragraphs": ["Use the same device for a useful comparison."], "items": [], "links": []}],
    }
    return {
        "version": 1, "site_key": site, "domain": publisher.DOMAINS[site], "revision": "a" * 64,
        "locales": list(publisher.LOCALES), "labels": {locale: {"readArticle": "Read article"} for locale in publisher.LOCALES},
        "posts": [{"id": blog_id, "slug": site + "-device-check", "revision": "b" * 64,
                   "published_at": "2026-10-27T05:00:00Z", "updated_at": "2026-10-27T05:00:00Z",
                   "author": {"type": "Organization", "name": "Opplex IPTV Subscription Services"},
                   "image": "https://" + publisher.DOMAINS[site] + "/share-card.png",
                   "translations": {locale: copy.deepcopy(translation) for locale in publisher.LOCALES}}],
    }


class FakeRunner:
    def __init__(self, site="getiptv"):
        self.site = site
        self.calls = []
        self.run_status = "draft"
        self.enabled = True
        self.revision = "b" * 64
        self.eligibility = {"eligible": True, "revision": "b" * 64, "snapshot_revision": "a" * 64}
        self.failures = set()
        self.dirty = False
        self.document = snapshot(site)
        self.publishing_date = "2026-10-27"
        self.git_source = None
        self.head = "d" * 40
        self.source_blob = b'{"version":1,"site_key":"getiptv"}\n'
        self.revision_blobs = {}
        self.ancestor = True
        self.notification = {"complete": True, "deliveries": [{"channel": "indexnow", "status": "accepted", "http_status": 200}]}

    def __call__(self, arguments, **kwargs):
        self.calls.append((list(arguments), kwargs))
        if "shell" in kwargs:
            raise AssertionError("No shell commands are permitted")
        if "blogs:publishing" in arguments:
            action = arguments[arguments.index("blogs:publishing") + 1]
            if action in self.failures:
                return subprocess.CompletedProcess(arguments, 1, json.dumps({"ok": False, "error_code": action + "_failed"}), "private server detail")
            data = {
                "status": {"site_key": self.site, "enabled": self.enabled, "eligibility": self.eligibility,
                           "run": {"id": 7, "blog_id": 41, "status": self.run_status, "revision": self.revision,
                                   "publishing_date": self.publishing_date, "git_source": self.git_source}},
                "snapshot": self.document, "activate": {"published": True}, "rollback": {"rolled_back": True},
                "block": {"blocked": True}, "notify": self.notification,
            }[action]
            return subprocess.CompletedProcess(arguments, 0, json.dumps({"ok": True, "data": data}), "")
        if arguments[0] == "/usr/bin/git":
            if arguments[1:2] == ["-c"]:
                if arguments[2] != "safe.directory=" + str(kwargs["cwd"].resolve()):
                    raise AssertionError("Only the exact repository may be trusted for this command")
                arguments = [arguments[0], *arguments[3:]]
            if arguments[1:] == ["rev-parse", "HEAD"]:
                return subprocess.CompletedProcess(arguments, 1 if "git-head" in self.failures else 0, self.head + "\n", "private server detail")
            if arguments[1] == "show":
                revision = arguments[2].split(":", 1)[0]
                return subprocess.CompletedProcess(arguments, 1 if "git-blob" in self.failures else 0,
                                                   self.revision_blobs.get(revision, self.source_blob), b"private server detail")
            if arguments[1:3] == ["merge-base", "--is-ancestor"]:
                return subprocess.CompletedProcess(arguments, 0 if self.ancestor else 1, b"", b"private server detail")
            if arguments[1:] == ["status", "--porcelain=v1"]:
                return subprocess.CompletedProcess(arguments, 0, " M source.jsx" if self.dirty else "", "")
            raise AssertionError("Unexpected Git command")
        phase = arguments[-1]
        if phase not in {"build", "verify"}:
            raise AssertionError("Unexpected process")
        return subprocess.CompletedProcess(arguments, 1 if phase in self.failures else 0)

    def actions(self):
        return [arguments[arguments.index("blogs:publishing") + 1]
                for arguments, _ in self.calls if "blogs:publishing" in arguments]


class PublisherTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.root = Path(self.temporary.name)
        self.layout = publisher.Layout(self.root / "www", self.root / "main", self.root / "data", self.root / "backups")
        self.layout.main.mkdir()
        self.base = self.layout.www / "getiptv.store"
        self.repo = self.base / "repo"
        self.public = self.base / "public"
        for path in [self.repo / "out" / "_next" / "static", self.public / "_next" / "static"]:
            path.mkdir(parents=True)
        (self.repo / "out" / "index.html").write_text("new article export")
        (self.repo / "out" / "_next" / "static" / "new.js").write_text("new hash")
        (self.public / "index.html").write_text("previous publication")
        (self.public / "_next" / "static" / "old.js").write_text("old hash")
        (self.public / "BingSiteAuth.xml").write_text("existing ownership")
        self.runner = FakeRunner()
        self.availability = mock.Mock(return_value=True)
        self.instance = publisher.Publisher(self.layout, execute=self.runner, availability=self.availability, lock_factory=FakeLock)

    def tearDown(self):
        FakeLock.held.clear()
        self.temporary.cleanup()

    def git_source(self, runner=None):
        runner = runner or self.runner
        runner.publishing_date = publisher.dt.datetime.now(publisher.dt.timezone(publisher.dt.timedelta(hours=5))).date().isoformat()
        digest = publisher.hashlib.sha256(runner.source_blob).hexdigest()
        runner.git_source = {"commit": runner.head, "path": "resources/content/blog-publications/" + runner.site + "/" + runner.publishing_date + ".json",
                             "sha256": digest, "fingerprint": "e" * 64}
        return {"source_commit": runner.head, "source_sha256": digest, "source_fingerprint": "e" * 64}

    def git_command(self, *arguments):
        return ["/usr/bin/git", "-c", "safe.directory=" + str(self.layout.main.resolve()), *arguments]

    def test_dry_run_never_calls_process_network_or_creates_files(self):
        before = sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*"))
        result = self.instance.plan("getiptv", 7, True)
        self.assertTrue(result["dry_run"])
        self.assertFalse(result["mutations_performed"])
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()
        self.assertEqual(before, sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*")))

    def test_git_source_dry_run_validates_flags_without_reading_source_or_status(self):
        source = self.git_source()
        before = sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*"))
        result = self.instance.plan("getiptv", 7, True, **source)
        self.assertIn("verify_git_source", result["steps"])
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()
        self.assertEqual(before, sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*")))

    def test_git_source_expectations_are_all_or_none_and_reject_injection(self):
        valid = self.git_source()
        cases = [{"source_commit": "d" * 40}, {"source_sha256": "e" * 64}, {"source_fingerprint": "e" * 64}]
        for key, value in [("source_commit", "d" * 40 + ";id"), ("source_commit", "../HEAD"),
                           ("source_commit", "d" * 41), ("source_sha256", "x" * 64),
                           ("source_fingerprint", "e" * 63), ("source_commit", True)]:
            cases.append({**valid, key: value})
        for source in cases:
            with self.subTest(source=source), self.assertRaisesRegex(publisher.PublishError, "invalid_git_source_expectation"):
                self.instance.publish("getiptv", 7, **source)
        self.assertEqual([], self.runner.calls)

    def test_git_source_status_must_match_exact_commit_hash_fingerprint_and_fixed_path(self):
        source = self.git_source()
        original = copy.deepcopy(self.runner.git_source)
        changes = [{"commit": "f" * 40}, {"sha256": "f" * 64}, {"fingerprint": "f" * 64},
                   {"path": "resources/content/blog-publications/main/2026-10-28.json"}, {"extra": "unexpected"}]
        for change in changes + [None]:
            self.runner.calls.clear()
            self.runner.git_source = {**original, **change} if change else None
            with self.subTest(change=change), self.assertRaisesRegex(publisher.PublishError, "git_source_mismatch"):
                self.instance.publish("getiptv", 7, initialize=True, **source)
            self.assertEqual(["status"], self.runner.actions())
            self.assertFalse(self.layout.backups.exists())
            self.assertEqual("previous publication", (self.public / "index.html").read_text())
        self.availability.assert_not_called()

    def test_git_source_requires_current_automatic_day_before_published_retry(self):
        source = self.git_source()
        self.runner.run_status = "published"
        for date in [None, "2000-01-01", "2099-12-31", "../2026-10-28", "20261028"]:
            self.runner.calls.clear()
            self.runner.publishing_date = date
            with self.subTest(date=date), self.assertRaisesRegex(publisher.PublishError, "git_source_day_mismatch"):
                self.instance.publish("getiptv", 7, **source)
            self.assertEqual(["status"], self.runner.actions())
        self.availability.assert_not_called()

    def test_git_source_day_uses_pakistan_date_across_utc_midnight_boundary(self):
        source = self.git_source()
        self.runner.publishing_date = "2026-10-28"
        self.runner.git_source["path"] = "resources/content/blog-publications/getiptv/2026-10-28.json"
        instant = publisher.dt.datetime(2026, 10, 27, 19, 30, tzinfo=publisher.dt.timezone.utc)
        with mock.patch.object(publisher.dt, "datetime", wraps=publisher.dt.datetime) as clock:
            clock.now.side_effect = lambda zone: instant.astimezone(zone)
            self.instance._verify_git_source("getiptv", {"run": {"publishing_date": self.runner.publishing_date,
                                              "git_source": self.runner.git_source}}, self.instance._source_expectation(**{
                                                  "commit": source["source_commit"], "sha256": source["source_sha256"],
                                                  "fingerprint": source["source_fingerprint"]}))
        self.assertEqual(4, len(self.runner.calls))

    def test_git_head_and_committed_blob_are_verified_before_snapshot_or_build(self):
        source = self.git_source()
        original_blob = self.runner.source_blob
        for failure, expected in [("head", "git_source_commit_mismatch"), ("blob", "git_source_blob_mismatch"),
                                  ("git-head", "git_source_commit_mismatch"), ("git-blob", "git_source_blob_unavailable")]:
            self.runner.calls.clear()
            self.runner.head = "invalid" if failure == "head" else source["source_commit"]
            self.runner.source_blob = b"changed source" if failure == "blob" else original_blob
            self.runner.failures = {failure}
            with self.subTest(failure=failure), self.assertRaisesRegex(publisher.PublishError, expected):
                self.instance.publish("getiptv", 7, initialize=True, **source)
            self.assertEqual(["status"], self.runner.actions())
            self.assertFalse(self.layout.data.exists())
            self.assertFalse(self.layout.backups.exists())
        self.availability.assert_not_called()

    def test_git_source_read_failures_never_echo_subprocess_details(self):
        source = self.git_source()
        def execute(arguments, **kwargs):
            if arguments == self.git_command("rev-parse", "HEAD"):
                raise subprocess.TimeoutExpired(arguments, 30, output="private-secret")
            return self.runner(arguments, **kwargs)
        self.instance.execute = execute
        with self.assertRaises(publisher.PublishError) as caught:
            self.instance.publish("getiptv", 7, **source)
        self.assertEqual("git_source_verification_failed", caught.exception.code)
        self.assertNotIn("private-secret", str(caught.exception))

    def test_valid_published_git_source_retries_notification_without_rebuild(self):
        source = self.git_source()
        self.runner.run_status = "published"
        result = self.instance.publish("getiptv", 7, **source)
        self.assertTrue(result["already_published"])
        self.assertEqual(["status", "notify"], self.runner.actions())
        shows = [(args, kwargs) for args, kwargs in self.runner.calls if args[:4] == self.git_command("show")]
        self.assertEqual(self.git_command("show", source["source_commit"] + ":" + self.runner.git_source["path"]), shows[0][0])
        self.assertEqual(self.layout.main, shows[0][1]["cwd"])
        self.assertFalse(shows[0][1].get("text", False))
        self.assertFalse(self.layout.data.exists())
        self.availability.assert_not_called()

    def test_git_source_changes_after_build_never_switch_or_activate(self):
        changes = {
            "provenance": lambda: self.runner.git_source.update(fingerprint="f" * 64),
            "date": lambda: setattr(self.runner, "publishing_date", "2000-01-01"),
            "head": lambda: setattr(self.runner, "head", "f" * 40),
            "blob": lambda: setattr(self.runner, "source_blob", b"changed committed article"),
        }
        for label, change in changes.items():
            self.runner.calls.clear()
            self.runner.source_blob = b'{"version":1,"site_key":"getiptv"}\n'
            self.runner.head = "d" * 40
            source = self.git_source()
            def execute(arguments, **kwargs):
                result = self.runner(arguments, **kwargs)
                if arguments == ["/usr/bin/npm", "run", "verify"]:
                    change()
                return result
            self.instance.execute = execute
            with mock.patch.object(self.instance, "_switch") as switch:
                with self.subTest(change=label), self.assertRaises(publisher.PublishError):
                    self.instance.publish("getiptv", 7, initialize=True, **source)
            switch.assert_not_called()
            self.assertNotIn("activate", self.runner.actions())
            self.assertNotIn("notify", self.runner.actions())
            self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_valid_git_source_is_rechecked_and_forwarded_to_landing_activation(self):
        source = self.git_source()
        with mock.patch.object(self.instance, "_switch", return_value=publisher.PublicState(self.public, True)):
            result = self.instance.publish("getiptv", 7, initialize=True, **source)
        self.assertTrue(result["published"])
        for action, keyword, count in [("rev-parse", "HEAD", 2), ("show", source["source_commit"] + ":" + self.runner.git_source["path"], 4)]:
            self.assertEqual(count, sum(arguments == self.git_command(action, keyword) for arguments, _ in self.runner.calls))
        activation = next(arguments for arguments, _ in self.runner.calls if "activate" in arguments)
        for key, value in source.items():
            self.assertIn("--" + key.replace("_", "-") + "=" + value, activation)

    def test_main_git_source_is_rechecked_before_activation(self):
        runner = FakeRunner("main")
        runner.head = "d" * 64
        source = self.git_source(runner)
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7, **source)
        self.assertTrue(result["published"])
        self.assertEqual(["status", "snapshot", "status", "activate", "notify"], runner.actions())
        self.assertEqual(2, sum(arguments == self.git_command("rev-parse", "HEAD") for arguments, _ in runner.calls))
        activation = next(arguments for arguments, _ in runner.calls if "activate" in arguments)
        self.assertIn("--source-commit=" + "d" * 64, activation)

    def test_main_git_source_change_while_reading_snapshot_prevents_activation(self):
        runner = FakeRunner("main")
        source = self.git_source(runner)
        def execute(arguments, **kwargs):
            result = runner(arguments, **kwargs)
            if "snapshot" in arguments:
                runner.git_source["sha256"] = "f" * 64
            return result
        instance = publisher.Publisher(self.layout, execute=execute, availability=self.availability, lock_factory=FakeLock)
        with self.assertRaisesRegex(publisher.PublishError, "git_source_mismatch"):
            instance.publish("main", 7, **source)
        self.assertNotIn("activate", runner.actions())
        self.assertNotIn("notify", runner.actions())

    def test_original_article_commit_remains_valid_after_a_descendant_code_fix(self):
        runner = FakeRunner("main")
        source = self.git_source(runner)
        runner.head = "f" * 40
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7, **source)
        self.assertTrue(result["published"])
        activation = next(args for args, _ in runner.calls if "activate" in args)
        self.assertIn("--source-commit=" + source["source_commit"], activation)
        self.assertNotIn("--source-commit=" + runner.head, activation)
        for revision in (source["source_commit"], runner.head):
            expected = self.git_command("show", revision + ":" + runner.git_source["path"])
            self.assertEqual(2, sum(args == expected for args, _ in runner.calls))
        ancestry = self.git_command("merge-base", "--is-ancestor", source["source_commit"], runner.head)
        self.assertEqual(2, sum(args == ancestry for args, _ in runner.calls))

    def test_different_owner_main_repository_uses_only_exact_command_local_safe_directory(self):
        runner = FakeRunner("main")
        source = self.git_source(runner)
        runner.head = "f" * 40
        trusted = self.git_command()
        def execute(arguments, **kwargs):
            if arguments[:1] == ["/usr/bin/git"] and kwargs.get("cwd") == self.layout.main:
                if arguments[:3] != trusted:
                    return subprocess.CompletedProcess(arguments, 128, "", "fatal: detected dubious ownership")
            return runner(arguments, **kwargs)
        rejected = execute(["/usr/bin/git", "rev-parse", "HEAD"], cwd=self.layout.main)
        self.assertEqual(128, rejected.returncode)
        instance = publisher.Publisher(self.layout, execute=execute, availability=self.availability, lock_factory=FakeLock)
        with mock.patch.dict(publisher.os.environ, {"SUDO_UID": "1000"}):
            result = instance.publish("main", 7, **source)
        self.assertTrue(result["published"])
        reads = [(args, kwargs) for args, kwargs in runner.calls if args[:1] == ["/usr/bin/git"]]
        self.assertEqual(8, len(reads))
        for args, kwargs in reads:
            self.assertEqual(trusted, args[:3])
            self.assertEqual(self.layout.main, kwargs["cwd"])
            self.assertNotIn("SUDO_UID", kwargs["env"])
            self.assertNotIn("--global", args)
            self.assertNotIn("safe.directory=*", args)
        self.assertTrue(any(args[3:5] == ["merge-base", "--is-ancestor"] for args, _ in reads))
        for revision in (source["source_commit"], runner.head):
            self.assertTrue(any(args == self.git_command("show", revision + ":" + runner.git_source["path"]) for args, _ in reads))
        activation = next(args for args, _ in runner.calls if "activate" in args)
        self.assertIn("--source-commit=" + source["source_commit"], activation)

    def test_published_notification_retry_accepts_unchanged_source_from_an_ancestor(self):
        source = self.git_source()
        self.runner.head = "f" * 40
        self.runner.run_status = "published"
        result = self.instance.publish("getiptv", 7, **source)
        self.assertTrue(result["already_published"])
        self.assertEqual(["status", "notify"], self.runner.actions())
        self.assertFalse(self.layout.data.exists())
        self.availability.assert_not_called()

    def test_nonancestor_source_is_rejected_before_snapshot_or_build(self):
        source = self.git_source()
        self.runner.head = "f" * 40
        self.runner.ancestor = False
        with self.assertRaisesRegex(publisher.PublishError, "git_source_not_ancestor"):
            self.instance.publish("getiptv", 7, initialize=True, **source)
        self.assertEqual(["status"], self.runner.actions())
        self.assertFalse(self.layout.data.exists())
        self.assertFalse(self.layout.backups.exists())
        self.availability.assert_not_called()

    def test_both_original_and_current_article_blobs_must_match_expected_source_hash(self):
        source = self.git_source()
        self.runner.head = "f" * 40
        for revision in (source["source_commit"], self.runner.head):
            self.runner.calls.clear()
            self.runner.revision_blobs = {revision: b"changed article in this revision"}
            with self.subTest(revision=revision), self.assertRaisesRegex(publisher.PublishError, "git_source_blob_mismatch"):
                self.instance.publish("getiptv", 7, initialize=True, **source)
            self.assertEqual(["status"], self.runner.actions())
            self.assertFalse(self.layout.data.exists())
            self.assertFalse(self.layout.backups.exists())
        self.availability.assert_not_called()

    def test_checkout_head_change_during_build_blocks_switch_even_with_unchanged_article(self):
        source = self.git_source()
        self.runner.head = "f" * 40
        def execute(arguments, **kwargs):
            result = self.runner(arguments, **kwargs)
            if arguments == ["/usr/bin/npm", "run", "verify"]:
                self.runner.head = "1" * 40
            return result
        self.instance.execute = execute
        with mock.patch.object(self.instance, "_switch") as switch:
            with self.assertRaisesRegex(publisher.PublishError, "git_source_checkout_changed"):
                self.instance.publish("getiptv", 7, initialize=True, **source)
        switch.assert_not_called()
        self.assertNotIn("activate", self.runner.actions())
        self.assertNotIn("notify", self.runner.actions())
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_main_checkout_head_change_after_snapshot_blocks_activation(self):
        runner = FakeRunner("main")
        source = self.git_source(runner)
        runner.head = "f" * 40
        def execute(arguments, **kwargs):
            result = runner(arguments, **kwargs)
            if "snapshot" in arguments:
                runner.head = "1" * 40
            return result
        instance = publisher.Publisher(self.layout, execute=execute, availability=self.availability, lock_factory=FakeLock)
        with self.assertRaisesRegex(publisher.PublishError, "git_source_checkout_changed"):
            instance.publish("main", 7, **source)
        self.assertNotIn("activate", runner.actions())
        self.assertNotIn("notify", runner.actions())

    def test_whitelist_and_run_id_block_injection(self):
        for site, run in [("getiptv; touch unsafe", 7), ("../main", 7), ("unknown", 7), ("getiptv", 0), ("getiptv", True)]:
            with self.subTest(site=site, run=run), self.assertRaises(publisher.PublishError):
                self.instance.publish(site, run)
        self.assertEqual([], self.runner.calls)
        for invalid in ["0", "-1", "1;id", "../7", "7.0"]:
            with self.subTest(value=invalid), self.assertRaises(Exception):
                publisher.positive_id(invalid)

    def test_per_site_lock_blocks_overlapping_run(self):
        with FakeLock(self.layout.data / "locks" / "getiptv.lock"):
            with self.assertRaisesRegex(publisher.PublishError, "site_locked"):
                self.instance.publish("getiptv", 7)
        self.assertEqual([], self.runner.calls)

    def test_already_published_run_only_retries_notification(self):
        self.runner.run_status = "published"
        result = self.instance.publish("getiptv", 7)
        self.assertTrue(result["published"])
        self.assertTrue(result["already_published"])
        self.assertEqual(["status", "notify"], self.runner.actions())
        self.availability.assert_not_called()
        self.assertFalse(self.layout.data.exists())

    def test_paused_site_is_not_published(self):
        self.runner.enabled = False
        with self.assertRaisesRegex(publisher.PublishError, "site_paused"):
            self.instance.publish("getiptv", 7)
        self.assertEqual(["status"], self.runner.actions())
        self.availability.assert_not_called()

    def test_initial_ineligibility_stops_expired_or_superseded_runs_before_snapshot_or_build(self):
        for reason in ["automatic_day_expired", "daily_limit_used", "newer_revision_queued"]:
            with self.subTest(reason=reason):
                self.runner.calls.clear()
                self.runner.eligibility = {"eligible": False, "error_code": reason}
                with self.assertRaisesRegex(publisher.PublishError, reason):
                    self.instance.publish("getiptv", 7, initialize=True)
                self.assertEqual(["status"], self.runner.actions())
                self.assertFalse(self.layout.data.exists())
                self.assertFalse(self.layout.backups.exists())
                self.assertEqual("previous publication", (self.public / "index.html").read_text())
        self.availability.assert_not_called()

    def test_missing_initial_eligibility_is_not_assumed_safe(self):
        self.runner.eligibility = None
        with self.assertRaisesRegex(publisher.PublishError, "eligibility_missing"):
            self.instance.publish("getiptv", 7, initialize=True)
        self.assertEqual(["status"], self.runner.actions())
        self.availability.assert_not_called()

    def test_initial_eligibility_must_match_candidate_and_site_snapshot(self):
        for field in ["revision", "snapshot_revision"]:
            with self.subTest(field=field):
                self.runner.calls.clear()
                self.runner.eligibility = {"eligible": True, "revision": "b" * 64, "snapshot_revision": "a" * 64}
                if field == "revision":
                    self.runner.document["posts"][0]["revision"] = "c" * 64
                else:
                    self.runner.document = snapshot()
                    self.runner.eligibility[field] = "c" * 64
                with self.assertRaisesRegex(publisher.PublishError, "eligibility_.*_mismatch"):
                    self.instance.publish("getiptv", 7, initialize=True)
                self.assertNotIn("activate", self.runner.actions())
                self.assertFalse(self.layout.backups.exists())
                self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_post_build_eligibility_changes_never_switch_public_release(self):
        changes = {
            "expired": lambda: setattr(self.runner, "eligibility", {"eligible": False, "error_code": "automatic_day_expired"}),
            "newer edit": lambda: setattr(self.runner, "eligibility", {"eligible": False, "error_code": "newer_revision_queued"}),
            "paused": lambda: setattr(self.runner, "enabled", False),
            "article revision": lambda: (setattr(self.runner, "revision", "c" * 64), setattr(self.runner, "eligibility", {"eligible": True, "revision": "c" * 64, "snapshot_revision": "a" * 64})),
            "site revision": lambda: setattr(self.runner, "eligibility", {"eligible": True, "revision": "b" * 64, "snapshot_revision": "c" * 64}),
        }
        for label, change in changes.items():
            with self.subTest(change=label):
                self.runner.calls.clear()
                self.runner.enabled = True
                self.runner.revision = "b" * 64
                self.runner.eligibility = {"eligible": True, "revision": "b" * 64, "snapshot_revision": "a" * 64}
                def execute(arguments, **kwargs):
                    result = self.runner(arguments, **kwargs)
                    if arguments == ["/usr/bin/npm", "run", "verify"]:
                        change()
                    return result
                self.instance.execute = execute
                with mock.patch.object(self.instance, "_switch") as switch:
                    with self.assertRaises(publisher.PublishError):
                        self.instance.publish("getiptv", 7, initialize=True)
                switch.assert_not_called()
                self.assertNotIn("activate", self.runner.actions())
                self.assertNotIn("notify", self.runner.actions())
                self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_unavailable_domain_records_block_without_build_or_notify(self):
        self.availability.return_value = False
        result = self.instance.publish("getiptv", 7)
        self.assertFalse(result["published"])
        self.assertEqual("public-unavailable", result["blocked"])
        self.assertEqual(["status", "block"], self.runner.actions())
        self.assertFalse(self.layout.data.exists())

    def test_incomplete_translations_block_publication(self):
        del self.runner.document["posts"][0]["translations"]["ar"]
        with self.assertRaisesRegex(publisher.PublishError, "incomplete_translations"):
            self.instance.publish("getiptv", 7, initialize=True)
        self.assertNotIn("activate", self.runner.actions())
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_wrong_domain_or_candidate_is_rejected(self):
        for change in [lambda document: document.update(domain="example.invalid"),
                       lambda document: document["posts"][0].update(id=999),
                       lambda document: document["posts"][0].update(slug="../../private")]:
            self.runner.document = snapshot()
            change(self.runner.document)
            with self.assertRaises(publisher.PublishError):
                self.instance.publish("getiptv", 7, initialize=True)
        self.assertNotIn("activate", self.runner.actions())

    def test_physical_public_requires_explicit_bootstrap(self):
        with self.assertRaisesRegex(publisher.PublishError, "physical_public_requires_initialization"):
            self.instance.publish("getiptv", 7)
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_dirty_source_is_preserved_and_not_built(self):
        self.runner.dirty = True
        with self.assertRaisesRegex(publisher.PublishError, "source_repository_not_clean"):
            self.instance.publish("getiptv", 7, initialize=True)
        self.assertFalse(self.layout.backups.exists())
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_source_changed_by_build_is_not_discarded_or_published(self):
        def execute(arguments, **kwargs):
            result = self.runner(arguments, **kwargs)
            if arguments == ["/usr/bin/npm", "run", "verify"]:
                self.runner.dirty = True
            return result
        self.instance.execute = execute
        with self.assertRaisesRegex(publisher.PublishError, "source_repository_changed_during_build"):
            self.instance.publish("getiptv", 7, initialize=True)
        self.assertTrue(self.runner.dirty)
        self.assertEqual("previous publication", (self.public / "index.html").read_text())
        self.assertNotIn("activate", self.runner.actions())

    def test_generated_directory_symlinks_are_rejected_before_build(self):
        unsafe = self.repo / ".next" / "cache"
        with mock.patch.object(Path, "is_symlink", autospec=True, side_effect=lambda path: path == unsafe):
            with self.assertRaisesRegex(publisher.PublishError, "unsafe_generated_path"):
                self.instance._generated_paths(self.repo)

    def test_changed_public_files_are_not_overwritten_by_release_switch(self):
        old = self.instance._public_state(self.base, True)
        (self.public / "index.html").write_text("concurrent user publication")
        with self.assertRaisesRegex(publisher.PublishError, "concurrent_deployment"):
            self.instance._switch(self.base, old, self.base / "releases" / "new", 7)
        self.assertEqual("concurrent user publication", (self.public / "index.html").read_text())

    def test_build_or_verification_failure_preserves_current_public(self):
        for failure in ["build", "verify"]:
            with self.subTest(failure=failure):
                self.runner.failures = {failure}
                with self.assertRaisesRegex(publisher.PublishError, failure + "_failed"):
                    self.instance.publish("getiptv", 7, initialize=True)
                self.assertEqual("previous publication", (self.public / "index.html").read_text())
                self.assertNotIn("activate", self.runner.actions())
                self.assertNotIn("notify", self.runner.actions())

    def test_low_disk_space_never_removes_old_releases(self):
        with mock.patch.object(publisher.shutil, "disk_usage", return_value=mock.Mock(free=1)):
            with self.assertRaisesRegex(publisher.PublishError, "insufficient_free_space"):
                self.instance.publish("getiptv", 7, initialize=True)
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_staging_keeps_old_hashed_assets_and_ownership_files(self):
        old = publisher.PublicState(self.public, True)
        release = self.instance._stage(self.base, self.repo / "out", old, 7, "b" * 64)
        self.assertEqual("old hash", (release / "_next" / "static" / "old.js").read_text())
        self.assertEqual("new hash", (release / "_next" / "static" / "new.js").read_text())
        self.assertEqual("existing ownership", (release / "BingSiteAuth.xml").read_text())
        self.assertEqual("previous publication", (self.public / "index.html").read_text())

    def test_changed_ownership_file_is_never_published(self):
        (self.repo / "out" / "BingSiteAuth.xml").write_text("different ownership")
        with self.assertRaisesRegex(publisher.PublishError, "ownership_marker_changed"):
            self.instance._stage(self.base, self.repo / "out", publisher.PublicState(self.public, True), 7, "b" * 64)

    def test_rollback_refuses_to_overwrite_another_deployment(self):
        old_bytes = (self.public / "index.html").read_bytes()
        with self.assertRaisesRegex(publisher.PublishError, "rollback_concurrent_deployment"):
            self.instance._restore(self.base, publisher.PublicState(self.public, False), self.base / "releases" / "owned")
        self.assertEqual(old_bytes, (self.public / "index.html").read_bytes())

    def test_activation_failure_rolls_back_only_the_owned_release(self):
        self.runner.failures = {"activate"}
        with mock.patch.object(self.instance, "_switch", return_value=publisher.PublicState(self.public, True)) as switch:
            with mock.patch.object(self.instance, "_restore") as restore:
                with self.assertRaisesRegex(publisher.PublishError, "activate_failed"):
                    self.instance.publish("getiptv", 7, initialize=True)
        switch.assert_called_once()
        restore.assert_called_once()
        self.assertEqual(["status", "snapshot", "status", "activate", "rollback"], self.runner.actions())
        self.assertNotIn("notify", self.runner.actions())

    def test_concurrent_rollback_is_reported_without_php_rollback(self):
        self.runner.failures = {"activate"}
        with mock.patch.object(self.instance, "_switch", return_value=publisher.PublicState(self.public, True)):
            with mock.patch.object(self.instance, "_restore", side_effect=publisher.PublishError("rollback_concurrent_deployment")):
                with self.assertRaisesRegex(publisher.PublishError, "activation_failed_rollback_incomplete"):
                    self.instance.publish("getiptv", 7, initialize=True)
        self.assertNotIn("rollback", self.runner.actions())

    def test_notification_failure_does_not_roll_back_a_published_article(self):
        runner = FakeRunner("main")
        runner.failures = {"notify"}
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7)
        self.assertTrue(result["published"])
        self.assertFalse(result["notifications_complete"])
        self.assertEqual(["status", "snapshot", "activate", "notify"], runner.actions())
        self.assertNotIn("rollback", runner.actions())

    def test_missing_google_authorization_keeps_delivery_pending(self):
        runner = FakeRunner("main")
        runner.notification = {"complete": False, "deliveries": [{"channel": "google", "status": "blocked", "error_code": "google_credentials_missing"}]}
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7)
        self.assertTrue(result["published"])
        self.assertFalse(result["notifications_complete"])
        self.assertEqual("google_credentials_missing", result["deliveries"][0]["error_code"])

    def test_notification_details_never_echo_raw_provider_errors(self):
        runner = FakeRunner("main")
        runner.notification = {"complete": False, "deliveries": [{"channel": "google", "status": "failed", "error_code": "Bearer private-secret-value"}]}
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7)
        self.assertNotIn("private-secret", json.dumps(result))
        self.assertEqual("delivery_detail_withheld", result["deliveries"][0]["error_code"])

    def test_malformed_notification_receipt_is_not_reported_complete(self):
        runner = FakeRunner("main")
        runner.notification = {"complete": True, "deliveries": {"unexpected": "shape"}}
        instance = publisher.Publisher(self.layout, execute=runner, availability=self.availability, lock_factory=FakeLock)
        result = instance.publish("main", 7)
        self.assertTrue(result["published"])
        self.assertFalse(result["notifications_complete"])

    def test_timed_out_commands_stop_the_owned_process_group(self):
        process = mock.Mock(pid=123)
        process.communicate.side_effect = [subprocess.TimeoutExpired(["fixed-command"], 1), (b"", b"")]
        with mock.patch.object(publisher.os, "name", "posix"):
            with mock.patch.object(publisher.subprocess, "Popen", return_value=process) as popen:
                with mock.patch.object(publisher.os, "killpg", create=True) as kill_group:
                    with self.assertRaises(subprocess.TimeoutExpired):
                        publisher.run_command(["fixed-command"], timeout=1)
                    kill_group.assert_called_once_with(123, publisher.signal.SIGTERM)
        self.assertTrue(popen.call_args.kwargs["start_new_session"])

    def test_php_failures_never_echo_stderr(self):
        self.runner.failures = {"status"}
        with self.assertRaises(publisher.PublishError) as caught:
            self.instance.publish("getiptv", 7)
        self.assertEqual("status_failed", caught.exception.code)
        self.assertNotIn("private server detail", str(caught.exception))

    def test_cli_defaults_to_dry_run_without_linux_privileges(self):
        with contextlib.redirect_stdout(io.StringIO()) as output:
            result = publisher.main(["--site", "getiptv", "--run", "7"])
        self.assertEqual(0, result)
        self.assertTrue(json.loads(output.getvalue())["data"]["dry_run"])

    def test_cli_git_source_flags_require_complete_valid_expectations_even_for_dry_run(self):
        arguments = ["--site", "getiptv", "--run", "7", "--source-commit", "d" * 40,
                     "--source-sha256", "e" * 64, "--source-fingerprint", "f" * 64]
        with contextlib.redirect_stdout(io.StringIO()) as output:
            result = publisher.main(arguments)
        self.assertEqual(0, result)
        self.assertIn("verify_git_source", json.loads(output.getvalue())["data"]["steps"])
        with contextlib.redirect_stdout(io.StringIO()) as output:
            result = publisher.main(arguments[:-2])
        self.assertEqual(1, result)
        self.assertEqual("invalid_git_source_expectation", json.loads(output.getvalue())["error_code"])
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()

    @unittest.skipUnless(os.name == "posix", "POSIX lock integration")
    def test_posix_lock_rejects_another_open_lock_handle(self):
        path = self.layout.data / "locks" / "getiptv.lock"
        with publisher.SiteLock(path):
            with self.assertRaisesRegex(publisher.PublishError, "site_locked"):
                with publisher.SiteLock(path):
                    self.fail("A second publisher acquired the same site lock")

    @unittest.skipUnless(os.name == "posix", "POSIX atomic symlink integration")
    def test_posix_bootstrap_and_owned_rollback_preserve_both_releases(self):
        original = self.instance._public_state(self.base, True)
        release = self.instance._stage(self.base, self.repo / "out", original, 7, "b" * 64)
        previous = self.instance._switch(self.base, original, release, 7)
        self.assertTrue(self.public.is_symlink())
        self.assertEqual("new article export", (self.public / "index.html").read_text())
        self.instance._restore(self.base, previous, release)
        self.assertFalse(self.public.is_symlink())
        self.assertEqual("previous publication", (self.public / "index.html").read_text())
        self.assertTrue(release.is_dir())


if __name__ == "__main__":
    unittest.main()
