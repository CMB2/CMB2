<?php
/**
 * CMB2 Associated Objects Search
 * (i.e. a lot of work to get oEmbeds to work with non-post objects)
 *
 * @since  2.X.X
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 */
class CMB2_Associated_Objects_Search {

	/**
	 * [$args description]
	 *
	 * @since 2.X.X
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * [$field description]
	 *
	 * @since 2.X.X
	 *
	 * @var [type]
	 */
	protected $field;

	/**
	 * The field instance's query handler.
	 *
	 * @var CMB2_Type_Query_Associated_Objects
	 */
	protected $query;

	/**
	 * [__construct description]
	 *
	 * @since 2.X.X
	 *
	 * @param array $args [description]
	 */
	public function __construct( $args ) {
		$this->args = $args;

		$group    = isset( $args['group_id'] ) ? $args['group_id'] : '';
		$field_id = isset( $args['field_id'] ) ? $args['field_id'] : '';
		$cmb      = cmb2_get_metabox(
			isset( $args['cmb_id'] ) ? $args['cmb_id'] : '',
			isset( $args['object_id'] ) ? $args['object_id'] : 0,
			isset( $args['object_type'] ) ? $args['object_type'] : '0'
		);

		if ( $cmb && $group ) {
			$group = $cmb->get_field( $group );
		}

		if ( $cmb && $field_id ) {
			$group = $group ? $group : null;
			$this->field = $cmb->get_field( $field_id, $group );
		}

		if ( $this->field ) {
			$object_type = self::get_object_type( $args );

			// Unset args which we don't want to pass to the query object.
			unset( $args['group_id'] );
			unset( $args['field_id'] );
			unset( $args['cmb_id'] );
			unset( $args['source_object_type'] );

			$this->query = CMB2_Type_Query_Associated_Objects::get_query_object(
				$this->field,
				$args,
				$object_type
			);
		}
	}

	/**
	 * [get_object_type description]
	 *
	 * @since  2.X.X
	 *
	 * @param  [type]  $args [description]
	 *
	 * @return [type]        [description]
	 */
	public static function get_object_type( $args ) {
		$type = 'post';

		if ( ! empty( $args['source_object_type'] ) ) {
			$type = $args['source_object_type'];
			if ( is_array( $type ) && 1 === count( $type ) ) {
				$type = sanitize_text_field( end( $type ) );
			} elseif ( is_string( $type ) ) {
				$type = sanitize_text_field( $type );
			}
		}

		return $type;
	}

	/**
	 * [do_search description]
	 *
	 * @todo wp_ajax_find_posts() equivalents for non-post objects.
	 *
	 * @since  2.X.X
	 *
	 * @return void
	 */
	public function do_search() {
		if ( ! $this->query ) {
			return;
		}

		switch ( $this->query->get_source_type() ) {
			case 'user':
				// This is not working until we fix the user query bit.
				// add_action( 'pre_get_users', array( $this, 'maybe_callback' ) );
				$this->find( 'user' );
				break;

			default:
				// add_action( 'pre_get_posts', array( $this, 'modify_post_query' ) );
				// wp_ajax_find_posts();
				$this->find( 'post' );
				break;
		}
	}

	/**
	 * Modify the user search query.
	 * @todo Make this work
	 *
	 * @since  2.X.X
	 *
	 * @param  WP_User_Query  $query WP_User_Query instance during the pre_get_posts hook.
	 *
	 * @return void
	 */
	// public function modify_user_query( $query ) {
	// 	$this->maybe_callback( $query );
	// }

	/**
	 * Modify the post search query.
	 *
	 * @since  2.X.X
	 *
	 * @param  WP_Query  $query WP_Query instance during the pre_get_posts hook.
	 *
	 * @return void
	 */
	// public function modify_post_query( $query ) {
	// 	if ( $this->query ) {
	// 		$this->query->setup_query();
	// 		// ps                 : this.$input.val(),
	// 		// action             : 'cmb2_associated_objects_search',
	// 		// source_object_type : this.sourceType,
	// 		// search_types       : this.types,
	// 		// cmb_id             : this.cmbId,
	// 		// group_id           : this.groupId,
	// 		// field_id           : this.fieldId,
	// 		// object_id          : this.objectId,
	// 		// object_type        : this.objectType,
	// 		// exclude            : this.exclude,
	// 		// retrieved          : retrieved,
	// 		// _ajax_nonce        : $( '#find-posts #_ajax_nonce' ).val(),

	// 	}
	// 	$types = $this->get_arg( 'search_types', array() );
	// 	$types = is_array( $types ) ? array_map( 'esc_attr', $types ) : esc_attr( $types );

	// 	$query->set( 'post_type', $types );
	// 	$this->maybe_callback( $query );
	// }

	/**
	 * If field has a 'search_query_cb' param, run the callback.
	 *
	 * @since  2.X.X
	 *
	 * @param  mixed $query     The query instance during the pre_get_* hook.
	 *
	 * @return void
	 */
	// public function maybe_callback( $query ) {
	// 	if ( $this->field ) {
	// 		$cb = $this->field->maybe_callback( 'search_query_cb' );
	// 		if ( $cb ) {
	// 			call_user_func( $cb, $query, $this->field, $this );
	// 		}
	// 	}
	// }

	public function get_ids_to_exclude() {
		$ids     = (array) $this->get_and_absint_array( 'retrieved' );
		$exclude = $this->get_and_absint_array( 'exclude' );

		if ( ! empty( $exclude ) ) {

			// Exclude objects already existing.
			$ids = array_merge( $ids, $exclude );
		}

		return $ids;
	}

	public function get_and_absint_array( $arg ) {
		$arg_val = $this->get_arg( $arg, array() );

		return ! empty( $arg_val ) && is_array( $arg_val )
			? array_map( 'absint', $arg_val )
			: array();
	}

	/**
	 * @param $arg
	 * @param $fallback
	 */
	public function get_arg( $arg, $fallback = null ) {
		return isset( $this->args[ $arg ] ) ? $this->args[ $arg ] : $fallback;
	}

	public function find( $object_type ) {
		check_ajax_referer( 'cmb2-find-posts', 'nonce' );

		$s = wp_unslash( $this->get_arg( 'ps' ) );

		if ( '' !== $s ) {
			$this->query->set_search( sanitize_text_field( $s ) );
			$this->query->set_search( sanitize_text_field( $s ) );
		}

		$args = $this->query->get_query_args();

		unset( $args['ps'] );
		unset( $args['action'] );
		unset( $args['search_types'] );
		unset( $args['object_id'] );
		unset( $args['object_type'] );
		unset( $args['exclude'] );
		unset( $args['retrieved'] );
		unset( $args['nonce'] );

		$types = $this->get_arg( 'search_types', array() );
		$types = is_array( $types ) ? array_map( 'esc_attr', $types ) : esc_attr( $types );

		$args['post_type'] = $types;;

		$this->query->set_query_args( $args );
		$this->query->set_exclude( (array) (array) $this->get_ids_to_exclude() );
		$objects = $this->query->execute_query();

		if ( ! $objects ) {
			wp_send_json_error( __( 'No items found.' ) );
		}

		$data = array(
			'objectType'       => $object_type,
			'columnOneLabel'   => $this->query->get_search_column_one_label(),
			'columnTwoLabel'   => $this->query->get_search_column_two_label(),
			'columnThreeLabel' => $this->query->get_search_column_three_label(),
			'columnFourLabel'  => $this->query->get_search_column_four_label(),
			'results'          => array(),
		);

		foreach ( $objects as $object ) {
			$data['results'][] = array(
				'postId' => $this->query->get_id( $object ),
				'columnOne' => $this->query->get_search_column_one( $object ),
				'columnTwo' => ! empty( $data['columnTwoLabel'] )
					? $this->query->get_search_column_two( $object )
					: '',
				'columnThree' => ! empty( $data['columnThreeLabel'] )
					? $this->query->get_search_column_three( $object )
					: '',
				'columnFour' => ! empty( $data['columnFourLabel'] )
					? $this->query->get_search_column_four( $object )
					: '',
			);
		}

		wp_send_json_success( $data );
	}

}
