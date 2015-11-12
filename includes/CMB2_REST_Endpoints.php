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
class CMB2_REST_Endpoints extends WP_REST_Controller {

	/**
	 * The current CMB2 REST endpoint version
	 * @var string
	 * @since 2.2.0
	 */
	public $version = '1';

	/**
	 * The CMB2 REST namespace
	 * @var string
	 * @since 2.2.0
	 */
	public $namespace = 'cmb2/v';

	/**
	 * Constructor
	 * @since 2.2.0
	 */
	public function __construct() {
		$this->namespace .= $this->version;
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		// Returns all boxes data.
		register_rest_route( $this->namespace, '/boxes/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_all_boxes' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific box's data.
		register_rest_route( $this->namespace, '/boxes/(?P<cmb_id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_box' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific box's fields.
		register_rest_route( $this->namespace, '/boxes/(?P<cmb_id>[\w-]+)/fields/', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_box_fields' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		// Returns specific field data.
		register_rest_route( $this->namespace, '/boxes/(?P<cmb_id>[\w-]+)/fields/(?P<field_id>[\w-]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_field' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

	}

	/**
	 * Get all public fields
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_all_boxes( $request ) {
		$boxes = CMB2_Boxes::get_by_property( 'show_in_rest', false );

		if ( empty( $boxes ) ) {
			return $this->prepare_item_for_response( array( 'error' => __( 'No boxes found.', 'cmb2' ) ), $request );
		}

		$boxes_data = array();
		// Loop boxes and get specific field.
		foreach ( $boxes as $key => $cmb ) {
			$boxes_data[ $cmb->cmb_id ] = $this->get_rest_box( $cmb );
		}

		return $this->prepare_item_for_response( $boxes_data, $request );
	}

	/**
	 * Get all public fields
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_box( $request ) {
		$cmb_id = $request->get_param( 'cmb_id' );

		if ( $cmb_id && ( $cmb = cmb2_get_metabox( $cmb_id ) ) ) {
			return $this->prepare_item_for_response( $this->get_rest_box( $cmb ), $request );
		}

		return $this->prepare_item_for_response( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ), $request );
	}

	/**
	 * Get all box fields
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array
	 */
	public function get_box_fields( $request ) {
		$cmb_id = $request->get_param( 'cmb_id' );

		if ( $cmb_id && ( $cmb = cmb2_get_metabox( $cmb_id ) ) ) {
			$fields = array();
			foreach ( $cmb->prop( 'fields', array() ) as $field ) {
				$field = $this->get_rest_field( $cmb, $field['id'] );

				if ( ! is_wp_error( $field ) ) {
					$fields[ $field['id'] ] = $field;
				} else {
					$fields[ $field['id'] ] = array( 'error' => $field->get_error_message() );
				}
			}

			return $this->prepare_item_for_response( $fields, $request );
		}

		return $this->prepare_item_for_response( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ), $request );
	}

	/**
	 * Get a CMB2 box prepared for REST
	 *
	 * @param CMB2 $cmb
	 * @return array
	 */
	public function get_rest_box( $cmb ) {
		// TODO: handle callable properties
		$boxes_data = $cmb->meta_box;

		unset( $boxes_data['fields'] );

		// TODO research adding links the proper way
		// $data->add_links( $this->prepare_links( $post ) );

		$base = $this->namespace . '/boxes/' . $cmb->cmb_id;
		$boxbase = $base . '/' . $cmb->cmb_id;

		// Entity meta
		$boxes_data['_links'] = array(
			'self' => array(
				'href' => rest_url( trailingslashit( $boxbase ) ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $base ) ),
			),
			'fields' => array(
				'href' => rest_url( trailingslashit( $boxbase ) . 'fields/' ),
			),
		);

		return $boxes_data;
	}

	/**
	 * Get a specific field
	 *
	 * @param WP_REST_Request $request The API request object.
	 * @return array|WP_Error
	 */
	public function get_field( $request ) {
		$cmb = cmb2_get_metabox( $request->get_param( 'cmb_id' ) );

		if ( ! $cmb ) {
			return $this->prepare_item_for_response( array( 'error' => __( 'No box found by that id.', 'cmb2' ) ), $request );
		}

		$field = $this->get_rest_field( $cmb, $request->get_param( 'field_id' ) );

		if ( is_wp_error( $field ) ) {
			return $this->prepare_item_for_response( array( 'error' => $field->get_error_message() ), $request );
		}

		return $this->prepare_item_for_response( $field, $request );
	}

	/**
	 * Get a specific field
	 *
	 * @param CMB2 $cmb
	 * @return array|WP_Error
	 */
	public function get_rest_field( $cmb, $field_id ) {
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


		$field_data = array();
		$params_to_ignore = array( 'show_on_cb', 'show_in_rest', 'options' );
		$params_to_rename = array(
			'label_cb' => 'label',
			'options_cb' => 'options',
		);

		foreach ( $field->args() as $key => $value ) {
			if ( in_array( $key, $params_to_ignore, true ) ) {
				continue;
			}

			if ( 'render_row_cb' === $key ) {
				$value = '';
				// TODO: Use request get object
				if ( isset( $_GET['rendered'] ) && ( $cb = $field->maybe_callback( 'render_row_cb' ) ) ) {
					// Ok, callback is good, let's run it.
					ob_start();
					call_user_func( $cb, $field->args(), $this );
					$field_data[ 'rendered' ] = ob_get_clean();
				}
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
		$field_data['value'] = $field->get_data();

		$base = $this->namespace . '/boxes/' . $cmb->cmb_id;

		// Entity meta
		$field_data['_links'] = array(
			'self' => array(
				'href' => rest_url( trailingslashit( $base ) . 'fields/' . $field->_id() ),
			),
			'collection' => array(
				'href' => rest_url( trailingslashit( $base ) . 'fields/' ),
			),
			'box' => array(
				'href' => rest_url( trailingslashit( $base ) ),
			),
		);

		return $field_data;
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
	 * @param  mixed           $data
	 * @param  WP_REST_Request $request
	 * @return array           Taxonomy data
	 */
	public function prepare_item_for_response( $data, $request ) {

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->filter_response_by_context( $data, $context );

		return apply_filters( 'cmb2_rest_prepare', $data, $request );
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
