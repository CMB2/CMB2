<?php

/**
 * Class CMB2_Page
 * Builds an options page and displays metaboxes belonging to it.
 *
 * Uses:
 *     CMB2_Page_Display         Rendering engine for HTML output
 *     CMB2_Page_Hooks           Configures and adds page-specific WordPress hooks
 *     CMB2_Page_Menu            Adds page to WordPress admin menu
 *     CMB2_Page_Save            Saves options via settings api
 *     CMB2_Page_Shared          Reconciles CMB2 box properties that can be set on any box
 *     CMB2_Page_Utils           Static class, utility functions
 *
 * Applies CMB2 Filters: None
 *
 * Public methods:
 *     set_options_hookup()       Adds CMB2_Options_Hookup instance to ->hookups
 *
 * Public methods accessed via callback:
 *     init()                    Sets up class for use by determining shared vars, adds 'early' hooks
 *     add_metaboxes()           'post' only: triggers add_meta_boxes{->page_hook} action
 *     add_postbox_script()      'post' only: enqueues WP's postbox script
 *     add_postbox_toggle()      'post' only: adds JS to trigger postbox functionality
 *     add_registered_setting()  Checks if ->option_key is registered, registers it if not
 *     add_to_admin_menu()       Adds page to menu and triggers 'late' hooks
 *     add_update_notice()       Checks if page is updated, and adds notices if so
 *     render()                  Outputs HTML, the callback passed to add_[sub]menu_page()
 *     save()                    Saves options on submit from page
 *
 * Protected methods:
 *     is_setting_registered()   Checks whether ->option_key is already registered
 *     is_updated()              Checks if _GET['updated'] is set
 *
 * Private methods:
 *     _class()                  Used to call helper classes, checks ->classes for instance or calls new
 *
 * Magic methods:
 *     __get()                   Magic getter which passes back a reference, allowing direct checking of keys, etc.
 *     __isset()                 Allows determining whether property is empty or not
 *     __set()                   Used only by unit tests. Throws exception if property is not $hookups or $shared
 *
 * @since 2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read  bool                  $hooked
 * @property       CMB2_Options_Hookup[] $hookups
 * @property-read  string                $option_key
 * @property-read  string                $page_id
 * @property       array                 $shared
 * @property-read  string                $page_hook
 * @property-read  string                $wp_menu_hook
 */
class CMB2_Page {
	
	/**
	 * Has the page been hooked up?
	 *
	 * @since 2.XXX
	 * @var bool
	 */
	public $hooked;
	
	/**
	 * CMB2 options hookup objects used by this page
	 *
	 * @since 2.XXX
	 * @var CMB2_Options_Hookup[]
	 */
	protected $hookups = array();
	
	/**
	 * WP Options key
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $option_key = '';
	
	/**
	 * The page slug, equivalent to $_GET['page']
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $page_id = '';
	
	/**
	 * CMB2 box properties used by this page, as pulled from collective boxes on page
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $shared = array();
	
	/**
	 * The first box to call this instance will set the menu hook!
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $wp_menu_hook = '';
	
	/**
	 * The wordpress menu page hook.
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $page_hook = '';
	
	/**
	 * Holds instances of classes used by class.
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $classes = array(
		'Display' => NULL,
		'Hooks'   => NULL,
		'Menu'    => NULL,
		'Save'    => NULL,
		'Shared'  => NULL,
	);
	
	/**
	 * CMB2_Page constructor.
	 *
	 * @since 2.XXX
	 * @param string $page_id
	 * @param string $option_key
	 * @param string $wp_menu_hook
	 */
	public function __construct( $page_id, $option_key, $wp_menu_hook ) {
		
		$this->page_id      = $page_id;
		$this->option_key   = $option_key;
		$this->wp_menu_hook = $wp_menu_hook;
	}
	
	/**
	 * Hooked: 'wp_loaded'
	 * Determined shared properties, adds hooks that are tied to this page. Note that hooks dependent on
	 * knowing the WP menu 'page hook' are added by the add_to_menu() method.
	 *
	 * @since  2.XXX
	 * @return array|bool
	 */
	public function init() {

		if ( $this->hooked ) {
			return FALSE;
		}
		
		$this->hooked = TRUE;
		$this->shared = $this->_class( 'Shared' )->return_shared_properties();
		$this->maybe_scripts();
		$this->_class( 'Hooks' )->hooks();
		
		return TRUE;
	}
	
	/**
	 * Hooked: 'load-{PAGEHOOK}'
	 * Adds metaboxes to page
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_metaboxes() {
		
		if ( $this->shared['page_format'] == 'post' ) {
			
			do_action( 'add_meta_boxes_' . $this->page_id, NULL );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Hooked: 'admin_enqueue_scripts'
	 * Adds WP JS needed for post-style page.
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_postbox_script() {
		
		wp_enqueue_script( 'postbox' );
		
		return TRUE;
	}
	
	/**
	 * Hooked: 'admin_print_footer_scripts'
	 * Adds script to admin footer to toggle postboxes
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_postbox_toggle() {
		
		echo '<script>jQuery(document).ready(function()';
		echo '{postboxes.add_postbox_toggles("postbox-container");});</script>';
		
		return TRUE;
	}
	
	/**
	 * Hooked: $wp_menu_hook
	 * Checks to see if the setting is registered, and if not, registers it
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_registered_setting() {
		
		if ( ! $this->is_setting_registered() ) {
			
			register_setting( 'cmb2', $this->option_key );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Hooked: $wp_menu_hook
	 * Adds page to admin menu.
	 *
	 * @since  2.XXX
	 * @return array
	 */
	public function add_to_admin_menu() {
		
		$menu = $this->_class( 'Menu' )->add_to_menu();
		
		if ( ! empty( $menu['page_hook'] ) ) {
			
			$this->page_hook = $menu['page_hook'];
			
			$this->_class( 'Hooks' )->hooks();
		}
		
		return $menu;
	}
	
	/**
	 * Hooked: $wp_menu_hook
	 * Adds settings notice if updated.
	 *
	 * @since  2.XXX
	 * @return string
	 */
	public function add_update_notice() {
		
		$up   = $this->is_updated();
		$str  = '';
		$hook = $up === 'true' ? 'updated' : 'notice-warning';
		
		if ( $up === FALSE ) {
			return FALSE;
		}
		
		switch ( $up ) {
			case 'true':
				$str = __( 'Settings updated.', 'cmb2' );
				break;
			case 'false':
				$str = __( 'Nothing to update.', 'cmb2' );
				break;
			case 'reset':
				$str = __( 'Options reset to defaults.', 'cmb2' );
				break;
		}
		
		add_settings_error( $this->option_key . '-notices', 'cmb2', __( $str, 'cmb2' ), $hook );
		
		return $up;
	}
	
	/**
	 * Callback set via add_menu_page or add_submenu_page.
	 * Switch for display of options page, using either the callback or internal render function
	 *
	 * @since  2.XXX
	 * @param  bool|string $echo Allows this method to return instead of echoing. WP will set this to ''.
	 * @return bool|string
	 */
	public function render( $echo = TRUE ) {
		
		$echo     = $echo !== FALSE;
		$callback = $this->shared['display_cb'];
		
		if ( is_callable( $callback ) ) {
			$html = CMB2_Page_Utils::do_void_action( array( $this ), $callback );
		} else {
			$html = $this->_class( 'Display' )->render();
		}
		
		if ( $echo ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Hooked: admin_post_ . $option_key
	 * Gateway to CMB2_Page_Save->save_options, a void function.
	 *
	 * @since 2.XXX
	 */
	public function save() {
		
		$this->_class( 'Save' )->save_options();
	}
	
	/**
	 * Called from: CMB2_Options_Hookup->get_page_hookup()
	 * Adds a CMB2_Options_Hookup object to this page. Note it will overwrite existing hookup with same cmb_id.
	 *
	 * @since  2.XXX
	 * @param  \CMB2_Options_Hookup $hookup
	 * @return bool
	 */
	public function set_options_hookup( CMB2_Options_Hookup $hookup ) {
		
		$this->hookups[ $hookup->cmb->cmb_id ] = $hookup;
		
		return $hookup->cmb->cmb_id;
	}
	
	/**
	 * Checks if setting is already registered.
	 *
	 * @since  2.XXX
	 * @param  string $option_key Option key to check, defaults to class property
	 * @return bool
	 */
	protected function is_setting_registered( $option_key = '' ) {
		
		$option_key = empty( $option_key ) ? $this->option_key : $option_key;
		
		global $wp_registered_settings;
		
		return ! empty( $wp_registered_settings[ $option_key ] );
	}
	
	/**
	 * Checks to see if the page is updated.
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	protected function is_updated() {
		
		$up   = isset( $_GET['updated'] ) ? $_GET['updated'] : FALSE;
		$page = isset( $_GET['page'] ) ? $_GET['page'] : FALSE;
		
		if ( empty( $up ) || $page !== $this->page_id ) {
			return FALSE;
		}
		
		return $up;
	}
	
	/**
	 * Checks shared props to see if JS or CSS should be added, does the appropriate thing
	 *
	 * @since  2.XXX
	 * @return array
	 */
	protected function maybe_scripts() {
		
		// Add filters to remove JS or CSS
		if ( ! $this->shared['cmb_styles'] ) {
			add_filter( 'cmb2_enqueue_css', false );
		}
		if ( ! $this->shared['enqueue_js'] ) {
			add_filter( 'cmb2_enqueue_js', false );
		}
		
		// ensure the JS or CSS is added
		if ( $this->shared['enqueue_js'] || $this->shared['cmb_styles'] ) {
			foreach ( $this->hookups as $hookup ) {
				if ( $this->shared['cmb_styles'] ) {
					$hookup::enqueue_cmb_css();
				}
				if ( $this->shared['enqueue_js'] ) {
					$hookup::enqueue_cmb_js();
				}
			}
		}

		// return array for testing purposes
		return array( 'js' => $this->shared['enqueue_js'], 'css' => $this->shared['cmb_styles'] );
	}
	
	/**
	 * Gets an instance of class needed by page.
	 *
	 * @since  2.XXX
	 * @param  string $name Last part of helper class name, such as 'Display'
	 * @return mixed
	 * @throws \Exception
	 */
	private function _class( $name ) {
		
		if ( ! isset( $this->classes[ $name ] ) ) {
			
			$class = 'CMB2_Page_' . $name;
			
			if ( ! class_exists( $class ) ) {
				throw new Exception( 'Tried to invoke a non-existant class' );
			}
			
			$this->classes[ $name ] = new $class( $this );
		}
		
		return $this->classes[ $name ];
	}
	
	/**
	 * Returns a copy of property asked for, by reference, allowing direct manipulation of property
	 *
	 * @since  2.XXX
	 * @param  string $property Class property to fetch
	 * @return mixed|null
	 */
	public function &__get( $property ) {
		
		$return = isset( $this->{$property} ) ? $this->{$property} : NULL;
		
		return $return;
	}
	
	/**
	 * Allows setting class properties. Used mainly for unit testing
	 *
	 * @since  2.XXX
	 * @param  string $property Class property to set
	 * @param  mixed  $value    Value to add
	 * @return array|\CMB2_Options_Hookup[]
	 * @throws \Exception
	 */
	public function __set( $property, $value ) {
		
		switch ( $property ) {
			
			case 'hookups':
				
				if ( ! empty( $value ) && is_array( $value ) ) {
					foreach ( $value as $key => $val ) {
						if ( ! $val instanceof CMB2_Options_Hookup ) {
							unset( $value[ $key ] );
						}
					}
					$this->hookups = $value;
				}
				
				return $this->hookups;
				break;
			
			case 'shared':
				if ( ! empty( $value ) && is_array( $value ) ) {
					$this->shared = $value;
				}
				
				return $this->shared;
				break;
			
			default:
				throw new Exception( 'Cannot set ' . $property . ' in ' . __CLASS__ );
				break;
		}
	}
	
	/**
	 * Allow determining if a property has been set
	 *
	 * @since  2.XXX
	 * @param  string $property
	 * @return bool
	 */
	public function __isset( $property ) {
		
		return isset( $this->$property );
	}
}