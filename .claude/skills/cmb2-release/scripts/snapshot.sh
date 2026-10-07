#!/usr/bin/env bash
# Repo snapshot for the cmb2-release skill — rendered into the skill prompt
# via `!`bash ${CLAUDE_SKILL_DIR}/scripts/snapshot.sh`` so Claude opens the
# release with current state already in context.

set -uo pipefail

# Fetch tags first so "latest tag" reflects the remote, not a stale local view.
git fetch --tags --quiet origin 2>/dev/null || true

echo "=== Branch & tags ==="
git branch --show-current
# Highest released version overall (sort -V), not `git describe` — on a diverged
# develop, describe returns the latest tag reachable from HEAD, which can lag.
HIGHEST=$(git tag -l 'v*' | sort -V | tail -1)
REACHABLE=$(git describe --tags --abbrev=0 2>/dev/null || echo none)
echo "Highest tag overall:      ${HIGHEST:-none}"
echo "Latest reachable from HEAD: $REACHABLE"
[ "$HIGHEST" != "$REACHABLE" ] && echo "⚠️  HEAD has diverged from the highest tag — verify branch state before releasing."
echo

echo "=== Working tree ==="
git status --short
echo

echo "=== init.php key values ==="
grep -nE '^( \* Version:|	const VERSION|	const PRIORITY|	class CMB2_Bootstrap_)' init.php
grep -n "if ( ! class_exists( 'CMB2_Bootstrap" init.php
echo

echo "=== Outstanding {{next}} placeholders ==="
grep -rn "{{next}}" --include="*.php" includes/ init.php 2>/dev/null
echo

echo "=== Recent CI runs on develop ==="
gh run list --branch develop -L 3 2>/dev/null || echo "(gh not available)"
echo

echo "=== Commits on HEAD not in the highest tag ($HIGHEST) ==="
[ -n "$HIGHEST" ] && git log "$HIGHEST"..HEAD --oneline 2>/dev/null | head -30
echo

echo "=== Current WordPress core version ==="
curl -s https://api.wordpress.org/core/version-check/1.7/ 2>/dev/null \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['offers'][0]['current'])" 2>/dev/null \
  || echo "(check wordpress.org/download/releases/)"
