# wp.org SVN deploy (Step 5 of cmb2-release)

There is **no GitHub Action for this**. It is fully manual. If you don't have credentials, stop and ask the user — the user must `svn co` with their wp.org login the first time.

This file assumes `$NEW` is exported from the parent SKILL.md's "Lock in the values" step.

## 1. Build and stage with `scripts/archive.sh`

```bash
scripts/archive.sh --svn=/tmp/cmb2-svn
```

It checks out (or updates) the SVN working copy, refuses to run if the working
copy is dirty or `tags/$NEW` already exists, then:

- re-runs the release checks against the `v$NEW` tag and builds
  `archives/cmb2/` from `git archive` (never the working tree, which holds
  gitignored files like `.env.local`);
- mirrors that tree into `trunk/`, stages adds and removes (`svn rm` gets a
  trailing `@`, since SVN parses `@` in a filename as a peg revision), and
  copies `trunk` to `tags/$NEW`;
- verifies `trunk` matches the export and the tag matches `trunk`, and lists
  what the SVN `svn:ignore` props withhold (`README.md` and some `*.css.map`,
  deliberately kept off wp.org).

It never runs `svn ci`. If it fails, fix the cause; don't stage by hand.

What ships is `.gitattributes` `export-ignore` plus those `svn:ignore` props.
When you add a dev/tooling file or directory to the repo, mark it
`export-ignore`.

## 2. 🛑 STOP-AND-VALIDATE before `svn ci`

Run `svn status /tmp/cmb2-svn` and `svn diff --diff-cmd diff -x -u /tmp/cmb2-svn/trunk | head -200` (the built-in diff can print only headers). Show the user the file list and a diff sample. SVN commits are public the moment they land — there's no "force-push" recovery. Confirm before committing.

## 3. Commit

```bash
svn ci /tmp/cmb2-svn -m "Release $NEW" --username <wp-org-username>
```

## 4. Verify

After `svn ci`, the wordpress.org listing updates within a few minutes. Verify:

- https://wordpress.org/plugins/cmb2/ shows new version + changelog
- https://wordpress.org/plugins/cmb2/advanced/ shows the new tag
- Download a fresh zip from wp.org and diff against the GitHub tag — should match modulo the excluded files
