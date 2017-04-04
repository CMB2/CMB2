<?php
/**
 * CMB2 objects/boxes endpoint for WordPres REST API.
 * Allows access to boxes configuration data.
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
class CMB2_REST_Controller_Boxes extends CMB2_REST_Controller {

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = 'boxes';

	/**
	 * The combined $namespace and $rest_base for these routes.
	 *
	 * @var string
	 */
	protected $namespace_base = '';

	/**
	 * Constructor
	 *
	 * @since 2.2.3
	 */
	public function __construct( WP_REST_Server $wp_rest_server ) {
		$this->namespace_base = $this->namespace . '/' . $this->rest_base;
		parent::__construct( $wp_rest_server );
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.2.3
	 */
	public function register_routes() {
		$args = array(
			'_embed' => array(
				'description' => __( 'Includes the registered fields for the box in the response.', 'cmb2' ),
			),
		);

		// @todo determine what belongs in the context param.
		// $args['context'] = $this->get_context_param();
		// $args['context']['required'] = false;
		// $args['context']['default'] = 'view';
		// $args['context']['enum'] = array( 'view', 'embed' );
		// Returns all boxes data.
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'callback'            => array( $this, 'get_items' ),
				'args'                => $args,
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		$args['_rendered'] = array(
			'description' => __( 'Includes the fully rendered attributes, \'form_open\', \'form_close\', as well as the enqueued \'js_dependencies\' script handles, and \'css_dependencies\' stylesheet handles.', 'cmb2' ),
		);

		// Returns specific box's data.
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<cmb_id>[\w-]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'callback'            => array( $this, 'get_item' ),
				'args'                => $args,
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check if a given request has access to get boxes.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		$this->initiate_request( $request, __FUNCTION__ );

		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_access Whether this CMB2 endpoint can be accessed.
		 * @param object $controller This CMB2_REST_Controller object.
		 */
		return apply_filters( 'cmb2_api_get_boxes_permissions_check', true, $this );
	}

	/**
	 * Get all public CMB2 boxes.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$this->initiate_request( $request, 'boxes_read' );

		$boxes = CMB2_REST::get_all();
		if ( empty( $boxes ) ) {
			return new WP_Error( 'cmb2_rest_no_boxes', __( 'No boxes found.', 'cmb2' ), array(
				'status' => 403,
			) );
		}

		$boxes_data = array();

		// Loop and prepare boxes data.
		foreach ( $boxes as $this->rest_box ) {
			if (
				// Make sure this box can be read
				$this->rest_box->rest_read
				// And make sure current user can view this box.
				&& $this->get_item_permissions_check_filter( $this->request )
			) {
				$boxes_data[] = $this->server->response_to_data(
					$this->get_rest_box(),
					isset( $this->request['_embed'] )
				);
			}
		}

		return $this->prepare_item( $boxes_data );
	}

	/**
	 * Check if a given request has access to a box.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$this->initiate_rest_read_box( $request, 'box_read' );

		return $this->get_item_permissions_check_filter();
	}

	/**
	 * Check by filter if a given request has access to a box.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  bool $can_access Whether the current request has access to view the box by default.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check_filter( $can_access = true ) {
		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_access Whether this CMB2 endpoint can be accessed.
		 * @param object $controller This CMB2_REST_Controller object.
		 */
		return $this->maybe_hook_callback_and_apply_filters( 'cmb2_api_get_box_permissions_check', $can_access );
	}

	/**
	 * Get one CMB2 box from the collection.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$this->initiate_rest_read_box( $request, 'box_read' );

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		return $this->prepare_item( $this->get_rest_box() );
	}

	/**
	 * Get a CMB2 box prepared for REST
	 *
	 * @since 2.2.3
	 *
	 * @return array
	 */
	public function get_rest_box() {
		$cmb = $this->rest_box->cmb;

		$boxes_data = $cmb->meta_box;

		if ( isset( $this->request['_rendered'] ) && $this->namespace_base !== ltrim( CMB2_REST_Controller::get_intial_route(), '/' ) ) {
			$boxes_data['form_open'] = $this->get_cb_results( array( $cmb, 'render_form_open' ) );
			$boxes_data['form_close'] = $this->get_cb_results( array( $cmb, 'render_form_close' ) );

			global $wp_scripts, $wp_styles;
			$before_css = $wp_styles->queue;
			$before_js = $wp_scripts->queue;

			CMB2_JS::enqueue();

			$boxes_data['js_dependencies'] = array_values( array_diff( $wp_scripts->queue, $before_js ) );
			$boxes_data['css_dependencies'] = array_values( array_diff( $wp_styles->queue, $before_css ) );
		}

		// TODO: look into 'embed' parameter.
		// http://demo.wp-api.org/wp-json/wp/v2/posts?_embed
		unset( $boxes_data['fields'] );
		// Handle callable properties.
		unset( $boxes_data['show_on_cb'] );

		$response = rest_ensure_response( $boxes_data );

		$response->add_links( $this->prepare_links( $cmb ) );

		return $response;
	}

	/**
	 * Return an array of contextual links for box/boxes.
	 *
	 * @since  2.2.3
	 *
	 * @param  CMB2_REST $cmb CMB2_REST object to build links from.
	 *
	 * @return array          Array of links
	 */
	protected function prepare_links( $cmb ) {
		$boxbase      = $this->namespace_base . '/' . $cmb->cmb_id;
		$query_string = $this->get_query_string();

		return array(
			// Standard Link Relations -- http://v2.wp-api.org/extending/linking/
			'self' => array(
				'href' => rest_url( $boxbase . $query_string ),
			),
			'collection' => array(
				'href' => rest_url( $this->namespace_base . $query_string ),
			),
			// Custom Link Relations -- http://v2.wp-api.org/extending/linking/
			// TODO URL should document relationship.
			'https://cmb2.io/fields' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields' . $query_string ),
				'embeddable' => true,
			),
		);
	}

}
