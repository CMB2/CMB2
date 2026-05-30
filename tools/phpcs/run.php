<?php
/**
 * Ensure the isolated PHPCS toolchain is installed, then run phpcs or phpcbf.
 *
 * Backs the `composer phpcs` / `composer phpcbf` scripts so a fresh checkout
 * works in one command: if tools/phpcs/vendor is missing, it is installed on
 * demand. Unlike the post-install hook (maybe-install.php), an explicit phpcs
 * invocation on PHP < 8.2 errors clearly rather than skipping — the caller
 * asked to lint, so a confusing skip would be worse than an honest failure.
 *
 * Usage (via composer): composer phpcs | composer phpcbf
 *
 * @package CMB2
 */

$tool = $argv[1] ?? 'phpcs';
if ( ! in_array( $tool, array( 'phpcs', 'phpcbf' ), true ) ) {
	fwrite( STDERR, "usage: run.php phpcs|phpcbf\n" );
	exit( 2 );
}

if ( PHP_VERSION_ID < 80200 ) {
	fwrite( STDERR, 'phpcs requires PHP 8.2+ (the ruleset uses sniffers that need it); you are on ' . PHP_VERSION . ".\n" );
	exit( 1 );
}

$bin = __DIR__ . '/vendor/bin/' . $tool;

if ( ! is_file( $bin ) ) {
	$composer = getenv( 'COMPOSER_BINARY' );
	if ( ! $composer ) {
		fwrite( STDERR, "phpcs: tooling not installed and composer binary not detected. Run `composer phpcs:install`.\n" );
		exit( 1 );
	}

	fwrite( STDERR, "phpcs: tooling not installed; installing in tools/phpcs ...\n" );
	$install = escapeshellarg( PHP_BINARY )
		. ' ' . escapeshellarg( $composer )
		. ' install --working-dir=' . escapeshellarg( __DIR__ )
		. ' --no-interaction --no-progress';
	passthru( $install, $install_code );

	if ( 0 !== $install_code || ! is_file( $bin ) ) {
		fwrite( STDERR, "phpcs: tooling install failed.\n" );
		exit( $install_code ?: 1 );
	}
}

// Run from the repo root so the .phpcs.xml.dist and includes/ init.php paths resolve.
passthru( escapeshellarg( $bin ) . ' --standard=.phpcs.xml.dist includes/ init.php', $code );
exit( $code );
