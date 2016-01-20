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
class CMB2_REST_Controller_Fields extends CMB2_REST_Controller {

	/**
	 * CMB2 Instance
	 *
	 * @var CMB2_REST
	 */
	protected $rest_box;

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.2.0
	 */
	public function register_routes() {
		// Returns specific box's fields.
		register_rest_route( CMB2_REST::BASE, '/boxes/(?P<cmb_id>[\w-]+)/fields/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific field data.
		register_rest_route( CMB2_REST::BASE, '/boxes/(?P<cmb_id>[\w-]+)/fields/(?P<field_id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Get all box fields
	 *
	 * @since 2.2.0
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_items( $request ) {
		$this->initiate_rest_read_box( $request, 'fields_read' );

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->prepare_item( array( 'error' => $this->rest_box->get_error_message() ) );
		}

		$fields = array();
		foreach ( $this->rest_box->cmb->prop( 'fields', array() ) as $field ) {
			$field_id = $field['id'];
			$rest_field = $this->get_rest_field( $field_id );

			if ( ! is_wp_error( $rest_field ) ) {
				$fields[ $field_id ] = $this->server->response_to_data( $rest_field, isset( $_GET['_embed'] ) );
			} else {
				$fields[ $field_id ] = array( 'error' => $rest_field->get_error_message() );
			}
		}

		return $this->prepare_item( $fields );
	}

	/**
	 * Get a specific field
	 *
	 * @since 2.2.0
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array|WP_Error
	 */
	public function get_item( $request ) {
		$this->initiate_rest_read_box( $request, 'field_read' );

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->prepare_item( array( 'error' => $this->rest_box->get_error_message() ) );
		}

		$field = $this->get_rest_field( $this->request->get_param( 'field_id' ) );

		if ( is_wp_error( $field ) ) {
			return $this->prepare_item( array( 'error' => $field->get_error_message() ) );
		}

		return $this->prepare_item( $field );
	}

	/**
	 * Get a specific field
	 *
	 * @since 2.2.0
	 *
	 * @param  string Field id
	 * @return array|WP_Error
	 */
	public function get_rest_field( $field_id ) {
		$field = $this->rest_box->field_can_read( $field_id, true );

		if ( ! $field ) {
			return new WP_Error( 'cmb2_rest_error', __( 'No field found by that id.', 'cmb2' ) );
		}

		$field_data = $this->prepare_field_data( $field );
		$response = rest_ensure_response( $field_data );

		$response->add_links( $this->prepare_links( $field ) );

		return $response;
	}

	protected function prepare_field_data( CMB2_Field $field ) {
		$field_data = array();
		$params_to_ignore = array( 'show_on_cb', 'show_in_rest', 'options' );
		$params_to_rename = array(
			'label_cb' => 'label',
			'options_cb' => 'options',
		);

		// Run this first so the js_dependencies arg is populated.
		$rendered = ( $cb = $field->maybe_callback( 'render_row_cb' ) )
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

		if ( isset( $_REQUEST['_rendered'] ) ) {
			$field_data['rendered'] = $rendered;
		}

		$field_data['value'] = $field->get_data();

		return $field_data;
	}

	protected function prepare_links( $field ) {
		$boxbase = CMB2_REST::BASE . '/boxes/' . $this->rest_box->cmb->cmb_id;

		$links = array(
			'self' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields/' . $field->_id() ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields' ),
			),
			'up' => array(
				'href' => rest_url( $boxbase ),
			),
		);

		error_log( '$this->request[context]: '. print_r( $this->request['context'], true ) );
		error_log( 'CMB2_REST_Controller::get_intial_route(): '. print_r( CMB2_REST_Controller::get_intial_route(), true ) );
		error_log( '$match: '. print_r( 'box_read' === CMB2_REST_Controller::get_intial_request_type(), true ) );


		// Don't embed boxes when looking at boxes route.
		if ( '/cmb2/v1/boxes' !== CMB2_REST_Controller::get_intial_route() ) {
			$links['up']['embeddable'] = true;
		}

		return $links;
	}

}
