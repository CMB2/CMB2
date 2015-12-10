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
	 * Get a CMB2 box prepared for REST
	 *
	 * @since 2.2.0
	 *
	 * @param CMB2 $cmb
	 * @return array
	 */
	public function get_rest_box( $cmb ) {
		$cmb->object_type( $this->object_id );
		$cmb->object_id( $this->object_type );

		$boxes_data = $cmb->meta_box;

		if ( isset( $_REQUEST['rendered'] ) ) {
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

		$base = CMB2_REST::BASE . '/boxes/' . $cmb->cmb_id;
		$boxbase = $base . '/' . $cmb->cmb_id;

		$response = new WP_REST_Response( $boxes_data );
		$response->add_links( array(
			'self' => array(
				'href' => rest_url( trailingslashit( $boxbase ) ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $base ) ),
			),
			'fields' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields/' ),
			),
		) );

		$boxes_data['_links'] = $response->get_links();

		return $boxes_data;
	}

	/**
	 * Get a specific field
	 *
	 * @since 2.2.0
	 *
	 * @param CMB2 $cmb
	 * @return array|WP_Error
	 */
	public function get_rest_field( $cmb, $field_id ) {

		// TODO: more robust show_in_rest checking. use rest_read/rest_write properties.
		// TODO: more robust show_in_rest checking. use rest_read/rest_write properties.
		// TODO: more robust show_in_rest checking. use rest_read/rest_write properties.

		if ( ! $cmb->prop( 'show_in_rest' ) ) {
			return new WP_Error( 'cmb2_rest_error', __( "You don't have permission to view this field.", 'cmb2' ) );
		}

		$field = $cmb->get_field( $field_id );

		if ( ! $field ) {
			return new WP_Error( 'cmb2_rest_error', __( 'No field found by that id.', 'cmb2' ) );
		}

		// TODO: check for show_in_rest property.
		// $can_read = $this->can_read
		// 	? 'write_only' !== $show_in_rest
		// 	: in_array( $show_in_rest, array( 'read_and_write', 'read_only' ), true );


		$field_data = $this->prepare_field_data( $field );

		$base = CMB2_REST::BASE . '/boxes/' . $cmb->cmb_id;

		$response = new WP_REST_Response( $field_data );
		$response->add_links( array(
			'self' => array(
				'href' => rest_url( trailingslashit( $base ) . 'fields/' . $field->_id() ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $base ) . 'fields/' ),
			),
			'box' => array(
				'href' => rest_url( trailingslashit( $base ) ),
			),
		) );

		$field_data['_links'] = $response->get_links();

		return $field_data;
	}

	public function prepare_field_data( CMB2_Field $field ) {
		$field_data = array();
		$params_to_ignore = array( 'show_on_cb', 'show_in_rest', 'options' );
		$params_to_rename = array(
			'label_cb' => 'label',
			'options_cb' => 'options',
		);

		// TODO: Use request get object
		// Run this first so the js_dependencies arg is populated.
		$rendered = isset( $_REQUEST['rendered'] ) && ( $cb = $field->maybe_callback( 'render_row_cb' ) )
			// Ok, callback is good, let's run it.
			? $this->get_cb_results( $cb, $field->args(), $field )
			: false;

		foreach ( $field->args() as $key => $value ) {
			if ( in_array( $key, $params_to_ignore, true ) ) {
				continue;
			}

			if ( 'render_row_cb' === $key ) {
				continue;
			}

			if ( 'options_cb' === $key ) {
				$value = $field->options();
			} elseif ( in_array( $key, CMB2_Field::$callable_fields ) ) {
				$value = $field->get_param_callback_result( $key );
			}

			$key = isset( $params_to_rename[ $key ] ) ? $params_to_rename[ $key ] : $key;

			if ( empty( $value ) || is_scalar( $value ) || is_array( $value ) ) {
				$field_data[ $key ] = $value;
			} else {
				$field_data[ $key ] = __( 'Value Error', 'cmb2' );
			}
		}

		if ( isset( $_REQUEST['rendered'] ) ) {
			$field_data['rendered'] = $rendered;
		}

		$field_data['value'] = $field->get_data();

		return $field_data;
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
	public function prepare_item_for_response( $data, $request ) {

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
		return apply_filters( 'cmb2_rest_prepare', $data, $this->request, $this );
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
