# wp.org SVN deploy (Step 5 of cmb2-release)

There is **no GitHub Action for this**. It is fully manual. If you don't have credentials, stop and ask the user — the user must `svn co` with their wp.org login the first time.

This file assumes `$NEW` is exported from the parent SKILL.md's "Lock in the values" step.

## 1. Check out (or update) the SVN working copy

```bash
SVN=/tmp/cmb2-svn
[ -d "$SVN" ] || svn co https://plugins.svn.wordpress.org/cmb2 "$SVN"
cd "$SVN" && svn up && cd -
```

## 2. Sync the GitHub tag's contents into `trunk/`

The exclude list matters — wp.org rejects oversized plugins and exposes anything not excluded:

```bash
rsync -av --delete \
  --exclude='.git' --exclude='.github' --exclude='.gitignore' \
  --exclude='.gitattributes' --exclude='.editorconfig' \
  --exclude='node_modules' --exclude='vendor' \
  --exclude='tests' --exclude='phpunit.xml.dist' --exclude='.phpcs.xml.dist' \
  --exclude='package.json' --exclude='package-lock.json' --exclude='composer.json' --exclude='composer.lock' \
  --exclude='Gruntfile.js' --exclude='scripts' \
  --exclude='*.scss' --exclude='css/sass' \
  --exclude='CLAUDE.md' --exclude='.claude' --exclude='.beads' \
  --exclude='.cursorrules' --exclude='.copilot' \
  ./ "$SVN/trunk/"
```

## 3. Stage adds/removes and tag-copy

```bash
cd "$SVN"
svn status                                              # review additions/deletions
svn add --force trunk
svn rm $(svn status trunk | awk '/^!/ {print $2}') 2>/dev/null
svn cp trunk "tags/$NEW"
```

## 4. 🛑 STOP-AND-VALIDATE before `svn ci`

Run `svn status` and `svn diff trunk | head -200`. Show the user the file list and a diff sample. SVN commits are public the moment they land — there's no "force-push" recovery. Confirm before committing.

## 5. Commit

```bash
svn ci -m "Release $NEW" --username <wp-org-username>
```

## 6. Verify

After `svn ci`, the wordpress.org listing updates within a few minutes. Verify:

- https://wordpress.org/plugins/cmb2/ shows new version + changelog
- https://wordpress.org/plugins/cmb2/advanced/ shows the new tag
- Download a fresh zip from wp.org and diff against the GitHub tag — should match modulo the excluded files
