<?php
/**
 * Class CMB2_Page.
 * Base class for Page hookups.
 *
 * @property-read  bool                  $hooked
 * @property       CMB2_Options_Hookup[] $hookups
 * @property-read  string                $option_key
 * @property-read  string                $page_id
 * @property       array                 $shared
 * @property-read  string                $wp_menu_hook
 */
abstract class CMB2_Page {
	
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
	 * Child class must define this method, which sets any wordpress action or filter hooks needed.
	 *
	 * @since  2.XXX
	 * @return mixed
	 */
	abstract protected function add_hooks();
	
	/**
	 * Child class must provide a way to get hook config array(s) needed
	 *
	 * @since  2.XXX
	 * @param  string $key Key can be method calling the hook array
	 * @return mixed
	 */
	abstract protected function get_hooks( $key = '' );
	
	/**
	 * Child class must define this method, which is expected to output the page
	 *
	 * @since 2.XXX
	 * @return mixed
	 */
	abstract protected function render_page();
	
	
	/**
	 * Adds a CMB2_Options_Hookup object to this page. Note it will overwrite existing hookup with same cmb_id
	 *
	 * @since  2.XXX
	 * @param  \CMB2_Options_Hookup $hookup
	 * @return bool
	 */
	public function add_hookup( CMB2_Options_Hookup $hookup ) {
		
		$this->hookups[ $hookup->cmb->cmb_id ] = $hookup;
		
		return $hookup->cmb->cmb_id;
	}
	
	/**
	 * Do the metabox actions for this page
	 *
	 * @since 2.XXX
	 */
	public function do_metaboxes() {
		
		if ( $this->shared['page_format'] == 'post' ) {
			do_action( 'add_meta_boxes_' . $this->page_id, NULL );
		}
	}
	
	/**
	 * Adds hooks tied to the creation of this page.
	 *
	 * @since  2.XXX
	 * @return array|bool
	 */
	public function hooks() {
		
		if ( $this->hooked ) {
			return FALSE;
		}
		
		$this->hooked = TRUE;
		
		$this->get_shared_props();
		
		return $this->add_hooks();
	}
	
	/**
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
			$html = $this->render_page();
		}
		
		if ( $echo ) {
			echo $html;
		}
		
		return $html;
	}
	
	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Checks multiple boxes
	 * @return void
	 */
	public function save_options() {
		
		$url = wp_get_referer() ? wp_get_referer() : admin_url();
		
		$action  = isset( $_POST['submit-cmb'] ) ? 'save' : ( isset( $_POST['reset-cmb'] ) ? 'reset' : FALSE );
		$option  = isset( $_POST['action'] ) ? $_POST['action'] : FALSE;
		$updated = FALSE;
		
		if ( $action && $option && $this->option_key === $option ) {
			
			foreach ( $this->hookups as $hookup ) {
				
				if ( $this->can_save( $hookup ) ) {
					
					if ( $action == 'reset' ) {
						$this->field_values_to_default( $hookup );
						$updated = 'reset';
					}
					
					$up = $hookup->cmb
						->save_fields( $this->option_key, $hookup->cmb->object_type(), $_POST )
						->was_updated();
					
					$updated = $updated ? $updated : $up;
				}
			}
		}
		
		$url = add_query_arg( 'updated', var_export( $updated, TRUE ), $url );
		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		
		exit;
	}
	
	/**
	 * Adds page to wordpress admin menu and returns page hook.
	 *
	 * @since  2.XXX
	 * @param  $params
	 * @return false|null|string
	 */
	protected function add_to_admin_menu( $params ) {
		
		$menu = $submenu = NULL;
		
		// if null is passed, the page is created but not added to the menu system
		if ( empty( $params['parent_slug'] ) && $params['parent_slug'] !== NULL ) {
			
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
		if ( $params['parent_slug'] || $params['parent_slug'] === NULL ) {
			
			$submenu = add_submenu_page(
				$params['parent_slug'],
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				$params['action']
			);
		}
		
		return $menu !== NULL ? $menu : $submenu;
	}
	
	/**
	 * Adaptation of CMB_Hookup 'can_save' -- allows arbitrary box to be checked
	 *
	 * @param \CMB2_Options_Hookup $hookup
	 * @return mixed
	 */
	protected function can_save( CMB2_Options_Hookup $hookup ) {
		
		$can_save = (
			$hookup->cmb->prop( 'save_fields' )
			&& isset( $_POST[ $hookup->cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $hookup->cmb->nonce() ], $hookup->cmb->nonce() )
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			&& ( 'options-page' && in_array( 'options-page', $hookup->cmb->box_types() ) )
			&& ! ( is_multisite() && ms_is_switched() )
		);
		
		return apply_filters( 'cmb2_can_save', $can_save, $hookup->cmb );
	}
	
	/**
	 * Changes _POST values for box fields to default values.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Options_Hookup $hookup
	 */
	protected function field_values_to_default( $hookup ) {
		
		$fields = $hookup->cmb->prop( 'fields' );
		
		foreach ( $fields as $fid => $field ) {
			$f             = $hookup->cmb->get_field( $field );
			$_POST[ $fid ] = $hookup->cmb->prop( 'reset_action' ) == 'remove' ? '' : $f->get_default();
		}
	}
	
	/**
	 * Determines how many columns for a post-style page
	 *
	 * @since  2.XXX
	 * @param  int|string $cols Value of the shared property
	 * @return int Value will be '1' or '2'
	 */
	protected function find_page_columns( $cols = 'auto' ) {
		
		// a value was passed, it can either be 2 or 1
		if ( $cols !== 'auto' ) {
			return intval( $cols ) !== 2 ? 1 : 2;
		}
		
		$cols = 1;
		
		// run through the boxes, if a side box is found, cols equals 2
		foreach ( $this->hookups as $hook ) {
			if ( $hook->cmb->prop( 'context' ) == 'side' ) {
				$cols = 2;
				break;
			}
		}
		
		return $cols;
	}
	
	/**
	 * Checks hookups assigned to this page for CMB properties
	 *
	 * @since  2.XXX
	 * @param  string $property        Property to check
	 * @param  mixed  $fallback        Fallback if prop is null
	 * @param  bool   $empty_string_ok Whether an empty string is allowed to be returned
	 * @return mixed
	 */
	protected function get_page_prop( $property, $fallback = NULL, $empty_string_ok = TRUE ) {
		
		$prop = NULL;
		$ok   = $empty_string_ok;
		
		foreach ( $this->hookups as $hookup ) {
			$check = $hookup->cmb->prop( $property );
			if ( $check !== NULL && ( $ok || $check !== '' ) ) {
				$prop = $check;
			}
		}
		
		if ( ( ! $ok && $prop === '' && is_string( $fallback ) ) || ( $prop === NULL && $fallback !== NULL ) ) {
			$prop = $fallback;
		}
		
		return $prop;
	}
	
	/**
	 * Creates an array of properties used within the options page by checking each hookup for that property set
	 * on its CMB2 object. Uses the last non-null value returned.
	 *
	 * @since  2.XXX
	 * @param  array $passed Allows passing in an array for test purposes
	 * @return array|bool
	 */
	protected function get_shared_props( $passed = array() ) {
		
		if ( ( ! empty( $this->shared ) && empty( $passed ) ) || ! is_array( $passed ) ) {
			return FALSE;
		}
		
		$title = $this->get_page_prop( 'title' );
		
		$props = array(
			'capability'     => $this->get_page_prop( 'capability', 'manage_options' ),
			'cmb_styles'     => $this->get_page_prop( 'cmb_styles', TRUE ),
			'display_cb'     => $this->get_page_prop( 'display_cb', FALSE ),
			'enqueue_js'     => $this->get_page_prop( 'enqueue_js', TRUE ),
			'icon_url'       => $this->get_page_prop( 'icon_url', '' ),
			'menu_title'     => '', // set below so filtered page title can be passed as fallback
			'menu_first_sub' => $this->get_page_prop( 'menu_first_sub' ),
			'parent_slug'    => $this->get_page_prop( 'parent_slug' ),
			'page_columns'   => $this->get_page_prop( 'page_columns', 'auto' ),
			'page_format'    => $this->get_page_prop( 'page_format', 'simple' ),
			'position'       => $this->get_page_prop( 'position' ),
			'reset_button'   => $this->get_page_prop( 'reset_button', '' ),
			'reset_action'   => $this->get_page_prop( 'reset_action', 'default' ),
			'save_button'    => $this->get_page_prop( 'save_button', 'Save', FALSE ),
			'title'          => $this->get_page_prop( 'page_title', $title ),
		);
		
		// changes 'auto' into an int, and if not auto, ensures value is in range
		$props['page_columns'] = $this->find_page_columns( $props['page_columns'] );
		
		/**
		 * 'cmb2_options_page_title' filter.
		 * Alters the title for use on the page.
		 *
		 * @since 2.XXX
		 */
		$props['title'] = (string) apply_filters( 'cmb2_options_page_title', $props['title'], $this );
		
		// need to set after determining the filtered title to use as fallback
		$props['menu_title'] = $this->get_page_prop( 'menu_title', $props['title'] );
		
		/**
		 * 'cmb2_options_menu_title' filter.
		 * Alters the title for use on the menu.
		 *
		 * @since 2.XXX
		 */
		$props['menu_title'] =
			(string) apply_filters( 'cmb2_options_menu_title', $props['menu_title'], $this );
		
		// if passed properties, they overwrite array values
		$props = ! empty( $passed ) ? $this->merge_shared_props( $props, $passed ) : $props;
		
		/**
		 * 'cmb2_options_shared_properties' filter.
		 * Allows replacement/altering of shared_properties.
		 *
		 * @since 2.XXX
		 */
		$filtered = apply_filters( 'cmb2_options_shared_properties', $props, $this );
		
		// if passed properties, they overwrite array values
		$props = $props != $filtered ? $this->merge_shared_props( $props, $filtered ) : $props;
		
		// place into class property
		$this->shared = $props;
		
		return $props;
	}
	
	/**
	 * Prepares hooks array, applying filter
	 *
	 * @since  2.XXX
	 * @param  string $method Appended to the filter name for pinpoint
	 * @param  array  $hooks  The method being called, used to select hooks from class property
	 * @param  array  $tokens Tokens to be replaced
	 * @return array|bool
	 */
	protected function hooks_array( $method, $hooks, $tokens = array() ) {
		
		$hooks = array_merge( $hooks, $this->required_hooks( $method ) );
		
		/*
		 * By sending the hooks to the prepare_hooks_array method, they will be returned will all keys
		 * set, making them easier to understand for any dev asking for them by the filter below.
		 */
		$hooks = CMB2_Page_Utils::prepare_hooks_array( $hooks, $this->wp_menu_hook, $tokens );
		
		/**
		 * 'cmb2_options_pagehooks' filter.
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since 2.XXX
		 */
		$filtered = apply_filters( 'cmb2_options_pagehooks', $hooks, $this );
		
		/**
		 * 'cmb2_options_pagehooks_' filter.
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since 2.XXX
		 */
		$filtered = apply_filters( 'cmb2_options_pagehooks_' . $method, $filtered, $this );
		
		return $hooks != $filtered ? $filtered : $hooks;
	}
	
	/**
	 * Checks if setting is already registered.
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	protected function is_setting_registered() {
		
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
	 * Parameters needed to add menu item. Allows parameter array with matching keys to override
	 * the defaults.
	 *
	 * @since  2.XXX
	 * @param  array $add
	 * @return array|bool
	 */
	protected function menu_parameters( $add = array() ) {
		
		$add = ! is_array( $add ) ? array() : $add;
		
		$defaults = array(
			'parent_slug'    => $this->shared['parent_slug'],
			'title'          => $this->shared['title'],
			'menu_title'     => $this->shared['menu_title'],
			'capability'     => $this->shared['capability'],
			'menu_slug'      => $this->page_id,
			'action'         => array( $this, 'render' ),
			'icon_url'       => $this->shared['icon_url'],
			'position'       => $this->shared['position'],
			'menu_first_sub' => $this->shared['menu_first_sub'],
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
		if ( ! is_array( $filtered ) || array_keys( $filtered ) != array_keys( $defaults ) ) {
			return FALSE;
		}
		
		return $filtered;
	}
	
	/**
	 * Merges shared properties that are passed via filter or constructor. Checks keys and does some type checking.
	 *
	 * @since  2.XXX
	 * @param  array $props  The non-altered array
	 * @param  array $passed Modified version
	 * @return array
	 */
	protected function merge_shared_props( $props, $passed ) {
		
		if ( empty( $passed ) ) {
			return $props;
		}
		
		// remove keys from passed which are not in $props
		$passed = ! empty( $passed ) ? array_intersect_key( $passed, array_flip( array_keys( $props ) ) ) : $passed;
		
		// there is probably a better way of checking these types...?
		$types     = array(
			'is_object'  => array( 'display_cb' ),
			'is_bool'    => array( 'cmb_styles', 'display_cb', 'enqueue_js' ),
			'is_null'    => array( 'position', 'parent_slug' ),
			'is_string'  => array(
				'capability',
				'icon_url',
				'menu_title',
				'page_columns',
				'page_format',
				'parent_slug',
				'reset_button',
				'reset_action',
				'save_button',
				'title',
			),
			'is_numeric' => array( 'page_columns', 'position' ),
		);
		$not_empty = array(
			'reset_action',
			'page_format',
			'save_button',
			'title',
		);
		
		// checks the type of the passed in vars and if not OK, unsets them
		foreach ( $passed as $key => $pass ) {
			foreach ( $types as $check => $keys ) {
				if ( $check( $pass ) && ! in_array( $key, $keys ) ) {
					unset( $passed[ $key ] );
					break;
				}
				if ( empty( $pass ) && is_string( $pass ) && in_array( $key, $not_empty ) ) {
					unset( $passed[ $key ] );
					break;
				}
			}
		}
		
		// if passed properties, they overwrite array values
		$props = ! empty( $passed ) ? array_merge( $props, $passed ) : $props;
		
		return $props;
	}
	
	/**
	 * Options must be saved, and options must be registered.
	 *
	 * @since  2.XXX
	 * @param  string $key method calling this
	 * @return array
	 */
	protected function required_hooks( $key ) {
		
		$return = array();
		
		if ( $key == 'hooks' ) {
			$return = array(
				array(
					'id'   => 'register_setting',
					'hook' => $this->wp_menu_hook,
					'call' => array( $this, 'add_registered_setting' ),
				),
				array(
					'id'   => 'save_options',
					'call' => array( $this, 'save_options' ),
					'hook' => 'admin_post_' . $this->option_key,
				),
			);
		}
		
		return $return;
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
	 * Allows setting class properties. Used mainly for unit testing
	 *
	 * @since  2.XXX
	 * @param  string $property
	 * @param  mixed  $value
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
}