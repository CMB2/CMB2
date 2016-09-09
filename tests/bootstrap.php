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
	$test_root = getenv( 'WP_DEVELOP_DIR' );
} elseif ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$test_root = getenv( 'WP_TESTS_DIR' );
} elseif ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
}

require_once $test_root . '/includes/functions.php';

/**
 * Activates the CMB2 plugin in WordPress so it can be tested.
 *
 */
function _tests_cmb2_manually_load_plugin() {
	define( 'CMB2_TESTDATA', dirname( __FILE__ ) . '/data' );
	if ( ! defined( 'WP_ADMIN' ) ) {
		define( 'WP_ADMIN', true );
	}

	$plugin = dirname( dirname( __FILE__ ) );
	$wp_content = dirname( dirname( $plugin ) );
	$rest_api = $wp_content . '/plugins/WP-API/plugin.php';
	if ( file_exists( $rest_api ) ) {
		require_once $rest_api;
	} else {
		$rest_api = $wp_content . '/plugins/rest-api/plugin.php';
		if ( file_exists( $rest_api ) ) {
			require_once $rest_api;
		}
	}
	require_once $plugin . '/init.php';
}
tests_add_filter( 'muplugins_loaded', '_tests_cmb2_manually_load_plugin' );

require $test_root . '/includes/bootstrap.php';

