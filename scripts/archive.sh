#!/usr/bin/env bash
#
# archive.sh
#
# Builds the CMB2 release archive from a git ref, and optionally stages it into
# a wordpress.org SVN working copy. Never commits to SVN.
#
# The archive is a `git archive` of the ref, never a copy of the working tree:
# the working tree also holds untracked and gitignored files (.env.local,
# HANDOFF-*.md) that no exclude list can anticipate. `.gitattributes`
# export-ignore is the single list of what does not ship.

set -euo pipefail

show_help() {
	cat << 'EOF'
Build the CMB2 release archive from a git ref.

Usage: scripts/archive.sh [options]

Options:
  --ref=REF         Git ref to archive (default: v<version in init.php>)
  --svn=DIR         Also stage the build into this wp.org SVN working copy
                    (checked out if missing): sync trunk, stage adds/removes,
                    copy trunk to tags/<version>. Never runs `svn ci`.
  --allow-develop   Skip the release-only checks, for test builds of an
                    untagged ref (e.g. --ref=HEAD --allow-develop)
  -h, --help        Show this help

Output:
  archives/cmb2/                The exported plugin tree
  archives/cmb2-<version>.zip   Zip with a top-level cmb2/ folder, matching
                                wordpress.org's download layout
EOF
}

REF=''
SVN_DIR=''
ALLOW_DEVELOP=false
SVN_URL='https://plugins.svn.wordpress.org/cmb2'

for arg in "$@"; do
	case $arg in
		-h|--help) show_help; exit 0 ;;
		--ref=*) REF="${arg#*=}" ;;
		--svn=*) SVN_DIR="${arg#*=}" ;;
		--allow-develop) ALLOW_DEVELOP=true ;;
		*) echo "Unknown argument: $arg" >&2; show_help >&2; exit 1 ;;
	esac
done

cd "$(dirname "$0")/.."
ROOT="$PWD"

fail() { echo -e "\033[31m$*\033[0m" >&2; exit 1; }
ok()   { echo -e "\033[32m✔ $*\033[0m"; }

if [ -z "$REF" ]; then
	FILE_VERSION=$(grep -oE "const VERSION = '[^']+'" init.php | grep -oE "[0-9][0-9.]*")
	REF="v$FILE_VERSION"
fi
git rev-parse --verify --quiet "$REF^{commit}" > /dev/null || fail "Unknown git ref: $REF"

at_ref() { git show "$REF:$1"; }

VERSION=$(at_ref init.php | grep -oE "const VERSION = '[^']+'" | grep -oE "[0-9][0-9.]*")
[ -n "$VERSION" ] || fail "No const VERSION found in init.php at $REF"
echo "Archiving CMB2 $VERSION from $REF ($(git rev-parse --short "$REF"))"

# --- Validate the ref -------------------------------------------------------
# Files are read into variables first: piping `git show` into `grep -q` fails
# under pipefail once grep exits early and git show takes SIGPIPE.
# Every failure is collected before exiting, so one run shows the whole list.
ERRORS=()
check() { if ! eval "$2"; then ERRORS+=( "$1" ); fi; }
has() { grep -qE "$2" <<< "$1"; }

INIT=$(at_ref init.php)
PACKAGE=$(at_ref package.json)
README_TXT=$(at_ref readme.txt)
README_MD=$(at_ref README.md)
CHANGELOG=$(at_ref CHANGELOG.md)
V_RE=${VERSION//./\\.}

check "init.php Version: header is not $VERSION" \
	'has "$INIT" "^ \* Version: +$V_RE\$"'
check "package.json version is not $VERSION" \
	'has "$PACKAGE" "\"version\": \"$V_RE\""'
check "readme.txt Stable tag is not $VERSION" \
	'has "$README_TXT" "^Stable tag: +$V_RE\$"'
check "README.md Stable tag is not $VERSION" \
	'has "$README_MD" "^\*\*Stable tag:\*\* +$V_RE( |\$)"'
check "CHANGELOG.md has no ## [$VERSION - ...] section" \
	'has "$CHANGELOG" "^## \[$V_RE - "'
check "readme.txt has no ### $VERSION changelog entry" \
	'has "$README_TXT" "^### $V_RE\$"'

# Both the expanded and the minified CSS carry a version banner; the minified
# ones are only rewritten by `npm run build:css:minify`.
for css in $(git ls-tree --name-only "$REF" css/ | grep -E '\.css$'); do
	BANNER=$(at_ref "$css" | head -c 300 || true)
	check "$css banner is not v$VERSION" 'has "$BANNER" "v$V_RE "'
done

NEXT_HITS=$(git grep -l '{{next}}' "$REF" -- '*.php' | sed 's/^[^:]*://' || true)
check "{{next}} placeholders remain: ${NEXT_HITS//$'\n'/ }" '[ -z "$NEXT_HITS" ]'

if [ "$ALLOW_DEVELOP" = false ]; then
	BOOT="CMB2_Bootstrap_${VERSION//./}"
	check "init.php bootstrap class is not $BOOT (still _Develop?)" \
		'has "$INIT" "^	class $BOOT \\{"'
	check "$REF is not the v$VERSION tag" \
		'[ "$(git rev-parse "$REF^{commit}")" = "$(git rev-parse --verify --quiet "v$VERSION^{commit}")" ]'
fi

if [ ${#ERRORS[@]} -gt 0 ]; then
	printf '\033[31m✘ %s\033[0m\n' "${ERRORS[@]}" >&2
	fail "\n${#ERRORS[@]} check(s) failed; not building."
fi
ok "Version strings, banners and placeholders all match $VERSION"

# --- Build ------------------------------------------------------------------
ARCHIVES="$ROOT/archives"
EXPORT="$ARCHIVES/cmb2"
ZIP="$ARCHIVES/cmb2-$VERSION.zip"

rm -rf "$EXPORT" "$ZIP"
mkdir -p "$ARCHIVES"
git archive --prefix=cmb2/ "$REF" | tar -x -C "$ARCHIVES"
( cd "$ARCHIVES" && zip -qrX "$ZIP" cmb2 )

FILE_COUNT=$(find "$EXPORT" -type f | wc -l | tr -d ' ')
ok "Built ${ZIP#"$ROOT"/} ($FILE_COUNT files)"
echo "Top level:"
ls -A "$EXPORT" | sed 's/^/  /'

[ -n "$SVN_DIR" ] || exit 0

# --- Stage into SVN ---------------------------------------------------------
command -v svn > /dev/null || fail "svn is not installed"
mkdir -p "$(dirname "$SVN_DIR")"
SVN_DIR="$(cd "$(dirname "$SVN_DIR")" && pwd)/$(basename "$SVN_DIR")"
[ -d "$SVN_DIR/.svn" ] || svn co -q "$SVN_URL" "$SVN_DIR"
svn up -q "$SVN_DIR"

cd "$SVN_DIR"
[ -z "$(svn status)" ] || fail "SVN working copy $SVN_DIR has uncommitted changes; revert or commit them first."
[ ! -e "tags/$VERSION" ] || fail "tags/$VERSION already exists in SVN — $VERSION was already deployed."

rsync -a --delete --exclude='.svn' "$EXPORT/" trunk/
svn add --force -q trunk
# SVN parses '@' in a path as a peg revision (e.g. cmb2-en@pirate.po); the
# trailing '@' makes it literal. Removals land before the tag copy so the tag
# doesn't inherit missing files.
svn status trunk | awk '/^!/ {print $2}' | while read -r f; do svn rm -q "$f@"; done
svn cp -q trunk "tags/$VERSION"

diff -rq --exclude=.svn trunk "$EXPORT" > /dev/null || fail "trunk does not match the $REF export"
diff -rq --exclude=.svn trunk "tags/$VERSION" > /dev/null || fail "tags/$VERSION does not match trunk"
ok "trunk == $REF export, tags/$VERSION == trunk"

# svn:ignore props on trunk/ and trunk/css/ deliberately keep a few exported
# files (README.md, some *.css.map) out of wp.org; `svn add` skips them.
IGNORED=$(svn status --no-ignore trunk | awk '/^I/ {print $2}')
if [ -n "$IGNORED" ]; then
	echo "Withheld from wp.org by svn:ignore:"
	sed 's/^/  /' <<< "$IGNORED"
fi

echo "Staged in $SVN_DIR:"
svn status trunk | awk '{print $1}' | sort | uniq -c | sed 's/^/  /'
echo
echo "Review with:  svn status $SVN_DIR/trunk && svn diff --diff-cmd diff -x -u $SVN_DIR/trunk | less"
echo "Commit with:  svn ci $SVN_DIR -m \"Release $VERSION\" --username <wp-org-username>"
