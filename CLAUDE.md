# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CMB2 (Custom Metaboxes 2) is a WordPress metabox, custom fields, and forms library. It allows developers to create custom fields for posts, pages, users, terms, comments, and options pages with a clean, extensible API.

## Architecture

- **Main entry point**: `init.php` - handles plugin initialization and bootstrapping
- **Core classes**: Located in `includes/` directory:
  - `CMB2.php` - Main CMB2 class
  - `CMB2_Field.php` - Individual field handling
  - `CMB2_Types.php` - Field type rendering
  - `CMB2_Boxes.php` - Metabox management
  - `CMB2_Options.php` - Options page functionality
- **Field types**: Located in `includes/types/` - individual classes for each field type
- **REST API**: Located in `includes/rest-api/` - REST API integration
- **Frontend assets**: CSS in `css/`, JavaScript in `js/`

## Common Commands

### Testing

#### PHPUnit Tests (Unit & Integration)
```bash
# Recommended (local): run PHPUnit inside wp-env. Self-starts the env, so a
# cold checkout works with only Docker installed. Single PHP (8.3) vs WP latest.
npm run phptests

# Bare phpunit on your host — these do NOT set up WordPress; they require a
# manually-built WP test env + a reachable MySQL with WP_TESTS_DIR set (see below).
vendor/bin/phpunit
composer test

# Build that manual WP test env. The 4th arg is the DB host: prefer a
# socket-capable `localhost` — a Docker/OrbStack MySQL frequently squats
# 127.0.0.1:3306 and silently hijacks the connection (see Testing Strategy).
bash tests/bin/install-wp-tests.sh <db_name> <db_user> <db_pass> [db_host] [wp_version]
```

#### End-to-End Tests (Playwright)
```bash
# Run all E2E tests (recommended)
npm run test:e2e

# Run tests with UI (interactive debugging)
npm run test:e2e:ui

# Run tests in headed mode (visible browser)
npm run test:e2e:headed

# Debug tests step by step
npm run test:e2e:debug

# View test report
npm run test:e2e:report

# Run visual regression tests
npm run test:visual
```

### Development
```bash
# Install dependencies
npm install
composer install

# Watch for file changes and rebuild assets
npm run watch

# Full build (CSS + JS)
npm run build
```

### Code Quality

The PHPCS toolchain lives in an isolated `tools/phpcs/composer.json` (kept out of
the root `require-dev` so the PHP 7.4–8.3 test matrix isn't forced to resolve
sniffers that need PHP 8.2+). A root `composer install` auto-installs it on
PHP 8.2+ (skipped on older PHP and in CI). Requires PHP 8.2+ to run.

```bash
# Run PHP CodeSniffer (uses .phpcs.xml.dist + the includes/ init.php scope).
# Installs the tools/phpcs toolchain on first use if missing.
composer phpcs

# Fix auto-fixable PHP CodeSniffer issues
composer phpcbf

# Force a (re)install of the toolchain, e.g. after editing tools/phpcs/composer.json
composer phpcs:install

# JavaScript linting
npm run build:js:lint
```

#### How the PHPCS tooling is wired (and why)

The sniffer toolchain is **deliberately isolated** from the root project. Understand this before touching `composer.json`, `composer.lock`, or the CI workflows:

- **`tools/phpcs/composer.json`** owns all sniffer packages (`squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`, `phpcompatibility/phpcompatibility-wp`, `sirbrillig/phpcs-variable-analysis`, `fig-r/psr2r-sniffer` → which brings Slevomat + Spryker, plus the `dealerdirect` installer). The shared ruleset is the repo-root `.phpcs.xml.dist`.
- **Root `require-dev` stays `phpunit` + polyfills only.** The PHPUnit CI matrix spans PHP 7.4–8.3, and some sniffers require PHP 8.2+ (`fig-r/psr2r-sniffer` → `spryker/code-sniffer`). If sniffers are in root `require-dev`, `composer install` on the sub-8.2 legs (7.4/8.0/8.1) cannot resolve and the matrix fails. **Do not add phpcs/sniffer packages to the root `composer.json`.**
- **No committed `composer.lock`** (it is gitignored). CI runs `composer install` per PHP version so deps resolve per-version; a committed lock pins one PHP's resolution (e.g. `doctrine/instantiator 2.0.0`, php ^8.1) and breaks `composer install` on the others. This was removed in `8b74309` — **do not re-commit a lockfile.**
- **Three self-installing entry points** (all install the toolchain on demand, PHP 8.2+):
  - `composer install` → `post-install-cmd`/`post-update-cmd` → `tools/phpcs/maybe-install.php`. Guarded: runs only on **PHP ≥ 8.2 and not in CI** (best-effort; never fails the root install).
  - `composer phpcs` / `composer phpcbf` → `tools/phpcs/run.php`, which installs if missing then runs, and errors clearly on PHP < 8.2.
  - `composer phpcs:install` → explicit `composer install --working-dir=tools/phpcs`.
- **CI wiring:** `.github/workflows/phpcs.yml` runs on **PHP 8.3 only**, installs via `composer install --working-dir=tools/phpcs`, and calls `tools/phpcs/vendor/bin/phpcs` **directly** (never the composer scripts). The PHPUnit matrix never invokes phpcs. There is no standalone WPCS gate beyond this job.

### Build
```bash
# Full CSS pipeline (compile SCSS, generate RTL, add banners, minify)
npm run build:css

# Individual CSS steps
npm run build:css:compile   # SCSS → CSS
npm run build:css:rtl       # Generate RTL variants
npm run build:css:banner    # Add license headers
npm run build:css:minify    # Generate .min.css files

# Full JS pipeline (lint + minify)
npm run build:js

# Individual JS steps
npm run build:js:lint       # JSHint
npm run build:js:minify     # Concatenate + minify → cmb2.min.js
```

### Translation (release-time only)
```bash
# Generate .pot file
npm run build:i18n:pot

# Compile .po → .mo (requires system gettext)
npm run build:i18n:mo

# Both
npm run build:i18n
```

## Development Environment

The project uses WordPress's standard testing framework and includes:
- PHPUnit configuration in `phpunit.xml.dist`
- WordPress test environment setup via `tests/bin/install-wp-tests.sh`
- npm scripts for asset building (Sass, RTL, minification)
- Playwright for end-to-end testing (migrated from Cypress for better performance and reliability)
- Visual regression testing with screenshot comparison

## Code Standards

- Follows WordPress PHP Coding Standards with some modifications (see `.phpcs.xml.dist`)
- Uses PHP 7.4+ features
- Field types follow consistent naming patterns: `CMB2_Type_*`
- All classes are prefixed with `CMB2_`

## Key Files for Understanding

- `example-functions.php` - Shows how to use CMB2 API
- `includes/CMB2.php` - Main class with core functionality
- `includes/helper-functions.php` - Global helper functions
- `includes/CMB2_Field.php` - Field object and rendering logic

## Testing Strategy

CMB2 has four test layers, each with its own CI workflow. The most important
thing to internalize: **local and CI run PHPUnit on different runners**, so
"passes locally" is necessary but not sufficient — CI exercises the full PHP
version floor that local wp-env does not.

| Layer | Tool | Scope | CI workflow | PHP |
|---|---|---|---|---|
| Unit + integration | PHPUnit | PHP logic, field/sanitize/REST behavior against a real WP | `phpunit.yml` | 7.4–8.3 matrix |
| End-to-end + visual | Playwright | Admin-UI flows + screenshot regression in real browsers | `test.yml` | container (8.3) |
| Coding standards | PHPCS (WPCS) | Style/sniffs — see [Code Quality](#code-quality) | `phpcs.yml` | 8.3 only |
| PHP compatibility | PHPCompatibility | 7.4→latest syntax/API compat | `phpcompat.yml` | 8.x action |

### PHPUnit — local vs CI (they differ)

- **Local, recommended — `npm run phptests`:** runs phpunit inside the isolated
  tests environment's `cli` container (`wp-env run --config .wp-env-tests.json
  cli …`). Self-starts that env (`npm run env:tests:start && …`), so a cold
  checkout needs only Docker. Runs a **single** PHP (the container's, 8.3) vs WP
  latest. Fast, but does **not** cover the version matrix.
- **Local, bare — `vendor/bin/phpunit` / `composer test`:** phpunit on your host
  with no WordPress set up. Needs a WP test suite (`install-wp-tests.sh`) + a
  reachable MySQL and `WP_TESTS_DIR`. Only use if you've built that yourself.
- **CI — `phpunit.yml` (the real matrix):** PHP `7.4, 8.0, 8.1, 8.2, 8.3` ×
  WordPress `latest, 6.4, 6.3` (excluding 8.3+6.3, since WP 6.3 predates PHP 8.3),
  via `install-wp-tests.sh` against a MySQL 5.7 service container. **This is what
  guards the PHP 7.4 floor — local wp-env never sees it.** Coverage uploads to
  Codecov on the 8.1 + WP-latest leg. Ajax/ms-files/external-http groups are
  excluded by default.

### wp-env (local PHPUnit + all Playwright)

- **Two separate config files (no `env.tests`):** wp-env v11 deprecated the
  single-file `env`/`testsPort`/`testsEnvironment` model, so the dev and tests
  environments now live in distinct files, each with `"testsEnvironment": false`
  (which silences the deprecation warning and stops wp-env auto-starting a second
  env). Each `--config` file gets its own isolated Docker stack / work dir /
  database. The WordPress PHPUnit scaffold (`WP_TESTS_DIR`) now ships in **every**
  container, so phpunit runs in the tests env's plain `cli` container — there is
  no longer a `tests-cli` container.
  - `.wp-env.json` — **dev** playground, port **2622** (`npm run env:start`).
  - `.wp-env-tests.json` — **isolated tests** env, port **2623**, with the
    `cmb2-test-fields.php` fixture mapping. Used by Playwright + phptests
    (`npm run env:tests:start`).
- **Ports are pinned** to dev **2622** / tests **2623** — chosen so `wp-env start`
  never races for the common 8888/8889 defaults, which collide with other local
  Docker/OrbStack services. Playwright's `WP_BASE_URL` and CI default to
  `http://localhost:2623`. Don't reintroduce 8888/8889.
- **`install-wp-tests.sh` host gotcha:** the DB-host arg matters. On a machine
  running Docker/OrbStack, a container often squats TCP `127.0.0.1:3306`, so
  passing `127.0.0.1` connects to the *wrong* MySQL and fails with
  `Access denied for 'root'@'<docker-gateway-ip>'`. Use a socket-capable
  `localhost`, or a MySQL on a non-3306 port. CI avoids this with a dedicated
  MySQL service bound to 3306 in an isolated runner.

### Playwright E2E

- Tests in `tests/playwright/`; run via `npm run test:e2e` (plus `:ui`,
  `:headed`, `:debug`, `:report`, and `test:visual` for screenshot regression).
- Runs against the isolated tests env (`.wp-env-tests.json`, site on **2623**).
  Auth state is persisted across tests for speed; tests run in parallel. Locally
  Playwright starts that env itself (`npm run env:tests:start`); in CI
  (`test.yml`) the env is started first (`wp-env start --config
  .wp-env-tests.json`) and Playwright runs chromium
  with `SKIP_WP_SERVER=1`. Traces, screenshots, and videos are captured on failure.

## Releases

The release process is documented in the `cmb2-release` skill. Run `/cmb2-release` (or `/cmb2-release 2.X.Y` to skip version detection) to walk through it. Source: `.claude/skills/cmb2-release/SKILL.md`. Includes the wp.org SVN deploy, which has no automation.

## Meta Best Practices

- Never commit work-planning documents

## Compatibility Considerations

- Always keep in mind that the CMB2 library needs to work on all versions of php from 7.4 to the latest

<!-- BEGIN BEADS INTEGRATION v:1 profile:minimal hash:6cd5cc61 -->
## Beads Issue Tracker

This project uses **bd (beads)** for issue tracking. Run `bd prime` to see full workflow context and commands.

### Quick Reference

```bash
bd ready              # Find available work
bd show <id>          # View issue details
bd update <id> --claim  # Claim work
bd close <id>         # Complete work
```

### Rules

- Use `bd` for ALL task tracking — do NOT use TodoWrite, TaskCreate, or markdown TODO lists
- Run `bd prime` for detailed command reference and session close protocol
- Use `bd remember` for persistent knowledge — do NOT use MEMORY.md files

**Architecture in one line:** issues live in a local Dolt DB; sync uses `refs/dolt/data` on your git remote; `.beads/issues.jsonl` is a passive export. See https://github.com/gastownhall/beads/blob/main/docs/SYNC_CONCEPTS.md for details and anti-patterns.

## Agent Context Profiles

The managed Beads block is task-tracking guidance, not permission to override repository, user, or orchestrator instructions.

- **Conservative (default)**: Use `bd` for task tracking. Do not run git commits, git pushes, or Dolt remote sync unless explicitly asked. At handoff, report changed files, validation, and suggested next commands.
- **Minimal**: Keep tool instruction files as pointers to `bd prime`; use the same conservative git policy unless active instructions say otherwise.
- **Team-maintainer**: Only when the repository explicitly opts in, agents may close beads, run quality gates, commit, and push as part of session close. A current "do not commit" or "do not push" instruction still wins.

## Session Completion

This protocol applies when ending a Beads implementation workflow. It is subordinate to explicit user, repository, and orchestrator instructions.

1. **File issues for remaining work** - Create beads for anything that needs follow-up
2. **Run quality gates** (if code changed) - Tests, linters, builds
3. **Update issue status** - Close finished work, update in-progress items
4. **Handle git/sync by active profile**:
   ```bash
   # Conservative/minimal/default: report status and proposed commands; wait for approval.
   git status

   # Team-maintainer opt-in only, unless current instructions forbid it:
   git pull --rebase
   git push
   git status
   ```
5. **Hand off** - Summarize changes, validation, issue status, and any blocked sync/commit/push step

**Critical rules:**
- Explicit user or orchestrator instructions override this Beads block.
- Do not commit or push without clear authority from the active profile or the current user request.
- If a required sync or push is blocked, stop and report the exact command and error.
<!-- END BEADS INTEGRATION -->
