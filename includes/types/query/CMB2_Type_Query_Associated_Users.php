<?php
/**
 * CMB2 Query Associated Users
 *
 * @since  2.2.7
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

/**
 * Class CMB2_Type_Query_Associated_Users
 */
class CMB2_Type_Query_Associated_Users extends CMB2_Type_Query_Associated_Objects {

	/**
	 * @return string
	 */
	public function query_type() {
		return 'user';
	}

	/**
	 * @return array
	 */
	public function default_query_args() {
		return array(
			'number'  => 100,
			'exclude' => array(),
		);
	}

	/**
	 *
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_title( $object ) {
		return $object->data->display_name;
	}

	/**
	 * @param WP_User $object
	 *
	 * @return false|string
	 */
	public function get_thumb( $object ) {
		return get_avatar( $object->ID, 25 );
	}

	/**
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_edit_link( $object ) {
		return get_edit_user_link( $object->ID );
	}

	/**
	 * @param int $id
	 *
	 * @return false|WP_User
	 */
	public function get_object( $id ) {
		return get_user_by( 'id', absint( $id ) );
	}

	/**
	 * @param WP_User $object
	 *
	 * @return mixed
	 */
	public function get_id( $object ) {
		return $object->ID;
	}

	/**
	 * @param WP_User $object
	 *
	 * @return string
	 */
	public function get_object_type_label( $object ) {
		return __( 'Users' );
	}

	/**
	 * @return array
	 */
	public function get_all_object_type_labels() {
		return array(
			__( 'Users' ),
		);
	}
}
