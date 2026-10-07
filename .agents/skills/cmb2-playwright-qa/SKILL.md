---
name: cmb2-playwright-qa
description: Run evidence-focused browser QA for CMB2 changes and reports. Use when translating a pull-request procedure, review discussion, issue reproduction, or manual test into a temporary Playwright run against a configured WordPress site or the isolated wp-env suite, capturing claim-level evidence and deciding whether coverage should graduate into tests/playwright.
---

# CMB2 Playwright QA

Turn each requested behavior into an observable claim. Test only the agreed environment; leave source changes, comments, and publication to the caller.

Read [the environment reference](references/environment.md) before selecting a target. Copy [the report template](assets/qa-report-template.md) into the ignored run directory. For the isolated suite's default metabox, copy [the exploratory script](assets/exploratory-metabox.mjs) and adapt it rather than duplicating authentication code.

## Skip the run when it would prove nothing

A browser run is expensive and its output is only as good as the claim behind it. Route these elsewhere instead:

- **No rendered output** — sanitization, escaping, REST permission callbacks, option read/write paths. These belong in PHPUnit, where the assertion is exact.
- **A single "does it render?" look** — use the `claude-in-chrome` tools against an already-running site. Do not stand up a Playwright run for one screenshot.
- **No stated expected behavior** — ask what should and should not appear first. Without that, a screenshot documents the current state rather than proving anything about it.

## Choose the input mode

Use one of these modes:

- **Procedure mode:** Extract the requested steps and acceptance claims from a pull request, issue, review, or written test plan. State assumptions before running it.
- **Conversation mode:** Convert the agreed behavior into a short claim list. Ask only for an environment or action that cannot be safely inferred.

Do not post comments, change a pull request, publish a report, or alter the target site's content outside the agreed QA setup.

## Preflight

1. Inspect `git status`, the current branch, existing Playwright config, and the target's field registration. Preserve unrelated worktree changes; do not switch branches, discard changes, commit, or push as part of QA.
2. Configure the repository-root `.env.local` from `.env.local.example`. Shell and CI values override that file. Never print, commit, screenshot, or put credentials in a report.
3. Use the isolated `wp-env` suite by default. For another local or external WordPress site, set `WP_BASE_URL` and `SKIP_WP_SERVER=1`; do not set `SKIP_WP_CHECK=1` unless availability has already been deliberately checked.
4. Refresh the reusable login state with `npx playwright test tests/playwright/auth.setup.js --project=setup` before a direct exploratory script. It writes the ignored `tests/playwright/.auth/user.json`.
5. Place every exploratory script, screenshot, trace, and report below `tests/playwright/.qa/<run-slug>/`. That directory is ignored and explicitly excluded from normal Playwright collection. Use a name such as `run.mjs`, never `*.spec.js` or `*.test.js`.

## Establish CMB2 readiness

Wait for the behavior being tested, not merely `networkidle`. Navigate with `domcontentloaded`, then assert stable CMB2 and WordPress locators before interaction.

For the isolated suite's classic-editor fixture, use all of these as appropriate:

- `#cmb2_integration_tests_default_closed` for the metabox;
- `.hndle` and `button.handlediv` within it for visible title and toggle controls;
- `#cmb2_integration_tests_field_text` after opening the box.

For another target, inspect its box and field IDs first, then wait for the registered metabox container, its visible header/control, and the specific field or result that proves readiness. Scroll a metabox into view before clicking. Do not substitute a generic page-ready assertion for these checks.

## Capture evidence and report it honestly

For every claim, capture a before and after screenshot that includes the relevant WordPress and metabox context, not a tight crop with no orientation. Name each artifact for the claim it proves, not the page it sits on — `claim-01-notice-absent-before.png`, never `claim-01-before.png`. A bare ordinal makes a reviewer replay the run to learn what they are looking at. Capture a final full relevant page state on every failure before reporting the error, current URL, and unmet locator or assertion.

Keep a local Markdown report in the same run directory. Record environment, steps, claims, result, artifact paths, and limitations. A claim resolves to exactly one of **pass**, **fail**, or **skipped** — the last always carrying its reason (an infrastructure dependency, preserving the target site's state, coverage that already exists in PHPUnit). A claim that could not be run is not a failure, and burying it is worse than either. Add a Markdown file link only after verifying that its target exists from the report directory; omit unavailable artifacts instead of leaving broken links. Make clear that the report is local and not published.

## Graduate only durable coverage

Move or rewrite a successful exploratory run into `tests/playwright/<feature>.spec.js` only when it is all of the following:

- deterministic and behavior-asserting;
- fixture/setup/cleanup driven;
- free of local URLs, credentials, and machine-specific state;
- valuable for a recurring workflow or regression risk.

Reuse the existing fixtures, utilities, configuration, and authentication setup. Run the focused Chromium test first, then the broader relevant suite. Add no new coverage to the legacy Cypress suite.
