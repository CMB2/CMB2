# GitHub security advisories (GHSA)

How a report filed through GitHub private vulnerability reporting moves from
intake to publication, and how that maps onto this skill. Outward-facing steps
(accept, comment, close, request CVE, merge, publish) need JT's explicit go
for each step; with it, an agent may run them via `gh api`. Without it, agents
only read, draft text and prepare values.

`<GHSA>` below is the advisory id, e.g. `GHSA-xxxx-xxxx-xxxx`. UI path for
every step: repo → **Security** tab → **Advisories** → the advisory.

## Lifecycle and who sees what

| State | How it gets there | Visible to |
|---|---|---|
| `triage` | Reporter submits. GitHub adds them as a collaborator and a credited `reporter`. | Repo admins/security managers + collaborators (the reporter). |
| `draft` | Maintainer clicks **Accept and open as draft**. Still private. | Same. |
| `closed` | Maintainer clicks **Close security advisory** (not a security risk). Comment why first. | Same. |
| `published` | Maintainer clicks **Publish advisory**. | Anyone: the advisory data and accepted credits. Collaborators: also the comment history. |

- Comments on the advisory are visible only to the reporter and collaborators.
  ([manage reports][manage])
- Collaborators, the reporter included, have **write** on the advisory: they
  see the draft, push to the private fork and open PRs in it. Only admins
  merge, edit credits, close or publish. ([permissions][perms],
  [report privately][report])
- That write access lets reporters send their own patch as a fork PR. When a
  fix is already written or underway, the acknowledgement comment says so
  plainly ("the fix is already committed and will ship in X; no patch needed")
  so the reporter doesn't duplicate it. "We'll share the commit when it lands"
  reads as "not started" and invites one.
- The advisory URL does not change on publish. Published advisories can still
  be edited. ([about][about], [edit][edit])

SECURITY.md promises acknowledgement within 7 days and an assessment
(accepted / needs info / not a vulnerability) within 14 days of submission.

## The temporary private fork (optional)

Created from the advisory (**Start a temporary private fork**), named
`<repo>-ghsa-xxxx-xxxx-xxxx`, reachable by advisory collaborators.
([fork][fork])

- **No CI.** Integrations and status checks never run there, and branch
  protection is not enforced on the merge. Run the suite locally in wp-env;
  `phpunit.yml` runs on develop after the merge.
- **Merging is all-or-nothing, from the advisory page** (**Merge pull
  request(s)**), into the real repo. Individual fork PRs cannot be merged, and
  only one PR may target a given branch.
- **The merge is public the moment it lands**: the commits are now on the
  public branch, while the advisory stays draft. So fork commit messages follow
  the disclosure rule, and the merge belongs in the same sitting as
  `/cmb2-release`, exactly like a direct push to develop.
- **Publishing deletes the fork.** ([publish][publish])

Use the fork when the reporter should review or verify the patch before it is
public. Otherwise fix on develop exactly as SKILL.md §2–3 describes; the
advisory does not need a fork.

## Affected products and ecosystem

GitHub-reviewed advisories (the ones that drive Dependabot alerts) exist only
for supported ecosystems, and WordPress/wp.org is not one. CMB2 is published on
Packagist as `cmb2/cmb2`, and Composer is supported.
([advisory database][gad], [about][about])

- The reporter form may record a `wordpress` ecosystem. The REST API and the
  global Advisory Database accept only `rubygems, npm, pip, maven, nuget,
  composer, go, rust, erlang, actions, pub, other, swift`, so a
  `wordpress`-only advisory gets no reviewed global entry and no Dependabot
  alerts. ([REST][rest])
- Before publishing, set the affected product to **Composer / `cmb2/cmb2`**,
  with affected versions in Advisory Database syntax (`<= <last-affected>`, or `< <fixed>`; never `< n` with `n`
  marked patched) and **Patched versions** set
  to the release. Without a patched version, Dependabot alerts with no safe
  upgrade. ([writing advisories][write], [about][about])
- Edit products in the UI (**Edit advisory**), or `PATCH` the full
  `vulnerabilities` array: a `PATCH` that sends `vulnerabilities` replaces the
  whole array and rejects `wordpress`. A `PATCH` that omits it (e.g. only
  `state`) leaves the existing entries untouched.
- The advisory page's "Required advisory information has been provided —
  you're ready to publish" only means the required fields are non-empty. It
  shows with a `wordpress`-only product and no patched version, so it is not a
  check that the advisory is correct.
- wp.org users are reached through the CVE, which the WordPress
  vulnerability databases (Wordfence, Patchstack, WPScan) ingest from the CVE
  list. Confirm the entry appears there after publishing.

## CVE: which CNA

GitHub is a CNA. Requesting a CVE does not publish anything; GitHub usually
reviews within 72 hours, reserves the id, and pushes it to the CVE list when
the advisory is published. It needs affected versions filled in, and admin
access. GitHub cannot assign one if another CNA already covers the issue.
([about][about], [publish][publish])

- **Ask the reporter first** (advisory comment): did they also file with
  Wordfence, Patchstack or WPScan? Researchers often do, and two CNAs means two
  CVEs for one bug.
- Another CNA already assigned one → put it in the advisory's **CVE
  identifier** field ("I have an existing CVE identifier"); don't request.
- Otherwise → **Request CVE** soon after accepting, so the id is reserved
  before the release.
- A duplicate report on another channel: one beads issue, one fix, one CVE.
  Tell each channel about the other and which CVE is canonical.

## Disclosure timing

The draft advisory, its comments and the fork are private, so they may use
security framing. Publishing is the plain disclosure, which the disclosure
rule allows only once the fix is on by default in a released version: publish
**after** the wp.org release is live (SVN tag + `Stable tag` updated and the
download serving the new version), not after the GitHub release alone. The
release's changelog credit and the advisory go out the same day.

## Description

The advisory's description starts as the reporter's submission, PoC and
reproduction steps included, and is published as-is unless edited. Before
publishing, replace it (UI **Edit advisory**, or `PATCH` with `description`)
with a maintainer write-up at the disclosure rule's *plainly, not how* level
(SKILL.md): Impact (class, required role/capability, precondition, impact),
Patches (version + fix commit URL), Workarounds, Credits. Save the
reporter's original to the beads issue first; the edit overwrites it. The
reporter may still publish their own write-up after
the advisory goes public; that is theirs to decide.

## Credits

The reporter is auto-credited and accepts in GitHub. Add others (e.g.
`remediation_developer`) in **Edit advisory**; credits show publicly only
once accepted and the advisory is published. Changelog credit:
`Props <researcher> (via GitHub security advisory)`. ([create][create])

## Commands

UI-only: merge fork PRs, and comment on them (the API refuses with "forbidden on
workspace repositories"; closing via `gh pr close` works). Everything else has
an API form. Comments use an
endpoint the REST docs don't list. `GET`, `POST` and `PATCH comments/<id>`
are verified. Pass bodies with `jq -j`/`-F body=@file`
and no trailing newline, or an edit changes the stored body by one byte.

```bash
# Read (safe for agents)
gh api 'repos/CMB2/CMB2/security-advisories?state=triage'
gh api repos/CMB2/CMB2/security-advisories/<GHSA>
gh api repos/CMB2/CMB2/security-advisories/<GHSA>/comments   # undocumented; reporter replies land here

# Outward-facing: run only on JT's explicit go for that step
# Accept (= UI "Accept and open as draft"; sets submission.accepted=true):
gh api -X PATCH repos/CMB2/CMB2/security-advisories/<GHSA> -f state=draft
#   Close:   UI "Close security advisory"  (or PATCH state=closed)
gh api -X POST repos/CMB2/CMB2/security-advisories/<GHSA>/comments -F body=@reply.md
gh api -X PATCH repos/CMB2/CMB2/security-advisories/<GHSA>/comments/<comment-id> -F body=@reply.md   # edit
gh api -X POST repos/CMB2/CMB2/security-advisories/<GHSA>/forks   # private fork (async, up to 5 min)
gh api -X POST repos/CMB2/CMB2/security-advisories/<GHSA>/cve     # request CVE
gh api -X PATCH repos/CMB2/CMB2/security-advisories/<GHSA> -f state=published   # or UI "Publish advisory"
```

[about]: https://docs.github.com/en/code-security/concepts/vulnerability-reporting-and-management/repository-security-advisories
[manage]: https://docs.github.com/en/code-security/how-tos/report-and-fix-vulnerabilities/fix-reported-vulnerabilities/manage-vulnerability-reports
[report]: https://docs.github.com/en/code-security/how-tos/report-and-fix-vulnerabilities/report-privately
[perms]: https://docs.github.com/en/code-security/reference/permissions/repository-security-advisory
[fork]: https://docs.github.com/en/code-security/tutorials/fix-reported-vulnerabilities/collaborate-in-a-fork
[publish]: https://docs.github.com/en/code-security/how-tos/report-and-fix-vulnerabilities/fix-reported-vulnerabilities/publish-repository-advisory
[create]: https://docs.github.com/en/code-security/how-tos/report-and-fix-vulnerabilities/fix-reported-vulnerabilities/create-repository-advisory
[edit]: https://docs.github.com/en/code-security/how-tos/report-and-fix-vulnerabilities/fix-reported-vulnerabilities/edit-repository-advisories
[write]: https://docs.github.com/en/code-security/tutorials/fix-reported-vulnerabilities/write-security-advisories
[gad]: https://docs.github.com/en/code-security/concepts/vulnerability-reporting-and-management/github-advisory-database
[rest]: https://docs.github.com/en/rest/security-advisories/repository-advisories
