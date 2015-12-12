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
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.2.0
	 */
	public function register_routes() {
		// Returns specific box's fields.
		register_rest_route( CMB2_REST::BASE, '/boxes/(?P<cmb_id>[\w-]+)/fields/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_fields' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific field data.
		register_rest_route( CMB2_REST::BASE, '/boxes/(?P<cmb_id>[\w-]+)/fields/(?P<field_id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_field' ),
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
	public function get_fields( $request ) {
		$this->initiate_request( $request );

		$cmb_id = $this->request->get_param( 'cmb_id' );

		if ( $cmb_id && ( $cmb = cmb2_get_metabox( $cmb_id, $this->object_id, $this->object_type ) ) ) {
			$fields = array();
			foreach ( $cmb->prop( 'fields', array() ) as $field ) {
				$field = $this->get_rest_field( $cmb, $field['id'] );

				if ( ! is_wp_error( $field ) ) {
					$fields[ $field['id'] ] = $field;
				} else {
					$fields[ $field['id'] ] = array( 'error' => $field->get_error_message() );
				}
			}

			return $this->prepare_item( $fields );
		}

		return $this->prepare_item( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ) );
	}

	/**
	 * Get a specific field
	 *
	 * @since 2.2.0
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array|WP_Error
	 */
	public function get_field( $request ) {
		$this->initiate_request( $request );

		$cmb = cmb2_get_metabox( $this->request->get_param( 'cmb_id' ), $this->object_id, $this->object_type  );

		if ( ! $cmb ) {
			return $this->prepare_item( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ) );
		}

		$field = $this->get_rest_field( $cmb, $this->request->get_param( 'field_id' ) );

		if ( is_wp_error( $field ) ) {
			return $this->prepare_item( array( 'error' => $field->get_error_message() ) );
		}

		return $this->prepare_item( $field );
	}
}
