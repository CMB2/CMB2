<?php

/**
 * Class CMB2_Page_Hooks
 * Gets and normalizes hook configuration arrays, and adds hooks. Can be called 'early' or 'late', which is
 * determined by examining CMB2_Page->page_hook.
 *
 * Uses:
 *     CMB2_Page_Utils                Static class, utility functions
 *
 * Applies CMB2 Filters:
 *     'cmb2_options_pagehooks'       Allows manipulation of hooks array before they're added.
 *
 * Public methods directly accessed:
 *     hooks()                        Calls get_hooks(), uses utility to add hooks to WP
 *
 * Public methods accessed via callback: None
 *
 * Protected methods:
 *     get_hooks()                    Contains array of hooks. Returns either 'early' or 'late' subset.
 *     hooks_array()                  Normalizes hooks, applies filter
 *
 * Private methods: None
 *
 * Magic methods:
 *     __get()
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @since         2.XXX
 * @property-read CMB2_Page $page
 */
class CMB2_Page_Hooks {
	
	/**
	 * @since 2.XXX
	 * @var \CMB2_Page
	 */
	protected $page;
	
	/**
	 * CMB2_Page_Hooks constructor.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Page $page
	 */
	public function __construct( CMB2_Page $page ) {
		
		$this->page = $page;
	}
	
	/**
	 * Adds hooks tied to the creation of this page.
	 *
	 * @since  2.XXX
	 * @return array|bool
	 */
	public function hooks() {
		
		$hooks = $this->hooks_array( $this->get_hooks() );
		
		return ! empty( $hooks ) ?
			CMB2_Page_Utils::add_wp_hooks_from_config_array( $hooks, $this->page->wp_menu_hook ) : FALSE;
	}
	
	/**
	 * Default hooks used by this class. Root keys are method by which they are requested.
	 *
	 * @since  2.XXX
	 * @return array
	 */
	protected function get_hooks() {
		
		$hooks = array(
			'early' => array(
				array(
					'id'   => 'menu_hook',
					'hook' => $this->page->wp_menu_hook,
					'call' => array( $this->page, 'add_to_admin_menu' ),
				),
				array(
					'id'       => 'updated',
					'hook'     => $this->page->wp_menu_hook,
					'call'     => array( $this->page, 'add_update_notice' ),
					'priority' => 11,
				),
				array(
					'id'   => 'register_setting',
					'hook' => $this->page->wp_menu_hook,
					'call' => array( $this->page, 'add_registered_setting' ),
				),
				array(
					'id'      => 'postbox',
					'hook'    => 'admin_enqueue_scripts',
					'call'    => array( $this->page, 'add_postbox_script' ),
					'only_if' => $this->page->shared['page_format'] !== 'post',
				),
				array(
					'id'      => 'toggle',
					'hook'    => 'admin_print_footer_scripts',
					'call'    => array( $this->page, 'add_postbox_toggle' ),
					'only_if' => $this->page->shared['page_format'] !== 'post',
				),
				array(
					'id'   => 'save_options',
					'hook' => 'admin_post_' . $this->page->option_key,
					'call' => array( $this->page, 'save' ),
				),
			),
			'late'  => array(
				array(
					'id'   => 'add_metaboxes',
					'hook' => 'load-' . $this->page->page_hook,
					'call' => array( $this->page, 'add_metaboxes' ),
				),
				array(
					'id'      => 'add_cmb_css_to_head',
					'hook'    => 'admin_print_styles-' . $this->page->page_hook,
					'call'    => array( 'CMB2_hookup', 'enqueue_cmb_css' ),
					'only_if' => $this->page->shared['cmb_styles'] == TRUE,
				),
			),
		);
		
		// page_hook is set when page is added to admin menu; late hooks are called just after
		$when = $this->page->page_hook === '' ? 'early' : 'late';
		
		return $hooks[ $when ];
	}
	
	/**
	 * Prepares hooks array, applying filter
	 *
	 * @since  2.XXX
	 * @param  array $hooks  The method being called, used to select hooks from class property
	 * @param  array $tokens Tokens to be replaced
	 * @return array|bool
	 */
	protected function hooks_array( $hooks, $tokens = array() ) {
		
		/*
		 * By sending the hooks to the prepare_hooks_array method, they will be returned with all keys
		 * set, making them easier to understand for any dev asking for them by the filter below.
		 */
		$hooks = CMB2_Page_Utils::prepare_hooks_array( $hooks, $this->page->wp_menu_hook, $tokens );
		
		/**
		 * 'cmb2_options_pagehooks' filter.
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since 2.XXX
		 */
		$filtered =  CMB2_Page_Utils::prepare_hooks_array( apply_filters( 'cmb2_options_pagehooks', $hooks, $this ) );
		
		return $hooks != $filtered ? $filtered : $hooks;
	}
	
	/**
	 * Returns property asked for. Note asking for any property with the method returning a reference
	 * means a PHP warning or error, you have been warned!
	 *
	 * @since  2.XXX
	 * @param  string $property Class property to fetch
	 * @return mixed|null
	 */
	public function &__get( $property ) {
		
		$return = isset( $this->{$property} ) ? $this->{$property} : NULL;
		
		return $return;
	}
}