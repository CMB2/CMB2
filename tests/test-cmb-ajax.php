<?php
/**
 * CMB2_Field tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-tests-base.php' );

/**
 * Test the oEmbed functionality
 */
class Test_CMB2_Ajax extends CMB2TestCase {

	// Ajax-specific test properties
	protected $oembed_args;

	/**
	 * Set up the test fixture
	 */
	public function set_up() {
		parent::set_up();

		// Mock oembed HTTP requests to avoid flaky external API calls.
		add_filter( 'pre_http_request', array( $this, 'mock_oembed_request' ), 10, 3 );

		$this->cmb = cmb2_get_metabox( array(
			'id'      => 'metabox_id',
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => 'options-page-id',
			),
			'fields' => array(
				array(
					'id'   => 'test_embed',
					'type' => 'oembed',
				),
				array(
					'id'   => 'another_value',
					'type' => 'text',
				),
			),
		), 'options-page-id', 'options-page' );

		$this->oembed_args = array(
			'url'         => 'https://www.youtube.com/watch?v=NCXyEKqmWdA',
			'object_id'   => 'options-page-id',
			'object_type' => 'options-page',
			'oembed_args' => array(
				'width' => '640',
			),
			'field_id'    => 'test_embed',
			'src'         => 'https://www.youtube.com/embed/NCXyEKqmWdA?feature=oembed',
		);

	}

	public function tear_down() {
		delete_option( $this->oembed_args['object_id'] );
		remove_filter( 'pre_http_request', array( $this, 'mock_oembed_request' ), 10 );

		// Reset the CMB2_Ajax singleton's per-request state so it does not leak
		// into later tests/classes. get_oembed()/oembed_handler() set ajax_update,
		// hijack, object_id/type and register the hijack_* metadata filters on the
		// shared instance; left dirty, they can flip a later network-dependent
		// oEmbed test onto its fallback path.
		$this->reset_cmb2_ajax_state();

		parent::tear_down();
	}

	/**
	 * Clears the CMB2_Ajax singleton's mutable state and the metadata filters it
	 * registers, restoring constructor defaults between tests.
	 */
	protected function reset_cmb2_ajax_state() {
		$ajax = cmb2_ajax();

		remove_filter( 'get_post_metadata', array( $ajax, 'hijack_oembed_cache_get' ), 10 );
		remove_filter( 'update_post_metadata', array( $ajax, 'hijack_oembed_cache_set' ), 10 );

		$reset = Closure::bind( function () {
			$this->hijack      = false;
			$this->object_id   = 0;
			$this->object_type = 'post';
			$this->ajax_update = false;
		}, $ajax, CMB2_Ajax::class );
		$reset();
	}

	/**
	 * Mock HTTP requests for oembed endpoints to return deterministic responses.
	 */
	public function mock_oembed_request( $preempt, $parsed_args, $url ) {
		// YouTube oembed API
		if ( false !== strpos( $url, 'youtube.com/oembed' ) ) {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'type'          => 'video',
					'provider_name' => 'YouTube',
					'title'         => 'Hello - Adele',
					'html'          => '<iframe width="640" height="360" src="https://www.youtube.com/embed/NCXyEKqmWdA?feature=oembed" frameborder="0" allowfullscreen title="Hello - Adele"></iframe>',
					'width'         => 640,
					'height'        => 360,
				) ),
			);
		}

		// Twitter/X oembed API
		if ( false !== strpos( $url, 'publish.twitter.com' ) ) {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array(
					'type'          => 'rich',
					'provider_name' => 'Twitter',
					'html'          => '<blockquote class="twitter-tweet" data-width="550" data-dnt="true"><p lang="en" dir="ltr">That time we did Adele’s “Hello” at @generationschch…<a href="https://t.co/aq89T5VM5x">https://t.co/aq89T5VM5x</a></p>&mdash; Justin Sternberg (@Jtsternberg) <a href="https://twitter.com/Jtsternberg/status/703434891518726144?ref_src=twsrc%5Etfw">February 27, 2016</a></blockquote><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>',
					'width'         => 550,
				) ),
			);
		}

		return $preempt;
	}

	public function test_cmb2_ajax_instance() {
		$this->assertInstanceOf( 'CMB2_Ajax', cmb2_ajax() );
	}

	public function test_correct_properties() {
		$this->assertEquals( $this->oembed_args['object_id'], $this->cmb->object_id() );
		$this->assertEquals( $this->oembed_args['object_type'], $this->cmb->object_type() );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_get_oembed() {
		$args = $this->oembed_args;

		$args['oembed_result'] = array(
			sprintf( '<iframe width="640" height="360" src="%s"', $args['src'] ),
			'></iframe>',
		);

		if ( CMB2_Utils::wp_at_least( '5.2.0' ) ) {
			$args['oembed_result'][0] = str_replace( 'iframe ', 'iframe title="Hello - Adele" ', $args['oembed_result'][0] );
		}

		$this->assertOEmbedResult( $args );

		// Test another oembed URL
		$args['url'] = 'https://twitter.com/Jtsternberg/status/703434891518726144';

		$args['oembed_result'] = array(
			'<blockquote class="twitter-tweet" ',
			'<p lang="en" dir="ltr">That time we did Adele’s “Hello” at ',
			'<a href="https://t.co/aq89T5VM5x">https://t.co/aq89T5VM5x</a></p>&mdash; Justin Sternberg (@Jtsternberg) ',
			sprintf( '<a href="%1$s', $args['url'] ),
			'">February 27, 2016</a></blockquote><script async src="',
			'platform.twitter.com/widgets.js',
			'</script>',
		);

		$this->assertOEmbedResult( $args );
	}

	/**
	 * The field_id is reflected into the rel="" attribute of the remove-embed
	 * link, so it must be escaped with esc_attr() to prevent it breaking out of
	 * the attribute (XSS / WP Plugin Check violation).
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_get_oembed_escapes_field_id_in_rel_attribute() {
		$args = $this->oembed_args;
		$args['field_id'] = 'evil" onmouseover="alert(1)';
		unset( $args['src'] );

		$actual = cmb2_ajax()->get_oembed( $args );

		// The attribute-breaking value must not be reflected verbatim.
		$this->assertStringNotContainsString( 'rel="evil" onmouseover="alert(1)"', $actual );

		// It must appear in its escaped form instead.
		$this->assertStringContainsString( 'rel="' . esc_attr( $args['field_id'] ) . '"', $actual );
	}

	/**
	 * oembed_handler() reads $_REQUEST['object_id'] directly. Without an isset()
	 * guard, a request missing object_id emits an "Undefined array key" warning
	 * (PHP 8+) — the same class of notice the field_id guard in this PR prevents.
	 * The handler should reach wp_send_json_success() without a PHP warning.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_tolerates_missing_object_id() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'throw_oembed_die_handler' ), 99 );

		$_REQUEST['cmb2_ajax_nonce'] = wp_create_nonce( 'ajax_nonce' );
		$_REQUEST['oembed_url']      = $this->oembed_args['url'];
		$_REQUEST['field_id']        = 'test_embed';
		unset( $_REQUEST['object_id'] ); // The param under test is intentionally absent.

		$reached_send_json = false;
		$json              = '';

		ob_start();
		try {
			cmb2_ajax()->oembed_handler();
		} catch ( CMB2_Test_Oembed_Die $e ) {
			$reached_send_json = true;
			$json              = ob_get_contents();
		} finally {
			ob_end_clean();
			unset( $_REQUEST['cmb2_ajax_nonce'], $_REQUEST['oembed_url'], $_REQUEST['field_id'] );
		}

		$this->assertTrue( $reached_send_json, 'oembed_handler() should reach wp_send_json_success() without a PHP warning when object_id is missing.' );

		$decoded = json_decode( $json, true );
		$this->assertTrue( ! empty( $decoded['success'] ), 'Handler should return a successful JSON response.' );
	}

	/**
	 * A field_id submitted as an array (?field_id[]=x) must be handled safely.
	 * WordPress's sanitize_text_field() returns '' for array/object input (and
	 * wp_unslash() is array-safe), so sanitize_text_field( wp_unslash( array ) )
	 * yields '' with no PHP error — no is_string() guard is required. This locks
	 * that behavior so the guard is not reintroduced on a false TypeError premise.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_handles_array_field_id() {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'throw_oembed_die_handler' ), 99 );

		$_REQUEST['cmb2_ajax_nonce'] = wp_create_nonce( 'ajax_nonce' );
		$_REQUEST['oembed_url']      = $this->oembed_args['url'];
		$_REQUEST['object_id']       = 'options-page-id';
		$_REQUEST['field_id']        = array( 'unexpected', 'array' ); // The odd input under test.

		$reached_send_json = false;
		$json              = '';

		ob_start();
		try {
			cmb2_ajax()->oembed_handler();
		} catch ( CMB2_Test_Oembed_Die $e ) {
			$reached_send_json = true;
			$json              = ob_get_contents();
		} finally {
			ob_end_clean();
			unset( $_REQUEST['cmb2_ajax_nonce'], $_REQUEST['oembed_url'], $_REQUEST['object_id'], $_REQUEST['field_id'] );
		}

		$this->assertTrue( $reached_send_json, 'oembed_handler() should handle an array field_id without a PHP error.' );

		$decoded = json_decode( $json, true );
		$this->assertTrue( ! empty( $decoded['success'] ), 'Handler should return a successful JSON response.' );
		// The array field_id collapses to '' and is escaped into an empty rel attribute.
		$this->assertStringContainsString( 'rel=""', $decoded['data'] );
	}

	/**
	 * Returns a wp_die handler (for the wp_die_ajax_handler filter) that throws a
	 * marker exception, letting the test observe that wp_die() was reached.
	 */
	public function throw_oembed_die_handler() {
		return function () {
			throw new CMB2_Test_Oembed_Die();
		};
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_values_cached() {
		$options = $this->get_option();

		// Verify each cached oembed value exists and matches expected format.
		foreach ( $options as $key => $value ) {
			if ( 0 === strpos( $key, '_oembed_time_' ) ) {
				$this->assertTrue( is_int( $value ) );
			} elseif ( 0 === strpos( $key, '_oembed_' ) ) {
				$this->assertTrue( is_string( $value ) && strlen( $value ) > 0 );
			}
		}
	}

	public function test_get_oembed_delete_with_expired_ttl() {
		add_filter( 'oembed_ttl', '__return_zero' );
		add_action( 'cmb2_save_options-page_fields', array( 'CMB2_Ajax', 'clean_stale_options_page_oembeds' ) );

		$new = array(
			'another_value' => 'value',
		);
		if ( $this->is_3_8() ) {
			$new = array(
				'_oembed_887df34cb3e109936f1e848042f873a3' => '<iframe width="640" height="360" src="https://www.youtube.com/embed/NCXyEKqmWdA?feature=oembed" frameborder="0" allowfullscreen></iframe>',
			);
		}
		$_POST = array_merge( $new, $this->get_option() );

		$this->cmb->save_fields();
		$options = $this->get_option();

		if ( $this->is_3_8() ) {
			foreach ( array(
				'<iframe',
				'src="https://www.youtube.com/embed/NCXyEKqmWdA?feature=oembed"',
				'</iframe>',
			) as $part ) {
				$this->assertTrue( false !== strpos( $options['_oembed_887df34cb3e109936f1e848042f873a3'], $part ), $part );
			}
		} else {
			$this->assertEquals( $new, $options );
		}
	}

	protected function get_option() {
		return cmb2_options( $this->oembed_args['object_id'] )->get_options();
	}

	protected function is_3_8() {
		return ! CMB2_Utils::wp_at_least( '3.8.1' );
	}

}

/**
 * Marker exception thrown by the test's wp_die handler so a test can assert that
 * the AJAX handler reached wp_send_json_*() (which terminates via wp_die()).
 */
class CMB2_Test_Oembed_Die extends \Exception {}
