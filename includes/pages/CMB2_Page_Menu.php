<?php

/**
 * Class CMB2_Page_Menu
 * Prepares parameters and adds page to WordPress admin menu.
 *
 * Uses:
 *     CMB2_Page_Utils                   Static class, utility functions
 * Applies CMB2 Filters:
 *     'cmb2_options_page_menu_params'   Allows manipulation of menu params before calling WP menu functions
 * Public methods:
 *     add_to_menu()                     Calls add_to_admin_menu(), menu_parameters()
 * Public methods accessed via callback: None
 * Protected methods:
 *     add_to_admin_menu()               Calls appropriate WP function add_menu_page() or add_submenu_page()
 *     menu_parameters()                 Gets values needed from CMB2_Page instance
 * Private methods:                      None
 * Magic methods:
 *     __get()                           Allows examining $page
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Page_Menu {
	
	/**
	 * @since 2.XXX
	 * @var \CMB2_Page
	 */
	protected $page;
	
	/**
	 * CMB2_Page_Menu constructor.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Page $page
	 */
	public function __construct( CMB2_Page $page ) {
		
		$this->page = $page;
	}
	
	/**
	 * Adds page to menu. Allows parameters to be filtered. Returns array containing results, for testing purposes.
	 *
	 * @since  2.XXX
	 * @return array
	 */
	public function add_to_menu() {
		
		$return = array();
		
		// get menu parameters; will always return parameters. Whether they will actually add a menu is WP's call.
		$params = $this->menu_parameters();
		
		// gets menu parameters and adds menu to WP admin menu
		$page_hook = $this->add_to_admin_menu( $params );
		
		// menu not added, exit
		if ( ! $page_hook ) {
			return $return;
		}
		
		return array(
			'type'      => ( empty( $params['parent_slug'] ) ? 'menu' : 'submenu' ),
			'params'    => $params,
			'page_hook' => $page_hook,
		);
	}
	
	/**
	 * Adds page to wordpress admin menu and returns page hook.
	 *
	 * @since  2.XXX
	 * @param  array $params It is assumed that array has been checked before passing
	 * @return false|string
	 */
	protected function add_to_admin_menu( $params ) {
		
		$menu = $submenu = NULL;
		
		// if null is passed, the page is created but not added to the menu system
		if ( empty( $params['parent_slug'] ) && ! $params['hide_menu'] ) {
			
			$menu = add_menu_page(
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				$params['action'],
				$params['icon_url'],
				$params['position']
			);
			
			// changes the wording on the first sub-menu of the top-level menu
			if ( ! empty( $params['menu_first_sub'] ) && is_string( $params['menu_first_sub'] ) ) {
				$params['parent_slug'] = $params['menu_slug'];
				$params['menu_title']  = $params['menu_first_sub'];
			}
		}
		
		// Add submenu page
		if ( $params['parent_slug'] || $params['hide_menu'] ) {
			
			if ( $params['hide_menu'] ) {
				$params['parent_slug'] = NULL;
			}
			
			$submenu = add_submenu_page(
				$params['parent_slug'],
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				$params['action']
			);
		}
		
		return ( $menu === NULL && $submenu === NULL ) ? FALSE : ( $menu !== NULL ? $menu : $submenu );
	}
	
	/**
	 * Parameters needed to add menu item. Allows parameter array with matching keys to override
	 * the defaults.
	 *
	 * @since  2.XXX
	 * @param  array $add Array of parameters to swap with the default values
	 * @return array
	 */
	protected function menu_parameters( $add = array() ) {
		
		$add = ! is_array( $add ) ? array() : $add;
		
		$defaults = array(
			'action'         => array( $this->page, 'render' ),
			'capability'     => $this->page->shared['capability'],
			'hide_menu'      => $this->page->shared['hide_menu'],
			'icon_url'       => $this->page->shared['icon_url'],
			'menu_first_sub' => $this->page->shared['menu_first_sub'],
			'menu_slug'      => $this->page->page_id,
			'menu_title'     => $this->page->shared['menu_title'],
			'parent_slug'    => $this->page->shared['parent_slug'],
			'position'       => $this->page->shared['position'],
			'title'          => $this->page->shared['title'],
		);
		
		$params = CMB2_Page_Utils::array_replace_recursive_strict( $defaults, $add );
		
		/**
		 * 'cmb2_options_page_menu_params' filter.
		 * Allows modifying the menu parameters before they're used to add a menu. All parameters
		 * must be returned.
		 *
		 * @since 2.XXX
		 */
		$filtered = apply_filters( 'cmb2_options_page_menu_params', $params, $this );
		
		// ensure that no keys required below are missing after filter
		if ( ! is_array( $filtered ) ) {
			return $defaults;
		}
		
		return CMB2_Page_Utils::array_replace_recursive_strict( $defaults, $filtered );
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