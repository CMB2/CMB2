<?php
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	// Shim the WP_REST_Controller class if wp-api plugin not installed, & not in core.
	require_once cmb2_dir( 'includes/shim/WP_REST_Controller.php' );
}

/**
 * Creates CMB2 objects/fields endpoint for WordPress REST API.
 * Allows access to fields registered to a specific post type and more.
 *
 * @todo  Add better documentation.
 * @todo  Research proper schema.
 *
 * @since 2.2.3
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
abstract class CMB2_REST_Controller extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = CMB2_REST::NAME_SPACE;

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * The current request object
	 *
	 * @var WP_REST_Request $request
	 * @since 2.2.3
	 */
	public $request;

	/**
	 * The current server object
	 *
	 * @var WP_REST_Server $server
	 * @since 2.2.3
	 */
	public $server;

	/**
	 * Box object id
	 *
	 * @var   mixed
	 * @since 2.2.3
	 */
	public $object_id = null;

	/**
	 * Box object type
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	public $object_type = '';

	/**
	 * CMB2 Instance
	 *
	 * @var CMB2_REST
	 */
	protected $rest_box;

	/**
	 * CMB2_Field Instance
	 *
	 * @var CMB2_Field
	 */
	protected $field;

	/**
	 * The initial route
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected static $route = '';

	/**
	 * Defines which endpoint the initial request is.
	 *
	 * @var string $request_type
	 * @since 2.2.3
	 */
	protected static $request_type = '';

	/**
	 * Constructor
	 *
	 * @since 2.2.3
	 */
	public function __construct( WP_REST_Server $wp_rest_server ) {
		$this->server = $wp_rest_server;
	}

	/**
	 * A wrapper for `apply_filters` which checks for box/field properties to hook to the filter.
	 *
	 * Checks if a CMB object callback property exists, and if it does,
	 * hook it to the permissions filter.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $filter         The name of the filter to apply.
	 * @param  bool   $default_access The default access for this request.
	 *
	 * @return void
	 */
	public function maybe_hook_callback_and_apply_filters( $filter, $default_access ) {
		if ( ! $this->rest_box && $this->request->get_param( 'cmb_id' ) ) {
			$this->rest_box = CMB2_REST::get_rest_box( $this->request->get_param( 'cmb_id' ) );
		}

		$default_access = $this->maybe_hook_registered_callback( $filter, $default_access );

		/**
		 * Apply the permissions check filter.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $default_access Whether this CMB2 endpoint can be accessed.
		 * @param object $controller     This CMB2_REST_Controller object.
		 */
		$default_access = apply_filters( $filter, $default_access, $this );

		$this->maybe_unhook_registered_callback( $filter );

		return $default_access;
	}

	/**
	 * Optionally gates a read behind a capability, aligning with WordPress core.
	 *
	 * WordPress core exposes object meta reads publicly, but gates settings/options
	 * reads behind a capability (WP_REST_Settings_Controller). CMB2's
	 * `rest_read_capability` property is how a box (or a single field on it) declares
	 * which side of that line it sits on; see CMB2_REST::get_rest_read_capability() for
	 * the full precedence. With the property left unset, CMB2 keeps its historical
	 * public-read behavior unless the box is an options page and the site-wide
	 * `cmb2_rest_enforce_options_page_read_permissions` filter is enabled.
	 *
	 * @since  2.13.0
	 *
	 * @param  bool                  $can_access The default access for this read request.
	 * @param  CMB2_Field|array|null $field      The field being read, when the read is of
	 *                                           a single field rather than the box.
	 *
	 * @return bool                              The possibly-adjusted access value.
	 */
	protected function maybe_gate_read_by_capability( $can_access, $field = null ) {
		if ( ! $this->rest_box && $this->request->get_param( 'cmb_id' ) ) {
			$this->rest_box = CMB2_REST::get_rest_box( $this->request->get_param( 'cmb_id' ) );
		}

		if ( ! $this->rest_box || is_wp_error( $this->rest_box ) ) {
			return $can_access;
		}

		$capability = CMB2_REST::get_rest_read_capability( $this->rest_box->cmb, $field );

		if ( null === $capability ) {
			return $can_access;
		}

		return current_user_can( $capability );
	}

	/**
	 * Gates a read of an object's field values by WordPress core's REST read
	 * permissions for that object.
	 *
	 * Core returns object meta publicly, but only for objects its REST controllers
	 * show to the current user. The object checked is the one the box resolved
	 * (from `object_id`, or the box's own fallback), so it is the object the fields
	 * read from. Terms, options pages and non-core object types are not gated here.
	 *
	 * @since  2.13.4
	 *
	 * @param  bool $can_access The default access for this read request.
	 *
	 * @return bool             The possibly-adjusted access value.
	 */
	protected function maybe_gate_read_by_object( $can_access ) {
		if ( ! $can_access || ! $this->rest_box || is_wp_error( $this->rest_box ) ) {
			return $can_access;
		}

		$cmb = $this->rest_box->cmb;

		// The metadata API reads these types by absint() of the id, so check the same object.
		$object_id = absint( $cmb->object_id() );

		if ( ! $object_id ) {
			return $can_access;
		}

		switch ( $cmb->object_type() ) {
			case 'post':
				$post = get_post( $object_id );

				return ! $post || ( $this->can_read_post_type( $post ) && $this->can_read_post( $post ) );

			case 'comment':
				$comment = get_comment( $object_id );

				return ! $comment || $this->can_read_comment( $comment );

			case 'user':
				if ( ! get_userdata( $object_id ) || $this->can_read_user( $object_id ) ) {
					return $can_access;
				}

				$enforce = $this->enforce_user_read_permissions();

				if ( null === $enforce ) {
					$this->deprecated_user_read();
				}

				return ! $enforce;
		}

		return $can_access;
	}

	/**
	 * Whether the post's type allows a REST read of it by the current user.
	 *
	 * Core's routes exist only for post types shown in REST. CMB2 boxes are commonly
	 * registered for post types that are not, so the box's own types are exempt, and
	 * a requester who can `read_post` passes for any registered type.
	 *
	 * @since  2.13.4
	 *
	 * @param  WP_Post $post The post.
	 *
	 * @return bool
	 */
	protected function can_read_post_type( WP_Post $post ) {
		if ( $this->rest_box->cmb->is_box_type( $post->post_type ) ) {
			return true;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! $post_type ) {
			return false;
		}

		return ! empty( $post_type->show_in_rest ) || current_user_can( 'read_post', $post->ID );
	}

	/**
	 * Whether WordPress core's REST API shows the post to the current user.
	 *
	 * Mirrors WP_REST_Posts_Controller::check_read_permission(), minus its check that
	 * the post type is shown in REST, which can_read_post_type() handles.
	 *
	 * @since  2.13.4
	 *
	 * @param  WP_Post $post The post.
	 *
	 * @return bool
	 */
	protected function can_read_post( WP_Post $post ) {
		if ( 'publish' === $post->post_status || current_user_can( 'read_post', $post->ID ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $post->post_status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
			$parent = get_post( $post->post_parent );
			if ( $parent ) {
				return $this->can_read_post_type( $parent ) && $this->can_read_post( $parent );
			}
		}

		// An inherit post without a parent is treated as published, per get_post_status().
		return 'inherit' === $post->post_status;
	}

	/**
	 * Whether WordPress core's REST API shows the comment to the current user.
	 *
	 * Mirrors WP_REST_Comments_Controller::check_read_permission().
	 *
	 * @since  2.13.4
	 *
	 * @param  WP_Comment $comment The comment.
	 *
	 * @return bool
	 */
	protected function can_read_comment( WP_Comment $comment ) {
		if ( 'note' !== $comment->comment_type && ! empty( $comment->comment_post_ID ) ) {
			$post = get_post( $comment->comment_post_ID );
			if ( $post && 1 === (int) $comment->comment_approved && $this->can_read_comment_post( $post ) ) {
				return true;
			}
		}

		if ( 0 === get_current_user_id() ) {
			return false;
		}

		if ( empty( $comment->comment_post_ID ) && ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		if ( ! empty( $comment->user_id ) && get_current_user_id() === (int) $comment->user_id ) {
			return true;
		}

		return current_user_can( 'edit_comment', $comment->comment_ID );
	}

	/**
	 * Whether the current user can read comments on the post.
	 *
	 * Mirrors WP_REST_Comments_Controller::check_read_post_permission().
	 *
	 * @since  2.13.4
	 *
	 * @param  WP_Post $post The comment's post.
	 *
	 * @return bool
	 */
	protected function can_read_comment_post( WP_Post $post ) {
		if ( ! $this->can_read_post_type( $post ) ) {
			return false;
		}

		if ( post_password_required( $post ) ) {
			return current_user_can( 'edit_post', $post->ID );
		}

		return $this->can_read_post( $post );
	}

	/**
	 * Whether WordPress core's REST API shows the user to the current user.
	 *
	 * Mirrors WP_REST_Users_Controller::get_item_permissions_check().
	 *
	 * @since  2.13.4
	 *
	 * @param  int $user_id The user id.
	 *
	 * @return bool
	 */
	protected function can_read_user( $user_id ) {
		if (
			get_current_user_id() === $user_id
			|| current_user_can( 'edit_user', $user_id )
			|| current_user_can( 'list_users' )
		) {
			return true;
		}

		return (bool) count_user_posts( $user_id, get_post_types( array( 'show_in_rest' => true ), 'names' ) );
	}

	/**
	 * Whether reads of users that core hides are denied, per the box's
	 * `rest_enforce_user_read_permissions` property, else the site-wide filter.
	 *
	 * @since  2.13.4
	 *
	 * @return bool|null The declared choice, or null when neither declares one.
	 */
	protected function enforce_user_read_permissions() {
		$cmb      = $this->rest_box->cmb;
		$declared = $cmb->prop( 'rest_enforce_user_read_permissions' );

		if ( null !== $declared ) {
			return (bool) $declared;
		}

		/**
		 * Whether CMB2 REST field reads of a user follow WordPress core's user read
		 * permissions: core shows a user only to that user, to holders of
		 * `edit_user`/`list_users`, or when the user has published posts.
		 *
		 * Defaults to null, which reads as false and fires a deprecation notice. Return
		 * true or false to declare a choice. Only consulted for boxes which have not
		 * declared a `rest_enforce_user_read_permissions` property.
		 *
		 * @since 2.13.4
		 *
		 * @param bool|null $enforce Whether to deny reads of users core does not show.
		 * @param CMB2      $cmb     The CMB2 box object being read.
		 */
		$enforce = apply_filters( 'cmb2_rest_enforce_user_read_permissions', null, $cmb );

		return null === $enforce ? null : (bool) $enforce;
	}

	/**
	 * Flags a read of a user that core does not show, which the future default denies.
	 *
	 * In REST requests core suppresses trigger_error() for deprecations and sends an
	 * `X-WP-DeprecatedParam` header only when WP_DEBUG is on, so the notice is also
	 * written to the debug log when that is enabled.
	 *
	 * @since  2.13.4
	 *
	 * @return void
	 */
	protected function deprecated_user_read() {
		$route   = $this->request->get_route();
		$version = '2.13.4';
		$message = __( 'Reading the fields of a user that the WordPress REST API does not show to the current user is deprecated. In a future version, CMB2 will follow WordPress core\'s user read permissions here by default. To keep the current behavior, set the box\'s "rest_enforce_user_read_permissions" property to false, or return false from the "cmb2_rest_enforce_user_read_permissions" filter.', 'cmb2' );

		_deprecated_argument( $route, $version, $message );

		// Core's rest_send_allow_header() re-runs the permission check, so log each route once.
		static $logged = array();

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && ! isset( $logged[ $route ] ) ) {
			$logged[ $route ] = true;
			error_log( sprintf( 'CMB2: %1$s (since %2$s; %3$s)', $route, $version, $message ) );
		}
	}

	/**
	 * Checks if the CMB2 box has any registered callback parameters for the given filter.
	 *
	 * The registered handlers will have a property name which matches the filter, except:
	 * - The 'cmb2_api' prefix will be removed
	 * - A '_cb' suffix will be added (to stay inline with other '*_cb' parameters).
	 *
	 * @since  2.2.3
	 *
	 * @param  string $filter      The filter name.
	 * @param  bool   $default_val The default filter value.
	 *
	 * @return bool                The possibly-modified filter value (if the '*_cb' param is non-callable).
	 */
	public function maybe_hook_registered_callback( $filter, $default_val ) {
		if ( ! $this->rest_box || is_wp_error( $this->rest_box ) ) {
			return $default_val;
		}

		// Hook box specific filter callbacks.
		$val = $this->rest_box->cmb->maybe_hook_parameter( $filter, $default_val );
		if ( null !== $val ) {
			$default_val = $val;
		}

		return $default_val;
	}

	/**
	 * Unhooks any CMB2 box registered callback parameters for the given filter.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $filter The filter name.
	 *
	 * @return void
	 */
	public function maybe_unhook_registered_callback( $filter ) {
		if ( ! $this->rest_box || is_wp_error( $this->rest_box ) ) {
			return;
		}

		// Unhook box specific filter callbacks.
		$this->rest_box->cmb->maybe_hook_parameter( $filter, null, 'remove_filter' );
	}

	/**
	 * Prepare a CMB2 object for serialization
	 *
	 * @since 2.2.3
	 *
	 * @param  mixed $data
	 * @return array $data
	 */
	public function prepare_item( $data ) {
		return $this->prepare_item_for_response( $data, $this->request );
	}

	/**
	 * Output buffers a callback and returns the results.
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed $cb Callable function/method.
	 * @return mixed     Results of output buffer after calling function/method.
	 */
	public function get_cb_results( $cb ) {
		$args = func_get_args();
		array_shift( $args ); // ignore $cb
		ob_start();
		call_user_func_array( $cb, $args );

		return ob_get_clean();
	}

	/**
	 * Prepare the CMB2 item for the REST response.
	 *
	 * @since 2.2.3
	 *
	 * @param  mixed           $item     WordPress representation of the item.
	 * @param  WP_REST_Request $request  Request object.
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $data, $request = null ) {
		$data = $this->filter_response_by_context( $data, $this->request['context'] );

		/**
		 * Filter the prepared CMB2 item response.
		 *
		 * @since 2.2.3
		 *
		 * @param mixed  $data           Prepared data
		 * @param object $request        The WP_REST_Request object
		 * @param object $cmb2_endpoints This endpoints object
		 */
		return apply_filters( 'cmb2_rest_prepare', rest_ensure_response( $data ), $this->request, $this );
	}

	/**
	 * Initiates the request property and the rest_box property if box is readable.
	 *
	 * @since  2.2.3
	 *
	 * @param  WP_REST_Request $request      Request object.
	 * @param  string          $request_type A description of the type of request being made.
	 *
	 * @return void
	 */
	protected function initiate_rest_read_box( $request, $request_type ) {
		$this->initiate_rest_box( $request, $request_type );

		if ( ! is_wp_error( $this->rest_box ) && ! $this->rest_box->rest_read ) {
			$this->rest_box = new WP_Error( 'cmb2_rest_no_read_error', __( 'This box does not have read permissions.', 'cmb2' ), array(
				'status' => 403,
			) );
		}
	}

	/**
	 * Initiates the request property and the rest_box property if box is writeable.
	 *
	 * @since  2.2.3
	 *
	 * @param  WP_REST_Request $request      Request object.
	 * @param  string          $request_type A description of the type of request being made.
	 *
	 * @return void
	 */
	protected function initiate_rest_edit_box( $request, $request_type ) {
		$this->initiate_rest_box( $request, $request_type );

		if ( ! is_wp_error( $this->rest_box ) && ! $this->rest_box->rest_edit ) {
			$this->rest_box = new WP_Error( 'cmb2_rest_no_write_error', __( 'This box does not have write permissions.', 'cmb2' ), array(
				'status' => 403,
			) );
		}
	}

	/**
	 * Initiates the request property and the rest_box property.
	 *
	 * @since  2.2.3
	 *
	 * @param  WP_REST_Request $request      Request object.
	 * @param  string          $request_type A description of the type of request being made.
	 *
	 * @return void
	 */
	protected function initiate_rest_box( $request, $request_type ) {
		$this->initiate_request( $request, $request_type );

		$this->rest_box = CMB2_REST::get_rest_box( $this->request->get_param( 'cmb_id' ) );

		if ( ! $this->rest_box ) {

			$this->rest_box = new WP_Error( 'cmb2_rest_box_not_found_error', __( 'No box found by that id. A box needs to be registered with the "show_in_rest" parameter configured.', 'cmb2' ), array(
				'status' => 403,
			) );

		} else {

			$object_id   = isset( $this->request['object_id'] ) ? sanitize_text_field( $this->request['object_id'] ) : null;
			$object_type = isset( $this->request['object_type'] ) ? sanitize_text_field( $this->request['object_type'] ) : null;

			$error = $this->validate_request_object( $object_type, $object_id );
			if ( is_wp_error( $error ) ) {
				$this->rest_box = $error;
				return;
			}

			if ( null !== $object_id ) {
				$this->rest_box->cmb->object_id( $object_id );
			}

			if ( null !== $object_type ) {
				$this->rest_box->cmb->object_type( $object_type );
			}
		}
	}

	/**
	 * Validates the requested object_type/object_id against the box's registration.
	 *
	 * The requested object_type selects the storage the box reads from and writes
	 * to, so it has to be a storage type the box was registered for. For the
	 * options-page type, the object_id is the option name, so it has to be one of
	 * the box's own option keys. This is request validation, so it is applied
	 * regardless of the permissions filters.
	 *
	 * @since  2.13.3
	 *
	 * @param  string|null $object_type The requested object type, if any.
	 * @param  string|null $object_id   The requested object id, if any.
	 *
	 * @return true|WP_Error            True if valid, or a WP_Error.
	 */
	protected function validate_request_object( $object_type, $object_id ) {
		if ( null === $object_type || '' === $object_type ) {
			return true;
		}

		$cmb = $this->rest_box->cmb;

		if ( ! in_array( $object_type, $this->get_box_storage_types( $cmb ), true ) ) {
			return new WP_Error( 'cmb2_rest_invalid_object_type', __( 'The object_type parameter does not match an object type this box is registered for.', 'cmb2' ), array(
				'status' => 400,
			) );
		}

		if (
			'options-page' === $object_type
			&& null !== $object_id
			&& '' !== $object_id
			&& ! in_array( $object_id, array_map( 'strval', (array) $cmb->options_page_keys() ), true )
		) {
			return new WP_Error( 'cmb2_rest_invalid_object_id', __( 'The object_id parameter does not match an option key this box is registered for.', 'cmb2' ), array(
				'status' => 400,
			) );
		}

		return true;
	}

	/**
	 * Gets the storage object types for a box's registered object types.
	 *
	 * Registered types that are not core object types (post types) store as 'post',
	 * matching CMB2::mb_object_type().
	 *
	 * @since  2.13.3
	 *
	 * @param  CMB2 $cmb The box.
	 *
	 * @return array     The storage object types.
	 */
	protected function get_box_storage_types( CMB2 $cmb ) {
		$types = array();

		if ( $cmb->is_options_page_mb() ) {
			$types[] = 'options-page';
		}

		foreach ( $cmb->box_types( array( 'post' ) ) as $type ) {
			$types[] = $cmb->is_supported_core_object_type( $type ) ? $type : 'post';
		}

		// A non-core type from the `cmb2_set_box_object_type` filter is the box's own declaration.
		$mb_object_type = $cmb->mb_object_type();
		if ( ! $cmb->is_supported_core_object_type( $mb_object_type ) ) {
			$types[] = $mb_object_type;
		}

		return array_unique( $types );
	}

	/**
	 * Initiates the request property and sets up the initial static properties.
	 *
	 * @since  2.2.3
	 *
	 * @param  WP_REST_Request $request      Request object.
	 * @param  string          $request_type A description of the type of request being made.
	 *
	 * @return void
	 */
	public function initiate_request( $request, $request_type ) {
		$this->request = $request;

		if ( ! isset( $this->request['context'] ) || empty( $this->request['context'] ) ) {
			$this->request['context'] = 'view';
		}

		if ( ! self::$request_type ) {
			self::$request_type = $request_type;
		}

		if ( ! self::$route ) {
			self::$route = $this->request->get_route();
		}
	}

	/**
	 * Useful when getting `_embed`-ed items
	 *
	 * @since  2.2.3
	 *
	 * @return string  Initial requested type.
	 */
	public static function get_intial_request_type() {
		return self::$request_type;
	}

	/**
	 * Useful when getting `_embed`-ed items
	 *
	 * @since  2.2.3
	 *
	 * @return string  Initial requested route.
	 */
	public static function get_intial_route() {
		return self::$route;
	}

	/**
	 * Get CMB2 fields schema, conforming to JSON Schema
	 *
	 * @since 2.2.3
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'CMB2',
			'type'                 => 'object',
			'properties'           => array(
				'description' => array(
					'description' => __( 'A human-readable description of the object.', 'cmb2' ),
					'type'        => 'string',
					'context'     => array(
						'view',
					),
				),
				'name' => array(
					'description' => __( 'The id for the object.', 'cmb2' ),
					'type'        => 'integer',
					'context'     => array(
						'view',
					),
				),
				'name' => array(
					'description' => __( 'The title for the object.', 'cmb2' ),
					'type'        => 'string',
					'context'     => array(
						'view',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Return an array of contextual links for endpoint/object
	 *
	 * @link http://v2.wp-api.org/extending/linking/
	 * @link http://www.iana.org/assignments/link-relations/link-relations.xhtml
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed $object Object to build links from.
	 *
	 * @return array          Array of links
	 */
	abstract protected function prepare_links( $object );

	/**
	 * Get whitelisted query strings from URL for appending to link URLS.
	 *
	 * @since  2.2.3
	 *
	 * @return string URL query stringl
	 */
	public function get_query_string() {
		$defaults = array(
			'object_id'   => 0,
			'object_type' => '',
			'_rendered'   => '',
			// '_embed'      => '',
		);

		$query_string = '';

		foreach ( $defaults as $key => $value ) {
			if ( isset( $this->request[ $key ] ) ) {
				$query_string .= $query_string ? '&' : '?';
				$query_string .= $key;
				if ( $value = sanitize_text_field( $this->request[ $key ] ) ) {
					$query_string .= '=' . $value;
				}
			}
		}

		return $query_string;
	}

}
