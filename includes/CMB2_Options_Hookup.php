<?php

/**
 * Handles hooking CMB2 forms/metaboxes into WordPress admin "options" pages.
 *
 * @since     2.XXX Now allows multiple metaboxes on an options page.
 *
 *                  New methods:
 *                    - can_box_save              : copy of can_save not tied to $this->cmb

 *                    - field_values_to_default   : returns field values to their default values
 *                    - find_page_boxes           : finds all boxes which appear on this page
 *                    - is_updated                : were the values updated?
 *                    - is_page_registered        : checks to be sure page has not already been added to menu
 *                    - is_setting_registered     : Has the setting already been registered?
 *                    - set_page                  : determines $this->page value
 *                    - page_prop                 : scans $this->boxes for requested CMB2 property
 *                    - page_properties           : compiles shared properties from multiple boxes
 *                    - postbox_scripts           : Adds WP postbox JS for 'post' format pages
 *                    - get_prop                  : Get value of class property
 *
 *                  New properties:
 *                    - boxes             : All CMB2 boxes which appear on this page
 *                    - cmb               : Explicitly declared to quiet strict mode squeaking
 *                    - page              : menu slug of page
 *                    - shared_properties : ->prop() values shared across boxes
 *
 *                  New CMB2 box parameters:
 *                    - menu_slug    : ''                   : allows menu item to not use option key as WP id
 *                    - page_title   : ''                   : allows passing the options page title
 *                    - page_format  : 'simple' | 'post'    : Allows post editor style; defaults to 'simple'
 *                    - page_columns : 1 | 2 | 'auto'       : If 'post' style, how many columns. Default 'auto'
 *                    - reset_button : ''                   : Text for reset button; empty hides reset button
 *                    - reset_action : 'default' | 'remove' : Default is 'default'; 'remove'blanks field
 *
 *                  New filters:
 *                    - cmb2_options_page_title  : allow changing page title to something other than box title
 *                    - cmb2_options_page_before : HTML content before form
 *                    - cmb2_options_page_after  : HTML content after form
 *                    - cmb2_options_form_id     : ID of the options page form
 *                    - cmb2_options_form_top    : Insert HTML after form opening tag
 *                    - cmb2_options_form_bottom : Insert HTML before form closing tag
 *
 *                  Removed methods:
 *                    - options_page_metabox : now in CMB2_Options_Display
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
	 * Common cmb->prop values shared across all boxes
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
	 * @param CMB2   $cmb               The CMB2 object to hookup
	 * @param string $option_key        Option key box is displaying values for
	 * @param string $page              Value of $_GET['page'],
	 * @param array  $boxes             CMB2 boxes used for this page; normally set on hookup
	 * @param array  $shared_properties CMB2 box properties, normally set on hookup
	 */
	public function __construct( CMB2 $cmb, $option_key, $boxes = array(), $page = '', $shared_properties = array() ) {
		
		$this->cmb               = $cmb;
		$this->option_key        = $option_key;
		$this->page              = empty( $page ) ? $this->set_page() : $page;
		$this->boxes             = $boxes;
		$this->shared_properties = $shared_properties;
	}
	
	/**
	 * Default hooks. Also does some preliminary data prep.
	 *
	 * @since 2.XXX  Checks if settings have already been registered
	 * @since 2.2.5  (?) Method undocumented
	 *
	 * @return string|bool   Aid to unit tests
	 */
	public function hooks() {
		
		$return = false;
		
		// set for line lengths and readability
		$OPT = $this->option_key;
		
		// Optionally network_admin_menu.
		$hook = $this->cmb->prop( 'admin_menu_hook' );
		
		// if this setting has not been registered, do so
		if ( ! $this->is_setting_registered( $OPT ) ) {
			
			// Register setting to cmb2 group.
			register_setting( 'cmb2', $OPT );
			
			// Handle saving the data.
			add_action( 'admin_post_' . $OPT, array( $this, 'save_options' ) );
			
			$return = 'setting';
		}
		
		// Hook in determining which boxes are on page
		add_action( $hook, array( $this, 'find_page_boxes' ), 8 );
		
		// Hook shared data prep
		add_action( $hook, array( $this, 'page_properties' ), 9 );
		
		// Hook in to add our menu.
		add_action( $hook, array( $this, 'options_page_menu_hooks' ) );
		
		// Hook to check if updated
		add_action( $hook, array( $this, 'postbox_scripts' ) );
		
		// Hook to check if updated
		add_action( $hook, array( $this, 'is_updated' ) );
		
		// If in the network admin, need to use get/update_site_option.
		if ( 'network_admin_menu' === $hook ) {
			
			// Override CMB's getter.
			add_filter( "cmb2_override_option_get_{$OPT}", array( $this, 'network_get_override' ), 10, 2 );
			
			// Override CMB's setter.
			add_filter( "cmb2_override_option_save_{$OPT}", array( $this, 'network_update_override', ), 10, 2 );
		}
		
		// return the status of
		return ! $return ? 'page' : 'both';
	}
	
	/**
	 * Checks to see if the setting has already been registered with WP.
	 *
	 * @param string $option_key
	 *
	 * @return bool
	 */
	public function is_setting_registered( $option_key ) {
		
		global $wp_registered_settings;
		
		if ( empty( $option_key ) || ! empty( $wp_registered_settings[ $option_key ] ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks to see if a menu or submenu page has already been registered
	 *
	 * @since  2.XXX
	 *
	 * @param  string $menu_slug   Equal to $this->page
	 * @param  string $parent_slug From box properties
	 *
	 * @return bool
	 */
	public function is_page_registered( $menu_slug, $parent_slug = '' ) {
		
		global $_registered_pages;
		
		// get the hook name given the menu_slug and parent_slug
		$hookname = get_plugin_page_hookname( $menu_slug, $parent_slug );
		
		// see if the hookname is in the global _registered_pages var
		return isset( $_registered_pages[ $hookname ] ) && $_registered_pages[ $hookname ] === TRUE;
	}
	
	/**
	 * Hook up our admin menu item and admin page. Used as action
	 *
	 * @since  2.XXX  Checks to see if page has been registered; Adds JS for postboxes; Moved 'updated' to own method
	 * @since  2.2.5
	 *
	 * @return string|bool
	 */
	public function options_page_menu_hooks() {
		
		$parent_slug = $this->cmb->prop( 'parent_slug' );
		
		// Menu_slug is blank, menu page is already registered, no boxes: exit
		if ( ! $this->page || $this->is_page_registered( $this->page, $parent_slug ) || empty( $this->boxes ) ) {
			return false;
		}
		
		if ( $parent_slug ) {
			$page_hook = add_submenu_page(
				$parent_slug,
				$this->shared_properties['title'],
				$this->shared_properties['menu_title'],
				$this->shared_properties['capability'],
				$this->page,
				array( $this, 'options_page_output' )
			);
		} else {
			$page_hook = add_menu_page(
				$this->shared_properties['title'],
				$this->shared_properties['menu_title'],
				$this->shared_properties['capability'],
				$this->page,
				array( $this, 'options_page_output' ),
				$this->shared_properties['icon_url'],
				$this->shared_properties['position']
			);
		}
		
		// Include CMB CSS in the head to avoid FOUC; added here to take advantage of $page_hook
		if ( $this->shared_properties['cmb_styles'] ) {
			add_action( "admin_print_styles-{$page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
		
		// return indicates where the menu was added
		return empty( $parent_slug ) ? 'menu' : 'submenu';
	}
	
	/**
	 * Get the menu slug. We cannot pass a fallback to CMB2 as it will prevent additional pages from being generated.
	 *
	 * - Menu slugs must ALWAYS be accompianied by an explicit declaration of the 'option_key' box parameter, on
	 *   every box shown on the page
	 *
	 * @return string
	 */
	public function set_page() {
		
		$menu_slug = $this->cmb->prop( 'menu_slug' );
		
		$this->page = empty( $menu_slug ) || ( $menu_slug && ! $this->cmb->prop( 'option_key' ) ) ?
			$this->option_key : $menu_slug;
		
		return $this->page;
	}
	
	/**
	 * Checks if settings were updated. Formerly within options_page_menu_hooks.
	 *
	 * @since 2.XXX
	 *
	 * @return bool|string
	 */
	public function is_updated() {
		
		$OPT = $this->option_key . '-notices';
		$UP  = isset( $_GET['updated'] ) ? $_GET['updated'] : false;
		$PG  = isset( $_GET['page'] ) ? $_GET['page'] : false;
		
		if ( empty( $UP ) || $PG !== $this->page ) {
			return false;
		}
		
		if ( 'true' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Settings updated.', 'cmb2' ), 'updated' );
			
		} else if ( 'false' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Nothing to update.', 'cmb2' ), 'notice-warning' );
			
		} else if ( 'reset' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Options reset to defaults.', 'cmb2' ), 'notice-warning' );
		}
		
		return $UP;
	}
	
	/**
	 * Adds postbox scripts if page format is 'post'.
	 *
	 * @since 2.XXX
	 *
	 * @return bool
	 */
	public function postbox_scripts() {
		
		if (
			$this->is_page_registered( $this->page, $this->cmb->prop( 'parent_slug' ) )
			|| $this->shared_properties['page_format'] !== 'post'
		) {
			return false;
		}
		
		// include WP postbox JS
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_script( 'postbox' );
		} );
		
		// trigger the postbox script
		add_action( 'admin_print_footer_scripts', function () {
			echo '<script>jQuery(document).ready(function()'
			     . '{postboxes.add_postbox_toggles("postbox-container");});</script>';
		} );
		
		return true;
	}
	
	/**
	 * Finds all boxes which should appear on this page, add to $this->boxes. Note that if page is a submenu page,
	 * all boxes which are in that submenu page must have menu_slug and parent_slug set.
	 *
	 * @since 2.XXX
	 *
	 * @return array|bool
	 */
	public function find_page_boxes() {
		
		// Hookups below are only needed when first box set to this page is encountered
		if ( ! $this->page || $this->is_page_registered( $this->page, $this->cmb->prop( 'parent_slug' ) ) ) {
			return false;
		}
		
		$all_boxes  = CMB2_Boxes::get_all();
		$page_boxes = array();
		
		foreach ( $all_boxes as $box ) {
			
			$BOX = $box->prop( 'menu_slug' );
			
			// if this box is already in the box list, or it has a menu slug set which does not match the slug, skip
			if ( ! empty( $page_boxes[ $box->cmb_id ] ) || ( $BOX !== NULL && $BOX != $this->page ) ) {
				continue;
			}
			
			/*
			 * Logic:
			 *   $bxslg == $this->page                                      : boxes specifically have this slug set
			 *   in_array( $this->page, (array) $box->options_page_keys() ) : boxes have menu_slug as option key
			 */
			if ( $BOX == $this->page || in_array( $this->page, (array) $box->options_page_keys() ) ) {
				$page_boxes[ $box->cmb_id ] = $box;
			}
		}
		
		$this->boxes = $page_boxes;
		
		return $page_boxes;
	}
	
	/**
	 * Finds shared properties which may have been set on any of the included boxes. First value encountered
	 * is used if two boxes both set the value.
	 *
	 * @since 2.XXX
	 *
	 * @return bool|array
	 */
	public function page_properties() {
		
		if (
			! empty( $this->shared_properties )
			|| $this->is_page_registered( $this->page, $this->cmb->prop( 'parent_slug' ) )
		) {
			return false;
		}
		
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
			'reset_button' => $this->page_prop( 'reset_button', '' ),
			'reset_action' => $this->page_prop( 'reset_action', 'default' ),
			'save_button'  => $this->page_prop( 'save_button', 'Save', FALSE ),
			'title'        => $this->page_prop( 'page_title', $this->cmb->prop( 'title' ) ),
		);
		
		/**
		 * 'cmb2_options_page_title' filter.
		 *
		 * Alters the title for use on the page. Note it's always a good idea to set 'menu_title' separately to
		 * avoid excessively long menu titles
		 *
		 * @since 2.XXX
		 *
		 * @property string                'title'          Title as set via 'page_title' or first box's 'title'
		 * @var      string                $this ->page      Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Display $this Instance of this class
		 */
		$props['title'] = apply_filters( 'cmb2_options_page_title', $props['title'], $this->page, $this );
		
		// need to set this after determining the title
		$props['menu_title'] = $this->page_prop( 'menu_title', $props['title'] );
		
		// place into class property
		$this->shared_properties = $props;
		
		return $props;
	}
	
	/**
	 * Checks set of boxes for property; first box with property set is returned.
	 *
	 * @since 2.XXX
	 *
	 * @param string      $property        CMB2 box property to look for
	 * @param null|string $fallback        Value to use as fallback
	 * @param bool        $empty_string_ok Flag to force using fallback if CMB2 returns empty string
	 *
	 * @return mixed
	 */
	public function page_prop( $property, $fallback = NULL, $empty_string_ok = TRUE ) {
		
		$prop = NULL;
		
		foreach ( $this->boxes as $box ) {
			
			$prop = $box->prop( $property );
			
			// if a value is found and it doesn't break empty string rules, we're done
			if ( $prop !== NULL && ( $empty_string_ok || $prop !== '' ) ) {
				break;
			}
		}
		
		if (
			( ! $empty_string_ok && $prop === '' && is_string( $fallback ) ) // value was empty string, not ok
			|| ( $prop === NULL && $empty_string_ok && $fallback !== NULL )  // value was null, fallback OK
		) {
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
		
		// allow this to return html for tests/future uses; WP sets this to an empty string when called via action
		$echo = $echo === '' || $echo === TRUE;
		
		settings_errors( "{$this->option_key}-notices" );
		
		$callback = $this->shared_properties['display_cb'];
		
		if ( is_callable( $callback ) ) {
			
			// this will catch callbacks which return html or echo
			ob_start();
			$returned = $callback( $this );
			$echoed   = ob_get_clean();
			$html     = $echoed ? $echoed : $returned;
			
		} else {
			
			if ( $this->shared_properties['cmb_styles'] ) {
				self::enqueue_cmb_css();
			}
			if ( $this->shared_properties['enqueue_js'] ) {
				self::enqueue_cmb_js();
			}
			
			// get instance of display class
			$dis = new CMB2_Options_Display( $this->option_key, $this->page, $this->boxes, $this->shared_properties );
			
			// get output from that class
			$html = $dis->options_page_output();
		}
		
		if ( $echo ) {
			echo $html;
			$html = TRUE;
		}
		
		return $html;
	}
	
	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Checks multiple boxes
	 *               Calls $this->can_box_save() instead of $this->can_save()
	 *               Allows options to be reset
	 * @since  2.2.5
	 *
	 * @return void
	 */
	public function save_options() {
		
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}
		
		// set the option and action
		$action  = isset( $_POST['submit-cmb'] ) ? 'save' : ( isset( $_POST['reset-cmb'] ) ? 'reset' : FALSE );
		$option  = isset( $_POST['action'] ) ? $_POST['action'] : FALSE;
		$updated = FALSE;
		
		if ( $action && $option ) {
			
			foreach ( $this->boxes as $box ) {
				
				if ( $this->can_box_save( $box ) && $this->option_key === $option ) {
					
					// if the "reset" button was pressed, return fields to their default values
					if ( $action == 'reset' ) {
						$this->field_values_to_default( $box );
						$updated = 'reset';
					}
					
					$up = $box
						->save_fields( $this->option_key, $box->object_type(), $_POST )
						->was_updated();
					
					// set updated to true, prevent setting to false
					$updated = $updated ? $updated : $up;
				}
			}
			
			$url = add_query_arg( 'updated', var_export( $updated, TRUE ), $url );
		}
		
		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		exit;
	}
	
	/**
	 * Adaptation of parent class 'can_save' -- allows arbitrary box to be checked
	 *
	 * @param \CMB2 $box
	 *
	 * @return mixed
	 */
	public function can_box_save( CMB2 $box ) {
		
		$type = 'options-page';
		
		$can_save = (
			$box->prop( 'save_fields' )
			&& isset( $_POST[ $box->nonce() ] )
			&& wp_verify_nonce( $_POST[ $box->nonce() ], $box->nonce() )
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			&& ( $type && in_array( $type, $box->box_types() ) )
			&& ! ( is_multisite() && ms_is_switched() )
		);
		
		// See CMB2_hookup->can_save()
		return apply_filters( 'cmb2_can_save', $can_save, $box );
	}
	
	/**
	 * Changes _POST values for box fields to default values.
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2 $box
	 */
	public function field_values_to_default( CMB2 $box ) {
		
		$fields = $box->prop( 'fields' );
		
		/**
		 * Depending on value set by 'reset_action', either blanks the field or reverts to default value
		 *
		 * @var \CMB2_Field $field
		 */
		foreach ( $fields as $fid => $field ) {
			$f             = $box->get_field( $field );
			$_POST[ $fid ] = $this->shared_properties['reset_action'] == 'remove' ? '' : $f->get_default();
		}
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
	
	/**
	 * Returns property, allows checking state of class
	 *
	 * @param string $property
	 *
	 * @return mixed|null
	 */
	public function get_prop( $property = '' ) {
		
		return isset( $this->{$property} ) ? $this->{$property} : NULL;
	}
}