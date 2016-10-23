<?php
/**
 * CMB2 objects/fields endpoint for WordPres REST API.
 * Allows access to fields registered to a specific box.
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
class CMB2_REST_Controller_Fields extends CMB2_REST_Controller_Boxes {

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.2.3
	 */
	public function register_routes() {
		$args = array(
			'_embed' => array(
				'description' => __( 'Includes the box object which the fields are registered to in the response.', 'cmb2' ),
			),
			'_rendered' => array(
				'description' => __( 'When the \'rendered\' argument is passed, the renderable field attributes will be returned fully rendered. By default, the names of the callback handers for the renderable attributes will be returned.', 'cmb2' ),
			),
			'object_id' => array(
				'description' => __( 'To view or modify the field\'s value, the \'object_id\' and \'object_type\' arguments are required.', 'cmb2' ),
			),
			'object_type' => array(
				'description' => __( 'To view or modify the field\'s value, the \'object_id\' and \'object_type\' arguments are required.', 'cmb2' ),
			),
		);

		// Returns specific box's fields.
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<cmb_id>[\w-]+)/fields/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $args,
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		$delete_args = $args;
		$delete_args['object_id']['required'] = true;
		$delete_args['object_type']['required'] = true;

		// Returns specific field data.
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<cmb_id>[\w-]+)/fields/(?P<field_id>[\w-]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $args,
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_field_value' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				'permission_callback' => array( $this, 'update_field_value_permissions_check' ),
				'args'                => $args,
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_field_value' ),
				'permission_callback' => array( $this, 'delete_field_value_permissions_check' ),
				'args'                => $delete_args,
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Get all public CMB2 box fields.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$this->initiate_rest_read_box( $request, 'fields_read' );

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		$fields = array();
		foreach ( $this->rest_box->cmb->prop( 'fields', array() ) as $field ) {
			$field_id = $field['id'];
			$rest_field = $this->get_rest_field( $field_id );

			if ( ! is_wp_error( $rest_field ) ) {
				$fields[ $field_id ] = $this->server->response_to_data( $rest_field, isset( $this->request['_embed'] ) );
			} else {
				$fields[ $field_id ] = array( 'error' => $rest_field->get_error_message() );
			}
		}

		return $this->prepare_item( $fields );
	}

	/**
	 * Get one CMB2 field from the collection.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$this->initiate_rest_read_box( $request, 'field_read' );

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		$field = $this->get_rest_field( $this->request->get_param( 'field_id' ) );

		if ( is_wp_error( $field ) ) {
			return $field;
		}

		return $this->prepare_item( $field );
	}

	/**
	 * Update CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_field_value( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_update' );

		if ( ! $this->request['value'] ) {
			return new WP_Error( 'cmb2_rest_update_field_error', __( 'CMB2 Field value cannot be updated without the value parameter specified.', 'cmb2' ), array( 'status' => 400 ) );
		}

		$field = $this->rest_box->field_can_edit( $this->request->get_param( 'field_id' ), true );

		return $this->modify_field_value( 'updated', $field );
	}

	/**
	 * Delete CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_field_value( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_delete' );

		$field = $this->rest_box->field_can_edit( $this->request->get_param( 'field_id' ), true );

		return $this->modify_field_value( 'deleted', $field );
	}

	/**
	 * Modify CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  string     $activity The modification activity (updated or deleted).
	 * @param  CMB2_Field $field    The field object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function modify_field_value( $activity, $field ) {

		if ( ! $this->request['object_id'] || ! $this->request['object_type'] ) {
			return new WP_Error( 'cmb2_rest_modify_field_value_error', __( 'CMB2 Field value cannot be modified without the object_id and object_type parameters specified.', 'cmb2' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		if ( ! $field ) {
			return new WP_Error( 'cmb2_rest_no_field_by_id_error', __( 'No field found by that id.', 'cmb2' ), array( 'status' => 403 ) );
		}

		$field->args["value_{$activity}"] = (bool) 'deleted' === $activity
			? $field->remove_data()
			: $field->save_field( $this->request['value'] );

		// If options page, save the $activity options
		if ( 'options-page' == $this->request['object_type'] ) {
			$field->args["value_{$activity}"] = cmb2_options( $this->request['object_id'] )->set();
		}

		$field_data = $this->get_rest_field( $field );

		if ( is_wp_error( $field_data ) ) {
			return $field_data;
		}

		return $this->prepare_item( $field_data );
	}

	/**
	 * Get a specific field
	 *
	 * @since 2.2.3
	 *
	 * @param  string Field id
	 * @return array|WP_Error
	 */
	public function get_rest_field( $field_id ) {
		$field = $this->rest_box->field_can_read( $field_id, true );

		if ( ! $field ) {
			return new WP_Error( 'cmb2_rest_no_field_by_id_error', __( 'No field found by that id.', 'cmb2' ), array( 'status' => 403 ) );
		}

		$field_data = $this->prepare_field_data( $field );
		$response = rest_ensure_response( $field_data );

		$response->add_links( $this->prepare_links( $field ) );

		return $response;
	}

	/**
	 * Prepare the field data array for JSON.
	 *
	 * @since  2.2.3
	 *
	 * @param  CMB2_Field $field field object.
	 *
	 * @return array             Array of field data.
	 */
	protected function prepare_field_data( CMB2_Field $field ) {
		$field_data = array();
		$params_to_ignore = array( 'show_in_rest', 'options' );
		$params_to_rename = array(
			'label_cb' => 'label',
			'options_cb' => 'options',
		);

		// Run this first so the js_dependencies arg is populated.
		$rendered = ( $cb = $field->maybe_callback( 'render_row_cb' ) )
			// Ok, callback is good, let's run it.
			? $this->get_cb_results( $cb, $field->args(), $field )
			: false;

		$field_args = $field->args();

		foreach ( $field_args as $key => $value ) {
			if ( in_array( $key, $params_to_ignore, true ) ) {
				continue;
			}

			if ( 'options_cb' === $key ) {
				$value = $field->options();
			} elseif ( in_array( $key, CMB2_Field::$callable_fields, true ) ) {

				if ( isset( $this->request['_rendered'] ) ) {
					$value = $key === 'render_row_cb' ? $rendered : $field->get_param_callback_result( $key );
				} elseif ( is_array( $value ) ) {
					// We need to rewrite callbacks as string as they will cause
					// JSON recursion errors.
					$class = is_string( $value[0] ) ? $value[0] : get_class( $value[0] );
					$value = $class . '::' . $value[1];
				}
			}

			$key = isset( $params_to_rename[ $key ] ) ? $params_to_rename[ $key ] : $key;

			if ( empty( $value ) || is_scalar( $value ) || is_array( $value ) ) {
				$field_data[ $key ] = $value;
			} else {
				$field_data[ $key ] = sprintf( __( 'Value Error for %s', 'cmb2' ), $key );
			}
		}

		if ( $this->request['object_id'] && $this->request['object_type'] ) {
			$field_data['value'] = $field->get_data();
		}

		return $field_data;
	}

	/**
	 * Return an array of contextual links for field/fields.
	 *
	 * @since  2.2.3
	 *
	 * @param  CMB2_Field $field Field object to build links from.
	 *
	 * @return array             Array of links
	 */
	protected function prepare_links( $field ) {
		$boxbase      = $this->namespace_base . '/' . $this->rest_box->cmb->cmb_id;
		$query_string = $this->get_query_string();

		$links = array(
			'self' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields/' . $field->_id() . $query_string ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields' . $query_string ),
			),
			'up' => array(
				'href' => rest_url( $boxbase . $query_string ),
			),
		);

		// Don't embed boxes when looking at boxes route.
		if ( '/cmb2/v1/boxes' !== CMB2_REST_Controller::get_intial_route() ) {
			$links['up']['embeddable'] = true;
		}

		return $links;
	}

}
