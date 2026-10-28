"""Daily Git workflow checks using private temp files and mocked Git/SSH/PHP."""

import contextlib
import copy
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
from unittest import mock


SPEC = importlib.util.spec_from_file_location("daily_blog_workflow", Path(__file__).with_name("publish-daily-blogs.py"))
daily = importlib.util.module_from_spec(SPEC)
sys.modules[SPEC.name] = daily
SPEC.loader.exec_module(daily)

DATE = "2026-10-28"
NOW = daily.dt.datetime(2026, 10, 28, 5, 0, tzinfo=daily.dt.timezone.utc)
INITIAL = "a" * 40
COMMIT = "b" * 40
CHANGED = "c" * 40


class FakeLock:
    held = set()

    def __init__(self, path):
        self.path = str(path)

    def __enter__(self):
        if self.path in self.held:
            raise daily.WorkflowError("daily_workflow_locked")
        self.held.add(self.path)
        return self

    def __exit__(self, *_):
        self.held.remove(self.path)


def article(site, *, date=DATE, theme=None):
    theme = theme or {
        "main": "network", "trex": "television", "opplex": "account", "geo": "router",
        "5g": "playback", "iptvsubscription": "subscription", "getiptv": "device",
    }[site]
    sections = []
    for index in range(2):
        sections.append({"heading": f"{theme} preparation {index}",
            "paragraphs": [" ".join(f"{theme}step{number}" for number in range(index * 35, (index + 1) * 35))],
            "items": [f"Keep the {theme} result for comparison."], "links": []})
    row = {"title": f"Practical {theme} preparation", "excerpt": f"Use this {theme} preparation before making a change.",
           "seo_title": f"Practical {theme} preparation", "seo_description": f"Check the {theme} details before making a change.",
           "sections": sections}
    return {"version": 1, "site_key": site, "publishing_date": date,
            "topic": f"Practical {theme} checks for {site}", "primary_keyword": f"{theme} preparation",
            "slug": f"{site}-{theme}-preparation", "translations": {locale: copy.deepcopy(row) for locale in ("en", "es", "fr", "it", "nl", "pt", "ru", "ur", "ar", "hi")},
            "sources": [{"label": "Google Search guidance", "url": "https://developers.google.com/search/docs/fundamentals/creating-helpful-content"}]}


def encode(value):
    return (json.dumps(value, ensure_ascii=False, indent=2) + "\n").encode()


class FakeRunner:
    def __init__(self, layout):
        self.layout = layout
        self.calls = []
        self.trees = {INITIAL: {}}
        self.heads = {"local": INITIAL, "server": INITIAL}
        self.remote = INITIAL
        self.branches = {"local": "main", "server": "main"}
        self.origin = "https://github.com/ameeq96/opplexiptv.git"
        self.status = {"local": b"", "server": b""}
        self.index = {}
        self.extra_staged = set()
        self.failures = set()
        self.preflight_overrides = {}
        self.import_overrides = {}
        self.release_overrides = {}
        self.release_code = 0
        self.release_failures = set()
        self.pull_head = None
        self.hook = None
        self.records = {}
        self.commit_count = 0
        self.server_receives = 0

    @staticmethod
    def option(arguments, name):
        prefix = "--" + name + "="
        return next(value[len(prefix):] for value in arguments if value.startswith(prefix))

    def protocol(self, arguments, data, code=0):
        return subprocess.CompletedProcess(arguments, code, encode({"ok": True, "data": data}), b"private stderr must stay private")

    def git_calls(self, action, side=None):
        return [entry for entry in self.calls if entry[0][:1] == ["git"] and entry[0][3:4] == [action]
                and (side is None or entry[1].get("cwd") == (self.layout.repo if side == "local" else self.layout.server))]

    def actions(self, action):
        return [entry for entry in self.calls if "blogs:publishing" in entry[0]
                and entry[0][entry[0].index("blogs:publishing") + 1] == action]

    def local_status(self):
        result = self.status["local"]
        tracked = self.trees[self.heads["local"]]
        for path in sorted(self.layout.repo.glob(daily.SOURCE_PREFIX + "/**/*.json")):
            relative = path.relative_to(self.layout.repo).as_posix()
            raw = path.read_bytes()
            if relative in self.index and self.index[relative] != tracked.get(relative):
                result += b"A  " + relative.encode() + b"\0"
            elif relative not in tracked:
                result += b"?? " + relative.encode() + b"\0"
            elif tracked[relative] != raw:
                result += b" M " + relative.encode() + b"\0"
        return result

    def __call__(self, arguments, **kwargs):
        arguments = list(arguments)
        self.calls.append((arguments, kwargs))
        if "shell" in kwargs:
            raise AssertionError("Shell execution is outside the workflow contract")
        if arguments[:1] == ["git"]:
            result = self._git(arguments, kwargs)
        elif "blogs:publishing" in arguments:
            result = self._php(arguments, kwargs)
        elif arguments[:1] == ["ssh"] and daily.SERVER_DAILY in arguments:
            self.server_receives += 1
            manifest = json.loads(kwargs["input"])
            receiver = daily.GitReceiver(self.layout, execute=self, now=lambda: NOW, lock_factory=FakeLock, pause=lambda _: None)
            try:
                response = receiver.receive(manifest)
                code = 0 if all(item["notifications_complete"] for item in response["published"]) else 3
                result = self.protocol(arguments, response, code)
            except daily.WorkflowError as error:
                result = subprocess.CompletedProcess(arguments, 1, encode({"ok": False, "error_code": error.code,
                                                                           "progress": error.progress}), b"private server detail")
        elif daily.SERVER_PUBLISHER in arguments:
            site = self.option(arguments, "site")
            run = int(self.option(arguments, "run"))
            release = {"site_key": site, "run_id": run, "published": True, "notifications_complete": True}
            release.update(self.release_overrides)
            if site in self.release_failures:
                result = subprocess.CompletedProcess(arguments, 1, b'{"ok":false,"error_code":"publisher_failed"}', b"private publisher detail")
            else:
                result = self.protocol(arguments, release, self.release_code)
        else:
            raise AssertionError("Unexpected process: " + repr(arguments))
        if self.hook:
            self.hook(arguments, kwargs, result)
        return result

    def _git(self, arguments, kwargs):
        root = kwargs.get("cwd")
        side = "local" if root == self.layout.repo else "server"
        if root not in (self.layout.repo, self.layout.server):
            raise AssertionError("Git ran outside the temporary repositories")
        args = arguments[3:]
        command = args[0]
        if command in self.failures or (side + "-" + command) in self.failures:
            return subprocess.CompletedProcess(arguments, 1, b"private Git failure", b"private token")
        if args == ["rev-parse", "--show-toplevel"]:
            raw = str(root).encode()
        elif args == ["symbolic-ref", "--short", "HEAD"]:
            raw = self.branches[side].encode()
        elif args[:2] == ["remote", "get-url"]:
            raw = self.origin.encode()
        elif args[:1] == ["status"]:
            raw = self.local_status() if side == "local" else self.status["server"]
        elif args == ["rev-parse", "HEAD"]:
            raw = self.heads[side].encode()
        elif args[:1] == ["fetch"]:
            raw = b""
        elif args[:1] == ["rev-parse"] and args[1].startswith("refs/remotes/origin/"):
            raw = self.remote.encode()
        elif args[:1] == ["merge-base"]:
            raw = b""
        elif args[:2] == ["diff", "--cached"]:
            tree = self.trees[self.heads["local"]]
            changed = {path for path, value in self.index.items() if tree.get(path) != value} | self.extra_staged
            raw = b"".join(path.encode() + b"\0" for path in sorted(changed))
        elif args[:1] == ["diff"]:
            before, after = (self.trees[revision] for revision in args[-2:])
            raw = b"".join(path.encode() + b"\0" for path in sorted(set(before) | set(after)) if before.get(path) != after.get(path))
        elif args[:1] == ["add"]:
            self.index.update({path: (root / path).read_bytes() for path in args[2:]})
            raw = b""
        elif args[:1] == ["show"]:
            revision, path = args[1].split(":", 1)
            raw = self.index[path] if revision == "" else self.trees[revision][path]
        elif args[:1] == ["commit"]:
            self.commit_count += 1
            paths = args[args.index("--") + 1:]
            self.trees[COMMIT] = {**self.trees[self.heads["local"]], **{path: self.index[path] for path in paths}}
            self.heads["local"] = COMMIT
            self.index.clear()
            raw = b""
        elif args[:1] == ["push"]:
            self.remote = args[2].split(":", 1)[0]
            raw = b""
        elif args[:1] == ["pull"]:
            target = args[-1] if daily.SHA.fullmatch(args[-1]) else self.remote
            self.heads["server"] = self.pull_head or target
            for path, value in self.trees[target].items():
                file = root / path
                file.parent.mkdir(parents=True, exist_ok=True)
                file.write_bytes(value)
            raw = b""
        else:
            raise AssertionError("Unexpected Git command: " + repr(args))
        return subprocess.CompletedProcess(arguments, 0, raw, b"")

    def _php(self, arguments, kwargs):
        action = arguments[arguments.index("blogs:publishing") + 1]
        if action in self.failures:
            return subprocess.CompletedProcess(arguments, 1, b'{"ok":false,"error_code":"private-check-failed"}', b"private account detail")
        site, date = self.option(arguments, "site"), self.option(arguments, "date")
        raw = kwargs["input"]
        if action == "preflight":
            result = {"valid": True, "site_key": site, "publishing_date": date,
                      "source_sha256": hashlib.sha256(raw).hexdigest(), "fingerprint": hashlib.sha256(b"normalized:" + raw).hexdigest()}
            result.update(self.preflight_overrides)
            return self.protocol(arguments, result)
        if action == "import":
            source = {"commit": self.option(arguments, "source-commit"), "path": daily.source_path(site, date),
                      "sha256": self.option(arguments, "source-sha256"), "fingerprint": self.option(arguments, "source-fingerprint")}
            key = (site, date)
            self.records.setdefault(key, {"id": len(self.records) + 1, "git_source": source})
            result = copy.deepcopy(self.records[key])
            result.update(self.import_overrides)
            return self.protocol(arguments, {"run": result})
        raise AssertionError("Unexpected PHP action")


class DailyGitTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name).resolve()
        self.layout = daily.Layout(repo=self.root / "repo", private=self.root / "private", server=self.root / "server",
                                   server_lock=self.root / "runtime" / "daily.lock",
                                   baseline=self.root / "runtime" / "live-dirty-baseline.json")
        for directory in (self.layout.repo, self.layout.private / DATE, self.layout.server):
            directory.mkdir(parents=True)
        self.runner = FakeRunner(self.layout)
        self.availability = mock.Mock(return_value=True)
        self.workflow = daily.DailyWorkflow(self.layout, execute=self.runner, availability=self.availability,
                                           now=lambda: NOW, lock_factory=FakeLock)
        self.receiver = daily.GitReceiver(self.layout, execute=self.runner, now=lambda: NOW, lock_factory=FakeLock, pause=lambda _: None)
        self.baseline()
        self.addCleanup(FakeLock.held.clear)

    def baseline(self, entries=()):
        document = {"version": 1, "root": str(self.layout.server), "repository": "ameeq96/opplexiptv",
                    "base_commit": INITIAL, "entries": list(entries)}
        self.layout.baseline.parent.mkdir(parents=True, exist_ok=True)
        self.layout.baseline.write_bytes(encode(document))
        self.layout.baseline.chmod(0o600)
        return document

    def draft(self, site, value=None):
        value = article(site) if value is None else value
        raw = encode(value)
        (self.layout.private / DATE / (site + ".json")).write_bytes(raw)
        return raw

    def server_manifest(self, sites=("geo",)):
        entries, tree = [], {}
        for site in sites:
            raw = encode(article(site))
            path = daily.source_path(site, DATE)
            tree[path] = raw
            entries.append({"site_key": site, "path": path, "sha256": hashlib.sha256(raw).hexdigest(),
                            "fingerprint": hashlib.sha256(b"normalized:" + raw).hexdigest()})
        self.runner.trees[COMMIT] = tree
        self.runner.remote = COMMIT
        return {"version": 1, "date": DATE, "commit": COMMIT, "branch": "main", "articles": entries}

    def assert_no_mutating_git_or_publication(self):
        for action in ("add", "commit", "push", "pull"):
            self.assertEqual([], self.runner.git_calls(action))
        self.assertEqual([], self.runner.actions("import"))
        self.assertEqual(0, self.runner.server_receives)
        self.assertFalse(any(daily.SERVER_PUBLISHER in args for args, _ in self.runner.calls))

    def assert_no_import_or_publish(self):
        self.assertEqual([], self.runner.actions("import"))
        self.assertFalse(any(daily.SERVER_PUBLISHER in args for args, _ in self.runner.calls))

    def test_full_local_to_server_flow_versions_only_final_sources_before_live_pull_and_publish(self):
        raw = {site: self.draft(site) for site in ("geo", "getiptv")}
        result = self.workflow.publish(["geo", "getiptv"], DATE)
        self.assertEqual(COMMIT, result["commit"])
        self.assertEqual(2, len(result["published"]))
        self.assertEqual(1, self.runner.commit_count)
        paths = sorted(daily.source_path(site, DATE) for site in raw)
        self.assertEqual(paths, self.runner.git_calls("add")[0][0][5:])
        self.assertEqual({path for path in self.runner.trees[COMMIT]}, set(paths))
        first_add = next(i for i, (args, _) in enumerate(self.runner.calls) if args[3:4] == ["add"])
        local_preflights = [i for i, (args, _) in enumerate(self.runner.calls) if args[:1] == ["ssh"] and "preflight" in args]
        push = next(i for i, (args, _) in enumerate(self.runner.calls) if args[3:4] == ["push"])
        pull = next(i for i, (args, _) in enumerate(self.runner.calls) if args[3:4] == ["pull"])
        first_import = next(i for i, (args, _) in enumerate(self.runner.calls) if "import" in args)
        self.assertTrue(all(i < first_add for i in local_preflights))
        self.assertLess(first_add, push)
        self.assertLess(push, pull)
        self.assertLess(pull, first_import)
        self.assertEqual(["pull", "--ff-only", "origin", COMMIT], self.runner.git_calls("pull")[0][0][3:])
        for site in raw:
            self.assertEqual(raw[site], (self.layout.repo / daily.source_path(site, DATE)).read_bytes())
            self.assertEqual(raw[site], (self.layout.server / daily.source_path(site, DATE)).read_bytes())
        self.assertEqual(2, len(self.runner.records))

    def test_all_seven_finalized_articles_have_separate_sources_and_no_ids_in_git(self):
        for site in daily.SITES:
            self.draft(site)
        result = self.workflow.publish(list(daily.SITES), DATE)
        self.assertEqual(7, len(result["published"]))
        self.assertEqual(7, len(self.runner.trees[COMMIT]))
        for raw in self.runner.trees[COMMIT].values():
            source = json.loads(raw)
            self.assertEqual(10, len(source["translations"]))
            self.assertFalse(set(source) & {"id", "run_id", "blog_id", "git_source", "password", "status"})

    def test_dry_run_reads_no_private_files_and_calls_no_process_or_network(self):
        before = sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*"))
        result = self.workflow.plan(list(daily.SITES), DATE)
        self.assertTrue(result["dry_run"])
        self.assertFalse(result["mutations_performed"])
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()
        self.assertEqual(before, sorted(str(path.relative_to(self.root)) for path in self.root.rglob("*")))

    def test_default_private_drafts_and_baseline_have_fixed_approved_locations(self):
        self.assertEqual(Path(daily.__file__).resolve().parents[1] / "storage/app/private/blog-writing", daily.Layout().private)
        self.assertEqual(Path("/var/lib/opplex-blog-publishing/live-dirty-baseline.json"), daily.Layout().baseline)

    def test_git_auth_selection_is_noninteractive_and_ephemeral(self):
        with mock.patch.dict(daily.os.environ, {"GIT_CONFIG_COUNT": "1", "GIT_CONFIG_KEY_0": "existing.setting",
                                               "GIT_CONFIG_VALUE_0": "existing-value"}):
            original = dict(daily.os.environ)
            self.workflow.head(self.layout.repo)
            self.assertEqual(original, dict(daily.os.environ))
        arguments, kwargs = self.runner.calls[-1]
        environment = kwargs["env"]
        self.assertEqual("0", environment["GIT_TERMINAL_PROMPT"])
        self.assertEqual("never", environment["GCM_INTERACTIVE"])
        self.assertEqual("2", environment["GIT_CONFIG_COUNT"])
        self.assertEqual("credential.username", environment["GIT_CONFIG_KEY_1"])
        self.assertEqual("ameeq96", environment["GIT_CONFIG_VALUE_1"])
        self.assertEqual("existing.setting", environment["GIT_CONFIG_KEY_0"])
        self.assertNotIn("config", arguments[3:4])

    def test_invalid_or_noncurrent_days_and_site_injection_fail_without_actions(self):
        cases = [(["geo"], "2026-10-27"), (["geo"], "2026-02-30"), (["geo"], "20261028"),
                 (["../geo"], DATE), (["geo;id"], DATE), (["geo", "geo"], DATE), ([], DATE)]
        for sites, date in cases:
            with self.subTest(sites=sites, date=date), self.assertRaises(daily.WorkflowError):
                self.workflow.publish(sites, date)
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()

    def test_pakistan_day_is_used_when_utc_date_is_previous_day(self):
        before_midnight_utc = daily.dt.datetime(2026, 10, 27, 19, 1, tzinfo=daily.dt.timezone.utc)
        daily.validate_day(DATE, before_midnight_utc)
        with self.assertRaisesRegex(daily.WorkflowError, "publication_day_is_not_current"):
            daily.validate_day("2026-10-27", before_midnight_utc)

    def test_missing_or_oversized_private_draft_never_creates_git_content(self):
        for raw in (None, b"x" * (daily.MAX_ARTICLE + 1)):
            if raw is not None:
                (self.layout.private / DATE / "geo.json").write_bytes(raw)
            with self.subTest(oversized=raw is not None), self.assertRaisesRegex(daily.WorkflowError, "missing_or_oversized_private_draft"):
                self.workflow.publish(["geo"], DATE)
            self.assert_no_mutating_git_or_publication()
        self.assertFalse((self.layout.repo / daily.SOURCE_PREFIX).exists())

    def test_wrong_draft_site_or_day_fails_before_preflight(self):
        for change in ({"site_key": "getiptv"}, {"publishing_date": "2026-10-27"}):
            self.draft("geo", {**article("geo"), **change})
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, "draft_identity_mismatch"):
                self.workflow.publish(["geo"], DATE)
            self.assertEqual([], self.runner.actions("preflight"))
            self.assert_no_mutating_git_or_publication()

    def test_rejected_or_mismatched_live_preflight_never_publicly_commits_draft(self):
        self.draft("geo")
        for change in ({"valid": False}, {"site_key": "main"}, {"publishing_date": "2026-10-27"},
                       {"source_sha256": "f" * 64}, {"fingerprint": "invalid"}):
            self.runner.preflight_overrides = change
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, "preflight_identity_mismatch"):
                self.workflow.publish(["geo"], DATE)
            self.assert_no_mutating_git_or_publication()
            self.assertFalse((self.layout.repo / daily.SOURCE_PREFIX).exists())

    def test_failed_live_preflight_keeps_all_drafts_private(self):
        self.draft("geo")
        self.runner.failures.add("preflight")
        with self.assertRaisesRegex(daily.WorkflowError, "workflow_check_failed"):
            self.workflow.publish(["geo"], DATE)
        self.assert_no_mutating_git_or_publication()
        self.assertFalse((self.layout.repo / daily.SOURCE_PREFIX).exists())

    def test_unrelated_dirty_or_staged_work_is_preserved_and_blocks_before_preflight(self):
        self.draft("geo")
        for status in (b" M app/Example.php\0", b"A  app/NewCode.php\0", b"?? private-token.txt\0",
                       b" M " + daily.source_path("geo", DATE).encode() + b"\0"):
            self.runner.status["local"] = status
            with self.subTest(status=status), self.assertRaisesRegex(daily.WorkflowError, "unrelated_repository_changes"):
                self.workflow.publish(["geo"], DATE)
            self.assertEqual(status, self.runner.status["local"])
            self.assertEqual([], self.runner.actions("preflight"))
            self.assert_no_mutating_git_or_publication()

    def test_unrelated_unpushed_code_commit_is_not_pushed_with_daily_content(self):
        self.draft("geo")
        self.runner.trees[CHANGED] = {"app/Unrelated.php": b"user code"}
        self.runner.heads["local"] = CHANGED
        with self.assertRaisesRegex(daily.WorkflowError, "unrelated_unpushed_commits"):
            self.workflow.publish(["geo"], DATE)
        self.assert_no_mutating_git_or_publication()
        self.assertEqual(CHANGED, self.runner.heads["local"])

    def test_head_change_during_private_preflight_blocks_before_writing_sources(self):
        self.draft("geo")
        self.runner.trees[CHANGED] = {}
        def change(arguments, _kwargs, _result):
            if arguments[:1] == ["ssh"] and "preflight" in arguments:
                self.runner.heads["local"] = CHANGED
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "local_head_changed_during_preflight"):
            self.workflow.publish(["geo"], DATE)
        self.assert_no_mutating_git_or_publication()
        self.assertFalse((self.layout.repo / daily.SOURCE_PREFIX).exists())

    def test_new_unrelated_staged_path_is_never_committed(self):
        self.draft("geo")
        self.runner.extra_staged.add("app/Concurrent.php")
        with self.assertRaisesRegex(daily.WorkflowError, "staged_content_or_head_changed"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual([], self.runner.git_calls("commit"))
        self.assertEqual([], self.runner.git_calls("push"))
        self.assert_no_import_or_publish()

    def test_existing_different_final_source_is_never_overwritten(self):
        self.draft("geo")
        path = self.layout.repo / daily.source_path("geo", DATE)
        path.parent.mkdir(parents=True)
        path.write_bytes(b"existing user source")
        with self.assertRaisesRegex(daily.WorkflowError, "final_source_already_exists_with_different_content"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual(b"existing user source", path.read_bytes())
        self.assert_no_mutating_git_or_publication()

    def test_git_filter_changing_staged_bytes_is_rejected_before_commit(self):
        self.draft("geo")
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["add"]:
                self.runner.index[daily.source_path("geo", DATE)] = b"filtered content"
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "git_filter_changed_final_content"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual([], self.runner.git_calls("commit"))
        self.assertEqual([], self.runner.git_calls("push"))
        self.assert_no_import_or_publish()

    def test_commit_hook_adding_unrelated_code_is_not_pushed(self):
        self.draft("geo")
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["commit"]:
                self.runner.trees[COMMIT]["app/Unrelated.php"] = b"unexpected hook code"
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "unexpected_paths_in_content_commit"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual([], self.runner.git_calls("push"))
        self.assert_no_import_or_publish()

    def test_local_head_change_after_push_prevents_server_request(self):
        self.draft("geo")
        self.runner.trees[CHANGED] = {}
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["push"]:
                self.runner.heads["local"] = CHANGED
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "local_head_changed_after_push"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual(0, self.runner.server_receives)
        self.assert_no_import_or_publish()

    def test_push_failure_cannot_start_server_pull_import_or_publish(self):
        self.draft("geo")
        self.runner.failures.add("push")
        with self.assertRaisesRegex(daily.WorkflowError, "command_failed_or_outcome_unknown"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual(1, self.runner.commit_count)
        self.assertEqual(0, self.runner.server_receives)
        self.assertEqual([], self.runner.git_calls("pull"))
        self.assert_no_import_or_publish()
        self.assertTrue((self.layout.repo / daily.source_path("geo", DATE)).is_file())

    def test_retry_after_push_failure_reuses_existing_final_commit_and_single_article(self):
        self.draft("geo")
        self.runner.failures.add("push")
        with self.assertRaises(daily.WorkflowError):
            self.workflow.publish(["geo"], DATE)
        self.runner.failures.clear()
        first = self.workflow.publish(["geo"], DATE)
        second = self.workflow.publish(["geo"], DATE)
        self.assertEqual(COMMIT, first["commit"])
        self.assertEqual(COMMIT, second["commit"])
        self.assertEqual(1, self.runner.commit_count)
        self.assertEqual(1, len(self.runner.records))
        self.assertEqual(first["published"][0]["run_id"], second["published"][0]["run_id"])

    def test_held_site_stays_private_while_available_site_publishes(self):
        held = self.draft("opplex")
        self.draft("geo")
        self.availability.side_effect = lambda domain: domain != "opplexiptv.store"
        result = self.workflow.publish(["opplex", "geo"], DATE)
        self.assertEqual([{"site_key": "opplex", "reason": "public_unavailable"}], result["skipped"])
        self.assertEqual(1, len(result["published"]))
        self.assertNotIn(daily.source_path("opplex", DATE), self.runner.trees[COMMIT])
        self.assertEqual(held, (self.layout.private / DATE / "opplex.json").read_bytes())
        self.assertFalse((self.layout.repo / daily.source_path("opplex", DATE)).exists())

    def test_all_unavailable_sites_produce_no_git_mutations_or_publication(self):
        self.availability.return_value = False
        result = self.workflow.publish(["opplex"], DATE)
        self.assertEqual([], result["published"])
        self.assert_no_mutating_git_or_publication()
        self.assertEqual([], self.runner.actions("preflight"))

    def test_cross_article_topic_or_body_duplicates_are_rejected_before_git_stage(self):
        for duplicate in ("topic", "body"):
            first, second = article("geo"), article("getiptv")
            if duplicate == "topic":
                second["topic"] = first["topic"]
            else:
                second["translations"]["en"]["sections"] = copy.deepcopy(first["translations"]["en"]["sections"])
            self.draft("geo", first)
            self.draft("getiptv", second)
            with self.subTest(duplicate=duplicate), self.assertRaisesRegex(daily.WorkflowError, "daily_articles_are_too_similar"):
                self.workflow.publish(["geo", "getiptv"], DATE)
            self.assert_no_mutating_git_or_publication()
            self.assertFalse((self.layout.repo / daily.SOURCE_PREFIX).exists())

    def test_final_sources_normalize_only_line_endings_before_hashing_and_commit(self):
        raw = self.draft("geo")
        (self.layout.private / DATE / "geo.json").write_bytes(raw.replace(b"\n", b"\r\n"))
        self.workflow.publish(["geo"], DATE)
        self.assertEqual(raw, self.runner.trees[COMMIT][daily.source_path("geo", DATE)])

    def test_unexpected_branch_or_remote_blocks_before_private_preflight(self):
        self.draft("geo")
        for branch, remote in (("feature", self.runner.origin), ("main", "https://example.invalid/user/repo.git")):
            self.runner.branches["local"] = branch
            self.runner.origin = remote
            with self.subTest(branch=branch, remote=remote), self.assertRaises(daily.WorkflowError):
                self.workflow.publish(["geo"], DATE)
            self.assertEqual([], self.runner.actions("preflight"))
            self.assert_no_mutating_git_or_publication()

    def test_every_fetch_and_push_endpoint_must_be_the_exact_repository(self):
        self.draft("geo")
        self.runner.origin = "https://github.com/ameeq96/opplexiptv.git\nhttps://github.com/another/repo.git"
        with self.assertRaisesRegex(daily.WorkflowError, "unexpected_repository_remote"):
            self.workflow.publish(["geo"], DATE)
        self.assertEqual([], self.runner.actions("preflight"))
        self.assert_no_mutating_git_or_publication()

    def test_existing_authenticated_exact_github_remote_is_accepted_without_echoing_secret(self):
        self.draft("geo")
        self.runner.origin = "https://unused-private-token@github.com/ameeq96/opplexiptv.git"
        result = self.workflow.publish(["geo"], DATE)
        self.assertTrue(result["published"][0]["published"])
        self.assertNotIn("unused-private-token", json.dumps(result))

    def test_lock_blocks_concurrent_local_run_before_any_process(self):
        with FakeLock(self.layout.private / DATE / ".publish.lock"):
            with self.assertRaisesRegex(daily.WorkflowError, "daily_workflow_locked"):
                self.workflow.publish(["geo"], DATE)
        self.assertEqual([], self.runner.calls)

    def test_invalid_remote_manifest_date_path_sha_or_duplicate_site_fails_before_git(self):
        valid = self.server_manifest()
        cases = [{**valid, "date": "2026-10-27"}, {**valid, "commit": "HEAD;id"},
                 {**valid, "branch": "feature"}, {**valid, "version": 2},
                 {**valid, "articles": valid["articles"] * 2}]
        for key, value in (("path", "../../private.json"), ("site_key", "unknown"), ("sha256", "invalid"),
                           ("fingerprint", "invalid"), ("path", daily.source_path("main", DATE))):
            changed = copy.deepcopy(valid)
            changed["articles"][0][key] = value
            cases.append(changed)
        for manifest in cases:
            with self.subTest(manifest=manifest), self.assertRaises(daily.WorkflowError):
                self.receiver.receive(manifest)
        self.assertEqual([], self.runner.calls)

    def test_dirty_server_branch_remote_or_wrong_remote_commit_blocks_before_pull(self):
        for change, expected in (("dirty", "unrelated_repository_changes"), ("branch", "server_branch_mismatch"),
                                 ("remote", "unexpected_repository_remote"), ("commit", "remote_head_is_not_expected_commit")):
            self.runner.calls.clear()
            self.runner.status["server"] = b""
            self.runner.branches["server"] = "main"
            self.runner.origin = "https://github.com/ameeq96/opplexiptv.git"
            manifest = self.server_manifest()
            if change == "dirty":
                self.runner.status["server"] = b" M app/UserEdit.php\0"
            elif change == "branch":
                self.runner.branches["server"] = "master"
            elif change == "remote":
                self.runner.origin = "https://example.invalid/repo.git"
            else:
                self.runner.remote = CHANGED
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(manifest)
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()

    def test_fixed_dirty_files_deletions_and_dynamic_uploads_survive_daily_pull(self):
        modified = self.layout.server / "package-lock.json"
        modified.write_bytes(b"pre-existing user lockfile")
        entries = [{"path": "package-lock.json", "status": " M", "sha256": hashlib.sha256(modified.read_bytes()).hexdigest()},
                   {"path": "tests/OldTest.php", "status": " D", "missing": True}]
        self.baseline(entries)
        initial_baseline = self.layout.baseline.read_bytes()
        status = b" M package-lock.json\0 D tests/OldTest.php\0"
        uploads = {}
        for prefix in daily.RUNTIME_UNTRACKED:
            path = prefix + "customer-file.webp"
            file = self.layout.server / path
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_bytes(b"private customer upload")
            uploads[path] = file.read_bytes()
            status += b"?? " + path.encode() + b"\0"
        self.runner.status["server"] = status
        self.draft("geo")
        result = self.workflow.publish(["geo"], DATE)
        self.assertTrue(result["published"][0]["published"])
        self.assertEqual(b"pre-existing user lockfile", modified.read_bytes())
        self.assertFalse((self.layout.server / "tests/OldTest.php").exists())
        self.assertEqual(initial_baseline, self.layout.baseline.read_bytes())
        for path, raw in uploads.items():
            self.assertEqual(raw, (self.layout.server / path).read_bytes())
            self.assertNotIn(path, self.runner.trees[COMMIT])
        self.assertEqual({daily.source_path("geo", DATE)}, set(self.runner.trees[COMMIT]))

    def test_new_untracked_runtime_uploads_do_not_require_baseline_refresh(self):
        manifest = self.server_manifest()
        original = self.layout.baseline.read_bytes()
        for name in ("first.webp", "later.webp"):
            path = "public/uploads/blogs/" + name
            file = self.layout.server / path
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_bytes(b"new customer image")
            self.runner.status["server"] += b"?? " + path.encode() + b"\0"
            self.receiver.receive(manifest)
        self.assertEqual(original, self.layout.baseline.read_bytes())
        self.assertEqual(1, len(self.runner.records))

    def test_static_hash_status_or_missing_state_change_is_never_accepted(self):
        protected = self.layout.server / "package-lock.json"
        protected.write_bytes(b"original private user change")
        self.baseline([{"path": "package-lock.json", "status": " M", "sha256": hashlib.sha256(protected.read_bytes()).hexdigest()}])
        for change, expected in (("hash", "live_baseline_content_changed"), ("status", "unrelated_repository_changes"),
                                 ("clean", "live_baseline_status_changed")):
            self.runner.calls.clear()
            protected.write_bytes(b"changed" if change == "hash" else b"original private user change")
            self.runner.status["server"] = b"M  package-lock.json\0" if change == "status" else (b"" if change == "clean" else b" M package-lock.json\0")
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(self.server_manifest())
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()
        self.baseline([{"path": "tests/OldTest.php", "status": " D", "missing": True}])
        restored = self.layout.server / "tests/OldTest.php"
        restored.parent.mkdir()
        restored.write_bytes(b"later user restoration")
        self.runner.status["server"] = b" D tests/OldTest.php\0"
        with self.assertRaisesRegex(daily.WorkflowError, "live_baseline_content_changed"):
            self.receiver.receive(self.server_manifest())
        self.assertEqual(b"later user restoration", restored.read_bytes())

    def test_runtime_roots_never_exempt_tracked_staged_or_renamed_changes(self):
        file = self.layout.server / "public/uploads/blogs/customer.webp"
        file.parent.mkdir(parents=True)
        file.write_bytes(b"customer image")
        for status in (b" M public/uploads/blogs/customer.webp\0", b"A  public/uploads/blogs/customer.webp\0",
                       b"R  public/uploads/blogs/customer.webp\0old-name.webp\0", b" T public/uploads/blogs/customer.webp\0"):
            self.runner.calls.clear()
            self.runner.status["server"] = status
            with self.subTest(status=status), self.assertRaisesRegex(daily.WorkflowError, "unrelated_repository_changes"):
                self.receiver.receive(self.server_manifest())
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()

    def test_symlinked_baseline_or_upload_is_rejected_without_following_it(self):
        upload = self.layout.server / "public/uploads/blogs/customer.webp"
        upload.parent.mkdir(parents=True)
        upload.write_bytes(b"customer image")
        self.runner.status["server"] = b"?? public/uploads/blogs/customer.webp\0"
        original = Path.is_symlink
        for unsafe in (self.layout.baseline, upload):
            self.runner.calls.clear()
            with mock.patch.object(Path, "is_symlink", autospec=True, side_effect=lambda path: path == unsafe or original(path)):
                with self.subTest(path=unsafe), self.assertRaisesRegex(daily.WorkflowError, "unsafe_source_path"):
                    self.receiver.receive(self.server_manifest())
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()

    def test_missing_malformed_or_wrong_repository_baseline_fails_closed(self):
        valid = json.loads(self.layout.baseline.read_bytes())
        for change in (None, b"invalid JSON", {**valid, "root": str(self.layout.repo)},
                       {**valid, "repository": "different/repo"}, {**valid, "base_commit": "invalid"},
                       {**valid, "version": True}, {**valid, "unexpected": "field"}):
            self.runner.calls.clear()
            if change is None:
                self.layout.baseline.unlink()
            else:
                self.layout.baseline.write_bytes(change if isinstance(change, bytes) else encode(change))
            with self.subTest(change=change), self.assertRaises(daily.WorkflowError):
                self.receiver.receive(self.server_manifest())
            self.assertEqual([], self.runner.git_calls("fetch"))
            self.assert_no_import_or_publish()

    def test_baseline_cannot_authorize_runtime_sources_traversal_or_unsupported_git_status(self):
        invalid = [{"path": "../outside", "status": " D", "missing": True},
                   {"path": "public/sitemap.xml", "status": " D", "missing": True},
                   {"path": "public/uploads/blogs/user.webp", "status": " M", "sha256": "f" * 64},
                   {"path": daily.source_path("geo", DATE), "status": " M", "sha256": "f" * 64},
                   {"path": "package-lock.json", "status": "M ", "sha256": "f" * 64},
                   {"path": "tests/OldTest.php", "status": " D", "missing": False}]
        for entry in invalid:
            self.runner.calls.clear()
            self.baseline([entry])
            with self.subTest(entry=entry), self.assertRaisesRegex(daily.WorkflowError, "invalid_live_baseline"):
                self.receiver.receive(self.server_manifest())
            self.assertEqual([], self.runner.git_calls("fetch"))
            self.assert_no_import_or_publish()
        self.baseline([{"path": "tests/OldTest.php", "status": " D", "missing": True}] * 2)
        with self.assertRaisesRegex(daily.WorkflowError, "invalid_live_baseline"):
            self.receiver.receive(self.server_manifest())

    def test_posix_baseline_requires_root_owner_and_exact_0600_regular_file(self):
        original_stat = Path.stat
        original_os = daily.os
        metadata = self.layout.baseline.stat()
        cases = [(0, daily.stat.S_IFREG | 0o600, None), (1000, daily.stat.S_IFREG | 0o600, "unsafe_live_baseline_permissions"),
                 (0, daily.stat.S_IFREG | 0o644, "unsafe_live_baseline_permissions"),
                 (0, daily.stat.S_IFREG | 0o660, "unsafe_live_baseline_permissions"),
                 (0, daily.stat.S_IFDIR | 0o600, "invalid_live_baseline")]
        for owner, mode, expected in cases:
            def changed(path, *args, **kwargs):
                if path == self.layout.baseline:
                    return mock.Mock(st_uid=owner, st_mode=mode, st_size=metadata.st_size)
                return original_stat(path, *args, **kwargs)
            with mock.patch.object(daily, "os", mock.Mock(wraps=original_os, name="posix")) as platform:
                platform.name = "posix"
                with mock.patch.object(Path, "stat", autospec=True, side_effect=changed):
                    if expected:
                        with self.subTest(owner=owner, mode=mode), self.assertRaisesRegex(daily.WorkflowError, expected):
                            self.receiver._baseline_bytes()
                    else:
                        self.assertEqual(self.layout.baseline.read_bytes(), self.receiver._baseline_bytes())

    def test_baseline_base_commit_must_be_an_ancestor_of_current_live_head(self):
        self.runner.failures.add("server-merge-base")
        with self.assertRaisesRegex(daily.WorkflowError, "command_failed_or_outcome_unknown"):
            self.receiver.receive(self.server_manifest())
        self.assertEqual([], self.runner.git_calls("pull"))
        self.assert_no_import_or_publish()

    def test_changed_protected_file_after_pull_or_between_sites_stops_further_imports(self):
        protected = self.layout.server / "package-lock.json"
        protected.write_bytes(b"original user change")
        self.baseline([{"path": "package-lock.json", "status": " M", "sha256": hashlib.sha256(protected.read_bytes()).hexdigest()}])
        self.runner.status["server"] = b" M package-lock.json\0"
        for phase in ("pull", "first-publication"):
            self.runner.calls.clear()
            self.runner.records.clear()
            self.runner.heads["server"] = INITIAL
            protected.write_bytes(b"original user change")
            def change(arguments, _kwargs, _result):
                if (phase == "pull" and arguments[3:4] == ["pull"]) or (phase == "first-publication" and daily.SERVER_PUBLISHER in arguments and "--site=geo" in arguments):
                    protected.write_bytes(b"later user change")
            self.runner.hook = change
            with self.subTest(phase=phase), self.assertRaisesRegex(daily.WorkflowError, "live_baseline_content_changed") as caught:
                self.receiver.receive(self.server_manifest(("geo", "getiptv")))
            self.assertEqual(b"later user change", protected.read_bytes())
            self.assertEqual(0 if phase == "pull" else 1, len(self.runner.records))
            self.assertEqual([] if phase == "pull" else ["geo"], caught.exception.progress["published_sites"])

    def test_baseline_file_changed_after_pull_is_not_silently_reloaded(self):
        original = self.layout.baseline.read_bytes()
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["pull"]:
                self.layout.baseline.write_bytes(original + b" ")
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "live_baseline_changed"):
            self.receiver.receive(self.server_manifest())
        self.assertEqual(original + b" ", self.layout.baseline.read_bytes())
        self.assert_no_import_or_publish()

    def test_non_fast_forward_or_unrelated_committed_code_never_gets_pulled(self):
        for change, expected in (("ancestry", "command_failed_or_outcome_unknown"), ("path", "unexpected_paths_in_live_pull")):
            self.runner.calls.clear()
            self.runner.failures.clear()
            manifest = self.server_manifest()
            if change == "ancestry":
                self.runner.failures.add("server-merge-base")
            else:
                self.runner.trees[COMMIT]["app/Unrelated.php"] = b"new code"
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(manifest)
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()

    def test_live_pull_stays_pinned_to_verified_commit_when_origin_branch_moves(self):
        manifest = self.server_manifest()
        self.runner.trees[CHANGED] = {"app/Unreviewed.php": b"later remote commit"}
        def change(arguments, kwargs, _result):
            if arguments[3:4] == ["diff"] and kwargs.get("cwd") == self.layout.server:
                self.runner.remote = CHANGED
        self.runner.hook = change
        result = self.receiver.receive(manifest)
        self.assertEqual(COMMIT, result["commit"])
        self.assertEqual(COMMIT, self.runner.heads["server"])
        self.assertEqual(["pull", "--ff-only", "origin", COMMIT], self.runner.git_calls("pull")[0][0][3:])
        self.assertFalse((self.layout.server / "app/Unreviewed.php").exists())

    def test_unstaged_runtime_sitemap_is_preserved_without_blocking_source_pull(self):
        manifest = self.server_manifest()
        sitemap = self.layout.server / "public/sitemap.xml"
        sitemap.parent.mkdir()
        sitemap.write_bytes(b"existing generated sitemap")
        self.runner.status["server"] = b" M public/sitemap.xml\0"
        result = self.receiver.receive(manifest)
        self.assertTrue(result["published"][0]["published"])
        self.assertEqual(b"existing generated sitemap", sitemap.read_bytes())

    def test_staged_or_untracked_sitemap_is_not_treated_as_runtime_output(self):
        for status in (b"M  public/sitemap.xml\0", b"?? public/sitemap.xml\0"):
            self.runner.calls.clear()
            manifest = self.server_manifest()
            self.runner.status["server"] = status
            with self.subTest(status=status), self.assertRaisesRegex(daily.WorkflowError, "unrelated_repository_changes"):
                self.receiver.receive(manifest)
            self.assertEqual([], self.runner.git_calls("pull"))
            self.assert_no_import_or_publish()

    def test_live_pull_changing_existing_sitemap_blocks_import_without_restoring_over_user_change(self):
        manifest = self.server_manifest()
        sitemap = self.layout.server / "public/sitemap.xml"
        sitemap.parent.mkdir()
        sitemap.write_bytes(b"existing generated sitemap")
        self.runner.status["server"] = b" M public/sitemap.xml\0"
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["pull"]:
                sitemap.write_bytes(b"concurrently changed sitemap")
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "live_pull_changed_existing_sitemap"):
            self.receiver.receive(manifest)
        self.assertEqual(b"concurrently changed sitemap", sitemap.read_bytes())
        self.assert_no_import_or_publish()

    def test_main_publication_can_update_sitemap_before_next_site_is_published(self):
        manifest = self.server_manifest(("main", "geo"))
        sitemap = self.layout.server / "public/sitemap.xml"
        sitemap.parent.mkdir()
        sitemap.write_bytes(b"initial sitemap")
        def change(arguments, _kwargs, _result):
            if daily.SERVER_PUBLISHER in arguments and "--site=main" in arguments:
                sitemap.write_bytes(b"sitemap generated after main article")
                self.runner.status["server"] = b" M public/sitemap.xml\0"
        self.runner.hook = change
        result = self.receiver.receive(manifest)
        self.assertEqual(2, len(result["published"]))
        self.assertEqual(b"sitemap generated after main article", sitemap.read_bytes())

    def test_failed_pull_or_wrong_resulting_head_never_imports_or_publishes(self):
        for change, expected in (("failure", "command_failed_or_outcome_unknown"), ("head", "live_pull_revision_mismatch")):
            self.runner.calls.clear()
            self.runner.failures.clear()
            self.runner.heads["server"] = INITIAL
            manifest = self.server_manifest()
            if change == "failure":
                self.runner.failures.add("pull")
            else:
                self.runner.pull_head = CHANGED
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(manifest)
            self.assert_no_import_or_publish()

    def test_hash_mismatch_or_changed_live_working_content_blocks_before_import(self):
        for change, expected in (("hash", "committed_source_hash_mismatch"), ("working", "working_content_differs_from_commit")):
            self.runner.calls.clear()
            self.runner.heads["server"] = INITIAL
            self.runner.hook = None
            manifest = self.server_manifest()
            if change == "hash":
                manifest["articles"][0]["sha256"] = "f" * 64
            else:
                def changed(arguments, _kwargs, _result):
                    if arguments[3:4] == ["pull"]:
                        (self.layout.server / daily.source_path("geo", DATE)).write_bytes(b"concurrent changed file")
                self.runner.hook = changed
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(manifest)
            self.assert_no_import_or_publish()

    def test_live_preflight_changed_for_any_article_prevents_all_imports(self):
        manifest = self.server_manifest(("geo", "getiptv"))
        self.runner.preflight_overrides["fingerprint"] = "f" * 64
        with self.assertRaisesRegex(daily.WorkflowError, "live_preflight_changed"):
            self.receiver.receive(manifest)
        self.assert_no_import_or_publish()

    def test_live_head_change_during_preflight_stops_before_import(self):
        manifest = self.server_manifest()
        def change(arguments, _kwargs, _result):
            if arguments[:1] == ["/usr/sbin/runuser"] and "preflight" in arguments:
                self.runner.heads["server"] = CHANGED
        self.runner.hook = change
        with self.assertRaisesRegex(daily.WorkflowError, "live_source_changed_during_publication"):
            self.receiver.receive(manifest)
        self.assert_no_import_or_publish()

    def test_invalid_import_id_or_source_provenance_never_calls_publisher(self):
        for change, expected in (({"id": True}, "invalid_import_response"), ({"id": 0}, "invalid_import_response"),
                                 ({"id": "1"}, "invalid_import_response"), ({"git_source": {}}, "import_source_provenance_mismatch")):
            self.runner.calls.clear()
            manifest = self.server_manifest()
            self.runner.import_overrides = change
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, expected):
                self.receiver.receive(manifest)
            self.assertFalse(any(daily.SERVER_PUBLISHER in args for args, _ in self.runner.calls))

    def test_publisher_receives_same_fixed_source_provenance_as_import(self):
        manifest = self.server_manifest()
        self.receiver.receive(manifest)
        imported = self.runner.actions("import")[0][0]
        released = next(args for args, _ in self.runner.calls if daily.SERVER_PUBLISHER in args)
        for prefix in ("--source-commit=", "--source-sha256=", "--source-fingerprint="):
            self.assertEqual(next(arg for arg in imported if arg.startswith(prefix)), next(arg for arg in released if arg.startswith(prefix)))
        self.assertIn("--publish", released)

    def test_receiver_retry_reuses_one_database_article_for_same_git_source(self):
        manifest = self.server_manifest()
        first = self.receiver.receive(manifest)
        second = self.receiver.receive(manifest)
        self.assertEqual(1, len(self.runner.records))
        self.assertEqual(first["published"], second["published"])
        self.assertEqual(COMMIT, self.runner.heads["server"])

    def test_published_partial_notification_exit_three_is_returned_without_rollback(self):
        manifest = self.server_manifest()
        self.runner.release_code = 3
        self.runner.release_overrides = {"notifications_complete": False}
        result = self.receiver.receive(manifest)
        self.assertTrue(result["published"][0]["published"])
        self.assertFalse(result["published"][0]["notifications_complete"])
        self.assertEqual([], self.runner.actions("rollback"))

    def test_exit_three_without_publication_acknowledgement_is_not_reported_published(self):
        manifest = self.server_manifest()
        self.runner.release_code = 3
        self.runner.release_overrides = {"published": False, "notifications_complete": False}
        with self.assertRaises(daily.WorkflowError):
            self.receiver.receive(manifest)

    def test_wrong_published_site_or_run_is_rejected(self):
        for change in ({"site_key": "main"}, {"run_id": 999}):
            manifest = self.server_manifest()
            self.runner.release_overrides = change
            with self.subTest(change=change), self.assertRaisesRegex(daily.WorkflowError, "published_result_identity_mismatch"):
                self.receiver.receive(manifest)

    def test_protocol_error_never_echoes_raw_stderr_or_credentials(self):
        def bad_response(arguments, **_kwargs):
            return subprocess.CompletedProcess(arguments, 1, b"private source contents", b"private token")
        commands = daily.Commands(execute=bad_response)
        with self.assertRaises(daily.WorkflowError) as caught:
            commands.protocol(["fixed-command"])
        self.assertEqual("invalid_command_response", str(caught.exception))
        self.assertNotIn("private", str(caught.exception))

    def test_second_site_publication_failure_keeps_first_acknowledgement_and_commit_in_progress(self):
        manifest = self.server_manifest(("geo", "getiptv"))
        self.runner.release_failures.add("getiptv")
        with self.assertRaises(daily.WorkflowError) as caught:
            self.receiver.receive(manifest)
        self.assertEqual(COMMIT, caught.exception.progress["commit"])
        self.assertEqual("publish", caught.exception.progress["phase"])
        self.assertEqual(["geo"], caught.exception.progress["published_sites"])
        self.assertTrue(caught.exception.progress["git_changes_possible"])
        self.assertTrue(caught.exception.progress["live_changes_possible"])
        self.assertTrue(caught.exception.progress["outcome_unknown"])
        self.assertEqual([], self.runner.actions("rollback"))

    def test_local_failure_receipt_preserves_remote_partial_publication_progress(self):
        self.draft("geo")
        self.draft("getiptv")
        self.runner.release_failures.add("getiptv")
        with self.assertRaises(daily.WorkflowError) as caught:
            self.workflow.publish(["geo", "getiptv"], DATE)
        self.assertEqual("workflow_check_failed", caught.exception.code)
        self.assertEqual(COMMIT, caught.exception.progress["commit"])
        self.assertEqual("publish", caught.exception.progress["phase"])
        self.assertEqual(["geo"], caught.exception.progress["published_sites"])
        self.assertTrue(caught.exception.progress["git_changes_possible"])
        self.assertTrue(caught.exception.progress["live_changes_possible"])
        self.assertTrue(caught.exception.progress["outcome_unknown"])
        self.assertNotIn("private", json.dumps(caught.exception.progress))

    def test_ssh_timeout_after_push_reports_possible_live_changes_and_unknown_outcome(self):
        self.draft("geo")
        def timeout(arguments, **kwargs):
            if daily.SERVER_DAILY in arguments:
                self.runner.calls.append((list(arguments), kwargs))
                raise subprocess.TimeoutExpired(arguments, 10800, output=b"private response contents")
            return self.runner(arguments, **kwargs)
        self.workflow.execute = timeout
        with self.assertRaises(daily.WorkflowError) as caught:
            self.workflow.publish(["geo"], DATE)
        self.assertEqual(COMMIT, caught.exception.progress["commit"])
        self.assertEqual("live_pull_import_publish", caught.exception.progress["phase"])
        self.assertTrue(caught.exception.progress["git_changes_possible"])
        self.assertTrue(caught.exception.progress["live_changes_possible"])
        self.assertTrue(caught.exception.progress["outcome_unknown"])
        self.assertEqual([], caught.exception.progress["published_sites"])
        self.assertEqual(0, self.runner.server_receives)
        self.assertNotIn("private", str(caught.exception))

    def test_private_preflight_failure_reports_no_git_or_live_mutation(self):
        self.draft("geo")
        for failure in ("rejected", "timeout"):
            self.runner.failures = {"preflight"} if failure == "rejected" else set()
            def execute(arguments, **kwargs):
                if failure == "timeout" and "preflight" in arguments:
                    raise subprocess.TimeoutExpired(arguments, 180, output=b"private preflight details")
                return self.runner(arguments, **kwargs)
            self.workflow.execute = execute
            with self.subTest(failure=failure), self.assertRaises(daily.WorkflowError) as caught:
                self.workflow.publish(["geo"], DATE)
            progress = caught.exception.progress
            self.assertEqual("preflight", progress["phase"])
            self.assertFalse(progress["git_changes_possible"])
            self.assertFalse(progress["live_changes_possible"])
            self.assertFalse(progress["outcome_unknown"])
            self.assertEqual([], progress["published_sites"])
            self.assertNotIn("commit", progress)
            self.assert_no_mutating_git_or_publication()

    def test_postpush_local_head_change_reports_commit_without_claiming_live_work_started(self):
        self.draft("geo")
        self.runner.trees[CHANGED] = {}
        def change(arguments, _kwargs, _result):
            if arguments[3:4] == ["push"]:
                self.runner.heads["local"] = CHANGED
        self.runner.hook = change
        with self.assertRaises(daily.WorkflowError) as caught:
            self.workflow.publish(["geo"], DATE)
        progress = caught.exception.progress
        self.assertEqual(COMMIT, progress["commit"])
        self.assertEqual("git_push", progress["phase"])
        self.assertTrue(progress["git_changes_possible"])
        self.assertFalse(progress["live_changes_possible"])
        self.assertFalse(progress["outcome_unknown"])
        self.assertEqual([], progress["published_sites"])

    def test_push_timeout_keeps_commit_and_marks_remote_git_outcome_unknown(self):
        self.draft("geo")
        def timeout(arguments, **kwargs):
            if arguments[3:4] == ["push"]:
                self.runner.calls.append((list(arguments), kwargs))
                raise subprocess.TimeoutExpired(arguments, 120, output=b"private Git credential")
            return self.runner(arguments, **kwargs)
        self.workflow.execute = timeout
        with self.assertRaises(daily.WorkflowError) as caught:
            self.workflow.publish(["geo"], DATE)
        progress = caught.exception.progress
        self.assertEqual(COMMIT, progress["commit"])
        self.assertEqual("git_push", progress["phase"])
        self.assertTrue(progress["git_changes_possible"])
        self.assertFalse(progress["live_changes_possible"])
        self.assertTrue(progress["outcome_unknown"])
        self.assert_no_import_or_publish()

    def test_pull_timeout_reports_expected_commit_and_unknown_live_git_outcome(self):
        manifest = self.server_manifest()
        def timeout(arguments, **kwargs):
            if arguments[3:4] == ["pull"]:
                self.runner.calls.append((list(arguments), kwargs))
                raise subprocess.TimeoutExpired(arguments, 120, output=b"private pull details")
            return self.runner(arguments, **kwargs)
        self.receiver.execute = timeout
        with self.assertRaises(daily.WorkflowError) as caught:
            self.receiver.receive(manifest)
        progress = caught.exception.progress
        self.assertEqual(COMMIT, progress["commit"])
        self.assertEqual("live_pull", progress["phase"])
        self.assertTrue(progress["git_changes_possible"])
        self.assertFalse(progress["live_changes_possible"])
        self.assertTrue(progress["outcome_unknown"])
        self.assertEqual([], progress["published_sites"])
        self.assert_no_import_or_publish()

    def test_stage_or_commit_timeout_does_not_claim_git_changes_were_rolled_back(self):
        self.draft("geo")
        for command, phase in (("add", "git_stage"), ("commit", "git_commit")):
            self.runner.calls.clear()
            def timeout(arguments, **kwargs):
                if arguments[3:4] == [command]:
                    self.runner.calls.append((list(arguments), kwargs))
                    raise subprocess.TimeoutExpired(arguments, 120, output=b"private source")
                return self.runner(arguments, **kwargs)
            self.workflow.execute = timeout
            with self.subTest(command=command), self.assertRaises(daily.WorkflowError) as caught:
                self.workflow.publish(["geo"], DATE)
            progress = caught.exception.progress
            self.assertEqual(phase, progress["phase"])
            self.assertTrue(progress["git_changes_possible"])
            self.assertFalse(progress["live_changes_possible"])
            self.assertTrue(progress["outcome_unknown"])
            self.assertNotIn("commit", progress)
            self.assertTrue((self.layout.repo / daily.source_path("geo", DATE)).is_file())
            self.assertEqual([], self.runner.git_calls("push"))
            self.assert_no_import_or_publish()

    def test_failed_protocol_receipt_redacts_private_progress_and_invalid_sites(self):
        injected = {"phase": "unapproved_private_phase", "commit": "private secret", "password": "private password",
                    "git_changes_possible": True, "live_changes_possible": "true", "outcome_unknown": 1,
                    "published_sites": ["geo", "private-secret", {"token": "private"}, "geo", "main"]}
        def failed(arguments, **_kwargs):
            return subprocess.CompletedProcess(arguments, 1, encode({"ok": False, "error_code": "Bearer private-token",
                                                                     "progress": injected}), b"private stderr")
        commands = daily.Commands(execute=failed)
        with self.assertRaises(daily.WorkflowError) as caught:
            commands.protocol(["fixed-command"])
        self.assertEqual("workflow_check_failed", caught.exception.code)
        self.assertEqual({"git_changes_possible": True, "published_sites": ["geo", "main"]}, caught.exception.progress)
        self.assertNotIn("private", json.dumps(caught.exception.progress))

    def test_cli_failure_emits_only_sanitized_progress_and_safe_error_code(self):
        error = daily.WorkflowError("Bearer private error code", {"phase": "publish", "commit": COMMIT,
            "git_changes_possible": True, "live_changes_possible": True, "outcome_unknown": True,
            "published_sites": ["geo", "private-site", "geo"], "credentials": "private token"})
        with mock.patch.object(daily, "DailyWorkflow", return_value=self.workflow):
            with mock.patch.object(self.workflow, "publish", side_effect=error):
                with contextlib.redirect_stdout(io.StringIO()) as output:
                    code = daily.main(["--date", DATE, "--site", "geo", "--publish"])
        receipt = json.loads(output.getvalue())
        self.assertEqual(1, code)
        self.assertFalse(receipt["ok"])
        self.assertEqual("daily_workflow_failed", receipt["error_code"])
        self.assertEqual(COMMIT, receipt["progress"]["commit"])
        self.assertEqual(["geo"], receipt["progress"]["published_sites"])
        self.assertNotIn("private", output.getvalue())

    def test_new_attempt_does_not_inherit_previous_partial_publication_acknowledgements(self):
        manifest = self.server_manifest(("geo", "getiptv"))
        self.runner.release_failures.add("getiptv")
        with self.assertRaises(daily.WorkflowError):
            self.receiver.receive(manifest)
        self.runner.status["server"] = b" M app/NewUserEdit.php\0"
        with self.assertRaises(daily.WorkflowError) as caught:
            self.receiver.receive(manifest)
        self.assertEqual([], caught.exception.progress["published_sites"])
        self.assertFalse(caught.exception.progress["git_changes_possible"])
        self.assertFalse(caught.exception.progress["live_changes_possible"])
        self.assertEqual("live_preflight", caught.exception.progress["phase"])

    def test_next_builds_wait_for_a_fresh_api_budget_without_delaying_the_main_site(self):
        self.receiver.pause = mock.Mock()
        receipt = self.receiver.receive(self.server_manifest(("main", "geo", "trex", "getiptv")))
        self.assertEqual([mock.call(30), mock.call(30), mock.call(5)] * 2, self.receiver.pause.call_args_list)
        self.assertEqual(["main", "geo", "trex", "getiptv"], [item["site_key"] for item in receipt["published"]])
        self.assertEqual(4, len(self.runner.actions("import")))

    def test_source_is_rechecked_after_api_budget_wait_before_the_next_import(self):
        def concurrent_edit(_):
            self.runner.status["server"] = b" M app/NewConcurrentEdit.php\0"
        self.receiver.pause = mock.Mock(side_effect=concurrent_edit)
        with self.assertRaises(daily.WorkflowError) as caught:
            self.receiver.receive(self.server_manifest(("geo", "getiptv")))
        self.assertEqual("unrelated_repository_changes", caught.exception.code)
        self.assertEqual(["geo"], caught.exception.progress["published_sites"])
        self.assertEqual(1, len(self.runner.actions("import")))

    def test_cli_defaults_to_plan_without_git_network_or_private_file_access(self):
        with mock.patch.object(daily, "DailyWorkflow", return_value=self.workflow):
            with contextlib.redirect_stdout(io.StringIO()) as output:
                code = daily.main(["--date", DATE, "--site", "geo"])
        self.assertEqual(0, code)
        self.assertTrue(json.loads(output.getvalue())["data"]["dry_run"])
        self.assertEqual([], self.runner.calls)
        self.availability.assert_not_called()


if __name__ == "__main__":
    unittest.main()
