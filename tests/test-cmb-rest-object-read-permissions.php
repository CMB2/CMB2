<?php
/**
 * CMB2 REST field reads follow WordPress core's object read permissions.
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-rest-tests-base.php' );

/**
 * @group cmb2-rest-api
 */
class Test_CMB2_REST_Object_Read_Permissions extends Test_CMB2_Rest_Base {

	protected $editor;

	public function set_up() {
		$this->set_up_and_init( array(
			'id'           => 'obj_post_box',
			'show_in_rest' => WP_REST_Server::READABLE,
			'object_types' => array( 'post' ),
			'fields'       => array(
				'obj_field' => array(
					'name' => 'Obj Field',
					'id'   => 'obj_field',
					'type' => 'text',
				),
			),
		) );

		$this->editor = $this->factory->user->create( array(
			'role' => 'editor',
		) );
	}

	public function tear_down() {
		remove_all_filters( 'cmb2_api_get_field_permissions_check' );
		remove_all_filters( 'cmb2_api_get_fields_permissions_check' );
		remove_all_filters( 'cmb2_rest_enforce_user_read_permissions' );
		remove_all_filters( 'cmb2_field_defaults' );
		parent::tear_down();
	}

	protected function register_box( $args ) {
		$rest = new CMB2_REST( new CMB2( wp_parse_args( $args, array(
			'show_in_rest' => WP_REST_Server::READABLE,
			'fields'       => array(
				'obj_field' => array(
					'name' => 'Obj Field',
					'id'   => 'obj_field',
					'type' => 'text',
				),
			),
		) ) ) );
		$rest->universal_hooks();
	}

	protected function field_url( $cmb_id = 'obj_post_box', $field_id = 'obj_field' ) {
		return '/' . CMB2_REST::NAME_SPACE . '/boxes/' . $cmb_id . '/fields/' . $field_id;
	}

	protected function request( $url, $params ) {
		$request = new WP_REST_Request( 'GET', $url );
		foreach ( $params as $key => $value ) {
			$request[ $key ] = $value;
		}

		return rest_do_request( $request );
	}

	protected function read_field( $object_id, $object_type = 'post', $cmb_id = 'obj_post_box' ) {
		return $this->request( $this->field_url( $cmb_id ), array(
			'object_id'   => $object_id,
			'object_type' => $object_type,
		) );
	}

	protected function create_post( $status, $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_status' => $status,
		) );

		if ( 'future' === $status ) {
			$args['post_date'] = gmdate( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS );
		}

		$post_id = $this->factory->post->create( $args );
		update_post_meta( $post_id, 'obj_field', strtoupper( $status ) . '-VALUE' );

		return $post_id;
	}

	protected function user_route( $cmb_id = 'obj_user_box' ) {
		return '/' . CMB2_REST::NAME_SPACE . '/boxes/' . $cmb_id . '/fields/obj_field';
	}

	public function non_public_status_provider() {
		return array(
			array( 'draft' ),
			array( 'private' ),
			array( 'pending' ),
			array( 'future' ),
			array( 'trash' ),
		);
	}

	/**
	 * @dataProvider non_public_status_provider
	 */
	public function test_non_public_post_field_read_requires_read_post( $status ) {
		$post_id = $this->create_post( $status );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $post_id ), 'rest_forbidden' );

		wp_set_current_user( $this->editor );
		$response = $this->read_field( $post_id );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( strtoupper( $status ) . '-VALUE', $response->get_data()['value'] );
	}

	public function test_published_and_password_post_field_reads_stay_public() {
		wp_set_current_user( 0 );

		$post_id  = $this->create_post( 'publish' );
		$response = $this->read_field( $post_id );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'PUBLISH-VALUE', $response->get_data()['value'] );

		$post_id = $this->create_post( 'publish', array(
			'post_password' => 'secret',
		) );
		update_post_meta( $post_id, 'obj_field', 'PASSWORD-VALUE' );
		$response = $this->read_field( $post_id );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'PASSWORD-VALUE', $response->get_data()['value'] );
	}

	public function test_post_of_hidden_type_the_box_is_not_for_is_denied() {
		register_post_type( 'obj_hidden_cpt', array(
			'public'       => false,
			'show_in_rest' => false,
		) );
		$post_id = $this->create_post( 'publish', array(
			'post_type' => 'obj_hidden_cpt',
		) );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $post_id ), 'rest_forbidden' );

		wp_set_current_user( $this->editor );
		$this->assertResponseStatus( 200, $this->read_field( $post_id ) );

		// A box registered for the type keeps reading it publicly.
		$this->register_box( array(
			'id'           => 'obj_hidden_cpt_box',
			'object_types' => array( 'obj_hidden_cpt' ),
		) );
		wp_set_current_user( 0 );
		$response = $this->read_field( $post_id, 'post', 'obj_hidden_cpt_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'PUBLISH-VALUE', $response->get_data()['value'] );

		// Comments are not the box's post type, so their parent's type must be shown in REST.
		$this->register_box( array(
			'id'           => 'obj_comment_box',
			'object_types' => array( 'comment' ),
		) );
		$comment = $this->factory->comment->create( array(
			'comment_post_ID'  => $post_id,
			'comment_approved' => 1,
		) );
		$this->assertResponseStatus( 401, $this->read_field( $comment, 'comment', 'obj_comment_box' ) );
	}

	public function test_attachment_of_hidden_type_parent_is_denied() {
		register_post_type( 'obj_hidden_cpt', array(
			'public'       => false,
			'show_in_rest' => false,
		) );
		$this->register_box( array(
			'id'           => 'obj_attachment_box',
			'object_types' => array( 'attachment' ),
		) );
		$attachment_id = $this->factory->attachment->create( array(
			'post_parent'    => $this->create_post( 'publish', array(
				'post_type' => 'obj_hidden_cpt',
			) ),
			'post_mime_type' => 'image/jpeg',
		) );
		update_post_meta( $attachment_id, 'obj_field', 'ATTACHMENT-VALUE' );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $attachment_id, 'post', 'obj_attachment_box' ), 'rest_forbidden' );

		wp_set_current_user( $this->editor );
		$response = $this->read_field( $attachment_id, 'post', 'obj_attachment_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'ATTACHMENT-VALUE', $response->get_data()['value'] );
	}

	public function test_inherit_status_follows_parent() {
		wp_set_current_user( 0 );

		$draft      = $this->create_post( 'draft' );
		$attachment = $this->factory->attachment->create_object( 'file.jpg', $draft );
		$this->assertResponseStatus( 401, $this->read_field( $attachment ) );

		$orphan = $this->factory->attachment->create_object( 'orphan.jpg', 0 );
		$this->assertResponseStatus( 200, $this->read_field( $orphan ) );
	}

	public function test_rendered_object_id_only_read_is_gated() {
		$post_id = $this->create_post( 'draft' );
		wp_set_current_user( 0 );

		$response = $this->request( $this->field_url(), array(
			'object_id' => $post_id,
			'_rendered' => true,
		) );
		$this->assertResponseStatus( 401, $response, 'rest_forbidden' );
	}

	/**
	 * With no object_id param, the box falls back to the `post` request key, so the
	 * gate checks the object the box actually resolved.
	 */
	public function test_rendered_read_of_request_fallback_object_is_gated() {
		$post_id = $this->create_post( 'draft' );
		wp_set_current_user( 0 );

		$_REQUEST['post'] = $post_id;
		try {
			$response = $this->request( $this->field_url(), array(
				'_rendered' => true,
			) );
		} finally {
			unset( $_REQUEST['post'] );
		}

		$this->assertResponseStatus( 401, $response, 'rest_forbidden' );
	}

	public function test_fields_collection_for_hidden_object_is_denied() {
		$post_id = $this->create_post( 'draft' );
		$url     = '/' . CMB2_REST::NAME_SPACE . '/boxes/obj_post_box/fields';

		wp_set_current_user( 0 );
		$response = $this->request( $url, array(
			'object_id'   => $post_id,
			'object_type' => 'post',
		) );
		$this->assertResponseStatus( 401, $response, 'rest_forbidden' );

		wp_set_current_user( $this->editor );
		$response = $this->request( $url, array(
			'object_id'   => $post_id,
			'object_type' => 'post',
		) );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'DRAFT-VALUE', $response->get_data()['obj_field']['value'] );
	}

	public function test_permission_filters_have_final_say() {
		$post_id = $this->create_post( 'draft' );
		wp_set_current_user( 0 );

		add_filter( 'cmb2_api_get_field_permissions_check', '__return_true' );
		$response = $this->read_field( $post_id );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'DRAFT-VALUE', $response->get_data()['value'] );

		// The collection gates the object once, so its own filter is enough.
		remove_all_filters( 'cmb2_api_get_field_permissions_check' );
		add_filter( 'cmb2_api_get_fields_permissions_check', '__return_true' );
		$response = $this->request( '/' . CMB2_REST::NAME_SPACE . '/boxes/obj_post_box/fields', array(
			'object_id'   => $post_id,
			'object_type' => 'post',
		) );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'DRAFT-VALUE', $response->get_data()['obj_field']['value'] );
	}

	public function test_box_permission_callback_prop_restores_access() {
		$this->register_box( array(
			'id'                             => 'obj_cb_box',
			'object_types'                   => array( 'post' ),
			'get_field_permissions_check_cb' => '__return_true',
		) );
		$post_id = $this->create_post( 'draft' );
		wp_set_current_user( 0 );

		$response = $this->read_field( $post_id, 'post', 'obj_cb_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'DRAFT-VALUE', $response->get_data()['value'] );
	}

	/**
	 * A field built for one object must not serve its value for another object
	 * later in the same PHP process.
	 */
	public function test_field_value_follows_requested_object_across_requests() {
		$draft     = $this->create_post( 'draft' );
		$published = $this->create_post( 'publish' );
		wp_set_current_user( 0 );

		$this->assertResponseStatus( 401, $this->read_field( $draft ) );

		$response = $this->read_field( $published );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'PUBLISH-VALUE', $response->get_data()['value'] );
	}

	public function test_single_request_builds_each_field_once() {
		$post_id = $this->create_post( 'publish' );
		wp_set_current_user( 0 );

		$builds = 0;
		add_filter( 'cmb2_field_defaults', function( $defaults, $field_id ) use ( &$builds ) {
			if ( 'obj_field' === $field_id ) {
				$builds++;
			}
			return $defaults;
		}, 10, 2 );

		$this->assertResponseStatus( 200, $this->read_field( $post_id ) );
		$this->assertSame( 1, $builds );
	}

	public function test_unapproved_comment_field_read_requires_comment_access() {
		$this->register_box( array(
			'id'           => 'obj_comment_box',
			'object_types' => array( 'comment' ),
		) );

		$commenter  = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$approved   = $this->factory->comment->create( array(
			'comment_post_ID'  => $this->post_id,
			'comment_approved' => 1,
		) );
		$unapproved = $this->factory->comment->create( array(
			'comment_post_ID'  => $this->post_id,
			'comment_approved' => 0,
			'user_id'          => $commenter,
		) );
		update_comment_meta( $approved, 'obj_field', 'APPROVED-VALUE' );
		update_comment_meta( $unapproved, 'obj_field', 'UNAPPROVED-VALUE' );

		wp_set_current_user( 0 );
		$response = $this->read_field( $approved, 'comment', 'obj_comment_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'APPROVED-VALUE', $response->get_data()['value'] );

		$this->assertResponseStatus( 401, $this->read_field( $unapproved, 'comment', 'obj_comment_box' ), 'rest_forbidden' );

		wp_set_current_user( $this->subscriber );
		$this->assertResponseStatus( 403, $this->read_field( $unapproved, 'comment', 'obj_comment_box' ), 'rest_forbidden' );

		wp_set_current_user( $commenter );
		$this->assertResponseStatus( 200, $this->read_field( $unapproved, 'comment', 'obj_comment_box' ) );

		wp_set_current_user( $this->editor );
		$response = $this->read_field( $unapproved, 'comment', 'obj_comment_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'UNAPPROVED-VALUE', $response->get_data()['value'] );
	}

	public function test_approved_comment_on_hidden_post_is_denied() {
		$this->register_box( array(
			'id'           => 'obj_comment_box',
			'object_types' => array( 'comment' ),
		) );
		$comment = $this->factory->comment->create( array(
			'comment_post_ID'  => $this->create_post( 'draft' ),
			'comment_approved' => 1,
		) );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $comment, 'comment', 'obj_comment_box' ) );
	}

	public function test_approved_comment_on_password_post_requires_edit_post() {
		$this->register_box( array(
			'id'           => 'obj_comment_box',
			'object_types' => array( 'comment' ),
		) );
		$comment = $this->factory->comment->create( array(
			'comment_post_ID'  => $this->create_post( 'publish', array(
				'post_password' => 'secret',
			) ),
			'comment_approved' => 1,
		) );
		update_comment_meta( $comment, 'obj_field', 'PW-COMMENT-VALUE' );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $comment, 'comment', 'obj_comment_box' ) );

		wp_set_current_user( $this->editor );
		$response = $this->read_field( $comment, 'comment', 'obj_comment_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'PW-COMMENT-VALUE', $response->get_data()['value'] );
	}

	protected function set_up_user_box( $args = array() ) {
		$this->register_box( wp_parse_args( $args, array(
			'id'           => 'obj_user_box',
			'object_types' => array( 'user' ),
		) ) );

		$user_id = $this->factory->user->create( array( 'role' => 'author' ) );
		update_user_meta( $user_id, 'obj_field', 'USER-VALUE' );

		return $user_id;
	}

	public function test_hidden_user_read_allowed_with_deprecation_by_default() {
		$user_id = $this->set_up_user_box();
		wp_set_current_user( 0 );

		$fired = array();
		add_action( 'deprecated_argument_run', function( $function, $message, $version ) use ( &$fired ) {
			$fired[] = compact( 'function', 'message', 'version' );
		}, 10, 3 );
		$this->setExpectedDeprecated( $this->user_route() );

		$response = $this->read_field( $user_id, 'user', 'obj_user_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'USER-VALUE', $response->get_data()['value'] );

		$this->assertCount( 1, $fired );
		$this->assertSame( '2.13.4', $fired[0]['version'] );
		$this->assertStringContainsString( 'rest_enforce_user_read_permissions', $fired[0]['message'] );
		$this->assertStringContainsString( 'In a future version', $fired[0]['message'] );
	}

	public function test_hidden_user_read_denied_when_enforced_by_filter() {
		$user_id = $this->set_up_user_box();
		add_filter( 'cmb2_rest_enforce_user_read_permissions', '__return_true' );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $user_id, 'user', 'obj_user_box' ), 'rest_forbidden' );

		$response = $this->request( $this->user_route(), array(
			'object_id' => $user_id,
			'_rendered' => true,
		) );
		$this->assertResponseStatus( 401, $response );

		wp_set_current_user( $this->subscriber );
		$this->assertResponseStatus( 403, $this->read_field( $user_id, 'user', 'obj_user_box' ), 'rest_forbidden' );

		$response = $this->request( '/' . CMB2_REST::NAME_SPACE . '/boxes/obj_user_box/fields', array(
			'object_id'   => $user_id,
			'object_type' => 'user',
		) );
		$this->assertResponseStatus( 403, $response, 'rest_forbidden' );
	}

	public function test_explicit_user_switch_values_do_not_fire_deprecation() {
		// Prop true enforces, even with the filter off.
		$user_id = $this->set_up_user_box( array(
			'id'                                 => 'obj_user_box',
			'rest_enforce_user_read_permissions' => true,
		) );
		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $user_id, 'user', 'obj_user_box' ) );

		// Prop false keeps reads public, and wins over the filter.
		$this->register_box( array(
			'id'                                 => 'obj_user_box_public',
			'object_types'                       => array( 'user' ),
			'rest_enforce_user_read_permissions' => false,
		) );
		add_filter( 'cmb2_rest_enforce_user_read_permissions', '__return_true' );
		$response = $this->read_field( $user_id, 'user', 'obj_user_box_public' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'USER-VALUE', $response->get_data()['value'] );

		// A filter returning false is an explicit choice too.
		remove_all_filters( 'cmb2_rest_enforce_user_read_permissions' );
		add_filter( 'cmb2_rest_enforce_user_read_permissions', '__return_false' );
		$this->assertResponseStatus( 200, $this->read_field( $user_id, 'user', 'obj_user_box_public' ) );
		$this->register_box( array(
			'id'           => 'obj_user_box_default',
			'object_types' => array( 'user' ),
		) );
		$this->assertResponseStatus( 200, $this->read_field( $user_id, 'user', 'obj_user_box_default' ) );
	}

	public function test_filter_passing_through_its_input_does_not_declare_a_choice() {
		$user_id = $this->set_up_user_box();
		$this->register_box( array(
			'id'           => 'obj_user_box_other',
			'object_types' => array( 'user' ),
		) );
		add_filter( 'cmb2_rest_enforce_user_read_permissions', function( $enforce, $cmb ) {
			return 'obj_user_box_other' === $cmb->cmb_id ? true : $enforce;
		}, 10, 2 );

		wp_set_current_user( 0 );
		$this->assertResponseStatus( 401, $this->read_field( $user_id, 'user', 'obj_user_box_other' ) );

		$this->setExpectedDeprecated( $this->user_route() );
		$response = $this->read_field( $user_id, 'user', 'obj_user_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'USER-VALUE', $response->get_data()['value'] );
	}

	public function test_core_visible_user_reads_stay_allowed_without_deprecation() {
		$user_id = $this->set_up_user_box();
		add_filter( 'cmb2_rest_enforce_user_read_permissions', '__return_true' );
		$read_user = function() use ( $user_id ) {
			return $this->read_field( $user_id, 'user', 'obj_user_box' );
		};

		// Self.
		wp_set_current_user( $user_id );
		$this->assertResponseStatus( 200, $read_user() );

		// edit_user + list_users.
		wp_set_current_user( $this->administrator );
		$this->assertResponseStatus( 200, $read_user() );

		// list_users alone.
		$lister = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		get_userdata( $lister )->add_cap( 'list_users' );
		wp_set_current_user( $lister );
		$this->assertResponseStatus( 200, $read_user() );

		// A user with published posts is public, and with the switch off fires nothing.
		remove_all_filters( 'cmb2_rest_enforce_user_read_permissions' );
		$this->factory->post->create( array( 'post_author' => $user_id ) );
		wp_set_current_user( 0 );
		$response = $read_user();
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'USER-VALUE', $response->get_data()['value'] );
	}

	public function test_term_and_non_core_type_reads_are_not_gated() {
		$this->register_box( array(
			'id'           => 'obj_term_box',
			'object_types' => array( 'term' ),
			'taxonomies'   => array( 'category' ),
		) );
		$term_id = $this->factory->term->create( array( 'taxonomy' => 'category' ) );
		update_term_meta( $term_id, 'obj_field', 'TERM-VALUE' );

		wp_set_current_user( 0 );
		$response = $this->read_field( $term_id, 'term', 'obj_term_box' );
		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'TERM-VALUE', $response->get_data()['value'] );

		$set_type = function( $type, $found, $cmb ) {
			return 'obj_custom_box' === $cmb->cmb_id ? 'obj_custom' : $type;
		};
		$override = function( $value, $object_id, $args ) {
			return 'obj_custom' === $args['type'] ? 'CUSTOM-VALUE-' . $object_id : $value;
		};
		add_filter( 'cmb2_set_box_object_type', $set_type, 10, 3 );
		add_filter( 'cmb2_override_meta_value', $override, 10, 3 );
		try {
			$this->register_box( array(
				'id'           => 'obj_custom_box',
				'object_types' => array( 'obj_custom' ),
			) );
			$response = $this->read_field( 7, 'obj_custom', 'obj_custom_box' );
		} finally {
			remove_filter( 'cmb2_set_box_object_type', $set_type, 10 );
			remove_filter( 'cmb2_override_meta_value', $override, 10 );
		}

		$this->assertResponseStatus( 200, $response );
		$this->assertSame( 'CUSTOM-VALUE-7', $response->get_data()['value'] );
	}
}
