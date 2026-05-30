---
name: cmb2-release
description: Cuts a release of the CMB2 WordPress plugin. Walks through the full ordered checklist from develop branch hygiene to the manual wp.org SVN deploy.
when_to_use: |
  Use when releasing, publishing, shipping, tagging, or bumping the version of CMB2 —
  phrases like "release CMB2", "cut a CMB2 release", "ship CMB2 2.x.x", "bump CMB2
  version", "publish CMB2 to wordpress.org", "tag CMB2", "deploy CMB2 to wp.org".
  Also when in a CMB2 checkout and asked "how do I release this?" — there's no
  RELEASE.md in the repo, so this skill is the canonical documentation.
disable-model-invocation: true
argument-hint: "[version]"
allowed-tools:
  - Bash(git status*)
  - Bash(git diff*)
  - Bash(git log*)
  - Bash(git describe*)
  - Bash(git branch*)
  - Bash(git add*)
  - Bash(git commit*)
  - Bash(git checkout*)
  - Bash(git pull*)
  - Bash(git fetch*)
  - Bash(git merge*)
  - Bash(git tag*)
  - Bash(git show*)
  - Bash(git rev-parse*)
  - Bash(gh run*)
  - Bash(gh release*)
  - Bash(npm install*)
  - Bash(npm run*)
  - Bash(composer install*)
  - Bash(vendor/bin/*)
  - Bash(grep*)
  - Bash(sed*)
  - Bash(awk*)
  - Bash(tr*)
  - Bash(echo*)
  - Bash(curl*)
  - Bash(bash*)
  - Read
  - Edit
  - Write
  - TodoWrite
---

# Cutting a CMB2 Release

CMB2 has no `RELEASE.md` and no automated wp.org deploy. This skill is the canonical process — reconstructed from how `v2.10.0` and `v2.11.0` were cut. Follow it in order; skipping a step has historically caused broken `{{next}}` placeholders to ship, mismatched bootstrap class names, or wp.org being out of sync with GitHub.

If a version was passed as an argument (**`$ARGUMENTS`**), use it as `NEW` in the "Lock in the values" step. Otherwise, propose a version from the commit log shown in the snapshot below.

## Repo snapshot

!`bash ${CLAUDE_SKILL_DIR}/scripts/snapshot.sh`

## How to use this skill

Track progress with TodoWrite (one todo per top-level step). The release has hard-to-reverse actions (`git push`, `svn ci`) — at every **🛑 STOP-AND-VALIDATE** checkpoint, show the user the diff/output and wait for explicit confirmation before continuing. Releases are infrequent enough that "ask twice" is cheaper than "untag and republish."

## Why these steps look the way they do

A few things are non-obvious and easy to get wrong:

- **`CMB2_Bootstrap_XXXX` class name** in `init.php` encodes the version (e.g. `CMB2_Bootstrap_2110` for 2.11.0, derived by stripping dots). It's how multiple bundled copies of CMB2 in different plugins/themes negotiate which one wins. If you forget to rename it, two copies of the new version will collide instead of deduping.
- **`_Develop` suffix on the bootstrap class** between releases. Develop branch always carries `CMB2_Bootstrap_<NEXT>_Develop` — a distinct class name from any released `CMB2_Bootstrap_<N>` so the two can coexist when both are bundled in different plugins/themes. The release flips `_Develop` off; the post-release bump (Step 8) flips it back on for the *next* planned version. (See the PRIORITY note below for the load-order trade-off.)
- **`const PRIORITY` decrements by 1 each release** (9958 → 9957 → 9956…). Newer versions need to load *before* older ones (lower priority = earlier hook) so the bootstrap can pick the highest version. The decrement happens in the release commit (Step 1). The post-release develop bump (Step 8) does *not* touch PRIORITY — develop ends up at the same priority as the just-released version, and a plugin embedding develop CMB2 alongside another plugin embedding the released CMB2 will tie-break by plugin load order. This is a known trade-off matching 5 of the last 7 CMB2 releases; if it ever becomes a real problem, decrement PRIORITY in Step 8 too.
- **`{{next}}` placeholder** is sprinkled in `@since` docblocks during development so contributors don't have to guess the next version. The release is the moment those get resolved. If you ship with `{{next}}` still in the source, IDEs and docs tools will show literal `{{next}}` to users.
- **No wp.org automation exists.** GitHub tags do not deploy. You must SVN by hand or the wp.org listing will silently stay on the old version while GitHub shows the new one — confusing for users.

## Pre-flight

```bash
git checkout develop && git pull --rebase
```

The snapshot above already shows working tree state, CI status, and commits since the last tag — review it instead of re-running those commands.

### Determine the new version (semver)

If `$ARGUMENTS` was provided, use that. Otherwise classify the commits-since-last-tag from the snapshot:

- **MAJOR**: Breaking API removals, raised PHP minimum, removed field types/parameters
- **MINOR**: New field types, new field parameters, new filters/actions, new public methods
- **PATCH**: Bug fixes, internal refactors, doc tweaks

Present the recommendation and let the user confirm or override. CMB2 trends conservative — when in doubt between MINOR and PATCH, prefer MINOR if any new public API surface was added.

### Pre-release checks (all must pass)

```bash
npm install
composer install
vendor/bin/phpunit                # or: npm run phptests
vendor/bin/phpcs                  # PHPCS clean
npm run build:js:lint             # JSHint clean
npm run build                     # CSS+JS build succeeds
```

If any fail, stop. Don't release on red.

### Lock in the values

These are real shell variables. Set them once and reference them as `$NEW`, `$OLD`, etc. throughout the rest of the skill.

`OLD` is what the version strings in the files *currently* say — read it straight from `init.php` (same source as `OLDBOOT`/`OLDPRIO`), not from git tags. The last git tag can disagree with the files: tags carry a `v` prefix the files don't, and on a diverged develop `git describe` returns the latest tag *reachable from HEAD*, which may lag the actual latest release. Matching the files is what makes the Step 1 seds land.

```bash
export NEW=2.X.Y                                                        # the new version (bare, no "v")
export OLD=$(grep -oE "const VERSION = '[^']+'" init.php | grep -oE "[0-9][0-9.]*")   # current version in the files
export NEWBOOT=$(echo "$NEW" | tr -d .)                                 # 2.11.0 → 2110
export OLDBOOT=$(grep -oE "CMB2_Bootstrap_[0-9]+(_Develop)?" init.php | head -1 | sed 's/CMB2_Bootstrap_//')
export OLDPRIO=$(grep -oE "PRIORITY = [0-9]+" init.php | grep -oE "[0-9]+")
export NEWPRIO=$((OLDPRIO - 1))

echo "NEW=$NEW  OLD=$OLD  NEWBOOT=$NEWBOOT  OLDBOOT=$OLDBOOT  OLDPRIO=$OLDPRIO  NEWPRIO=$NEWPRIO"
```

Eyeball the echo before continuing. Empty values mean a grep didn't match — investigate before moving on. `$OLD` should match what `init.php`, `package.json`, and the CSS banners currently contain.

## Step 1 — Version bump commit on `develop`

This is a **single commit** titled like `Update changelog/readme/versions` (matches prior history). Touch every file below; do not split.

### `init.php`

```bash
sed -i.bak \
  -e "s/Version:      $OLD/Version:      $NEW/" \
  -e "s/CMB2_Bootstrap_$OLDBOOT/CMB2_Bootstrap_$NEWBOOT/g" \
  -e "s/const VERSION = '$OLD';/const VERSION = '$NEW';/" \
  -e "s/const PRIORITY = $OLDPRIO;/const PRIORITY = $NEWPRIO;/" \
  init.php
rm init.php.bak

grep -nE "Version:|Bootstrap_|VERSION|PRIORITY" init.php
```

The bootstrap class name should appear ~6 times — all should now read `CMB2_Bootstrap_$NEWBOOT`.

### `package.json` + `package-lock.json`

```bash
sed -i.bak "s/\"version\": \"$OLD\"/\"version\": \"$NEW\"/" package.json package-lock.json
rm package.json.bak package-lock.json.bak
grep "\"version\":" package.json package-lock.json
```

The lockfile has version in two places (top-level and under `packages.""`) — the single sed hits both.

### `readme.txt`

- `Tested up to: <CURRENT_WP>` — use the WordPress version shown in the snapshot at the top of this skill.
- `Stable tag: $NEW`
- Add a new changelog section under `== Changelog ==` matching the format below.

### `CHANGELOG.md`

CMB2 has an `## Unreleased` placeholder at the top. In theory contributors append bullets to it during development; in practice it's often empty (just `*`). **Check it first** — if it's empty or sparse, reconstruct the entries from the commit log since the last release tag (`v$OLD` — the bare file version always corresponds to the last released tag):

```bash
git log "v$OLD"..HEAD --oneline
```

Read the actual PRs/commits, group them into Enhancements / Bug Fixes, and write user-facing bullets — don't just paste commit subjects. Then **rename the `## Unreleased` header** to the new version section and add a fresh empty `## Unreleased` above it for the next cycle.

Format (verify against existing entries before writing):

```markdown
## [$NEW - YYYY-MM-DD](https://github.com/CMB2/CMB2/releases/tag/v$NEW)

### Enhancements

* User-facing or developer-facing improvement. Props [@author](https://github.com/author) ([#1234](https://github.com/CMB2/CMB2/pull/1234)).
* [Development] Internal tooling / CI / build changes get this prefix.

### Bug Fixes

* Fixed thing. Fixes [#5678](https://github.com/CMB2/CMB2/issues/5678).
```

So the top of the file ends up looking like:

```markdown
## Unreleased
*

## [$NEW - YYYY-MM-DD]...
```

Tone is **contributor-facing** — link PRs/issues, credit authors with `Props`, prefix internal tooling work with `[Development]`. Look at the 2.11.0 entry for the canonical example.

### `readme.txt` (changelog section)

Mirrors `CHANGELOG.md` but one heading level deeper. Append the new entry above the previous one — the wp.org changelog accumulates the full history:

```
### $NEW

#### Enhancements
* (same bullets as CHANGELOG.md)

#### Bug Fixes
* (same bullets as CHANGELOG.md)
```

### `README.md`

Search for `$OLD` and bump references (badges, "current version" mentions).

### `css/*.css`

Regenerate banners — do not hand-edit:

```bash
npm install   # if needed
npm run build:css:banner
grep -l "Version: $OLD" css/*.css   # should print nothing
```

### Commit

🛑 **STOP-AND-VALIDATE**: Run `git status` and `git diff --cached`. Show the user the staged diff and confirm before committing. Spot-check: `Bootstrap_$NEWBOOT` appears in `init.php` ~6 times all updated, `PRIORITY = $NEWPRIO` is exactly one less than `$OLDPRIO`, and `readme.txt` "Tested up to" matches current WP.

```bash
git add init.php package.json package-lock.json readme.txt CHANGELOG.md README.md css/
git commit -m "Update changelog/readme/versions"
```

## Step 2 — Rebuild i18n (separate commit)

```bash
npm run build:i18n
git add languages/
git commit -m "update i18n files"
```

This regenerates `languages/cmb2.pot` (and `.mo` files if `msgfmt` is on PATH). Kept as a separate commit historically because the pot diff is noisy.

## Step 3 — Resolve `{{next}}` placeholders

The snapshot at the top showed which files have `{{next}}`. For each hit, replace `{{next}}` with `$NEW` in `@since` docblocks:

```bash
grep -rl "{{next}}" --include="*.php" includes/ init.php | xargs sed -i.bak "s/{{next}}/$NEW/g"
find includes/ init.php -name "*.bak" -delete

grep -rn "{{next}}" --include="*.php" .   # should return zero results
git add -p
git commit -m "Replace next tag placeholders with $NEW"
```

## Step 4 — Tag on develop, fast-forward master

Per CMB2 history, the release-prep commits live on `develop` and `master` is fast-forwarded to the tagged commit. There is **no merge commit** — every release tag in the history sits on a develop commit, with master pointing at the same SHA.

You're still on `develop` with Steps 1–3 committed. Tag the latest commit:

```bash
git tag -a "v$NEW" -m "v$NEW"
```

🛑 **STOP-AND-VALIDATE**: Show `git log --oneline -5` and `git show v$NEW --stat`. Confirm the tag points to the right commit before pushing. Pushing a tag is hard to undo cleanly.

```bash
git push origin develop
git push origin "v$NEW"
git checkout master && git pull --rebase
git merge --ff-only "v$NEW"
git push origin master
git checkout develop
```

If `--ff-only` fails, master has commits develop doesn't — investigate before forcing anything.

Per the project's `CLAUDE.md` "Landing the Plane" rule: **work is not done until `git push` succeeds.**

## Step 5 — wordpress.org SVN deploy

This step is fully manual (no GitHub Action exists) and the most error-prone part of the release. Follow [references/svn-deploy.md](references/svn-deploy.md) — it has the exact rsync exclude list, the SVN tag-copy steps, the STOP-AND-VALIDATE gate before `svn ci`, and the post-deploy verification.

## Step 6 — GitHub Release

Pull the changelog body straight from `CHANGELOG.md` so the GitHub release page matches what users see in `readme.txt`:

```bash
NOTES=$(awk -v v="## [$NEW" 'index($0, v)==1{flag=1; next} /^## /{flag=0} flag' CHANGELOG.md)
gh release create "v$NEW" --title "v$NEW" --notes "$NOTES" --target master
gh release view "v$NEW" --web   # eyeball it
```

The wiki (`wiki-cmb2/Notable-Changes-in-CMB2.md`) is not auto-updated — update by hand if the release introduces user-facing changes worth highlighting.

## Step 7 — Verify the plane has landed

```bash
git fetch origin
for branch in develop master; do
  local=$(git rev-parse "$branch")
  remote=$(git rev-parse "origin/$branch")
  [ "$local" = "$remote" ] && echo "$branch: in sync" || echo "$branch: OUT OF SYNC"
done

git tag --contains HEAD                    # shows v$NEW on master
gh release view "v$NEW"                    # GitHub release exists
curl -sI "https://downloads.wordpress.org/plugin/cmb2.$NEW.zip" | head -1   # 200 OK
```

If any of those fail, the release isn't done.

## Step 8 — Post-release develop bump

Once the release is shipped, `develop` needs to be re-flagged for the next version. The pattern (used since 2.8.0): one commit on `develop` titled exactly **`Add develop suffix to init class`** that touches `init.php` only and renames the bootstrap class from `CMB2_Bootstrap_$NEWBOOT` → `CMB2_Bootstrap_<NEXT>_Develop`.

This keeps develop's bootstrap class name distinct from any released `CMB2_Bootstrap_<N>` so they coexist cleanly when both are bundled in the same WP install. (PRIORITY stays at the just-released value — see the trade-off note in "Why these steps look the way they do.")

**Skip this step for patch releases.** History (v2.10.1) shows patches don't get a develop bump — the next minor's bump rolls it in. If the user is cutting a patch, ask whether to skip; otherwise default to skipping.

For minor/major releases, ask the user what the next planned version is. Usually the next minor (e.g. after 2.11.0 → 2.12.0). Note that historically the bump skips ahead to the next *minor* even when a patch might come next (after 2.11.0, develop went straight to `2120_Develop`, not `2111_Develop`).

```bash
export NEXT=2.X.Y                                       # next planned version
export NEXTBOOT=$(echo "$NEXT" | tr -d .)               # 2.12.0 → 2120

# Rename only — VERSION, PRIORITY, and Version: header stay frozen
# at the just-released values until the next release prep.
sed -i.bak \
  "s/CMB2_Bootstrap_$NEWBOOT/CMB2_Bootstrap_${NEXTBOOT}_Develop/g" \
  init.php
rm init.php.bak

git diff init.php
```

The diff should show ~6 occurrences of the class rename and nothing else.

🛑 **STOP-AND-VALIDATE**: confirm only `init.php` is touched, only the class name changed, and `const VERSION` / `const PRIORITY` / `Version:` header are unchanged.

```bash
git add init.php
git commit -m "Add develop suffix to init class"
git push origin develop
```

## Known gaps & follow-ups

These aren't blockers but are worth raising with the user once shipped:

1. **No automated wp.org SVN deploy.** Adding `10up/action-wordpress-plugin-deploy` (or similar) as a `release.yml` workflow triggered on tag push would eliminate Step 5 entirely. It's been a long-standing gap; offer to file a `bd` issue for it.
2. **No release script.** Steps 1–4 are mechanical and could be a `bin/release.sh`. The bootstrap class rename + priority decrement are the only non-trivial parts.
3. **`{{next}}` is fragile.** If a contributor writes `@since 2.12.0` directly in a PR before 2.12.0 is cut, the placeholder grep won't catch it. A pre-release `bd preflight` check could grep for `@since` referencing unreleased versions.

Mention these once after Step 7 verification completes.
