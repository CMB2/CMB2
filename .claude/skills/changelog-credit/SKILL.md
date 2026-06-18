---
name: changelog-credit
description: Adds CHANGELOG.md entries for CMB2 pull requests, crediting contributors with Props links and linking the PR, following the repo's existing changelog conventions. Takes an explicit PR number/URL, or scans recent commits to find merged PRs not yet credited.
when_to_use: |
  Use when CMB2's CHANGELOG.md needs updating for one or more merged PRs —
  phrases like "PR #N was merged, update the changelog", "add a changelog entry
  for #N", "credit the contributor for this PR", "log this in CHANGELOG.md". Also
  in discovery mode with no PR given: "log any uncredited PRs", "catch up the
  changelog", "any recent commits missing from the changelog?". And when landing
  your own PR and the Unreleased section needs the new line.
argument-hint: "[pr-number-or-url]"
allowed-tools:
  - Bash(gh pr view*)
  - Bash(gh pr list*)
  - Bash(git log*)
  - Bash(grep*)
  - Read
  - Edit
---

# changelog-credit

Add a `CHANGELOG.md` entry for a CMB2 pull request, in the repo's house style.

## Modes

Two entry points:

- **Explicit PR** — the user gives a PR number or URL. Use it directly.
- **Discovery** — no PR given (e.g. "log any uncredited PRs", "catch up the
  changelog"). Scan recent commits for merged PRs **not yet in CHANGELOG.md** and
  process each.

### Discovery

1. **List recent PR-referencing commits.** Most CMB2 merges put `(#N)` in the
   subject (squash merges) or `Merge pull request #N`:
   ```bash
   git log --max-count=40 --pretty='%H%x09%s'
   ```
   Extract every `#N` from the subjects.

2. **Filter to uncredited.** For each PR number, check whether it already appears
   in `CHANGELOG.md` (the changelog links PRs as `pull/N)`):
   ```bash
   grep -q "pull/N)" CHANGELOG.md   # if found → already credited, skip
   ```
   Keep only PR numbers with no changelog hit.

3. **Confirm the list** with the user before writing — show the candidate PRs
   (number + subject) so they can drop any that shouldn't be logged (reverts,
   internal noise, already-released-but-unlinked work). Then process each through
   the per-PR steps below. Skip commits with no PR reference unless the user asks
   to log a bare commit.

## Per-PR steps

1. **Fetch PR metadata** — title, body, author login, merge state:
   ```bash
   gh pr view <N> --json title,body,number,mergedAt,author,url
   ```
   The `author.login` is the GitHub handle for the Props link. Read the title and
   body to write an accurate, user-facing summary (what changed and why it
   matters), not a verbatim copy of the title.

2. **Read `CHANGELOG.md`** to match current conventions before editing.

## House style (match exactly)

- The top section is `## Unreleased`. Add new entries there. Released sections
  are dated headers like `## [2.12.0 - 2026-05-30](...releases/tag/v2.12.0)` —
  never edit a released section.
- Entries are grouped under `### Enhancements` and `### Bug Fixes` (`###` H3).
  Pick the right group from the PR's nature. Create the subheading if Unreleased
  doesn't have it yet. If `## Unreleased` holds only a placeholder `*`, replace
  the placeholder rather than leaving an empty bullet.
- Each entry is a single `*` bullet, past tense, ending with a period:
  - **Props** the contributor: `Props [@login](https://github.com/login)`. Omit
    Props only when the author is a core maintainer landing their own routine
    work and the existing nearby entries also omit it (e.g. internal
    `[Development]` lines often have no Props).
  - Link the PR: `([#N](https://github.com/CMB2/CMB2/pull/N))`.
  - For tooling/build/CI work, prefix the summary with `[Development]`.
  - When the PR fixes a tracked issue, also link it: `Fixes [#M](...issues/M)`.

Reference template:
```
* <What changed, user-facing.> Props [@login](https://github.com/login) ([#N](https://github.com/CMB2/CMB2/pull/N)).
```

Example (from PR #1559):
```
* Sanitized the `field_id` input (with an `isset()` guard) and escaped the `rel`
  attribute in the oEmbed AJAX handler, addressing a reflected XSS vector flagged
  by WordPress Plugin Check. Props [@thisismyurl](https://github.com/thisismyurl)
  ([#1559](https://github.com/CMB2/CMB2/pull/1559)).
```

3. **Edit `CHANGELOG.md`** with the new bullet under the correct subheading in
   `## Unreleased`. In discovery mode, add a bullet per uncredited PR (newest
   last, to match chronological order within a group).

4. **Report** the added line(s) back to the user. Do not commit or push unless asked.
