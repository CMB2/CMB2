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
				'description' => __( 'When the \'_rendered\' argument is passed, the renderable field attributes will be returned fully rendered. By default, the names of the callback handers for the renderable attributes will be returned.', 'cmb2' ),
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
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'callback'            => array( $this, 'get_items' ),
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
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'callback'            => array( $this, 'get_item' ),
				'args'                => $args,
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'callback'            => array( $this, 'update_item' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				'args'                => $args,
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'callback'            => array( $this, 'delete_item' ),
				'args'                => $delete_args,
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Check if a given request has access to get fields.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		$this->initiate_rest_read_box( $request, 'fields_read' );
		$can_access = true;

		/**
		 * By default, no special permissions needed.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_access Whether this CMB2 endpoint can be accessed.
		 * @param object $controller This CMB2_REST_Controller object.
		 */
		return $this->maybe_hook_callback_and_apply_filters( 'cmb2_api_get_fields_permissions_check', $can_access );
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
		if ( ! $this->rest_box ) {
			$this->initiate_rest_read_box( $request, 'fields_read' );
		}

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		$fields = array();
		foreach ( $this->rest_box->cmb->prop( 'fields', array() ) as $field ) {

			// Make sure this field can be read.
			$this->field = $this->rest_box->field_can_read( $field['id'], true );

			// And make sure current user can view this box.
			if ( $this->field && $this->get_item_permissions_check_filter() ) {
				$fields[ $field['id'] ] = $this->server->response_to_data(
					$this->prepare_field_response(),
					isset( $this->request['_embed'] )
				);
			}
		}

		return $this->prepare_item( $fields );
	}

	/**
	 * Check if a given request has access to a field.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		$this->initiate_rest_read_box( $request, 'field_read' );
		if ( ! is_wp_error( $this->rest_box ) ) {
			$this->field = $this->rest_box->field_can_read( $this->request->get_param( 'field_id' ), true );
		}

		return $this->get_item_permissions_check_filter();
	}

	/**
	 * Check by filter if a given request has access to a field.
	 * By default, no special permissions needed, but filtering return value.
	 *
	 * @since 2.2.3
	 *
	 * @param  bool $can_access Whether the current request has access to view the field by default.
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
		return $this->maybe_hook_callback_and_apply_filters( 'cmb2_api_get_field_permissions_check', $can_access );
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

		return $this->prepare_read_field( $this->request->get_param( 'field_id' ) );
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
	public function update_item_permissions_check( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_update' );
		if ( ! is_wp_error( $this->rest_box ) ) {
			$this->field = $this->rest_box->field_can_edit( $this->request->get_param( 'field_id' ), true );
		}

		$can_update = current_user_can( 'edit_others_posts' );

		/**
		 * By default, 'edit_others_posts' is required capability.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_update Whether this CMB2 endpoint can be accessed.
		 * @param object $controller This CMB2_REST_Controller object.
		 */
		return $this->maybe_hook_callback_and_apply_filters( 'cmb2_api_update_field_value_permissions_check', $can_update );
	}

	/**
	 * Update CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_update' );

		if ( ! $this->request['value'] ) {
			return new WP_Error( 'cmb2_rest_update_field_error', __( 'CMB2 Field value cannot be updated without the value parameter specified.', 'cmb2' ), array( 'status' => 400 ) );
		}

		return $this->modify_field_value( 'updated' );
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
	public function delete_item_permissions_check( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_delete' );
		if ( ! is_wp_error( $this->rest_box ) ) {
			$this->field = $this->rest_box->field_can_edit( $this->request->get_param( 'field_id' ), true );
		}

		$can_delete = current_user_can( 'delete_others_posts' );

		/**
		 * By default, 'delete_others_posts' is required capability.
		 *
		 * @since 2.2.3
		 *
		 * @param bool   $can_delete Whether this CMB2 endpoint can be accessed.
		 * @param object $controller This CMB2_REST_Controller object.
		 */
		return $this->maybe_hook_callback_and_apply_filters( 'cmb2_api_delete_field_value_permissions_check', $can_delete );
	}

	/**
	 * Delete CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$this->initiate_rest_read_box( $request, 'field_value_delete' );

		return $this->modify_field_value( 'deleted' );
	}

	/**
	 * Modify CMB2 field value.
	 *
	 * @since 2.2.3
	 *
	 * @param  string $activity The modification activity (updated or deleted).
	 * @return WP_Error|WP_REST_Response
	 */
	public function modify_field_value( $activity) {

		if ( ! $this->request['object_id'] || ! $this->request['object_type'] ) {
			return new WP_Error( 'cmb2_rest_modify_field_value_error', __( 'CMB2 Field value cannot be modified without the object_id and object_type parameters specified.', 'cmb2' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $this->rest_box ) ) {
			return $this->rest_box;
		}

		$this->field = $this->rest_box->field_can_edit(
			$this->field ? $this->field : $this->request->get_param( 'field_id' ),
			true
		);

		if ( ! $this->field ) {
			return new WP_Error( 'cmb2_rest_no_field_by_id_error', __( 'No field found by that id.', 'cmb2' ), array( 'status' => 403 ) );
		}

		$this->field->args["value_{$activity}"] = (bool) 'deleted' === $activity
			? $this->field->remove_data()
			: $this->field->save_field( $this->request['value'] );

		// If options page, save the $activity options
		if ( 'options-page' == $this->request['object_type'] ) {
			$this->field->args["value_{$activity}"] = cmb2_options( $this->request['object_id'] )->set();
		}

		return $this->prepare_read_field( $this->field );
	}

	/**
	 * Get a response object for a specific field ID.
	 *
	 * @since 2.2.3
	 *
	 * @param  string\CMB2_Field Field id or Field object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_read_field( $field ) {
		$this->field = $this->rest_box->field_can_read( $field, true );

		if ( ! $this->field ) {
			return new WP_Error( 'cmb2_rest_no_field_by_id_error', __( 'No field found by that id.', 'cmb2' ), array( 'status' => 403 ) );
		}

		return $this->prepare_item( $this->prepare_field_response() );
	}

	/**
	 * Get a specific field response.
	 *
	 * @since 2.2.3
	 *
	 * @param  CMB2_Field Field object.
	 * @return array      Response array.
	 */
	public function prepare_field_response() {
		$field_data = $this->prepare_field_data( $this->field );
		$response = rest_ensure_response( $field_data );

		$response->add_links( $this->prepare_links( $this->field ) );

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
				'embeddable' => true,
				'href' => rest_url( $boxbase . $query_string ),
			),
		);

		return $links;
	}

	/**
	 * Checks if the CMB2 box or field has any registered callback parameters for the given filter.
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
	 * @return bool                The possibly-modified filter value (if the _cb param is a non-callable).
	 */
	public function maybe_hook_registered_callback( $filter, $default_val ) {
		$default_val = parent::maybe_hook_registered_callback( $filter, $default_val );

		if ( $this->field ) {

			// Hook field specific filter callbacks.
			$val = $this->field->maybe_hook_parameter( $filter, $default_val );
			if ( null !== $val ) {
				$default_val = $val;
			}
		}

		return $default_val;
	}

	/**
	 * Unhooks any CMB2 box or field registered callback parameters for the given filter.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $filter The filter name.
	 *
	 * @return void
	 */
	public function maybe_unhook_registered_callback( $filter ) {
		parent::maybe_unhook_registered_callback( $filter );

		if ( $this->field ) {
			// Unhook field specific filter callbacks.
			$this->field->maybe_hook_parameter( $filter, null, 'remove_filter' );
		}
	}

}
