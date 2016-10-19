<?php
/**
 * Base class for hooking CMB2 into WordPress.
 *
 * @since  2.2.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_Hookup_Base {

	/**
	 * @var   CMB2 object
	 * @since 2.0.2
	 */
	protected $cmb;

	/**
	 * The object type we are performing the hookup for
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'post';

	/**
	 * Constructor
	 * @since 2.0.0
	 * @param CMB2 $cmb The CMB2 object to hookup
	 */
	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
		$this->object_type = $this->cmb->mb_object_type();
	}

	abstract public function universal_hooks();

	/**
	 * Ensures WordPress hook only gets fired once per object.
	 * @since  2.0.0
	 * @param string   $action        The name of the filter to hook the $hook callback to.
	 * @param callback $hook          The callback to be run when the filter is applied.
	 * @param integer  $priority      Order the functions are executed
	 * @param int      $accepted_args The number of arguments the function accepts.
	 */
	public function once( $action, $hook, $priority = 10, $accepted_args = 1 ) {
		static $hooks_completed = array();

		$args = func_get_args();

		// Get object hash.. This bypasses issues with serializing closures.
		if ( is_object( $hook ) ) {
			$args[1] = spl_object_hash( $args[1] );
		} elseif ( is_array( $hook ) && is_object( $hook[0] ) ) {
			$args[1][0] = spl_object_hash( $hook[0] );
		}

		$key = md5( serialize( $args ) );

		if ( ! isset( $hooks_completed[ $key ] ) ) {
			$hooks_completed[ $key ] = 1;
			add_filter( $action, $hook, $priority, $accepted_args );
		}
	}

}
