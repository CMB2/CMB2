<?php

/**
 * Handles creating an options page, which may contain multiple metaboxes.
 *
 * The default page format is the same as the old-school CMB2 style. It can mimic the 'post' editor, however.
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property  string $page  Allow IDEs, etc. to reconize property accessed via magic method.
 */
class CMB2_Options_Page_Hookup {
	
	/**
	 * Have the page hooks have been hooked up?
	 *
	 * @since 2.XXX
	 * @var bool
	 */
	protected $hooked = FALSE;
	
	/**
	 * The page slug, equivalent to $_GET['page']
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $page = '';
	
	/**
	 * WP Options key
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $option_key = '';
	
	/**
	 * The first box to call this instance will set the menu hook!
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $wp_menu_hook = '';
	
	/**
	 * CMB2 boxes which appear on this page
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $hookups = array();
	
	/**
	 * CMB2 box properties used by this page, as pulled from collective boxes on page
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $shared = array();
	
	/**
	 * All WP hooks added to this page via 'add_action' or 'add_filter'. The array key is the method where they
	 * are added. Tokens will be replaced by values specified in the method (usually because they are not available
	 * until that method).
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $hooks = array(
		
		'hooks'       => array(
			array(
				'id'   => 'menu_hook',
				'hook' => '{WPMENUHOOK}',
				'call' => array( '{THIS}', 'add_to_menu' ),
			),
			array(
				'id'       => 'updated',
				'priority' => 11,
				'hook'     => '{WPMENUHOOK}',
				'call'     => array( '{THIS}', 'add_update_notice' ),
			),
			array(
				'id'      => 'postbox',
				'hook'    => 'admin_enqueue_scripts',
				'call'    => '{POSTBOX}',
				'only_if' => '{POSTBOX_CHECK}',
			),
			array(
				'id'      => 'toggle',
				'hook'    => 'admin_print_footer_scripts',
				'call'    => '{TOGGLE}',
				'only_if' => '{POSTBOX_CHECK}',
			),
		),
		'add_to_menu' => array(
			array(
				'id'   => 'do_metaboxes',
				'hook' => 'load-{PAGEHOOK}',
				'call' => array( '{THIS}', 'do_metaboxes' ),
			),
			array(
				'id'   => 'add_cmb_css_to_head',
				'hook' => 'admin_print_styles-{PAGEHOOK}',
				'call' => array( 'CMB2_hookup', 'enqueue_cmb_css' ),
				'only_if' => '{CMBSTYLES_CHECK}'
			),
		),
	);
	
	/**
	 * CMB2_Options_Page_Hookup constructor.
	 *
	 * @since 2.XXX
	 * @param string $page         The page slug, equivalent to $_GET['page']
	 * @param string $option_key   The WP Options key
	 * @param string $wp_menu_hook The menu hook which was set on the box (usually 'admin_menu')
	 * @param array  $hookups      Optional array of CMB2_Options_Hookup objects, mainly used by tests
	 * @param array  $shared       Optional array of shared values, mainly used by tests
	 */
	public function __construct( $page, $option_key, $wp_menu_hook, $hookups = array(), $shared = array() ) {
		
		$this->page         = $page;
		$this->option_key   = $option_key;
		$this->wp_menu_hook = $wp_menu_hook;
		
		// allow passing in array of boxes; default is to add them via ->add_box() during box hookup
		if ( ! empty( $hookups ) ) {
			$this->add_hookups( $hookups );
		}
		
		// allow passing in shared properties; default is to add them when page hooks are called
		if ( ! empty( $shared ) ) {
			$this->get_shared_props( $shared );
		}
	}
	
	/**
	 * Adds hooks tied to the creation of this page.
	 *
	 * @since 2.XXX
	 * @return array|bool
	 */
	public function hooks() {
		
		if ( $this->hooked ) {
			return FALSE;
		}
		
		// only add hooks once
		$this->hooked = TRUE;
		
		// trigger the generation of shared properties
		$this->get_shared_props();
		
		// tokens needed by this method's hooks
		$tokens = array(
			'{POSTBOX}'       => function () { wp_enqueue_script( 'postbox' ); },
			'{TOGGLE}'        => function () { echo '<script>jQuery(document).ready(function()'
				     . '{postboxes.add_postbox_toggles("postbox-container");});</script>'; },
			'{POSTBOX_CHECK}' => $this->shared['page_format'] !== 'post',
		);
		
		// get hooks from class property
		$hooks = $this->hooks_array( 'hooks', $tokens );
		
		$return = ! empty( $hooks ) ? CMB2_Utils::add_wp_hooks_from_config_array( $hooks, $this->wp_menu_hook ) : FALSE;
		
		// call utility function to add hooks from array
		return $return;
	}
	
	/**
	 * Prepares hooks array, applying filter
	 *
	 * @param string $method     The method being called, used to select hooks from class property
	 * @param array  $add_tokens Any additional tokens needed
	 * @return array|bool
	 */
	public function hooks_array( $method, $add_tokens = array() ) {
		
		$tokens = array(
			'{THIS}'       => $this,
			'{WPMENUHOOK}' => $this->wp_menu_hook,
		);
		
		$tokens = ! empty( $tokens ) && is_array( $tokens ) ? $tokens + $add_tokens : $tokens;
		
		/*
		 * By sending the hooks to the prepare_hooks_array method, they will be returned will all keys
		 * set, making them easier to understand for any dev asking for them by the filter below.
		 */
		$hooks = CMB2_Utils::prepare_hooks_array( $this->hooks[ $method ], $this->wp_menu_hook, $tokens );
		
		/**
		 * 'cmb2_options_page_hooks' filter.
		 *
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since    2.XXX
		 * @internal array               $hooks          Array of hook config arrays
		 * @internal \CMB2_Options_Hookup $this          Instance of this class
		 */
		$filtered = apply_filters( 'cmb2_options_page_hooks', $hooks, $this );
		
		return $hooks != $filtered ? $filtered : $hooks;
	}
	
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
	 * Adds multiple hookups. Returns hookups added.
	 *
	 * @since 2.XXX
	 * @param  array $hookups Array of CMB2 box objects or CMB2 box ids, or mix
	 * @return array
	 */
	public function add_hookups( $hookups = array() ) {
		
		$return = array();
		
		if ( empty( $hookups ) || ! is_array( $hookups ) ) {
			return $return;
		}
		
		foreach ( $hookups as $hookup ) {
			$return[ $hookup->cmb->cmb_id ] = $this->add_hookup( $hookup );
		}
		
		return $return;
	}
	
	/**
	 * Checks to see if the setting is registered, and if not, registers it
	 *
	 * @since 2.XXX
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
	 * Adds page to menu. Allows parameters to be filtered. Returns array containing results, for testing purposes.
	 *
	 * @since 2.XXX
	 * @return array
	 */
	public function add_to_menu() {
		
		$parent_slug = $this->shared['parent_slug'];
		$return      = array();
		
		// Menu_slug is blank, menu page is already registered, no boxes: exit
		if ( ! $this->page || empty( $this->hookups ) ) {
			return $return;
		}
		
		$params = array(
			'parent_slug' => $parent_slug,
			'title'       => $this->shared['title'],
			'menu_title'  => $this->shared['menu_title'],
			'capability'  => $this->shared['capability'],
			'menu_slug'   => $this->page,
			'action'      => array( $this, 'render' ),
			'icon_url'    => $this->shared['icon_url'],
			'position'    => $this->shared['position'],
			'cmb_styles'  => $this->shared['cmb_styles'],
		);
		
		$check = array_keys( $params );
		
		/**
		 * 'cmb2_options_page_menu_params' filter.
		 *
		 * Allows modifying the menu parameters before they're used to add a menu. All parameters
		 * must be returned.
		 *
		 * @todo     : must be sure null is allowed to be a parent_slug value in shared properties
		 *
		 * @since    2.XXX
		 *
		 * @internal array                     $hooks       Array of hook config arrays
		 * @internal string                    $this->page  Menu slug ($_GET['page']) value
		 * @internal \CMB2_Options_Page_Hookup $this        Instance of this class
		 */
		$filtered = apply_filters( 'cmb2_options_page_menu_params', $params, $this->page, $this );
		
		// ensure that no keys required below are missing after filter
		if ( ! is_array( $filtered ) || array_keys( $filtered ) != $check ) {
			return $return;
		}
		
		$params = $params != $filtered ? $filtered : $params;
		
		// if null is passed, the page is created but not added to the menu system, should be allowed
		if ( $parent_slug || $parent_slug === NULL ) {
			$page_hook = add_submenu_page(
				$params['parent_slug'],
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				$params['action']
			);
		} else {
			$page_hook = add_menu_page(
				$params['title'],
				$params['menu_title'],
				$params['capability'],
				$params['menu_slug'],
				$params['action'],
				$params['icon_url'],
				$params['position']
			);
		}
		
		// add page hooks which needed to have $page_hook available
		$tokens = array(
			'{PAGEHOOK}'        => $page_hook,
			'{CMBSTYLES_CHECK}' => $params['cmb_styles'] === true
		);
		$hooks = $this->hooks_array( 'add_to_menu', $tokens );
		
		$hooks = ! empty( $hooks ) ? CMB2_Utils::add_wp_hooks_from_config_array( $hooks ) : FALSE;
		
		return array(
			'type'      => ( empty( $parent_slug ) ? 'menu' : 'submenu' ),
			'params'    => $params,
			'page_hook' => $page_hook,
			'hooks'     => $hooks,
		);
	}
	
	/**
	 * Adds settings notice if updated.
	 *
	 * @since 2.XXX
	 * @return string
	 */
	public function add_update_notice() {
		
		$OPT = $this->option_key . '-notices';
		$UP  = $this->is_updated();
		
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
	 * Do the metabox actions for this page
	 *
	 * @since 2.XXX
	 */
	public function do_metaboxes() {
		do_action( 'add_meta_boxes_' . $this->page, $this->shared['page_format'] );
	}
	
	/**
	 * Checks if setting is already registered.
	 *
	 * @since 2.XXX
	 * @return bool
	 */
	public function is_setting_registered() {
		
		$option_key = empty( $option_key ) ? $this->option_key : $option_key;
		
		global $wp_registered_settings;
		
		return ! empty( $wp_registered_settings[ $option_key ] );
	}
	
	/**
	 * Checks to see if the page is updated.
	 *
	 * @since 2.XXX
	 * @return bool
	 */
	public function is_updated() {
		
		$UP = isset( $_GET['updated'] ) ? $_GET['updated'] : FALSE;
		$PG = isset( $_GET['page'] ) ? $_GET['page'] : FALSE;
		
		if ( empty( $UP ) || $PG !== $this->page ) {
			return FALSE;
		}
		
		return $UP;
	}
	
	/**
	 * Creates an array of properties used within the options page by checking each hookup for that property set
	 * on its CMB2 object. Uses the last non-null value returned.
	 *
	 * @since  2.XXX
	 * @param  array $passed
	 * @return array|bool
	 */
	public function get_shared_props( $passed = array() ) {
		
		if ( ( ! empty( $this->shared ) && empty( $passed ) ) || ! is_array( $passed ) ) {
			return FALSE;
		}
		
		$title = $this->get_page_prop( 'title' );
		
		$props = array(
			'capability'   => $this->get_page_prop( 'capability', 'manage_options' ),
			'cmb_styles'   => $this->get_page_prop( 'cmb_styles', TRUE ),
			'display_cb'   => $this->get_page_prop( 'display_cb', FALSE ),
			'enqueue_js'   => $this->get_page_prop( 'enqueue_js', TRUE ),
			'icon_url'     => $this->get_page_prop( 'icon_url', '' ),
			'menu_title'   => '', // set below so filtered page title can be passed as fallback
			'parent_slug'  => $this->get_page_prop( 'parent_slug' ),
			'page_columns' => $this->get_page_prop( 'page_columns', 'auto' ),
			'page_format'  => $this->get_page_prop( 'page_format', 'simple' ),
			'position'     => $this->get_page_prop( 'position' ),
			'reset_button' => $this->get_page_prop( 'reset_button', '' ),
			'reset_action' => $this->get_page_prop( 'reset_action', 'default' ),
			'save_button'  => $this->get_page_prop( 'save_button', 'Save', FALSE ),
			'title'        => $this->get_page_prop( 'page_title', $title ),
		);
		
		// changes 'auto' into an int, and if not auto, ensures value is in range
		$props['page_columns'] = $this->find_page_columns( $props['page_columns'] );
		
		/**
		 * 'cmb2_options_page_title' filter.
		 *
		 * Alters the title for use on the page. Note it's always a good idea to set 'menu_title' separately to
		 * avoid excessively long menu titles
		 *
		 * @since 2.XXX
		 *
		 * @property string               'title'        Title as set via 'page_title' or first box's 'title'
		 * @var      string               $this ->page   Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Hookup $this Instance of this class
		 */
		$props['title'] = (string) apply_filters( 'cmb2_options_page_title', $props['title'], $this->page, $this );
		
		// need to set after determining the filtered title to use as fallback
		$props['menu_title'] = $this->get_page_prop( 'menu_title', $props['title'] );
		
		/**
		 * 'cmb2_options_menu_title' filter.
		 *
		 * Alters the title for use on the menu.
		 *
		 * @since 2.XXX
		 *
		 * @property string               'menu_title'   Menu title as configured
		 * @var      string               $this ->page   Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Hookup $this Instance of this class
		 */
		$props['menu_title'] =
			(string) apply_filters( 'cmb2_options_menu_title', $props['menu_title'], $this->page, $this );
		
		// if passed properties, they overwrite array values
		$props = ! empty( $passed ) ? $this->merge_shared_props( $props, $passed ) : $props;
		
		/**
		 * 'cmb2_options_shared_properties' filter.
		 *
		 * Allows replacement/altering of shared_properties.
		 *
		 * @since 2.XXX
		 *
		 * @var array                $props Properties being returned by this method
		 * @var string               $this  ->page   Menu slug ($_GET['page']) value
		 * @var \CMB2_Options_Hookup $this  Instance of this class
		 */
		$filtered = apply_filters( 'cmb2_options_shared_properties', $props, $this->page, $this );
		
		// if passed properties, they overwrite array values
		$props = $props != $filtered ? $this->merge_shared_props( $props, $filtered ) : $props;
		
		// place into class property
		$this->shared = $props;
		
		return $props;
	}
	
	/**
	 * Checks hookups assigned to this page for CMB properties
	 *
	 * @since 2.XXX
	 * @param string $property        Property to check
	 * @param mixed  $fallback        Fallback if prop is null
	 * @param bool   $empty_string_ok Whether an empty string is allowed to be returned
	 * @return mixed
	 */
	public function get_page_prop( $property, $fallback = NULL, $empty_string_ok = TRUE ) {
		
		$prop = NULL;
		
		foreach ( $this->hookups as $hookup ) {
			$check = $hookup->cmb->prop( $property );
			if ( $check !== NULL && ( $empty_string_ok || $check !== '' ) ) {
				$prop = $check;
			}
		}
		
		if (
			( ! $empty_string_ok && $prop === '' && is_string( $fallback ) ) // value was empty string, flag set
			|| ( $prop === NULL && $fallback !== NULL )  // value was null, fallback OK
		) {
			$prop = $fallback;
		}
		
		return $prop;
	}
	
	/**
	 * Determines how many columns for a post-style page
	 *
	 * @since 2.XXX
	 * @param int|string $cols Value of the shared property
	 * @return int Value will be '1' or '2'
	 */
	public function find_page_columns( $cols = 'auto' ) {
		
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
		
		// if the 'side' context is in the array, page has two columns
		return $cols;
	}
	
	
	/**
	 * Switch for display of options page, using either the callback or internal render function
	 *
	 * @since  2.XXX
	 * @param  bool|string $echo Allows this method to return instead of echoing. WP will set this to ''.
	 * @return bool|string
	 */
	public function render( $echo ) {
		
		$echo     = $echo === '' || $echo === TRUE;
		$callback = $this->shared['display_cb'];
		
		if ( is_callable( $callback ) ) {
			ob_start();
			$returned = $callback( $this );
			$echoed   = ob_get_clean();
			$html     = $echoed ? $echoed : $returned;
		} else {
			$html = $this->render_html();
		}
		
		if ( $echo ) {
			echo $html;
			$html = TRUE;
		}
		
		return $html;
	}
	
	/**
	 * Returns the rendered HTML from options display class
	 *
	 * @since 2.XXX
	 * @return string
	 */
	public function render_html() {
		
		ob_start();
		settings_errors( "{$this->option_key}-notices" );
		$notices = ob_get_clean();
		
		// We will use first hookup in our array to trigger the style/js
		$hup = reset( $this->hookups );
		
		if ( $this->shared['cmb_styles'] ) {
			$hup::enqueue_cmb_css();
		}
		if ( $this->shared['enqueue_js'] ) {
			$hup::enqueue_cmb_js();
		}
		
		// get instance of display class
		$dis  = new CMB2_Options_Page_Display( $this->option_key, $this->page, $this->shared );
		$html = $notices . $dis->options_page();
		
		return $html;
	}
	
	/**
	 * Merges shared properties that are passed via filter or constructor. Checks keys and does some type checking.
	 *
	 * @since 2.XXX
	 * @param array $props  The non-altered version
	 * @param array $passed Altered version
	 * @return array
	 */
	public function merge_shared_props( $props, $passed ) {
		
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
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Checks multiple boxes
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
	 * Adaptation of parent class 'can_save' -- allows arbitrary box to be checked
	 *
	 * @param \CMB2_Options_Hookup $hookup
	 * @return mixed
	 */
	public function can_save( CMB2_Options_Hookup $hookup ) {
		
		$type = 'options-page';
		
		$can_save = (
			$hookup->cmb->prop( 'save_fields' )
			&& isset( $_POST[ $hookup->cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $hookup->cmb->nonce() ], $hookup->cmb->nonce() )
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			&& ( $type && in_array( $type, $hookup->cmb->box_types() ) )
			&& ! ( is_multisite() && ms_is_switched() )
		);
		
		// See CMB2_hookup->can_save()
		return apply_filters( 'cmb2_can_save', $can_save, $hookup->cmb );
	}
	
	/**
	 * Changes _POST values for box fields to default values.
	 *
	 * @since    2.XXX
	 * @param    \CMB2_Options_Hookup $hookup
	 */
	public function field_values_to_default( $hookup ) {
		
		$fields = $hookup->cmb->prop( 'fields' );
		
		foreach ( $fields as $fid => $field ) {
			$f             = $hookup->cmb->get_field( $field );
			$_POST[ $fid ] = $hookup->cmb->prop( 'reset_action' ) == 'remove' ? '' : $f->get_default();
		}
	}
	
	/**
	 * Returns property, allows checking state of class
	 *
	 * @since 2.XXX
	 * @param string $property Class property to fetch
	 * @return mixed|null
	 */
	public function __get( $property = '' ) {
		
		return isset( $this->{$property} ) ? $this->{$property} : NULL;
	}
}