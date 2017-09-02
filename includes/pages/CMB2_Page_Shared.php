<?php

/**
 * Class CMB2_Page_Shared
 * Examines all boxes on page and returns array of common values
 *
 * Uses: None
 *
 * Applies CMB2 Filters:
 *     'cmb2_options_page_title'          Allows manipulation of the page title
 *     'cmb2_options_menu_title'          Allows manipulation of the menu label
 *     'cmb2_options_shared_properties'   Allows direct manipulation of the entire shared props array
 *
 * Public methods:
 *     return_shared_properties()         Calls get_shared_props()
 *
 * Public methods accessed via callback: None
 *
 * Protected methods:
 *     find_page_columns()                Allows use of 'auto' for 'post' style pages
 *     get_page_prop()                    Accesses CMB2->prop() with additional rules for fallbacks
 *     get_shared_props()                 Uses a default list to call get_page_prop() for each
 *     merge_shared_props()               If list was filtered, ensure it conforms. Includes type-checking
 *
 * Private methods: None
 * Magic methods: None
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Page_Shared {
	
	/**
	 * @since 2.XXX
	 * @var \CMB2_Page
	 */
	protected $page;
	
	/**
	 * CMB2_Page_Shared constructor.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Page $page
	 */
	public function __construct( CMB2_Page $page ) {
		
		$this->page = $page;
	}
	
	/**
	 * Public interface with class; returns array of shared properties
	 *
	 * @since  2.XXX
	 * @return array|bool
	 */
	public function return_shared_properties() {
		
		return $this->get_shared_props();
	}
	
	/**
	 * Determines how many columns for a post-style page. If 'auto', looks through boxes for any in 'side'
	 * context, and returns 2 rather than 1 if it finds any.
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
		foreach ( $this->page->hookups as $hook ) {
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
		
		foreach ( $this->page->hookups as $hookup ) {
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
		
		if ( ( ! empty( $this->page->shared ) && empty( $passed ) ) || ! is_array( $passed ) ) {
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
		
		return $props;
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
		$passed = ! empty( $passed ) ?
			array_intersect_key( $passed, array_flip( array_keys( $props ) ) ) : $passed;
		
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
}