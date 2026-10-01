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
  CVE or GHSA id, "vulnerability", "security", "exploit", "XSS", "disclosure",
  "Wordfence" or other reporter-platform names.
- *Public artifacts* are anything outside beads and JT's email: commit
  messages, branch names, PR titles and bodies, CHANGELOG.md, readme.txt,
  code comments, CONVENTIONS.md, cmb2.io docs, GitHub release notes. When
  unsure, treat it as public.
- *Plainly* means the release that turns the fix on may say "Security" in the
  readme `== Upgrade Notice ==` and credit "Props <researcher> (via
  <channel>)" in the changelog, e.g. "(via Wordfence)" or "(via GitHub
  security advisory)". The CVE link can be added once the CVE is published.
- A draft GitHub security advisory, its comments and its temporary private
  fork are private. Publishing the advisory is the plain disclosure, so it
  waits for the same release.
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
- **GitHub private vulnerability reporting** (SECURITY.md routes there):
  the advisory arrives in `triage`, with the reporter as a collaborator.
  SECURITY.md promises acknowledgement in 7 days and an assessment in 14. Its
  states, private fork, ecosystem, CVE and commands are in
  [references/github-advisories.md](references/github-advisories.md).
- **Email** to hello@cmb2.io.

Ask the reporter whether they filed the same issue on another channel
(Wordfence, Patchstack, WPScan, GitHub). One issue gets one beads issue, one
fix and one CVE.

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
   When a GitHub advisory's private fork carries the fix, merging it from the
   advisory is this push: same commit rules, no CI until it lands.
2. Add the CHANGELOG `## Unreleased` entry, following the disclosure rule for
   the release it will ship in.
3. Push to develop: `phpunit.yml` runs the full PHP 7.4–8.3 × WP matrix on
   push. Watch it green.
4. Cut the patch release in the same sitting (JT runs `/cmb2-release`), so
   the window between "fix is public" and "fix is installable" is minutes. A
   release that turns a security fix on gets an `== Upgrade Notice ==`.

## 4. Close out

### Wordfence

- **"Submit Patch Details"** (JT submits the form): give JT the
  exact, copy-paste-ready value or selection for **every field shown in the
  form**, even fields he should leave empty. Use this order and the field
  labels from the form:
  - **Changeset URL:** the full
    `https://github.com/CMB2/CMB2/commit/<fix-sha>` URL, with the actual fix
    commit SHA filled in. Do not use the release-prep or post-release commit.
  - **or Upload Patch File:** leave empty when providing the changeset URL;
    otherwise give the exact ZIP to upload.
  - **This patch has already been released to the public:** checked only after
    the fixed version is publicly downloadable; otherwise unchecked.
  - **Version Number:** the bare released version, e.g. `2.13.2`. The form
    already supplies the `v` prefix, so do not type it again.
  - **Notes:** provide the complete text to paste, naming the affected
    renderer/save path and how the fix closes the reported issue. Do not leave
    JT to compose the note from a summary.
  Verify the SHA, release version and public download before giving these
  values. Present them together in the handoff, not across earlier updates.

### GitHub security advisory

JT publishes; give him the values, verified, in one handoff. Details in
[references/github-advisories.md](references/github-advisories.md).

- **CVE identifier:** the other CNA's CVE if one exists; otherwise confirm the
  CVE GitHub reserved (requested right after accepting).
- **Affected products:** Composer / `cmb2/cmb2`, affected versions in Advisory
  Database syntax, **Patched versions** = the released version.
- **Description:** complete text to paste (impact, affected path, fixed
  version, fix commit URL, upgrade advice).
- **Credits:** the reporter's credit accepted; add others if any.
- **Publish** only after the wp.org release serves the fixed version.

### Both

- Confirm how the researcher wants to be credited if the report only gives a
  first name.
- After the CVE is published, optionally add the CVE link to the changelog
  entry.
- Record what was sent and when in the beads issue.
