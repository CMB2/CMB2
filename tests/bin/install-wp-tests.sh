#!/usr/bin/env bash
# WordPress Test Installation Script for CMB2 (and other projects)
#
# This script automates the setup of a WordPress testing environment.
# It downloads and configures the WordPress core, the WordPress test suite,
# and sets up a dedicated database for testing.
#
# Original script from WordPress Core, adapted by the WordPress community,
# and further modified for CMB2 needs.
# Main changes include:
# - Fetching test suite files (includes, wp-tests-config-sample.php) from the
#   official WordPress Develop GitHub repository (github.com/WordPress/wordpress-develop).
# - Using GitHub tags or trunk for specific WordPress versions.
# - Default tmp directories (`WP_TESTS_DIR`, `WP_CORE_DIR`) are relative to this script's location.
# - Removed installation of custom wp-mysqli db.php and REST API plugin.
# - Added dependency checks and improved error handling.
#
# USAGE:
#
#   bash tests/bin/install-wp-tests.sh <db_name> <db_user> <db_pass> [db_host] [wp_version]
#
# EXAMPLES:
#
#   To install WordPress 'latest' version and use default DB host ('localhost'):
#   bash tests/bin/install-wp-tests.sh wordpress_tests_db root_user root_password
#
#   To install WordPress version 6.0 and specify DB host:
#   bash tests/bin/install-wp-tests.sh wordpress_tests_db user pass db.example.com 6.0
#
#   To install WordPress 'trunk' (equivalent to 'latest' for test suite files):
#   bash tests/bin/install-wp-tests.sh wordpress_tests_db user pass localhost trunk
#

# Exit immediately if a command exits with a non-zero status.
set -e

if [ $# -lt 3 ]; then
	echo "Usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest} # Default to 'latest' if not specified

# Determine the directory of this script to make relative paths work
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)

# Define default temporary directory locations relative to the script's parent directory
# These can be overridden by setting the environment variables WP_TESTS_DIR or WP_CORE_DIR.
WP_TESTS_DIR=${WP_TESTS_DIR-${SCRIPT_DIR}/../tmp/wordpress-tests-lib} # WordPress test suite library
WP_CORE_DIR=${WP_CORE_DIR-${SCRIPT_DIR}/../tmp/wordpress}             # WordPress core installation

# Function to check for required command-line utility dependencies
check_dependencies() {
    local missing_deps=0
    # Dependencies: command_name:"Error message if missing"
    local deps=("svn:Subversion (svn) is required for fetching test suite files via SVN." \
                "git:Git is required as a fallback for fetching test suite files via Git." \
                "tar:Tar is required for extracting WordPress archives." \
                "mysql:MySQL client (mysql) is required for database operations." \
                "mysqladmin:MySQL admin client (mysqladmin) is required for database operations.")

    echo "Checking for required dependencies..."

    for item in "${deps[@]}"; do
        local cmd="${item%%:*}"
        local msg="${item#*:}"
        if ! command -v "$cmd" >/dev/null 2>&1; then
            echo "Error: $msg (Command '$cmd' not found)." >&2
            missing_deps=$((missing_deps + 1))
        fi
    done

    # Check for curl or wget
    if ! command -v curl >/dev/null 2>&1 && ! command -v wget >/dev/null 2>&1; then
        echo "Error: Either curl or wget is required for downloading files." >&2
        missing_deps=$((missing_deps + 1))
    fi

    if [ "$missing_deps" -gt 0 ]; then
        echo "Please install the missing dependencies and try again." >&2
        exit 1
    else
        echo "All required dependencies are satisfied."
    fi
}

# Call dependency check early
check_dependencies

download() {
    local url="$1"
    local dest="$2"
    local success=0

    echo "Downloading $url to $dest..."
    if command -v curl >/dev/null 2>&1; then
        # -s: silent, -L: follow redirects, -f: fail silently on server errors (4xx, 5xx)
        if curl -s -L -f "$url" -o "$dest"; then
            success=1
        fi
    elif command -v wget >/dev/null 2>&1; then
        # -nv: non-verbose, -O: output to file, --user-agent: prevent potential warnings
        if wget -nv --user-agent="CMB2 Test Installer/1.0" -O "$dest" "$url"; then
            success=1
        fi
    else
        # This case should have been caught by check_dependencies, but as a safeguard:
        echo "Error: Neither curl nor wget is available to download files." >&2
        exit 1
    fi

    if [ "$success" -eq 0 ] || [ ! -s "$dest" ]; then
        echo "Error: Failed to download '$url'. Check URL and network." >&2
        # Remove potentially incomplete file
        [ -f "$dest" ] && rm -f "$dest"
        exit 1
    fi
    # echo "Successfully downloaded $url to $dest" # This can be verbose as download() is called multiple times.
}

# Function to fetch the latest stable WordPress version string from the WordPress.org API
get_latest_wp_version() {
	local latest_json_url="http://api.wordpress.org/core/version-check/1.7/" # WP API endpoint for version checks
	local latest_version_val=""
	local temp_json_file="/tmp/wp-latest-$$.json" # Unique temp file name

	# Attempt to download the JSON content
	download "$latest_json_url" "$temp_json_file"
	if [ ! -s "$temp_json_file" ]; then
		echo "Error: Failed to download version information from $latest_json_url" >&2
		rm -f "$temp_json_file"
		return 1
	fi

	if command -v jq >/dev/null 2>&1; then
		# Use jq if available for reliable JSON parsing
		latest_version_val=$(jq -r '.offers[0].version' "$temp_json_file")
	else
		# Fallback to grep/sed if jq is not available (less robust)
		# This attempts to extract the version from the first offer in the JSON structure.
		latest_version_val=$(grep -o -m 1 '"version":"[^"]*' "$temp_json_file" | sed 's/"version":"//' | head -n1)
	fi
	rm -f "$temp_json_file" # Clean up the temporary file

	# Validate the extracted version format (e.g., X.Y.Z or X.Y)
	if [[ -z "$latest_version_val" || ! "$latest_version_val" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?$ ]]; then
		echo "Error: Could not determine a valid latest WordPress version from API." >&2
		echo "Value obtained: '$latest_version_val'" >&2
		return 1 # Signal failure
	fi
	echo "$latest_version_val" # Output the determined version
	return 0 # Signal success
}

# Determine the GITHUB_REF (branch or tag) for fetching files from WordPress/wordpress-develop
# This ref is used for downloading test suite files and wp-tests-config-sample.php.
if [[ "$WP_VERSION" == "latest" || "$WP_VERSION" == "nightly" || "$WP_VERSION" == "trunk" ]]; then
	# For 'latest', 'nightly', or 'trunk', use the 'trunk' branch of wordpress-develop
	GITHUB_REF="trunk"
	# Fetch the latest stable version number for informational purposes or if needed by other parts of the script.
	LATEST_STABLE_VERSION=$(get_latest_wp_version)
	if [[ -z "$LATEST_STABLE_VERSION" ]]; then
		echo "Warning: Could not determine the latest stable WordPress version from API. Proceeding with GITHUB_REF=trunk."
		# This is not necessarily fatal if the user explicitly asked for 'latest'/'trunk',
		# as GITHUB_REF="trunk" is already set for test files.
	else
		echo "Using GITHUB_REF='trunk'. (Latest stable WordPress version for reference: $LATEST_STABLE_VERSION)"
	fi
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?$ ]]; then
	# For specific versions like "6.0", "5.9.1", use the version number as the GITHUB_REF (maps to a tag).
	GITHUB_REF="$WP_VERSION"
else
	# For any other format (e.g., a specific branch name or a beta tag like "6.0-beta1"),
	# use the provided WP_VERSION string directly as GITHUB_REF.
	echo "Warning: Unrecognized WP_VERSION format: '$WP_VERSION'."
    echo "Attempting to use it directly as GITHUB_REF (e.g., for a specific branch or pre-release tag)."
    echo "If this fails, please use 'latest', 'trunk', 'X.Y', or 'X.Y.Z'."
	GITHUB_REF="$WP_VERSION"
fi

# set -e is already set at the top of the script.

# Function to download and set up the WordPress core files
install_wp() {

	# Clean up existing WP_CORE_DIR before creating a new one
	if [ -n "$WP_CORE_DIR" ] && [ "$WP_CORE_DIR" != "/" ] && [ -d "$WP_CORE_DIR" ]; then
		echo "Cleaning up existing WP_CORE_DIR: $WP_CORE_DIR"
		rm -rf "$WP_CORE_DIR"
	fi

	mkdir -p "$WP_CORE_DIR"

	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	# Download WordPress core archive
	download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
	# Extract WordPress core files
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"

	# This path is specific to the WordPress testing framework's data structure.
	# It might be used for tests involving theme operations.
	# Note: This path is hardcoded and not relative to $WP_CORE_DIR.
	# This is consistent with how the original install-wp-tests.sh script handled it.
	if [ ! -d /tmp/wordpress/tests/data/themedir1/dummy-theme/ ]; then
		mkdir -p /tmp/wordpress/tests/data/themedir1/dummy-theme/
	fi

}

# Function to download and set up the WordPress PHPUnit test suite
install_test_suite() {
	# Determine the appropriate sed 'in-place' edit option based on the OS
	# (GNU sed uses -i, macOS sed requires an extension like -i .bak)
	if [[ $(uname -s) == 'Darwin' && $(which sed) == '/usr/bin/sed' ]]; then
		local ioption='-i .bak' # macOS sed
	else
		local ioption='-i'      # GNU sed
	fi

	# Clean up existing WP_TESTS_DIR directory before creating a new one
	if [ -n "$WP_TESTS_DIR" ] && [ "$WP_TESTS_DIR" != "/" ] && [ -d "$WP_TESTS_DIR" ]; then
		echo "Cleaning up existing WP_TESTS_DIR: $WP_TESTS_DIR"
		rm -rf "$WP_TESTS_DIR"
	fi

	# Create the test suite directory
	mkdir -p "$WP_TESTS_DIR"
	# Define a temporary directory for git clone operations
	local WP_GIT_DIR="/tmp/wordpress-develop-git-$$" # Unique temp dir name

	echo "Fetching WordPress test suite 'includes' directory..."
	if [[ "$GITHUB_REF" == "trunk" ]]; then
		# For 'trunk', use the trunk URL of the WordPress Develop repository
		SVN_EXPORT_URL="https://github.com/WordPress/wordpress-develop/trunk/tests/phpunit/includes"
	else
		# For specific versions (tags), use the tag URL
		SVN_EXPORT_URL="https://github.com/WordPress/wordpress-develop/tags/${GITHUB_REF}/tests/phpunit/includes"
	fi

	# Attempt to download the 'includes' directory using 'svn export' from GitHub's SVN bridge.
	# 'svn export' is generally faster as it doesn't download the full Git history.
	# The '--force' option ensures that it overwrites if the directory exists.
	if svn export --force "${SVN_EXPORT_URL}" "$WP_TESTS_DIR/includes"; then
		echo "Successfully downloaded 'includes' directory via svn export."
	else
		echo "SVN export failed. Attempting git clone fallback..."
		rm -rf "$WP_GIT_DIR" # Ensure the temporary git directory is clean before cloning
		# Fallback to cloning the repository (shallow clone for speed) and copying the directory.
		if git clone --depth 1 --branch "${GITHUB_REF}" https://github.com/WordPress/wordpress-develop.git "$WP_GIT_DIR"; then
			mkdir -p "$WP_TESTS_DIR/includes"
			# Copy the contents of the 'includes' directory
			cp -r "${WP_GIT_DIR}/tests/phpunit/includes/"* "$WP_TESTS_DIR/includes/"
			rm -rf "$WP_GIT_DIR" # Clean up the temporary cloned repository
			echo "Successfully copied 'includes' directory via git clone."
		else
			echo "Error: Failed to download test 'includes' files from GitHub using both SVN export and git clone." >&2
			echo "Please check your internet connection, SVN/Git installation, and the GITHUB_REF: ${GITHUB_REF}" >&2
			exit 1
		fi
	fi

	cd "$WP_TESTS_DIR" # Change to the tests directory for subsequent operations

	# Download wp-tests-config-sample.php from the WordPress Develop repository
	download "https://raw.githubusercontent.com/WordPress/wordpress-develop/${GITHUB_REF}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"

	# Configure wp-tests-config.php
	# Set the path to the WordPress core directory
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
	# Enable WP_DEBUG and related debugging constants for the test environment
	sed $ioption "s:define( 'WP_DEBUG', true );:define( 'WP_DEBUG', true );\ndefine( 'WP_DEBUG_DISPLAY', false );\ndefine( 'WP_DEBUG_LOG', true );:" "$WP_TESTS_DIR"/wp-tests-config.php
	# Set database credentials and host
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php # Use | as sed delimiter for paths/URLs
}

# Function to set up the test database
install_db() {
	# Parse DB_HOST to separate hostname and port/socket if specified (e.g., localhost:3306 or /tmp/mysql.sock)
	local PARTS=(${DB_HOST//\:/ }) # Split DB_HOST by ':'
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	# Handle Local by Flywheel socket paths that start with /
	if [[ "$DB_HOST" =~ ^/.* ]]; then
		EXTRA=" --socket=$DB_HOST"
elif [ -n "$DB_HOSTNAME" ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# Drop the existing database if it exists
	echo "Dropping existing database $DB_NAME (if it exists)..."
	mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "DROP DATABASE IF EXISTS \`$DB_NAME\`"

	# Create the new database
	echo "Creating database $DB_NAME..."
	mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS"$EXTRA
	echo "Database $DB_NAME created."
}

# Main execution flow
echo "Starting WordPress test environment setup..."

install_wp
install_test_suite
install_db

echo "WordPress test environment setup complete."
echo "You can now run your tests."
