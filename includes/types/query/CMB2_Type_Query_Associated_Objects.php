<?php
/**
 * CMB2 Query Associated Objects
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
 * Class CMB2_Type_Query_Associated_Objects
 */
abstract class CMB2_Type_Query_Associated_Objects {

	/**
	 * The query source object type.
	 *
	 * @since 2.X.X
	 * @var string
	 */
	protected $source_type = '';

	/**
	 * [$field description]
	 *
	 * @since 2.X.X
	 *
	 * @var [type]
	 */
	protected $field;

	/**
	 * @since 2.X.X
	 * @var array
	 */
	protected $original_args = array();

	/**
	 * @since 2.X.X
	 * @var array
	 */
	protected $query_args = array();

	/**
	 * @since 2.X.X
	 * @var array
	 */
	public $objects = array();

	/**
	 * CMB2_Type_Query_Associated_Objects constructor.
	 *
	 * @since 2.X.X
	 *
	 * @param CMB2_field $field
	 * @param $args
	 */
	public function __construct( CMB2_field $field, $args ) {
		$this->field = $field;
		$this->original_args = (array) $args;
		$this->set_query_args( $args );
	}

	/**
	 * @since 2.X.X
	 *
	 * @param CMB2_field $field
	 * @param array $args
	 * @param string $source_object_type
	 *
	 * @return CMB2_Type_Query_Associated_Objects
	 */
	public static function get_query_object( CMB2_field $field, $args = array(), $source_object_type = null ) {
		if ( empty( $source_object_type ) ) {
			$source_object_type = $field->options( 'source_object_type' );
		}

		if ( empty( $args ) ) {
			$args = (array) $field->options( 'query_args' );
		}

		/**
		 * A filter to bypass fetching the default CMB2_Type_Query_Associated_Objects object.
		 *
		 * Passing a CMB2_Type_Query_Associated_Objects object will short-circuit the method.
		 *
		 * @param null|CMB2_Type_Query_Associated_Objects $query Default null value.
		 * @param array $field The CMB2_Field object.
		 * @param array $args Array of arguments for the CMB2_Type_Query_Associated_Objects object.
		 * @param string $source_object_type The object type being requested.
		 */
		$query = apply_filters( 'cmb2_pre_type_associated_objects_query', null, $field, $args, $source_object_type );

		if ( ! ( $query instanceof CMB2_Type_Query_Associated_Objects ) ) {
			switch ( $source_object_type ) {
				case 'user';
					$query = new CMB2_Type_Query_Associated_Users( $field, $args );
					break;
				case 'term':
					$query = new CMB2_Type_Query_Associated_Terms( $field, $args );
					break;
				case 'post':
				default:
					$query = new CMB2_Type_Query_Associated_Posts( $field, $args );
					break;
			}
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
		$this->query_args = wp_parse_args( $args, $this->default_query_args( $args ) );
	}

	/**
	 * [get_query_args description]
	 *
	 * @since  2.X.X
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
	public function get_source_type() {
		return $this->source_type;
	}

	/**
	 * Execute the appropriate query callback for the current query type.
	 *
	 * @return array
	 */
	public function execute_query() {
		$this->setup_query();

		$this->objects = array();
		foreach ( (array) $this->fetch() as $object ) {
			$this->objects[ $this->get_id( $object ) ] = $object;
		}

		return $this->objects;
	}

	/**
	 * Setup the query.
	 *
	 * @return CMB2_Type_Associated_Objects
	 */
	public function setup_query() {
		$this->maybe_exclude_self();

		/**
		 * A filter to override the default query arguments just before fetching/querying.
		 *
		 * @param array $query_args The query arguments.
		 * @param CMB2_Type_Query_Associated_Objects $query The associated objects query object.
		 */
		$this->query_args = apply_filters( 'cmb2_associated_objects_query_args', $this->query_args, $this );

		return $this;
	}

	/**
	 * If the soruce object type matches the current object type, let's remove
	 * the current object from the query.
	 *
	 * @return CMB2_Type_Associated_Objects
	 */
	protected function maybe_exclude_self() {
		if ( $this->field->object_id() && $this->field->object_type() === $this->get_source_type() ) {
			$this->set_exclude( $this->field->object_id() );
		}

		return $this;
	}

	/**
	 * @return string
	 */
	abstract public function fetch();

	/**
	 * @param $args
	 * @return array
	 */
	abstract public function default_query_args( $args = array() );

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
		$this->query_args['exclude'] = array_merge( $this->query_args['exclude'], (array) $ids );
		$this->query_args['exclude'] = array_unique( $this->query_args['exclude'] );
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
