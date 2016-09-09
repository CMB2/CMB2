<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Support for:
 *
 * 1. `WP_DEVELOP_DIR` and `WP_TESTS_DIR` environment variables
 * 2. Plugin installed inside of WordPress.org developer checkout
 * 3. Tests checked out to /tmp
 *
 * @package CMB2
 */

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$GLOBALS['test_root'] = getenv( 'WP_DEVELOP_DIR' );
} elseif ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$GLOBALS['test_root'] = getenv( 'WP_TESTS_DIR' );
} elseif ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$GLOBALS['test_root'] = '../../../../tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$GLOBALS['test_root'] = '/tmp/wordpress-tests-lib';
}

require_once $GLOBALS['test_root'] . '/includes/functions.php';

/**
 * Activates the CMB2 plugin in WordPress so it can be tested.
 *
 */
function _tests_cmb2_manually_load_plugin() {
	global $test_root;

	define( 'CMB2_TESTDATA', dirname( __FILE__ ) . '/data' );
	if ( ! defined( 'WP_ADMIN' ) ) {
		define( 'WP_ADMIN', true );
	}

	$plugin = dirname( dirname( __FILE__ ) );

	define( 'CMB2_WP_CONTENT', dirname( dirname( $plugin ) ) );

	if (
		( $rest_api = '/tmp/wordpress/wp-content/plugins/rest-api/plugin.php' )
		&& file_exists( $rest_api )
	) {
		require_once $rest_api;
	} elseif (
		( $rest_api = CMB2_WP_CONTENT . '/plugins/rest-api/plugin.php' )
		&& file_exists( $rest_api )
	) {
		require_once $rest_api;
	} elseif (
		( $rest_api = CMB2_WP_CONTENT . '/plugins/WP-API/plugin.php' )
		&& file_exists( $rest_api )
	) {
		require_once $rest_api;
	}

	require_once $plugin . '/init.php';
}
tests_add_filter( 'muplugins_loaded', '_tests_cmb2_manually_load_plugin' );

require $GLOBALS['test_root'] . '/includes/bootstrap.php';

