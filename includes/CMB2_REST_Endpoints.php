<?php
/**
 * Creates  CMB2 objects/fields endpoint for WordPres REST API
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

		register_rest_route( 'cmb2/v1', '/fields/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'args'            => array(
					'post_type'   => array(
						'sanitize_callback' => 'sanitize_key',
					),
				),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( 'cmb2/v1', '/fields/(?P<field_slug>[\w-]+)', array(
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

		$data = array( 'cmb box','cmb box','cmb box','cmb box' );

		return $data;
	}

	/**
	 * Get a specific field
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	public function get_item( $request ) {

		$data = array(
			'name' => 'single cmb box',
			'description' => 'the description of a single cmb box'
		);

		return $this->prepare_item_for_response( (object) $data, $request );
	}

	/**
	 * Check if a given request has access to a field
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
	public function prepare_item_for_response( $cmb2, $request ) {

		$data = array(
			'name'         => $cmb2->name,
			'description'  => $cmb2->description,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );

		return apply_filters( 'rest_prepare_cmb2', $data, $cmb2, $request );
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
					'description'  => 'The title for the object.',
					'type'         => 'string',
					'context'      => array( 'view' ),
					),
				),
			);
		return $this->add_additional_fields_schema( $schema );
	}

}
