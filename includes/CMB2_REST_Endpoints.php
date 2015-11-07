<?php
/**
 * Creates CMB2 objects/fields endpoint for WordPres REST API
 * allows access to what fields are registered to a specific post type and more.
 *
 * @since  2.1.3
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

class CMB2_REST_Endpoints extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		$version = '1';
		$namespace = 'cmb2/v' . $version;

		// returns all boxes data
		register_rest_route( $namespace, '/boxes/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// returns specific field data
		register_rest_route( $namespace, '/boxes/(?P<box_slug>[\w-]+)/fields/(?P<field_slug>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

	}

	/**
	 * Get all public fields
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_items( $request ) {

		$cmb2 = new CMB2_Boxes();
		$cmb2_boxes = $cmb2->get_all();

		$data = array();

		if( $cmb2_boxes ) {
			// loop meta box and get specific field
			foreach ( $cmb2_boxes as $box ) {
				foreach( $box->meta_box as $key => $value ) {
					if( true === $box->meta_box['show_in_rest'] ){
						$data[$box->meta_box['id']][$key] = $value;
					}
				}
			}
		} else {
			$data = array(  __( 'no boxes found', 'cmb2' ) );
		}
		// return CMB2_REST_Endpoints box object to prepare_item_for_response
		return $this->prepare_item_for_response( $data, $request );
	}

	/**
	 * Get a specific field
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	public function get_item( $request ) {

		$box_slug = $request->get_param( 'box_slug' );
		$field_slug = $request->get_param( 'field_slug' );

		$cmb2 = new CMB2_Boxes();
		$cmb2_boxes = $cmb2->get( $box_slug );

		$data = array();

		if( $cmb2_boxes ) {
			// loop meta box and get specific field
			foreach( $cmb2_boxes->meta_box as $key => $value ) {
				if( true === $cmb2_boxes->meta_box['show_in_rest'] ){
					foreach( $cmb2_boxes->meta_box['fields'] as $field => $field_value ) {
						if( $field_slug === $field ){
							$data[$field] = $field_value;
						} else {
							$data = array( __( 'field does not exist', 'cmb2' ) );
						}
					}
				}
			}

		} else {
			$data = array(  __( 'box does not exist', 'cmb2' ) );
		}
		// return CMB2_REST_Endpoints field to prepare_item_for_response
		return $this->prepare_item_for_response( $data, $request );
	}

	/**
	 * Check if a given request has access to a field or box
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Prepare a CMB2 object for serialization
	 *
	 * @param WP_REST_Request $request
	 * @return array Taxonomy data
	 */
	public function prepare_item_for_response( $data, $request ) {


		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );

		return apply_filters( 'rest_prepare_cmb2', $data, $request );
	}

	/**
	 * Get CMB2 fields schema, conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'CMB2',
			'type'                 => 'object',
			'properties'           => array(
				'description'      => array(
					'description'  => 'A human-readable description of the object.',
					'type'         => 'string',
					'context'      => array( 'view' ),
					),
					'name'             => array(
						'description'  => 'The id for the object.',
						'type'         => 'integer',
						'context'      => array( 'view' ),
						),
				'name'             => array(
					'description'  => 'The title for the object.',
					'type'         => 'string',
					'context'      => array( 'view' ),
					),
				),
			);
		return $this->add_additional_fields_schema( $schema );
	}

}
