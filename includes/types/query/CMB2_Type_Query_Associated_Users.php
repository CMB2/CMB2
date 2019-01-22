<?php
/**
 * CMB2 Query Associated Users
 *
 * @since  2.X.X
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
	 * The query source object type.
	 *
	 * @var string
	 */
	protected $source_type = 'user';

	/**
	 * @return mixed
	 */
	public function fetch() {
		return get_users( $this->query_args );
	}

	/**
	 * @param $args
	 * @return array
	 */
	public function default_query_args( $args = array() ) {
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

	/**
	 * @return mixed
	 */
	public function get_search_column_one( $object ) {
		return '<span title="' . $object->user_email . '">' . $object->user_login . '</span>';
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_two( $object ) {
		$title = $this->get_title( $object );
		$title = trim( $title ) ? $title : __( '(no title)' );
		return '<span title="' . $object->user_email . '">' . $title . '</span>';
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_three( $object ) {
		$wp_roles = wp_roles();
		$role_list = array();

		foreach ( $object->roles as $role ) {
			if ( isset( $wp_roles->role_names[ $role ] ) ) {
				$role_list[ $role ] = translate_user_role( $wp_roles->role_names[ $role ] );
			}
		}

		if ( empty( $role_list ) ) {
			$role_list['none'] = _x( 'None', 'no user roles' );
		}

		// Comma-separated list of user roles.
		return implode( ', ', $role_list );
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_four( $object ) {
		$email = $object->user_email;
		return "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";
	}

	/**
	 * @return string
	 */
	public function get_search_column_one_label() {
		return __( 'Username' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_two_label() {
		return __( 'Name' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_three_label() {
		return __( 'Role' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_four_label() {
		return '';
	}

	/**
	 * @param $search
	 *
	 * @return void
	 */
	public function set_search( $search ) {
		$this->query_args['search'] = $search;
	}
}
