<?php

/**
 * Handles hooking CMB2 forms/metaboxes into WordPress admin "options" pages.
 * This class deals exclusively with actions on individual CMB2 boxes; it assigns the box to a specific
 * CMB_Options_Page_Hookup object based on $this->page_id
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
 * @property  \CMB2                     $cmb
 * @property  string                    $option_key
 * @property  string                    $page_id
 * @property  \CMB2_Options_Page_Hookup $page
 * @property  array                     $hooks
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
	 * Array of hook configuration arrays for standard hooks added by this class.
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $hooks = array(
		array(
			'id'       => 'admin_page_hooks',
			'call'     => array( '{PAGE}', 'hooks' ),
			'hook'     => 'wp_loaded',
			'priority' => 4,
		),
		array(
			'id'      => 'add_meta_boxes',
			'call'    => array( '{THIS}', 'add_options_metabox' ),
			'hook'    => 'add_meta_boxes_{PAGE_ID}',
			'only_if' => '{TYPE_CHECK}',
		),
		array(
			'id'      => 'add_context_metaboxes',
			'call'    => array( '{THIS}', 'add_context_metaboxes' ),
			'hook'    => 'edit_form_top',
			'only_if' => '{CONTEXT_CHECK}',
		),
		array(
			'id'   => 'save_options',
			'call' => array( '{PAGE}', 'save_options' ),
			'hook' => 'admin_post_{OPT}',
		),
		array(
			'id'      => 'cmb2_override_option_get',
			'call'    => array( '{THIS}', 'network_get_override' ),
			'hook'    => 'cmb2_override_option_get_{OPT}',
			'only_if' => '{NETWORK_CHECK}',
			'type'    => 'filter',
			'args'    => 2,
		),
		array(
			'id'      => 'cmb2_override_option_save',
			'call'    => array( '{THIS}', 'network_update_override' ),
			'hook'    => 'cmb2_override_option_save_{OPT}',
			'only_if' => '{NETWORK_CHECK}',
			'type'    => 'filter',
			'args'    => 2,
		),
		array(
			'id'   => 'cmb2_options_simple_page',
			'hook' => 'cmb2_options_simple_page',
			'call' => array( '{THIS}', 'get_simple_page_box' ),
		),
		array(
			'id'   => 'postbox_classes',
			'hook' => 'postbox_classes_{PAGE_ID}_{CMB_ID}',
			'call' => array( '{THIS}', 'add_postbox_classes' ),
			'type' => 'filter',
		),
	);
	
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
	 * @var \CMB2_Options_Page_Hookup
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
	 * @since 2.XXX                         Sets $this->page_id value, allows passing of default hooks and page
	 * @since 2.0.0
	 * @param CMB2                          $cmb        The CMB2 object to hookup
	 * @param string                        $option_key Option key box is displaying values for
	 * @param string                        $page_id    Value of _GET['page'], normally set automatically
	 * @param CMB2_Options_Page_Hookup|null $page       Page_Hookup instance for this box
	 * @param array                         $hooks      Array of hook config arrays
	 */
	public function __construct( CMB2 $cmb, $option_key, $page_id = '', $page = NULL, $hooks = array() ) {
		
		$this->cmb        = $cmb;
		$this->option_key = (string) $option_key;
		$this->page_id    = empty( $page_id ) ? $this->set_page() : (string) $page_id;
		
		if ( $page !== NULL && $page instanceOf CMB2_Options_Page_Hookup ) {
			$this->page = $page;
		}
		
		if ( ! empty( $hooks ) ) {
			$this->hooks = $hooks;
		}
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
	 * Options display class will uses "do metaboxes" call. Args places into array to allow for testing.
	 *
	 * @since 2.XXX
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
			$this->cmb->prop( 'priority' )
		);
		
		add_meta_box( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5] );
		
		return $args;
	}
	
	/**
	 * Clone of metabox_callback w/o ID. Also returns a value for testing/non-echo.
	 * future uses.
	 *
	 * @since  2.XXX
	 * @return string|bool
	 */
	public function get_metabox() {
		
		ob_start();
		$returned = $this->cmb->show_form( 0, $this->object_type );
		$echoed   = ob_get_clean();
		
		if ( $echoed ) {
			echo $echoed;
			$returned = TRUE;
		};
		
		return $returned;
	}
	
	/**
	 * Gets the options page and adds this metabox to it.
	 *
	 * @since  2.XXX
	 * @param  string $wp_menu_hook The first box to call the page hookup constructor will set it.
	 * @return \CMB2_Options_Page_Hookup
	 */
	public function get_options_page_and_add_hookup( $wp_menu_hook ) {
		
		$admin_page = CMB2_Options_Pages::get( $this->page_id );
		
		if ( $admin_page === FALSE ) {
			$admin_page = new CMB2_Options_Page_Hookup( $this->page_id, $this->option_key, $wp_menu_hook );
			CMB2_options_Pages::add( $admin_page );
		}
		
		$admin_page->add_hookup( $this );
		
		return $admin_page;
	}
	
	/**
	 * Returns the output for a 'simple' box. Allows future return of string by show_form if desired.
	 *
	 * @since  2.XXX
	 * @param  string $pageid
	 * @return bool|string
	 */
	public function get_simple_page_box( $pageid = '' ) {
		
		$return = FALSE;
		
		if ( $pageid == $this->page_id ) {
			
			ob_start();
			$return = $this->cmb->show_form( 0, $this->object_type );
			$echoed = ob_get_clean();
			
			if ( $echoed ) {
				echo $echoed;
				$return = TRUE;
			};
		}
		
		return $return;
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
		$wp_menu_hook = $this->cmb->prop( 'admin_menu_hook', 'admin_menu' );
		
		// get the options page and add $this->cmb to it. Options page handles hooking into menus, etc.
		$this->page = $this->get_options_page_and_add_hookup( $wp_menu_hook );
		
		// get the box context
		$context = $this->cmb->prop( 'context' ) ? $this->cmb->prop( 'context' ) : 'normal';
		
		// Tokens to substitute into hooks array
		$tokens = array(
			'{THIS}'          => $this,
			'{PAGE}'          => $this->page,
			'{PAGE_ID}'       => $this->page_id,
			'{CMB_ID}'        => $this->cmb->cmb_id,
			'{CONTEXT_CHECK}' => $context == 'form_top',
			'{OPT}'           => $this->option_key,
			'{NETWORK_CHECK}' => 'network_admin_menu' == $wp_menu_hook,
			'{TYPE_CHECK}'    => in_array( $context, array( 'normal', 'advanced', 'side' ) ),
		);
		
		/*
		 * By sending the hooks to the prepare_hooks_array method, partials will be returned with all keys set,
		 * making life easier for any dev asking for the list via this filter.
		 */
		$hooks = CMB2_Utils::prepare_hooks_array( $this->hooks, $wp_menu_hook, $tokens );
		
		/**
		 * 'cmb2_options_page_hooks_' filter.
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since 2.XXX
		 */
		$filtered = apply_filters( 'cmb2_options_page_hooks', $hooks, $this );
		$hooks    = $hooks != $filtered ? $filtered : $hooks;
		
		$return = ! empty( $hooks ) ? CMB2_Utils::add_wp_hooks_from_config_array( $hooks, $wp_menu_hook ) : FALSE;
		
		return $return;
	}
	
	/**
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
			get_site_option( $this->option_key, $default ) : false;
	}
	
	/**
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
			update_site_option( $this->option_key, $option_value ) : false;
	}
	
	/**
	 * Get the menu slug. We cannot pass a fallback to CMB2 as it will prevent additional pages from being generated.
	 * Menu slugs must ALWAYS be accompianied by an explicit declaration of the 'option_key' box parameter.
	 *
	 * @since  2.XXX
	 * @return string
	 */
	public function set_page() {
		
		$menu_slug = $this->cmb->prop( 'menu_slug' );
		
		$page = empty( $menu_slug ) || ( $menu_slug && ! $this->cmb->prop( 'option_key' ) ) ?
			$this->option_key : $menu_slug;
		
		return $page;
	}
}