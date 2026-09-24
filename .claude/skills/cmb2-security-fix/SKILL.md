---
name: cmb2-security-fix
description: Handles a reported CMB2 vulnerability end to end — intake, fixing the class of bug, disclosure-safe wording, landing and releasing the fix, and closing out with the reporter. Coordinates CONVENTIONS.md, /cmb2-release and beads rather than repeating them.
when_to_use: |
  Use when a vulnerability report arrives for CMB2 (Wordfence vendor portal,
  GitHub private vulnerability reporting, WPScan/Patchstack email to
  hello@cmb2.io), when fixing or releasing a security issue, or when deciding
  what a commit message, changelog entry, PR or doc may say about one —
  phrases like "Wordfence report", "CVE", "security fix", "vulnerability",
  "XSS/CSRF/auth bypass in CMB2", "submit patch details", "can the changelog
  say security?".
---

# CMB2 security fix

## The disclosure rule

**Stay neutral while a default install of the latest release is still
exposed. Disclose plainly once the fix is on by default in a released
version.**

- *Neutral* means public artifacts describe the change by what it does
  ("Sanitized and escaped `file_list` values"), never as a vulnerability: no
  CVE id, "vulnerability", "security", "exploit", "XSS", "disclosure",
  "Wordfence" or other reporter-platform names.
- *Public artifacts* are anything outside beads and JT's email: commit
  messages, branch names, PR titles and bodies, CHANGELOG.md, readme.txt,
  code comments, CONVENTIONS.md, cmb2.io docs, GitHub release notes. When
  unsure, treat it as public.
- *Plainly* means the release that turns the fix on may say "Security" in the
  readme `== Upgrade Notice ==` and credit "Props <researcher> (via
  Wordfence)" in the changelog. The CVE link can be added once the reporter
  publishes.
- A fix that ships **off by default** (a filter, a staged flip) leaves default
  installs exposed, so it stays neutral until the release that flips the
  default. Which fixes are currently in that state is recorded in beads, never
  here: this file is public too.

The security framing, correspondence and timelines live only in beads. Beads'
Dolt data syncs to the **private** `CMB2/beads-db` repo, and
`.beads/interactions.jsonl` is gitignored. Check `bd dolt remote list` before
relying on that: CMB2/CMB2 is public, and bd's default is to sync there.

## 1. Intake

- **Wordfence vendor portal:** report pages need JT logged in. In the browser,
  `read_page` returns the report; `get_page_text` returns only the cookie
  banner.
- **GitHub private vulnerability reporting** (SECURITY.md routes there), or
  email to hello@cmb2.io.

Open a beads issue holding the private framing: vector, every write path
(front-end `cmb2_get_metabox_form()` saves on a valid nonce with no capability
check; user-box saves rely on the WordPress profile flow for authorization),
affected versions, CVSS, researcher name, and any deadline.

## 2. Fix the class, not the instance

- Read `CONVENTIONS.md` first; it holds the escaping and sanitization seams
  (e.g. C10: types in `escaping_exception()` escape in their own renderer,
  `concat_attrs()` never escapes).
- Weigh the reporter's suggested fix against back-compat. Wordfence proposed
  escaping inside `concat_attrs()` for the 2.13.1 `file_list` fix, which would have
  double-escaped every attribute that callers already escape.
- Close both ends: sanitize on save *and* escape on render, so payloads
  already stored are neutralized too.
- Check sibling field types and write paths with the same shape.
- Decide what happens to existing stored data that the new validation rejects,
  and put that in the changelog entry.
- Failing-first tests, then the fix. Add or update a CONVENTIONS.md entry
  (the repo's capture-on-catch rule).

## 3. Land and release

1. Squash the fix into **one commit** on develop. Its SHA is the changeset
   URL the reporter asks for. Commit message follows the disclosure rule.
2. Add the CHANGELOG `## Unreleased` entry, following the disclosure rule for
   the release it will ship in.
3. Push to develop: `phpunit.yml` runs the full PHP 7.4–8.3 × WP matrix on
   push. Watch it green.
4. Cut the patch release in the same sitting (JT runs `/cmb2-release`), so
   the window between "fix is public" and "fix is installable" is minutes. A
   release that turns a security fix on gets an `== Upgrade Notice ==`.

## 4. Close out

- **Wordfence "Submit Patch Details"** (JT submits the form): changeset URL
  `https://github.com/CMB2/CMB2/commit/<fix-sha>`, the version, "already
  released to the public" checked, and a one-line note on where the fix lives.
- Confirm how the researcher wants to be credited if the report only gives a
  first name.
- After the reporter publishes, optionally add the CVE link to the changelog
  entry.
- Record what was sent and when in the beads issue.
