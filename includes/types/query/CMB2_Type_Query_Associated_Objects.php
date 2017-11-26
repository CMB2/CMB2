<?php
/**
 * CMB2 Query Associated Objects
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
 * Class CMB2_Type_Query_Associated_Objects
 */
abstract class CMB2_Type_Query_Associated_Objects {

	/**
	 * The query object type.
	 *
	 * @var string
	 */
	protected $query_type = '';

	/**
	 * @var array
	 */
	protected $original_args = array();

	/**
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * @var array
	 */
	public $objects = array();

	/**
	 * CMB2_Type_Query_Associated_Objects constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$this->original_args = $args;
		$this->set_query_args( $args );
	}

	/**
	 * @param string $query_object_type
	 * @param array $args
	 *
	 * @return CMB2_Type_Query_Associated_Objects
	 */
	public static function get_query_object( $query_object_type, $args, $field ) {

		/**
		 * A filter to bypass fetching the default CMB2_Type_Query_Associated_Objects object.
		 *
		 * Passing a CMB2_Type_Query_Associated_Objects object will short-circuit the method.
		 *
		 * @param null|CMB2_Type_Query_Associated_Objects $query Default null value.
		 * @param string $query_object_type The object type being requested.
		 * @param array $args Array of arguments for the CMB2_Type_Query_Associated_Objects object.
		 * @param array $field The CMB2_Field object.
		 */
		$query = apply_filters( 'cmb2_pre_type_associated_objects_query', null, $query_object_type, $args, $field );

		if ( $query instanceof CMB2_Type_Query_Associated_Objects ) {
			return $query;
		}

		switch ( $query_object_type ) {
			case 'user';
				$query = new CMB2_Type_Query_Associated_Users( $args );
				break;
			case 'term':
				$query = new CMB2_Type_Query_Associated_Terms( $args );
				break;
			case 'post':
			default:
				$query = new CMB2_Type_Query_Associated_Posts( $args );
		}

		return $query;
	}

	/**
	 * @param $args
	 */
	public function set_query_args( $args ) {
		if ( ! is_array( $args ) ) {
			$args = array();
		}
		$this->query_args = wp_parse_args( $args, $this->default_query_args() );
	}

	/**
	 * [get_query_args description]
	 *
	 * @since  [since]
	 *
	 * @return [type]  [description]
	 */
	public function get_query_args() {
		return $this->query_args;
	}

	/**
	 * @param $arg
	 * @param $fallback
	 */
	public function get_query_arg( $arg, $fallback = null ) {
		return isset( $this->query_args[ $arg ] ) ? $this->query_args[ $arg ] : $fallback;
	}

	/**
	 * Reset the query args to their original values.
	 */
	public function reset_query_args() {
		$this->set_query_args( $this->original_args );
	}

	/**
	 * Get the query object type.
	 *
	 * @return string
	 */
	public function get_query_type() {
		return $this->query_type;
	}

	/**
	 * Execute the appropriate query callback for the current query type.
	 *
	 * @return array
	 */
	public function execute_query() {
		$this->objects = array();
		$objects = $this->fetch();

		foreach ( $objects as $object ) {
			$this->objects[ $this->get_id( $object ) ] = $object;
		}

		return $this->objects;
	}

	/**
	 * @return string
	 */
	abstract public function fetch();

	/**
	 * @return array
	 */
	abstract public function default_query_args();

	/**
	 * @param object $object
	 *
	 * @return int
	 */
	abstract public function get_id( $object );

	/**
	 *
	 * @param object $object
	 *
	 * @return string
	 */
	abstract public function get_title( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract public function get_thumb( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract public function get_edit_link( $object );

	/**
	 * @param $id
	 *
	 * @return object
	 */
	abstract public function get_object( $id );

	/**
	 * @param object $object
	 *
	 * @return mixed
	 */
	abstract public function get_object_type_label( $object );

	/**
	 * @return array
	 */
	abstract public function get_all_object_type_labels();

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_include( $ids ) {
		$this->query_args['include'] = $ids;
	}

	/**
	 * @param int $count
	 *
	 * @return void
	 */
	public function set_number( $count ) {
		$this->query_args['number'] = $count;
	}

	/**
	 * @param $ids
	 *
	 * @return void
	 */
	public function set_exclude( $ids ) {
		$this->query_args['exclude'] = $ids;
	}

	/**
	 * @param $search
	 *
	 * @return void
	 */
	public function set_search( $search ) {
		$this->query_args['s'] = $search;
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_one( $object ) {
		return '&mdash;';
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_two( $object ) {
		return '&mdash;';
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_three( $object ) {
		return '&mdash;';
	}

	/**
	 * @return mixed
	 */
	public function get_search_column_four( $object ) {
		return '&mdash;';
	}

	/**
	 * @return string
	 */
	public function get_search_column_one_label() {
		return __( 'Title' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_two_label() {
		return __( 'Type' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_three_label() {
		return __( 'Date' );
	}

	/**
	 * @return string
	 */
	public function get_search_column_four_label() {
		return __( 'Status' );
	}
}
