<?php
/**
 * Creates CMB2 objects/fields endpoint for WordPres REST API.
 * Allows access to fields registered to a specific post type and more.
 *
 * @todo  Add better documentation.
 * @todo  Research proper schema.
 *
 * @since 2.2.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_REST_Controller extends WP_REST_Controller {

	/**
	 * The current request object
	 * @var WP_REST_Request $request
	 * @since 2.2.0
	 */
	public $request;

	/**
	 * The current server object
	 * @var WP_REST_Server $server
	 * @since 2.2.0
	 */
	public $server;

	/**
	 * Box object id
	 * @var   mixed
	 * @since 2.2.0
	 */
	public $object_id = null;

	/**
	 * Box object type
	 * @var   string
	 * @since 2.2.0
	 */
	public $object_type = '';

	/**
	 * Constructor
	 * @since 2.2.0
	 */
	public function __construct( WP_REST_Server $wp_rest_server ) {
		$this->server = $wp_rest_server;
	}

	/**
	 * Check if a given request has access to a field or box.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		$this->initiate_request( $request );

		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.0
		 *
		 * @param object $request        The WP_REST_Request object
		 * @param object $cmb2_endpoints This endpoints object
		 */
		return apply_filters( 'cmb2_request_permissions_check', true, $this->request );
	}

	/**
	 * Prepare a CMB2 object for serialization
	 *
	 * @since 2.2.0
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
	 * @since  2.2.0
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

	public function initiate_request( $request ) {
		$this->request = $request;

		if ( isset( $_REQUEST['object_id'] ) ) {
			$this->object_id = absint( $_REQUEST['object_id'] );
		}

		if ( isset( $_REQUEST['object_type'] ) ) {
			$this->object_type = absint( $_REQUEST['object_type'] );
		}
	}

	/**
	 * Prepare a CMB2 object for serialization
	 *
	 * @since 2.2.0
	 *
	 * @param  mixed           $data
	 * @param  WP_REST_Request $request Request object
	 * @return array $data
	 */
	public function prepare_item_for_response( $data, $request = null ) {

		$context = ! empty( $this->request['context'] ) ? $this->request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );

		/**
		 * Filter the prepared CMB2 item response.
		 *
		 * @since 2.2.0
		 *
		 * @param mixed  $data           Prepared data
		 * @param object $request        The WP_REST_Request object
		 * @param object $cmb2_endpoints This endpoints object
		 */
		return apply_filters( 'cmb2_rest_prepare', rest_ensure_response( $data ), $this->request, $this );
	}

	/**
	 * Get CMB2 fields schema, conforming to JSON Schema
	 *
	 * @since 2.2.0
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
					'description'  => 'A human-readable description of the object.',
					'type'         => 'string',
					'context'      => array( 'view' ),
					),
					'name'             => array(
						'description'  => 'The id for the object.',
						'type'         => 'integer',
						'context'      => array( 'view' ),
					),
				'name' => array(
					'description'  => 'The title for the object.',
					'type'         => 'string',
					'context'      => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
