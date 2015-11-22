<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Edit 'active_plugins' setting below to point to your main plugin file.
 *
 * @package wordpress-plugin-tests
 */

// Support for:
// 1. `WP_DEVELOP_DIR` environment variable
// 2. Plugin installed inside of WordPress.org developer checkout
// 3. Tests checked out to /tmp
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' );
} elseif ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$test_root = getenv( 'WP_TESTS_DIR' );
} else if ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} else if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
}

require_once $test_root . '/includes/functions.php';

// Activates this plugin in WordPress so it can be tested.
function _tests_cmb2_manually_load_plugin() {
	define( 'CMB2_TESTDATA', dirname( __FILE__ ) . '/data' );
	if ( ! defined( 'WP_ADMIN' ) ) {
		define( 'WP_ADMIN', true );
	}

	require dirname( __FILE__ ) . '/../init.php';
}
tests_add_filter( 'muplugins_loaded', '_tests_cmb2_manually_load_plugin' );

require $test_root . '/includes/bootstrap.php';

