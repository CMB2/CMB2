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
	 * Map of query type to appropriate callback.
	 *
	 * @var array
	 */
	protected $query_type_callbacks = array(
		'post' => 'get_posts',
		'user' => 'get_users',
		'term' => 'get_terms',
	);

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
	 * @param $args
	 */
	public function set_query_args( $args ) {
		$this->query_args = wp_parse_args( $args, $this->default_query_args() );
	}

	/**
	 * Reset the query args to their original values.
	 */
	public function reset_query_args() {
		$this->set_query_args( $this->original_args );
	}

	/**
	 * Execute the appropriate query callback for the current query type.
	 *
	 * @return array
	 */
	public function execute_query() {
		if ( ! isset( $this->query_type_callbacks[ $this->query_type() ] ) || ! is_callable( $this->query_type_callbacks[ $this->query_type() ] ) ) {
			return array();
		}

		$this->objects = array();
		$objects = call_user_func( $this->query_type_callbacks[ $this->query_type() ], $this->query_args );

		foreach ( $objects as $object ) {
			$this->objects[ $this->get_id( $object ) ] = $object;
		}

		return $this->objects;
	}

	/**
	 * @return string
	 */
	abstract function query_type();

	/**
	 * @return array
	 */
	abstract function default_query_args();

	/**
	 * @param object $object
	 *
	 * @return int
	 */
	abstract function get_id( $object );

	/**
	 *
	 * @param object $object
	 *
	 * @return string
	 */
	abstract function get_title( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract function get_thumb( $object );

	/**
	 * @param $object
	 *
	 * @return string
	 */
	abstract function get_edit_link( $object );

	/**
	 * @param $id
	 *
	 * @return object
	 */
	abstract function get_object( $id );

	/**
	 * @param object $object
	 *
	 * @return mixed
	 */
	abstract function get_object_type_label( $object );

	/**
	 * @return array
	 */
	abstract function get_all_object_type_labels();

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

}
