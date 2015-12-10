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
class CMB2_REST_Controller_Boxes extends CMB2_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.2.0
	 */
	public function register_routes() {
		// Returns all boxes data.
		register_rest_route( CMB2_REST::BASE, '/boxes/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_boxes' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific box's data.
		register_rest_route( CMB2_REST::BASE, '/boxes/(?P<cmb_id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_box' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Get all public fields
	 *
	 * @since 2.2.0
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_boxes( $request ) {
		$this->initiate_request( $request );

		$boxes = CMB2_Boxes::get_by_property( 'show_in_rest', false );

		if ( empty( $boxes ) ) {
			return $this->prepare_item( array( 'error' => __( 'No boxes found.', 'cmb2' ) ), $this->request );
		}

		$boxes_data = array();
		// Loop boxes and get specific field.
		foreach ( $boxes as $key => $cmb ) {
			$boxes_data[ $cmb->cmb_id ] = $this->get_rest_box( $cmb );
		}

		return $this->prepare_item( $boxes_data );
	}

	/**
	 * Get all public fields
	 *
	 * @since 2.2.0
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_box( $request ) {
		$this->initiate_request( $request );

		$cmb_id = $this->request->get_param( 'cmb_id' );

		if ( $cmb_id && ( $cmb = cmb2_get_metabox( $cmb_id, $this->object_id, $this->object_type ) ) ) {
			return $this->prepare_item( $this->get_rest_box( $cmb ) );
		}

		return $this->prepare_item( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ) );
	}
}
