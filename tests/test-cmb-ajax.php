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
	protected $subscriber;
	protected $editor;
	protected $administrator;

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

		$this->subscriber    = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->editor        = $this->factory->user->create( array( 'role' => 'editor' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );

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

		// The box registered in set_up() owns this option key, and its 'capability'
		// prop is the default `manage_options`.
		wp_set_current_user( $this->administrator );

		$_REQUEST['cmb2_ajax_nonce'] = wp_create_nonce( 'ajax_nonce' );
		$_REQUEST['oembed_url']      = $this->oembed_args['url'];
		$_REQUEST['object_id']       = 'options-page-id';
		$_REQUEST['object_type']     = 'options-page';
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
			unset( $_REQUEST['cmb2_ajax_nonce'], $_REQUEST['oembed_url'], $_REQUEST['object_id'], $_REQUEST['object_type'], $_REQUEST['field_id'] );
		}

		$this->assertTrue( $reached_send_json, 'oembed_handler() should handle an array field_id without a PHP error.' );

		$decoded = json_decode( $json, true );
		$this->assertTrue( ! empty( $decoded['success'] ), 'Handler should return a successful JSON response.' );
		// The array field_id collapses to '' and is escaped into an empty rel attribute.
		$this->assertStringContainsString( 'rel=""', $decoded['data'] );
	}

	/**
	 * A successful lookup is cached against the object named in the request, so the
	 * handler must only accept an object the caller may already edit. A subscriber
	 * has no edit rights over someone else's post.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_post_target_without_edit_rights() {
		$post_id = $this->factory->post->create( array( 'post_author' => $this->editor ) );

		wp_set_current_user( $this->subscriber );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $post_id,
			'object_type' => 'post',
		) );

		$this->assertFalse( $response['success'], 'A caller without edit rights over the post should not get a success response.' );
		$this->assertSame( array(), $this->oembed_cache_keys( get_metadata( 'post', $post_id ) ), 'Nothing should have been cached against the post.' );
	}

	/**
	 * The legitimate flow: someone editing a post gets the preview, and the result
	 * is cached against that post.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_serves_post_target_with_edit_rights() {
		$post_id = $this->factory->post->create( array( 'post_author' => $this->editor ) );

		wp_set_current_user( $this->editor );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $post_id,
			'object_type' => 'post',
		) );

		$this->assertTrue( $response['success'], 'The post author should still get the preview.' );
		$this->assertNotEmpty( $this->oembed_cache_keys( get_metadata( 'post', $post_id ) ), 'The result should be cached against the post.' );
	}

	/**
	 * A subscriber may edit their own user, but not another one.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_user_target_without_edit_rights() {
		wp_set_current_user( $this->subscriber );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $this->editor,
			'object_type' => 'user',
		) );

		$this->assertFalse( $response['success'], 'A caller without edit rights over the user should not get a success response.' );
		$this->assertSame( array(), $this->oembed_cache_keys( get_metadata( 'user', $this->editor ) ), 'Nothing should have been cached against the user.' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_serves_user_target_with_edit_rights() {
		wp_set_current_user( $this->administrator );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $this->subscriber,
			'object_type' => 'user',
		) );

		$this->assertTrue( $response['success'], 'A caller who may edit the user should still get the preview.' );
		$this->assertNotEmpty( $this->oembed_cache_keys( get_metadata( 'user', $this->subscriber ) ), 'The result should be cached against the user.' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_comment_target_without_edit_rights() {
		$comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $this->factory->post->create(),
		) );

		wp_set_current_user( $this->subscriber );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $comment_id,
			'object_type' => 'comment',
		) );

		$this->assertFalse( $response['success'], 'A caller without edit rights over the comment should not get a success response.' );
		$this->assertSame( array(), $this->oembed_cache_keys( get_metadata( 'comment', $comment_id ) ), 'Nothing should have been cached against the comment.' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_serves_comment_target_with_edit_rights() {
		$comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $this->factory->post->create(),
		) );

		wp_set_current_user( $this->editor );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $comment_id,
			'object_type' => 'comment',
		) );

		$this->assertTrue( $response['success'], 'A caller who may edit the comment should still get the preview.' );
		$this->assertNotEmpty( $this->oembed_cache_keys( get_metadata( 'comment', $comment_id ) ), 'The result should be cached against the comment.' );
	}

	/**
	 * Term targets follow the taxonomy's own `edit_terms` capability, the same
	 * check CMB2_Hookup::taxonomy_can_save() makes before writing term meta.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_term_target_without_taxonomy_rights() {
		$term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );

		wp_set_current_user( $this->subscriber );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $term_id,
			'object_type' => 'term',
		) );

		$this->assertFalse( $response['success'], 'A caller without the taxonomy capability should not get a success response.' );
		$this->assertSame( array(), $this->oembed_cache_keys( get_metadata( 'term', $term_id ) ), 'Nothing should have been cached against the term.' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_serves_term_target_with_taxonomy_rights() {
		$term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );

		wp_set_current_user( $this->editor );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $term_id,
			'object_type' => 'term',
		) );

		$this->assertTrue( $response['success'], 'A caller with the taxonomy capability should still get the preview.' );
		$this->assertNotEmpty( $this->oembed_cache_keys( get_metadata( 'term', $term_id ) ), 'The result should be cached against the term.' );
	}

	/**
	 * On an options-page target the object id IS the option name the result is
	 * cached into, so the gate is the registered box's own 'capability' prop
	 * (default `manage_options`) — the same value that governs its admin screen.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_options_page_target_without_box_capability() {
		wp_set_current_user( $this->subscriber );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $this->oembed_args['object_id'],
			'object_type' => 'options-page',
		) );

		$this->assertFalse( $response['success'], "A caller without the box's capability should not get a success response." );
		$this->assertSame( array(), $this->oembed_cache_keys( get_option( $this->oembed_args['object_id'] ) ), 'Nothing should have been cached into the option.' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_serves_options_page_target_with_box_capability() {
		wp_set_current_user( $this->administrator );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $this->oembed_args['object_id'],
			'object_type' => 'options-page',
		) );

		$this->assertTrue( $response['success'], "A caller holding the box's capability should still get the preview." );
		$this->assertNotEmpty( $this->oembed_cache_keys( get_option( $this->oembed_args['object_id'] ) ), 'The result should be cached into the option.' );
	}

	/**
	 * An options-page object id that no registered box owns has no box capability
	 * to consult, so it is not a valid target — not even for an administrator.
	 * This keeps the option name the handler writes to inside the set of option
	 * keys CMB2 boxes actually declared.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_options_page_target_no_box_declared() {
		$unowned = 'an-unregistered-option-key';

		wp_set_current_user( $this->administrator );

		$response = $this->request_oembed_handler( array(
			'object_id'   => $unowned,
			'object_type' => 'options-page',
		) );

		$this->assertFalse( $response['success'], 'An option key no box declared should not be an accepted target.' );
		$this->assertFalse( get_option( $unowned ), 'The option should not have been created.' );

		delete_option( $unowned );
	}

	/**
	 * object_type arrives in the request and decides which store the result is
	 * written to, so a value outside CMB2's core object types is not a target.
	 *
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed_handler_declines_unrecognized_object_type() {
		wp_set_current_user( $this->administrator );

		$response = $this->request_oembed_handler( array(
			'object_id'   => 1,
			'object_type' => 'not-an-object-type',
		) );

		$this->assertFalse( $response['success'], 'An unrecognized object_type should not be an accepted target.' );
	}

	/**
	 * Runs oembed_handler() with a valid nonce and the given request parameters,
	 * and returns the decoded JSON response it terminated with.
	 *
	 * @param  array $params Request parameters to add to (or override in) the request.
	 * @return array         The decoded JSON response.
	 */
	protected function request_oembed_handler( array $params ) {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'throw_oembed_die_handler' ), 99 );

		$backup   = $_REQUEST;
		$_REQUEST = array_merge( array(
			'cmb2_ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
			'oembed_url'      => $this->oembed_args['url'],
			'field_id'        => $this->oembed_args['field_id'],
		), $params );

		$json = '';

		ob_start();
		try {
			cmb2_ajax()->oembed_handler();
		} catch ( CMB2_Test_Oembed_Die $e ) {
			$json = ob_get_contents();
		} finally {
			ob_end_clean();
			$_REQUEST = $backup;
			$this->reset_cmb2_ajax_state();
		}

		$decoded = json_decode( $json, true );

		$this->assertIsArray( $decoded, 'oembed_handler() should terminate with a JSON response.' );

		return $decoded;
	}

	/**
	 * Picks the oEmbed cache keys out of an object's metadata (or an options-page
	 * option value), so a test can assert whether a cache write happened.
	 *
	 * @param  mixed $data Metadata array, option value, or false.
	 * @return array       The oEmbed cache keys found.
	 */
	protected function oembed_cache_keys( $data ) {
		$found = array();

		foreach ( (array) $data as $key => $unused ) {
			if ( is_string( $key ) && 0 === strpos( $key, '_oembed_' ) ) {
				$found[] = $key;
			}
		}

		return $found;
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
