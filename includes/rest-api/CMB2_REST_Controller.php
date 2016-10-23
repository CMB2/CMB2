<?php
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	// Shim the WP_REST_Controller class if wp-api plugin not installed, & not in core.
	require_once cmb2_dir( 'includes/shim/WP_REST_Controller.php' );
}

/**
 * Creates CMB2 objects/fields endpoint for WordPres REST API.
 * Allows access to fields registered to a specific post type and more.
 *
 * @todo  Add better documentation.
 * @todo  Research proper schema.
 *
 * @since 2.2.3
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
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
	 * @var WP_REST_Request $request
	 * @since 2.2.3
	 */
	public $request;

	/**
	 * The current server object
	 * @var WP_REST_Server $server
	 * @since 2.2.3
	 */
	public $server;

	/**
	 * Box object id
	 * @var   mixed
	 * @since 2.2.3
	 */
	public $object_id = null;

	/**
	 * Box object type
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
	 * The initial route
	 * @var   string
	 * @since 2.2.3
	 */
	protected static $route = '';

	/**
	 * Defines which endpoint the initial request is.
	 * @var string $request_type
	 * @since 2.2.3
	 */
	protected static $request_type = '';

	/**
	 * Constructor
	 * @since 2.2.3
	 */
	public function __construct( WP_REST_Server $wp_rest_server ) {
		$this->server = $wp_rest_server;
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		$this->initiate_request( $request, __FUNCTION__ );
		$can_access = true;

		$this->maybe_hook_callback( 'get_items_permissions_check_cb' );

		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_access Whether this CMB2 endpoint can be accessed.
		 * @param object $request    The WP_REST_Request object
		 */
		return apply_filters( 'cmb2_api_get_items_permissions_check', $can_access, $this );
	}

	/**
	 * Check if a given request has access to a field or box.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$this->initiate_request( $request, __FUNCTION__ );
		$can_access = true;

		$this->maybe_hook_callback( 'get_item_permissions_check_cb' );

		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_access Whether this CMB2 endpoint can be accessed.
		 * @param object $request    The WP_REST_Request object
		 */
		return apply_filters( 'cmb2_api_get_item_permissions_check', $can_access, $this );
	}

	/**
	 * Check if a given request has access to update a field value.
	 * By default, requires 'edit_others_posts' capability, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_field_value_permissions_check( $request ) {
		$this->initiate_request( $request, __FUNCTION__ );
		$can_update = current_user_can( 'edit_others_posts' );

		$this->maybe_hook_callback( 'update_field_value_permissions_check_cb' );

		/**
		 * By default, 'edit_others_posts' is required capability.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_update Whether this CMB2 endpoint can be accessed.
		 * @param object $request    The WP_REST_Request object
		 */
		return apply_filters( 'cmb2_api_update_field_value_permissions_check', $can_update, $this );
	}

	/**
	 * Check if a given request has access to delete a field value.
	 * By default, requires 'delete_others_posts' capability, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function delete_field_value_permissions_check( $request ) {
		$this->initiate_request( $request, __FUNCTION__ );
		$can_delete = current_user_can( 'delete_others_posts' );

		$this->maybe_hook_callback( 'delete_field_value_permissions_check_cb' );

		/**
		 * By default, 'delete_others_posts' is required capability.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_delete Whether this CMB2 endpoint can be accessed.
		 * @param object $request    The WP_REST_Request object
		 */
		return apply_filters( 'cmb2_api_delete_field_value_permissions_check', $can_delete, $this );
	}

	/**
	 * Check if a CMB object callback property exists, and if it does,
	 * hook it to the permissions filter.
	 *
	 * @since  2.2.3
	 *
	 * @param  string  $to_check The callback property to check.
	 *
	 * @return void
	 */
	public function maybe_hook_callback( $to_check ) {
		if ( ! $this->request->get_param( 'cmb_id' ) ) {
			return;
		}

		$rest_box = CMB2_REST::get_rest_box( $this->request->get_param( 'cmb_id' ) );

		if ( $rest_box && $rest_box->cmb->prop( "{$to_check}" ) ) {
			$filter = 'cmb2_api_' . ( substr( $to_check, 0, strlen( $to_check ) - 3 ) );
			add_filter( $filter, $rest_box->cmb->prop( "{$to_check}" ), 10, 2 );
		}
	}

	/**
	 * Prepare a CMB2 object for serialization
	 *
	 * @since 2.2.3
	 *
	 * @param  mixed $data
	 * @return array $data
	 */
	public function prepare_item( $post ) {
		return $this->prepare_item_for_response( $post, $this->request );
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
	 * @param  mixed            $item     WordPress representation of the item.
	 * @param  WP_REST_Request  $request  Request object.
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
			$this->rest_box = new WP_Error( 'cmb2_rest_no_read_error', __( 'This box does not have read permissions.', 'cmb2' ), array( 'status' => 403 ) );
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
			$this->rest_box = new WP_Error( 'cmb2_rest_no_write_error', __( 'This box does not have write permissions.', 'cmb2' ), array( 'status' => 403 ) );
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

			$this->rest_box = new WP_Error( 'cmb2_rest_box_not_found_error', __( 'No box found by that id. A box needs to be registered with the "show_in_rest" parameter configured.', 'cmb2' ), array( 'status' => 403 ) );

		} else {

			if ( isset( $this->request['object_id'] ) ) {
				$this->rest_box->cmb->object_id( sanitize_text_field( $this->request['object_id'] ) );
			}

			if ( isset( $this->request['object_type'] ) ) {
				$this->rest_box->cmb->object_type( sanitize_text_field( $this->request['object_type'] ) );
			}
		}
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
					'description'  => __( 'A human-readable description of the object.', 'cmb2' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
					),
					'name'             => array(
						'description'  => __( 'The id for the object.', 'cmb2' ),
						'type'         => 'integer',
						'context'      => array( 'view' ),
					),
				'name' => array(
					'description'  => __( 'The title for the object.', 'cmb2' ),
					'type'         => 'string',
					'context'      => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Return an array of contextual links for endpoint/object
	 * @link http://v2.wp-api.org/extending/linking/
	 * @link http://www.iana.org/assignments/link-relations/link-relations.xhtml
	 *
	 * @since  2.2.3
	 *
	 * @param  mixed  $object Object to build links from.
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
