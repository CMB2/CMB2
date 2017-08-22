<?php

/**
 * Handles hooking CMB2 forms/metaboxes into WordPress admin "options" pages.
 *
 * @since     2.XXX Now allows multiple metaboxes on an options page.
 *
 *                  New global:
 *                    - cmb2_options_hookup_filters : Array, tracks hooks per page
 *
 *                  New methods:
 *                    - add_page_box              : Adds a CMB2 box to this->boxes
 *                    - add_page_boxes            : Adds multiple CMB2 boxes to $this->boxes
 *                    - can_box_save              : copy of can_save not tied to $this->cmb
 *                    - error_already_exists      : Avoids duplicate notices
 *                    - field_values_to_default   : returns field values to their default values
 *                    - filtered                  : adds hook to global filter list
 *                    - find_page_boxes           : finds all boxes which appear on this page
 *                    - get_prop                  : Get value of class property
 *                    - has_filters               : checks if hookup filter has already been hooked
 *                    - is_setting_registered     : Has the setting already been registered?
 *                    - is_updated                : were the values updated?
 *                    - page_prop                 : scans this->boxes for requested CMB2 property
 *                    - page_properties           : compiles shared properties from multiple boxes
 *                    - postbox_scripts           : Adds WP postbox JS for 'post' format pages
 *                    - set_page                  : determines this->page value
 *                    - set_prop                  : Allows setting class property
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
	 * @param string $page              Value of _GET['page'],
	 * @param array  $boxes             CMB2 boxes used for this page; normally set on hookup
	 * @param array  $shared_properties CMB2 box properties, normally set on hookup
	 */
	public function __construct( CMB2 $cmb, $option_key, $boxes = array(), $page = '', $shared_properties = array() ) {
		
		$this->cmb        = $cmb;
		$this->option_key = (string) $option_key;
		$this->page       = empty( $page ) ? $this->set_page() : (string) $page;
		
		if ( ! empty( $boxes ) ) {
			$this->add_page_boxes( $boxes );
		}
		
		if ( ! empty( $shared_properties ) && is_array( $shared_properties ) ) {
			$this->page_properties( $shared_properties );
		}
	}
	
	/**
	 * Default hooks.
	 *
	 * @since 2.XXX  Checks if settings have already been registered
	 * @since 2.2.5  (?) Method undocumented
	 *
	 * @return array  Array of all hooks added
	 */
	public function hooks() {
		
		$OPT = $this->option_key;
		
		// Optionally network_admin_menu.
		$hook = $this->cmb->prop( 'admin_menu_hook' );
		
		// For testing purposes, return an array which records this method's set actions
		$return = array( 'admin_post_' . $OPT => array(), $hook => array() );
		
		// set hooks to be called
		$hooks = array(
			array( 'id' => 'add_page_boxes', 'priority' => 8 ),
			array( 'id' => 'page_properties', 'priority' => 9 ),
			array( 'id' => 'options_page_menu_hooks', ),
			array( 'id' => 'postbox_scripts', 'priority' => 11, ),
			array( 'id' => 'is_updated', 'priority' => 12, ),
			array(
				'id'      => 'cmb2_override_option_get_' . $OPT,
				'type'    => 'filter',
				'args'    => 2,
				'call'    => array( $this, 'network_get_override' ),
				'only_if' => 'network_admin_menu',
			),
			array(
				'id'      => 'cmb2_override_option_save_' . $OPT,
				'type'    => 'filter',
				'args'    => 2,
				'call'    => array( $this, 'network_update_override' ),
				'only_if' => 'network_admin_menu',
			),
		);
		
		// if setting isn't registered, add it now
		if ( ! $this->is_setting_registered() ) {
			
			register_setting( 'cmb2', $OPT );
			
			// add hook to save data
			$hooks[] = array( 'id' => 'save_options', 'hook' => 'admin_post_' . $OPT );
		}
		
		foreach ( $hooks as $f ) {
			
			// set defaults if not specified in config
			$f_hook     = ! empty( $f['hook'] ) ? $f['hook'] : $hook;
			$f_only_if  = ! empty( $f['only_if'] ) ? $f['only_if'] : $f_hook;
			$f_type     = ! empty( $f['type'] ) ? (string) $f['type'] : 'action';
			$f_priority = ! empty( $f['priority'] ) ? (int) $f['priority'] : 10;
			$f_args     = ! empty( $f['args'] ) ? (int) $f['args'] : 1;
			$f_call     = ! empty( $f['call'] ) ? $f['call'] : array( $this, $f['id'] );
			
			if ( ( $f_only_if === $f_hook ) && ( $this->has_filter( $f_hook, $f['id'] ) === FALSE ) ) {
				
				$wp_call = 'add_' . $f_type;
				$wp_call( $f_hook, $f_call, $f_priority, $f_args );
				
				// set the return value for testing
				$return[ $f_hook ][ $f['id'] ] = $this->filtered( $f_hook, $f['id'] );
			}
		}
		
		return $return;
	}
	
	/**
	 * Tracks via a global if this option key has CMB2_Options_Hookup hooks, returns true if so
	 *
	 * @param string $page   Equivalent to this->page
	 * @param string $hook   The tag being set
	 * @param        $method $method  Method being called
	 *
	 * @return bool
	 */
	public function has_filter( $hook, $method, $page = '' ) {
		
		$hook   = (string) $hook;
		$method = (string) $method;
		$page   = empty( $page ) ? $this->page : (string) $page;
		
		global $cmb2_options_hookup_filters;
		
		// set empty arrays if they are not set
		if ( empty( $cmb2_options_hookup_filters ) ) {
			$cmb2_options_hookup_filters = array( $page => array() );
		}
		if ( ! isset( $cmb2_options_hookup_filters[ $page ][ $hook ] ) ) {
			$cmb2_options_hookup_filters[ $page ][ $hook ] = array();
		}
		
		return in_array( $method, $cmb2_options_hookup_filters[ $page ][ $hook ] );
	}
	
	/**
	 * Saves the method name to the global to prevent adding another hook. Forces type on parameters.
	 *
	 * @param string $hook   The tag being set
	 * @param string $method Method being called
	 * @param string $page   Equivalent to this->page
	 *
	 * @return bool
	 */
	public function filtered( $hook, $method, $page = '' ) {
		
		$hook   = (string) $hook;
		$method = (string) $method;
		$page   = empty( $page ) ? $this->page : (string) $page;
		
		global $cmb2_options_hookup_filters;
		
		// checking this will ensure the arrays exist before setting
		if ( ! $this->has_filter( $hook, $method, $page ) ) {
			$cmb2_options_hookup_filters[ $page ][ $hook ][] = $method;
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Checks to see if the setting has already been registered with WP.
	 *
	 * @param string $option_key Resolves to this->option_key if not passed.
	 *
	 * @return bool
	 */
	public function is_setting_registered( $option_key = '' ) {
		
		$option_key = empty( $option_key ) ? $this->option_key : $option_key;
		
		global $wp_registered_settings;
		
		return ! empty( $wp_registered_settings[ $option_key ] );
	}
	
	/**
	 * Hook up our admin menu item and admin page. Returns array for testing purposes.
	 *
	 * @since  2.XXX  Checks to see if page has been registered; Adds JS for postboxes; Moved 'updated'
	 * @since  2.2.5
	 *
	 * @return array
	 */
	public function options_page_menu_hooks() {
		
		$parent_slug = $this->cmb->prop( 'parent_slug' );
		$return      = array();
		
		// Menu_slug is blank, menu page is already registered, no boxes: exit
		if ( ! $this->page || empty( $this->boxes ) ) {
			return $return;
		}
		
		$params = array(
			'parent_slug' => $parent_slug,
			'title'       => $this->shared_properties['title'],
			'menu_title'  => $this->shared_properties['menu_title'],
			'capability'  => $this->shared_properties['capability'],
			'menu_slug'   => $this->page,
			'action'      => 'options_page_output',
			'icon_url'    => $this->shared_properties['icon_url'],
			'position'    => $this->shared_properties['position'],
			'cmb_styles'  => $this->shared_properties['cmb_styles'],
		);
		
		if ( $parent_slug ) {
			$page_hook = add_submenu_page(
				$params['parent_slug'],
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				array( $this, $params['action'] )
			);
		} else {
			$page_hook = add_menu_page(
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				array( $this, $params['action'] ),
				$params['icon_url'],
				$params['position']
			);
		}
		
		// Include CMB CSS in the head to avoid FOUC; added here to take advantage of $page_hook
		if ( $params['cmb_styles'] ) {
			add_action( "admin_print_styles-{$page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}
		
		return array(
			'type'      => ( empty( $parent_slug ) ? 'menu' : 'submenu' ),
			'params'    => $params,
			'page_hook' => $page_hook,
		);
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
		
		$page = empty( $menu_slug ) || ( $menu_slug && ! $this->cmb->prop( 'option_key' ) ) ?
			$this->option_key : $menu_slug;
		
		return $page;
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
		$UP  = isset( $_GET['updated'] ) ? $_GET['updated'] : FALSE;
		$PG  = isset( $_GET['page'] ) ? $_GET['page'] : FALSE;
		
		if ( empty( $UP ) || $PG !== $this->page || $this->error_already_exists( $OPT ) ) {
			return FALSE;
		}
		
		if ( 'true' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Settings updated.', 'cmb2' ), 'updated' );
			
		} else if ( 'false' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Nothing to update.', 'cmb2' ), 'notice-warning' );
			
		} else if ( 'reset' === $UP ) {
			
			add_settings_error( $OPT, '', __( 'Options reset to defaults.', 'cmb2' ), 'notice-warning' );
		}
		
		// adding 'return_' to string to avoid type mismatch on testing
		return 'return_' . $UP;
	}
	
	/**
	 * Checks to see if setting has already been set
	 *
	 * @since 2.XXX
	 *
	 * @param string $opt Key sent to add_settings_error()
	 *
	 * @return bool
	 */
	public function error_already_exists( $opt ) {
		
		global $wp_settings_errors;
		
		if ( empty( $wp_settings_errors ) ) {
			return FALSE;
		}
		
		foreach ( $wp_settings_errors as $err ) {
			if ( $err['setting'] == $opt ) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Adds postbox scripts if page format is 'post'.
	 *
	 * @since 2.XXX
	 *
	 * @return bool
	 */
	public function postbox_scripts() {
		
		if ( $this->shared_properties['page_format'] !== 'post' ) {
			return FALSE;
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
		
		return TRUE;
	}
	
	/**
	 * Allows boxes to be passed in constructor, insures they're CMB2 instances. Can only be called if
	 * $this->boxes is empty (ie, on initialization).
	 *
	 * @since 2.XXX
	 *
	 * @param array $boxes Array of CMB2 boxes passed via constructor
	 *
	 * @return array|bool
	 */
	public function add_page_boxes( $boxes = array() ) {
		
		if ( ! empty( $this->boxes ) ) {
			return FALSE;
		}
		
		$add = array();
		
		if ( ! empty( $boxes ) ) {
			
			// this is invoked when boxes are passed in the constructor
			foreach ( $boxes as $box ) {
				$this->add_page_box( $box );
			}
			
		} else {
			
			// this looks through all configured CMB2 boxes and is the "normal" route to adding boxes
			$this->boxes = $this->find_page_boxes();
		}
		
		return empty( $this->boxes ) ? FALSE : $add;
	}
	
	/**
	 * Adds a single box to the $this->boxes array. Can be passed a CMB2 object or a CMB2 box ID.
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2|string $box
	 *
	 * @return bool
	 */
	public function add_page_box( $box ) {
		
		$return = FALSE;
		
		if ( is_string( $box ) ) {
			$box = cmb2_get_metabox( $box );
		}
		
		if ( ! isset( $this->boxes[ $box->cmb_id ] ) && $box instanceof CMB2 ) {
			$this->boxes[ $box->cmb_id ] = $box;
			$return                      = TRUE;
		}
		
		return $return;
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
		if ( ! $this->page ) {
			return FALSE;
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
	 * @param array $passed Page properties passed in via constructor.
	 *
	 * @return array|bool
	 */
	public function page_properties( $passed = array() ) {
		
		if ( ! empty( $this->shared_properties ) && empty( $passed ) ) {
			return FALSE;
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
		
		// if passed properties, they overwrite array values
		$props = ! empty( $passed ) ? array_merge( $props, $passed ) : $props;
		
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
		$boxes   = isset( $_POST['cmb2_boxes'] ) ? explode( ',', $_POST['cmb2_boxes'] ) : FALSE;
		$updated = FALSE;
		
		if ( $action && $option && $boxes ) {
			
			if ( ! empty( $boxes ) && empty( $this->boxes ) ) {
				$this->add_page_boxes( $boxes );
			}
			
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
	 * @since 2.XXX Added test for expected value of test before returning site option
	 * @since 2.2.5
	 *
	 * @param string $test should be 'cmb2_no_override_option_get'
	 * @param mixed  $default
	 *
	 * @return mixed  Value set for the network option.
	 */
	public function network_get_override( $test, $default = FALSE ) {
		
		return $test == 'cmb2_no_override_option_get'
			? get_site_option( $this->option_key, $default ) : $test;
	}
	
	/**
	 * Replaces update_option with update_site_option
	 *
	 * @since 2.XXX Added test for expected value of test
	 * @since 2.2.5
	 *
	 * @param string $test should be 'cmb2_no_override_option_save'
	 * @param mixed  $option_value
	 *
	 * @return bool Success/Failure
	 */
	public function network_update_override( $test, $option_value ) {
		
		return $test == 'cmb2_no_override_option_save'
			? update_site_option( $this->option_key, $option_value ) : $test;
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
	
	/**
	 * Sets class property. Useful mainly for testing, Boxes should carry all of this information.
	 *
	 * @param string $property Property name for internal property
	 * @param mixed  $value    Value to set. Must match type, though contents are not verified.
	 *
	 * @return bool
	 */
	public function set_prop( $property = '', $value = NULL ) {
		
		if ( isset( $this->{$property} ) && $value !== NULL && gettype( $value ) === gettype( $this->{$property} ) ) {
			$this->{$property} = $value;
			
			return TRUE;
		}
		
		return FALSE;
	}
}