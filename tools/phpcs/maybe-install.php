<?php
/**
 * Auto-install the isolated PHPCS toolchain after a root `composer install`.
 *
 * Wired to root composer.json post-install-cmd / post-update-cmd. It is GUARDED
 * so it never runs during the PHPUnit CI matrix:
 *
 *   - Skips on PHP < 8.2. The toolchain pulls fig-r/psr2r-sniffer ->
 *     spryker/code-sniffer, which requires PHP 8.2+. Running it on the
 *     7.4/8.0/8.1 test legs would fail `composer install` (the exact bug this
 *     setup avoids — see commit history around composer.lock removal).
 *   - Skips when CI is set. CI installs the toolchain explicitly in the PHPCS
 *     job (.github/workflows/phpcs.yml), so it must stay out of the test matrix.
 *
 * The install is best-effort: this script always exits 0 so a phpcs tooling
 * hiccup never fails the developer's root `composer install`.
 *
 * @package CMB2
 */

if ( PHP_VERSION_ID < 80200 || getenv( 'CI' ) ) {
	echo "phpcs: skipping tools/phpcs auto-install (needs PHP 8.2+, skipped in CI). Run `composer phpcs:install` manually if needed.\n";
	exit( 0 );
}

$composer = getenv( 'COMPOSER_BINARY' );
if ( ! $composer ) {
	echo "phpcs: COMPOSER_BINARY not set; run `composer phpcs:install` to set up linting.\n";
	exit( 0 );
}

echo "phpcs: installing isolated toolchain in tools/phpcs ...\n";

$cmd = escapeshellarg( PHP_BINARY )
	. ' ' . escapeshellarg( $composer )
	. ' install --working-dir=' . escapeshellarg( __DIR__ )
	. ' --no-interaction --no-progress';

passthru( $cmd );

// Never fail the parent install on a tooling hiccup.
exit( 0 );
