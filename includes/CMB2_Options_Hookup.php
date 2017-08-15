<?php

/**
 * Handles hooking CMB2 forms/metaboxes into the post/attachement/user screens
 * and handles hooking in and saving those fields.
 *
 * @since     2.XXX Now allows multiple metaboxes on an options page. First box encountered triggers menu hooks,
 *                  so all parameters which affect the menu must be set on first box of set.
 *
 *                  New methods:
 *                    - check_page_is_registered  : checks to be sure page has not already been added to menu
 *                    - find_page_boxes           : finds all boxes which appear on this page
 *                    - find_show_on              : see if box was assigned to page via show_on
 *                    - is_updated                : were the values updated?
 *                    - menu_slug                 : determines the menu slug
 *                    - page_properties           : compiles shared properties from multiple boxes
 *                    - page_prop                 : scans $this->boxes for requested CMB2 property
 *                    - postbox_scripts           : Adds WP postbox JS for 'post' format pages
 *
 *                  New properties:
 *                    - boxes   : All CMB2 boxes which appear on this page
 *                    - cmb     : Explicitly declared to quiet strict mode squeaking
 *                    - page    : menu slug of page
 *
 *                  New CMB2 box parameters:
 *                    - menu_slug    : ''                : allows menu item to not use option key as WP identifier
 *                    - page_title   : ''                : allows passing the options page title
 *                    - page_format  : 'simple' | 'post' : Allows options page to mimic post editor, defaults to 'simple'
 *                    - page_columns : 1 | 2 | 'auto'    : If 'post' style, how many columns. Defaults to 'auto'
 *
 *                  New filters:
 *                    - cmb2_options_page_title  : allow changing page title to something other than box title
 *                    - cmb2_options_page_before : HTML content before form
 *                    - cmb2_options_page_after  : HTML content after form
 *
 *                  See '@since' notices on existing methods and inline comments for changes.
 *                  Some whitespace and phpdoc cleanup.
 *
 * @since     2.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Options_Hookup extends CMB2_hookup {
	
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
	 * Stores all boxes which show_on() = true for this options key/menu_slug combination
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $boxes = array();
	
	/**
	 * Page ID in admin
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $page = '';
	
	/**
	 * Common cmb->prop values shared across all boxes (titles
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $shared_properties = array();
	
	/**
	 * CMB2 instance, added to avoid implicit assignment in constructor
	 *
	 * @var \CMB2
	 * @since 2.XXX
	 */
	protected $cmb;
	
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param CMB2   $cmb The CMB2 object to hookup
	 * @param string $option_key
	 */
	public function __construct( CMB2 $cmb, $option_key ) {
		
		$this->cmb        = $cmb;
		$this->option_key = $option_key;
	}
	
	/**
	 * Default hooks.
	 *
	 * @since 2.XXX Checks if settings have already been registered
	 * @since 2.2.5 (?) Method undocumented
	 */
	public function hooks() {
		
		global $wp_registered_settings;
		
		if ( empty( $this->option_key ) ) {
			return;
		}
		
		// Check if this setting has already been registered
		if ( empty( $wp_registered_settings[ $this->option_key ] ) ) {
			
			// Register setting to cmb2 group.
			register_setting( 'cmb2', $this->option_key );
			
			// Handle saving the data.
			add_action( 'admin_post_' . $this->option_key, array( $this, 'save_options' ) );
		}
		
		// Optionally network_admin_menu. Note, cannot use page_prop this early, so this must be set on first box
		$hook = $this->cmb->prop( 'admin_menu_hook' );
		
		// Hook in to add our menu.
		add_action( $hook, array( $this, 'options_page_menu_hooks' ) );
		
		// If in the network admin, need to use get/update_site_option.
		if ( 'network_admin_menu' === $hook ) {
			// Override CMB's getter.
			add_filter( "cmb2_override_option_get_{$this->option_key}", array( $this, 'network_get_override' ), 10, 2 );
			// Override CMB's setter.
			add_filter( "cmb2_override_option_save_{$this->option_key}", array( $this, 'network_update_override' ), 10, 2 );
		}
	}
	
	/**
	 * Hook up our admin menu item and admin page.
	 *
	 * @since  2.XXX  Allows 'menu_slug' to be set via box parameters
	 *                Checks to see if page has been registered already
	 *                Gathers all boxes which appear on this options page
	 *                Adds WP JS needed for postboxes if appropriate
	 *                Moved checking of 'updated' to own method
	 * @since  2.2.5
	 */
	public function options_page_menu_hooks() {
		
		// page ( equal to $_GET['page'] or current_screen->id ) is equal to menu_slug
		$this->page  = $menu_slug = $this->menu_slug( $this->cmb );
		
		$parent_slug = $this->cmb->prop( 'parent_slug' );
		
		// Set the screen ID and get boxes belonging to this screen
		$this->find_page_boxes( $menu_slug );
		
		// Menu_slug is blank, menu page is already registered, no boxes: exit
		if ( ! $menu_slug || $this->check_page_is_registered( $menu_slug, $parent_slug ) || empty( $this->boxes ) ) {
			return;
		}
		
		// Find values of shared cmb->prop values
		$this->page_properties();
		
		if ( $parent_slug ) {
			$page_hook = add_submenu_page(
				$parent_slug,
				$this->shared_properties['title'],
				$this->shared_properties['menu_title'],
				$this->shared_properties['capability'],
				$menu_slug,
				array( $this, 'options_page_output' )
			);
		} else {
			$page_hook = add_menu_page(
				$this->shared_properties['title'],
				$this->shared_properties['menu_title'],
				$this->shared_properties['capability'],
				$menu_slug,
				array( $this, 'options_page_output' ),
				$this->shared_properties['icon_url'],
				$this->shared_properties['position']
			);
		}
		
		if ( $this->shared_properties['cmb_styles'] ) {
			// Include CMB CSS in the head to avoid FOUC
			add_action( "admin_print_styles-{$page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
		
		// add scripts if page format is 'post'
		$this->postbox_scripts();
		
		// check if settings have been updated
		$this->is_updated();
	}
	
	/**
	 * Get the menu slug. We cannot pass a fallback to CMB2 as it will prevent additional pages from being generated.
	 *
	 * @param \CMB2 $box
	 *
	 * @return mixed|string
	 */
	public function menu_slug( CMB2 $box ) {
		
		$menu_slug = $box->prop( 'menu_slug' );
		
		return empty( $menu_slug ) ? $this->option_key : $menu_slug;
	}
	
	/**
	 * Checks if settings were updated. Formerly within options_page_menu_hooks.
	 *
	 * @since 2.XXX
	 */
	public function is_updated() {
		
		if ( empty( $_GET['updated'] ) ) {
			return;
		}
		
		if ( 'true' === $_GET['updated'] ) {
			add_settings_error( "{$this->option_key}-notices", '', __( 'Settings updated.', 'cmb2' ), 'updated' );
		} else {
			add_settings_error( "{$this->option_key}-notices", '', __( 'Nothing to update.', 'cmb2' ), 'notice-warning' );
		}
	}
	
	/**
	 * Adds postbox scripts if page format is 'post'
	 *
	 * @since 2.XXX
	 */
	public function postbox_scripts() {
		
		if ( $this->shared_properties['page_format'] !== 'post' ) {
			return;
		}
		
		// include WP postbox JS
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_script( 'postbox' );
		} );
		
		// trigger the postbox script
		add_action( 'admin_print_footer_scripts', function () {
			echo '<script>jQuery(document).ready(function(){postboxes.add_postbox_toggles("postbox-container");});</script>';
		});
	}
	
	/**
	 * Checks to see if a menu or submenu page has already been registered
	 *
	 * @since  2.XXX
	 *
	 * @param  string $menu_slug
	 * @param  string $parent_slug
	 *
	 * @return bool
	 */
	public function check_page_is_registered( $menu_slug, $parent_slug = '' ) {
		
		global $_registered_pages;
		
		// get the hook name given the menu_slug and parent_slug
		$hookname = get_plugin_page_hookname( $menu_slug, $parent_slug );
		
		// see if the hookname is in the global _registered_pages var
		return isset( $_registered_pages[ $hookname ] ) && $_registered_pages[ $hookname ] === TRUE;
	}
	
	/**
	 * Finds all boxes which should appear on this page, add to $this->boxes. Note that if page is a submenu page,
	 * all boxes which are in that submenu page must have menu_slug and parent_slug set.
	 *
	 * @since 2.XXX
	 *
	 * @param string $menu_slug
	 */
	public function find_page_boxes( $menu_slug ) {
		
		if ( ! $menu_slug ) return;
		
		$all_boxes = CMB2_Boxes::get_all();
		
		foreach ( $all_boxes as $box ) {
			
			$bxslg = $box->prop('menu_slug');
			
			// if this box is already in the box list, or it has a menu slug set which does not match the slug, skip
			if ( ! empty( $this->boxes[ $box->cmb_id ] ) || ( $bxslg !== null && $bxslg != $menu_slug ) ) {
				continue;
			}
			
			// $bxslg == $menu_slug : boxes which specifically have this slug set
			// in_array( $menu_slug, (array) $box->options_page_keys() ) : boxes which have menu_slug as option key
			if ( $bxslg == $menu_slug || in_array( $menu_slug, (array) $box->options_page_keys() ) ) {
				$this->boxes[ $box->cmb_id ] = $box;
			}
		}
	}
	
	/**
	 * Finds shared properties for reference by class methods which may have been set on any of the included boxes.
	 *
	 * @since 2.XXX
	 */
	public function page_properties() {
		
		$props = array(
			'capability'   => $this->page_prop( 'capability' ),
			'cmb_styles'   => $this->page_prop( 'cmb_styles' ),
			'display_cb'   => $this->page_prop( 'display_cb' ),
			'enqueue_js'   => $this->page_prop( 'enqueue_js' ),
			'icon_url'     => $this->page_prop( 'icon_url' ),
			'menu_title'   => '',
			'page_columns' => $this->page_prop( 'page_columns', 'auto' ),
			'page_format'  => $this->page_prop( 'page_format' ),
			'position'     => $this->page_prop( 'position' ),
			'save_button'  => $this->page_prop( 'save_button', 'Save', false ),
			'title'        => $this->page_prop( 'page_title', $this->cmb->prop( 'title' ) ),
		);
		
		// need to set this after determining the title
		$props['menu_title'] = $this->page_prop( 'menu_title', $props['title'] );
		
		// apply filters to title
		$props['title'] = apply_filters( 'cmb2_options_page_title', $props['title'], $this->page, $this );
		
		// place into class property
		$this->shared_properties = $props;
	}
	
	/**
	 * Checks set of boxes for property; first box with property set is returned. This allows a later box in a page's
	 * boxes to carry information which was not set on the "root" box.
	 *
	 * @since 2.XXX
	 *
	 * @param string      $property
	 * @param null|string $fallback
	 * @param bool        $empty_string_ok  Flag to force using fallback if CMB2 returns empty string
	 *
	 * @return mixed
	 */
	public function page_prop( $property, $fallback = NULL, $empty_string_ok = true ) {
		
		$prop = NULL;
		
		foreach ( $this->boxes as $box ) {
			
			$prop = $box->prop( $property, $fallback );
			
			// if a value is found and it doesn't break empty string rules, we're done
			if ( $prop !== NULL && ( $empty_string_ok || $prop !== '' ) ) {
				break;
			}
		}
		
		// specifically checks for empty string (not false/null) and subs $fallback if flag is set
		if ( ! $empty_string_ok && $prop === '' && is_string( $fallback ) ) {
			$prop = $fallback;
		}
		
		return $prop;
	}
	
	/**
	 * Display options-page output. To override, set 'display_cb' box property.
	 *
	 * @since  2.XXX Allows multiple metaboxes to appear on single page
	 *               Allows for return of results for testing
	 * @since  2.2.5
	 *
	 * @param bool $echo Set to false for testing purposes, WP always sends an empty string
	 *
	 * @return string
	 */
	public function options_page_output( $echo ) {
		
		// allow this to return html for tests/future uses
		$echo = $echo === '' || $echo === true;
		
		settings_errors( "{$this->option_key}-notices" );
		
		$callback = $this->shared_properties[ 'display_cb' ];
		
		if ( is_callable( $callback ) ) {
			
			// the callback is expected to echo, but this will catch it if it doesn't
			$html = $callback( $this );
			
		} else {
			
			if ( $this->shared_properties['cmb_styles'] ) {
				self::enqueue_cmb_css();
			}
			if ( $this->shared_properties[ 'enqueue_js'] ) {
				self::enqueue_cmb_js();
			}
			
			// get instance of display class and get html output
			$display = new CMB2_Options_Display( $this->option_key, $this->page, $this->boxes, $this->shared_properties );
			$html = $display->options_page_output();
		}
		
		if ( $echo ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Now checks multiple boxes
	 * @since  2.2.5
	 *
	 * @return void
	 */
	public function save_options() {
		
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}
		
		if (
			$this->can_save( 'options-page' )
			// check params
			&& isset( $_POST['submit-cmb'], $_POST['action'] )
			&& $this->option_key === $_POST['action']
		) {
			
			$updated = FALSE;
			
			// check if values from a box are updated
			foreach ( $this->boxes as $box ) {
				
				$up = $box
					->save_fields( $this->option_key, $box->object_type(), $_POST )
					->was_updated(); // Will be false if no values were changed/updated.
				
				// if $up is true, set $updated to true; prevent $updated being set back to false
				$updated = $updated || $up;
			}
			
			$url = add_query_arg( 'updated', $updated ? 'true' : 'false', $url );
		}
		
		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		exit;
	}
	
	/**
	 * Replaces get_option with get_site_option
	 *
	 * @since 2.2.5
	 *
	 * @param         $test
	 * @param bool    $default
	 *
	 * @return mixed  Value set for the network option.
	 */
	public function network_get_override( $test, $default = FALSE ) {
		
		return get_site_option( $this->option_key, $default );
	}
	
	/**
	 * Replaces update_option with update_site_option
	 *
	 * @since 2.2.5
	 *
	 * @param $test
	 * @param $option_value
	 *
	 * @return bool Success/Failure
	 */
	public function network_update_override( $test, $option_value ) {
		
		return update_site_option( $this->option_key, $option_value );
	}
}