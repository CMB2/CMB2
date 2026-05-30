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
# Run all PHPUnit tests
vendor/bin/phpunit

# Run tests via npm
npm run phptests

# Run tests via Composer
composer test

# Install WordPress test environment
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
```bash
# Run PHP CodeSniffer (auto-uses .phpcs.xml.dist; scope to source with a path)
vendor/bin/phpcs includes/ init.php

# Fix auto-fixable PHP CodeSniffer issues
vendor/bin/phpcbf includes/ init.php

# JavaScript linting
npm run build:js:lint
```

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

## Testing Notes

### PHPUnit Tests
- WordPress test environment installs to `tests/tmp/wordpress/`
- Test database is separate from development database
- Some tests may require specific WordPress versions
- Ajax and embed tests are excluded by default

### Playwright E2E Tests
- Tests are located in `tests/playwright/`
- Cross-browser testing: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- Visual regression testing with screenshot comparison
- Authentication state is persisted across tests for better performance
- Docker-free CI implementation eliminates previous reliability issues
- Tests run in parallel for faster execution
- Detailed reporting with traces, screenshots, and videos on failure

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
