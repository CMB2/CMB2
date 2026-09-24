# wp.org SVN deploy (Step 5 of cmb2-release)

There is **no GitHub Action for this**. It is fully manual. If you don't have credentials, stop and ask the user — the user must `svn co` with their wp.org login the first time.

This file assumes `$NEW` is exported from the parent SKILL.md's "Lock in the values" step.

## 1. Check out (or update) the SVN working copy

```bash
SVN=/tmp/cmb2-svn
[ -d "$SVN" ] || svn co https://plugins.svn.wordpress.org/cmb2 "$SVN"
cd "$SVN" && svn up && cd -
```

## 2. Sync the tagged release into `trunk/`

Export the **tag** with `git archive`, then mirror that export into trunk. Never
rsync from the working directory: it also holds untracked and gitignored files
(`.env.local`, `HANDOFF-*.md`, local build debris) that an exclude list can't
anticipate, and SVN commits are public and permanent.

`git archive` honors the `export-ignore` attributes in the repo-root
`.gitattributes`, which makes that file the single list of what does not ship —
the same list GitHub's release source zips use. When you add a dev/tooling file
or directory to the repo, mark it `export-ignore` there.

```bash
EXPORT=$(mktemp -d)
git archive "v$NEW" | tar -x -C "$EXPORT"
ls -a "$EXPORT"                                         # eyeball: no dotfiles/tooling beyond what trunk already ships

rsync -a --delete --exclude='.svn' "$EXPORT/" "$SVN/trunk/"
```

## 3. Stage adds/removes and tag-copy

Stage removals **before** the tag copy, so the tag doesn't inherit missing files.
SVN reads `@` in a path as a peg revision (e.g. `cmb2-en@pirate.po`), so every
path passed to `svn rm` gets a trailing `@`.

```bash
cd "$SVN"
svn add --force -q trunk
svn status trunk | awk '/^!/ {print $2}' | while read -r f; do svn rm -q "$f@"; done
svn cp trunk "tags/$NEW"
svn status                                              # review additions/deletions

diff -rq --exclude=.svn trunk "$EXPORT"  && echo "trunk == v$NEW"
diff -rq --exclude=.svn trunk "tags/$NEW" && echo "tag == trunk"
```

Both `diff`s must print their success line.

## 4. 🛑 STOP-AND-VALIDATE before `svn ci`

Run `svn status` and `svn diff --diff-cmd diff -x -u trunk | head -200` (the built-in diff can print only headers). Show the user the file list and a diff sample. SVN commits are public the moment they land — there's no "force-push" recovery. Confirm before committing.

## 5. Commit

```bash
svn ci -m "Release $NEW" --username <wp-org-username>
```

## 6. Verify

After `svn ci`, the wordpress.org listing updates within a few minutes. Verify:

- https://wordpress.org/plugins/cmb2/ shows new version + changelog
- https://wordpress.org/plugins/cmb2/advanced/ shows the new tag
- Download a fresh zip from wp.org and diff against the GitHub tag — should match modulo the excluded files
