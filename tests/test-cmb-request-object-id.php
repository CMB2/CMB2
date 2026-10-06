<?php
/**
 * Request-derived object ID tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-tests-base.php' );

/**
 * Object IDs read from the request are integers, and the oEmbed field
 * escapes its data attributes.
 */
class Test_CMB2_Request_Object_ID extends CMB2TestCase {

	const NON_NUMERIC_ID = "1' autofocus onfocus=alert(1) x";

	protected $saved_request;
	protected $saved_pagenow;
	protected $saved_user_id;
	protected $admin_id;

	public function set_up() {
		parent::set_up();
		$this->saved_request = $_REQUEST;
		$this->saved_pagenow = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : null;
		$this->saved_user_id = isset( $GLOBALS['user_ID'] ) ? $GLOBALS['user_ID'] : null;
		$this->admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_id );
	}

	public function tear_down() {
		$_REQUEST           = $this->saved_request;
		$GLOBALS['pagenow'] = $this->saved_pagenow;
		$GLOBALS['user_ID'] = $this->saved_user_id;
		unset( $GLOBALS['post'] );
		remove_all_filters( 'cmb2_set_object_id' );
		parent::tear_down();
	}

	protected function new_box( $id, $types ) {
		$cmb = new CMB2( array(
			'id'           => $id,
			'object_types' => $types,
			'taxonomies'   => array( 'category' ),
		) );
		$cmb->add_field( array(
			'name' => 'Embed',
			'id'   => $id . '_oembed',
			'type' => 'oembed',
		) );

		return $cmb;
	}

	/**
	 * Attributes of the rendered oEmbed input, as a browser would parse them.
	 */
	protected function oembed_input_attrs( $html ) {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<html><body>' . $html . '</body></html>' );
		libxml_clear_errors();

		foreach ( $doc->getElementsByTagName( 'input' ) as $input ) {
			if ( false !== strpos( $input->getAttribute( 'class' ), 'cmb2-oembed' ) ) {
				$attrs = array();
				foreach ( $input->attributes as $attr ) {
					$attrs[ $attr->name ] = $attr->value;
				}
				return $attrs;
			}
		}

		$this->fail( 'No oEmbed input rendered.' );
	}

	protected function assertOembedObjectId( $expected, $html ) {
		$attrs = $this->oembed_input_attrs( $html );
		$this->assertSame(
			array( 'type', 'class', 'name', 'id', 'value', 'data-objectid', 'data-objecttype', 'data-hash' ),
			array_keys( $attrs )
		);
		$this->assertSame( (string) $expected, $attrs['data-objectid'] );
	}

	protected function capture( $callable, $args = array() ) {
		ob_start();
		call_user_func_array( $callable, $args );
		return ob_get_clean();
	}

	// WordPress slashes superglobals, so request values arrive slashed.
	protected function set_request( $key, $value ) {
		$_REQUEST[ $key ] = wp_slash( $value );
	}

	public function test_user_edit_screen_uses_integer_user_id() {
		$GLOBALS['pagenow'] = 'user-edit.php';
		$this->set_request( 'user_id', self::NON_NUMERIC_ID );
		$hookup = new CMB2_Hookup( $this->new_box( 'request_id_user_edit', array( 'user' ) ) );

		$this->assertOembedObjectId( 1, $this->capture( array( $hookup, 'user_metabox' ) ) );
	}

	public function test_user_new_screen_uses_integer_user_id() {
		$GLOBALS['pagenow'] = 'user-new.php';
		$this->set_request( 'user_id', self::NON_NUMERIC_ID );
		$hookup = new CMB2_Hookup( $this->new_box( 'request_id_user_new', array( 'user' ) ) );

		$this->assertOembedObjectId( 1, $this->capture( array( $hookup, 'user_new_metabox' ), array( 'add-new-user' ) ) );
	}

	public function test_term_edit_screen_uses_integer_term_id() {
		$GLOBALS['pagenow'] = 'term.php';
		$this->set_request( 'tag_ID', self::NON_NUMERIC_ID );
		$hookup = new CMB2_Hookup( $this->new_box( 'request_id_term', array( 'term' ) ) );

		$this->assertOembedObjectId( 1, $this->capture( array( $hookup, 'term_metabox' ) ) );
	}

	public function test_front_end_post_form_uses_integer_post_id() {
		unset( $GLOBALS['post'] );
		$this->set_request( 'post', self::NON_NUMERIC_ID );

		$this->assertOembedObjectId( 1, cmb2_get_metabox_form( $this->new_box( 'request_id_front_post', array( 'post' ) ) ) );
	}

	public function test_front_end_user_form_uses_integer_user_id() {
		unset( $GLOBALS['post'] );
		$this->set_request( 'user_id', self::NON_NUMERIC_ID );

		$this->assertOembedObjectId( 1, cmb2_get_metabox_form( $this->new_box( 'request_id_front_user', array( 'user' ) ) ) );
	}

	public function test_comment_object_id_is_integer() {
		$GLOBALS['pagenow'] = 'comment.php';
		$this->set_request( 'c', self::NON_NUMERIC_ID );
		$cmb = $this->new_box( 'request_id_comment', array( 'comment' ) );

		$this->assertSame( 1, $cmb->object_id() );
	}

	public function test_oembed_escapes_object_id_from_filter() {
		unset( $GLOBALS['post'] );
		add_filter( 'cmb2_set_object_id', function () {
			return self::NON_NUMERIC_ID;
		} );

		$attrs = $this->oembed_input_attrs( cmb2_get_metabox_form( $this->new_box( 'request_id_filtered', array( 'post' ) ) ) );

		$this->assertSame( self::NON_NUMERIC_ID, $attrs['data-objectid'] );
		$this->assertArrayNotHasKey( 'onfocus', $attrs );
	}

	public function test_object_id_filter_receives_integer() {
		$GLOBALS['pagenow'] = 'term.php';
		$this->set_request( 'tag_ID', '7' );
		$received = null;
		add_filter( 'cmb2_set_object_id', function ( $object_id ) use ( &$received ) {
			$received = $object_id;
			return $object_id;
		} );

		$this->new_box( 'request_id_filter', array( 'term' ) )->object_id();

		$this->assertSame( 7, $received );
	}

	/*
	 * A request ID with no leading digits casts to 0. The fallback it then
	 * reaches must be the object WordPress itself shows on that screen.
	 */

	public function test_non_numeric_user_id_on_profile_falls_back_to_current_user() {
		// profile.php loads user-edit.php, which shows the current user when user_id casts to 0.
		$GLOBALS['pagenow'] = 'profile.php';
		$GLOBALS['user_ID'] = $this->admin_id;
		$this->set_request( 'user_id', 'abc' );

		$this->assertSame( $this->admin_id, $this->new_box( 'request_id_profile', array( 'user' ) )->object_id() );
	}

	public function test_non_numeric_user_id_on_user_new_has_no_object() {
		$GLOBALS['pagenow'] = 'user-new.php';
		$GLOBALS['user_ID'] = $this->admin_id;
		$this->set_request( 'user_id', 'abc' );
		$cmb    = $this->new_box( 'request_id_user_new_fallback', array( 'user' ) );
		$hookup = new CMB2_Hookup( $cmb );

		$this->capture( array( $hookup, 'user_new_metabox' ), array( 'add-new-user' ) );

		$this->assertSame( 0, $cmb->object_id() );
	}

	public function test_non_numeric_post_id_does_not_fall_back_to_global_post() {
		$GLOBALS['post'] = get_post( self::factory()->post->create() );
		$this->set_request( 'post', 'abc' );

		$this->assertSame( 0, $this->new_box( 'request_id_post_fallback', array( 'post' ) )->object_id() );
	}

	public function test_array_post_id_has_no_object() {
		// edit.php bulk actions send post[]; casting an array would yield 1.
		$GLOBALS['post'] = get_post( self::factory()->post->create() );
		$_REQUEST['post'] = array( '5', '6' );

		$this->assertSame( 0, $this->new_box( 'request_id_post_array', array( 'post' ) )->object_id() );
	}

	public function test_array_user_id_falls_back_to_current_user() {
		$GLOBALS['pagenow'] = 'profile.php';
		$GLOBALS['user_ID'] = $this->admin_id;
		$_REQUEST['user_id'] = array( '5' );

		$this->assertSame( $this->admin_id, $this->new_box( 'request_id_user_array', array( 'user' ) )->object_id() );
	}
}
