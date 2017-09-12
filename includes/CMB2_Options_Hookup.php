<?php

/**
 * Class CMB2_Options_Hookup
 * Handles hooking CMB2 forms/metaboxes into WordPress admin "options" pages.
 *
 * This class deals exclusively with actions on individual CMB2 boxes; it assigns the box to a specific
 * CMB2_Page object based on $this->page_id
 *
 * Uses:
 *     CMB2_Page                    Handles all page-specific hookups, rendering, etc.
 *     CMB2_Page_Utils              Static class, utility functions
 * Applies CMB2 Filters:
 *     'cmb2_options_hookup_hooks'  Allows manipulation of hooks being added for thie CMB2 instance
 * Public methods:
 *     hooks()                      Public accessor, calls get_page_hookup() and get_hooks()
 * Public methods accessed via callback:
 *     add_options_metabox()        Calls add_meta_box()
 *     get_metabox()                Callback from add_meta_box()
 *     network_get_override()       If options page is on multisite network menu
 *     network_update_override()    If options page is on multisite network menu
 * Protected methods:
 *     get_page_hookup()            Gets instance of CMB2_Page and adds $this to it
 *     get_hooks()                  Get hooks array needed for this box
 *     get_page_id()                Determine the page id based on passed params
 * Private methods:                 None
 * Magic methods:
 *     __get()                      Magic getter
 *     __set()                      Used only by unit tests.
 *
 * @since     2.XXX Now allows multiple metaboxes on an options page; rewrite to separate concerns
 * @since     2.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read \CMB2      $cmb
 * @property-read string     $option_key
 * @property-read string     $object_type
 * @property      string     $page_id
 * @property      \CMB2_Page $page
 */
class CMB2_Options_Hookup extends CMB2_hookup {
	
	/**
	 * CMB2 instance, added to avoid implicit assignment in constructor
	 *
	 * @var \CMB2
	 * @since 2.XXX
	 */
	protected $cmb;
	
	/**
	 * The object type we are performing the hookup for
	 *
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'options-page';
	
	/**
	 * Options page key.
	 *
	 * @var   string
	 * @since 2.2.5
	 */
	protected $option_key = '';
	
	/**
	 * Page hookup object
	 *
	 * @var \CMB2_Page
	 * @since 2.XXX
	 */
	protected $page;
	
	/**
	 * Page ID in admin
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $page_id = '';
	
	/**
	 * Constructor, allows injection by tests of normally determined or set properties.
	 *
	 * @since 2.XXX   Sets $this->page_id value
	 * @since 2.0.0
	 * @param CMB2   $cmb        CMB2 object to hookup
	 * @param string $option_key Option key box is displaying values for
	 */
	public function __construct( CMB2 $cmb, $option_key ) {
		
		$this->cmb        = $cmb;
		$this->option_key = (string) $option_key;
		$this->page_id    = $this->get_page_id();
	}
	
	/**
	 * Hook: 'add_meta_boxes_' . $this->page_id
	 * Options display class will uses "do metaboxes" call.
	 * Uses an array so that the call parameters can be returned.
	 *
	 * @since  2.XXX
	 * @return bool|array
	 */
	public function add_options_metabox() {
		
		if ( ! $this->show_on() || ! $this->cmb->prop( 'title' ) ) {
			return FALSE;
		}
		
		$args = array(
			$this->cmb->cmb_id,
			$this->cmb->prop( 'title' ),
			array( $this, 'get_metabox' ),
			$this->page_id,
			$this->cmb->prop( 'context' ),
			$this->cmb->prop( 'priority' ),
		);
		
		add_meta_box( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5] );
		
		return $args;
	}
	
	/**
	 * Clone of metabox_callback w/o ID. Returns a value for testing/non-echo use.
	 *
	 * @since  2.XXX
	 * @param  bool|string $echo
	 * @return string
	 */
	public function get_metabox( $echo = TRUE ) {
		
		$echo = $echo !== FALSE;
		$ret  = CMB2_Page_Utils::do_void_action( array( 0, $this->object_type ), array( $this->cmb, 'show_form' ) );
		
		if ( $echo ) {
			echo $ret;
		}
		
		return $ret;
	}
	
	/**
	 * Default hooks.
	 *
	 * @since  2.XXX       Complete re-write to use 'normal' CMB metabox actions
	 * @since  2.?.?       Method undocumented
	 * @return array|bool  Array of hooks added
	 */
	public function hooks() {
		
		// Optional 'network_admin_menu' value can be passed here
		$menu_hook = $this->cmb->prop( 'admin_menu_hook', 'admin_menu' );
		
		// get the options page and add $this->cmb to it. Options page handles hooking into menus, etc.
		$this->page = $this->get_page_hookup( $menu_hook );
		
		/*
		 * By sending the hooks to the prepare_hooks_array method, partials will be returned with all keys set,
		 * making life easier for any dev asking for the list via this filter.
		 */
		$hooks = CMB2_Page_Utils::prepare_hooks_array( $this->get_hooks( $menu_hook ), $menu_hook );
		
		/**
		 * 'cmb2_options_hookup_hooks' filter.
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since 2.XXX
		 */
		$filtered = CMB2_Page_Utils::prepare_hooks_array( apply_filters( 'cmb2_options_hookup_hooks', $hooks, $this ) );
		$hooks    = $hooks != $filtered ? $filtered : $hooks;
		
		$return = ! empty( $hooks ) ? CMB2_Page_Utils::add_wp_hooks_from_config_array( $hooks, $menu_hook ) : FALSE;
		
		return $return;
	}
	
	/**
	 * Hook: 'cmb2_override_option_get_' . $this->option_key
	 * Replaces get_option with get_site_option
	 *
	 * @since  2.XXX  Added test for expected value of test before returning site option
	 * @since  2.2.5
	 * @param  string $test should be 'cmb2_no_override_option_get'
	 * @param  mixed  $default
	 * @return mixed  Value set for the network option.
	 */
	public function network_get_override( $test, $default = FALSE ) {
		
		return $test == 'cmb2_no_override_option_get' ?
			get_site_option( $this->option_key, $default ) : FALSE;
	}
	
	/**
	 * Hook: 'cmb2_override_option_save_' . $this->option_key
	 * Replaces update_option with update_site_option
	 *
	 * @since  2.XXX  Added test for expected value of test
	 * @since  2.2.5
	 * @param  string $test should be 'cmb2_no_override_option_save'
	 * @param  mixed  $option_value
	 * @return bool   Success/Failure
	 */
	public function network_update_override( $test, $option_value ) {
		
		return $test == 'cmb2_no_override_option_save' ?
			update_site_option( $this->option_key, $option_value ) : FALSE;
	}
	
	/**
	 * Gets the options page and adds this metabox to it. Allows alternative derived class of CMB2_Page to be
	 * injected.
	 *
	 * @since  2.XXX
	 * @param  string $wp_menu_hook The first box to call the page hookup constructor will set it.
	 * @return bool|\CMB2_Page
	 */
	protected function get_page_hookup( $wp_menu_hook ) {
		
		$admin_page = CMB2_Pages::get( $this->page_id );
		
		if ( $admin_page === FALSE ) {
			
			$hookup_class = 'CMB2_Page';
			$class        = $this->cmb->prop( 'hookup_class', NULL );
			
			if (
				! empty( $class )
				&& is_string( $class )
				&& class_exists( $class )
				&& 'CMB2_Page' == get_parent_class( $class )
			) {
				$hookup_class = $class;
			}
			
			if ( $class instanceof CMB2_Page ) {
				$admin_page = $class;
			} else if ( is_string( $hookup_class ) ) {
				$admin_page = new $hookup_class( $this->page_id, $this->option_key, $wp_menu_hook );
			}
			
			CMB2_Pages::add( $admin_page );
		}
		
		if ( $admin_page !== FALSE ) {
			$admin_page->set_options_hookup( $this );
		}
		
		return $admin_page;
	}
	
	/**
	 * Default hooks added by this class.
	 *
	 * @since  2.XXX
	 * @param  string $wp_menu_hook
	 * @return array
	 */
	protected function get_hooks( $wp_menu_hook ) {
		
		$context = $this->cmb->prop( 'context' ) ? $this->cmb->prop( 'context' ) : 'normal';
		
		return array(
			array(
				'id'       => 'admin_page_init',
				'call'     => array( $this->page, 'init' ),
				'hook'     => 'wp_loaded',
				'priority' => 4,
			),
			array(
				'id'      => 'add_meta_boxes',
				'call'    => array( $this, 'add_options_metabox' ),
				'hook'    => 'add_meta_boxes_' . $this->page_id,
				'only_if' => in_array( $context, array( 'normal', 'advanced', 'side' ) ),
			),
			array(
				'id'      => 'add_context_metaboxes',
				'call'    => array( $this, 'add_context_metaboxes' ),
				'hook'    => 'edit_form_top',
				'only_if' => $context == 'form_top',
			),
			array(
				'id'      => 'cmb2_override_option_get',
				'call'    => array( $this, 'network_get_override' ),
				'hook'    => 'cmb2_override_option_get_' . $this->option_key,
				'only_if' => 'network_admin_menu' == $wp_menu_hook,
				'type'    => 'filter',
				'args'    => 2,
			),
			array(
				'id'      => 'cmb2_override_option_save',
				'call'    => array( $this, 'network_update_override' ),
				'hook'    => 'cmb2_override_option_save_' . $this->option_key,
				'only_if' => 'network_admin_menu' == $wp_menu_hook,
				'type'    => 'filter',
				'args'    => 2,
			),
			array(
				'id'      => 'cmb2_options_simple_page',
				'call'    => array( $this->cmb, 'show_form' ),
				'hook'    => 'cmb2_options_simple_page',
				'only_if' => isset( $_GET['page'] ) && $_GET['page'] == $this->page_id,
				'args'    => 2,
			),
			array(
				'id'   => 'postbox_classes',
				'call' => array( $this, 'postbox_classes' ),
				'hook' => 'postbox_classes_' . $this->page_id . '_' . $this->cmb->cmb_id,
				'type' => 'filter',
			),
		);
	}
	
	/**
	 * Get the menu slug. We cannot pass a fallback to CMB2 as it will prevent additional pages from being generated.
	 * Menu slugs must ALWAYS be accompianied by an explicit declaration of the 'option_key' box parameter.
	 *
	 * @since  2.XXX
	 * @return string
	 */
	protected function get_page_id() {
		
		$menu_slug = $this->cmb->prop( 'menu_slug' );
		
		return empty( $menu_slug ) || ( $menu_slug && ! $this->cmb->prop( 'option_key' ) ) ?
			$this->option_key : $menu_slug;
	}
	
	/**
	 * Returns property, allows checking state of class
	 *
	 * @since  2.XXX
	 * @param  string $property Class property to fetch
	 * @return mixed|null
	 */
	public function __get( $property ) {
		
		return isset( $this->{$property} ) ? $this->{$property} : NULL;
	}
	
	/**
	 * Allows setting of class properties, mainly used by unit tests.
	 *
	 * @since  2.XXX
	 * @param  string $property
	 * @param  mixed  $value
	 * @return mixed
	 * @throws \Exception
	 */
	public function __set( $property, $value ) {
		
		switch ( $property ) {
			
			case 'page_id':
				
				if ( ! empty( $value ) && is_string( $value ) ) {
					$this->page_id = $value;
				}
				
				return $this->page_id;
				break;
			
			case 'page':
				
				if ( $value !== NULL && $value instanceOf CMB2_Page ) {
					$this->page = $value;
				}
				
				return $this->page;
				break;
			
			default:
				
				throw new Exception( 'Cannot set ' . $property . ' in ' . __CLASS__ );
				break;
		}
	}
}