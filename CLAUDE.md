# MyAdmin Piwik Analytics Plugin

Piwik/Matomo analytics integration plugin for the MyAdmin control-panel framework. Type: `myadmin-plugin`.

## Commands

```bash
composer install                   # install deps
vendor/bin/phpunit                 # run tests
vendor/bin/phpunit tests/ -v       # run tests verbose
```

```bash
composer validate                  # validate composer.json
composer check-platform-reqs       # verify PHP extension requirements
```

```bash
vendor/bin/phpunit --filter testGetHooksValuesReferencePluginClass tests/
```

## Architecture

**Entry:** `src/Plugin.php` · Namespace: `Detain\MyAdminPiwik\` · Tests: `tests/PluginTest.php` · Namespace: `Detain\MyAdminPiwik\Tests\`

**CI/Automation:** `.github/` contains CI workflows for automated testing and deployment pipelines. IDE project settings in `.idea/` include inspection profiles under `.idea/inspectionProfiles/`, server deployment configuration in `.idea/deployment.xml`, and charset mappings in `.idea/encodings.xml`.

**Plugin class pattern** (`src/Plugin.php`):
- Static properties: `$name`, `$description`, `$help`, `$type`
- `getHooks()` → returns `[eventName => [Plugin::class, 'methodName']]`
- `getMenu(GenericEvent $event)` → reads `$event->getSubject()` for menu object
- `getRequirements(GenericEvent $event)` → calls `$loader->add_requirement($name, $path)`
- `getSettings(GenericEvent $event)` → reads `$event->getSubject()` for settings object

**Event system:** `symfony/event-dispatcher` ^5|^6|^7 · All handlers accept `GenericEvent` · Paths in `add_requirement()` are relative to vendor root.

## Coding Conventions

- Indentation: **tabs** (enforced by `.scrutinizer.yml`)
- Properties and params: **camelCase**
- Constants: **UPPERCASE**
- One class per file; no closing `?>`
- PHPUnit tests use `ReflectionClass` to verify static properties and method signatures — see `tests/PluginTest.php` for the established pattern
- Tests declare `strict_types=1`
- Static analysis config: `.scrutinizer.yml` · Code quality: `.codeclimate.yml` · CI: `.travis.yml`

## Dependencies

- `php`: `>=7.4` · `ext-soap` · `symfony/event-dispatcher`: `^5.0|^6.0|^7.0`
- Dev: `phpunit/phpunit`: `^9.6`
- Autoload plugin installer: `detain/myadmin-plugin-installer`

<!-- caliber:managed:pre-commit -->
## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
