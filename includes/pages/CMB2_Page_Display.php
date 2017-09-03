<?php

/**
 * Class CMB2_Page_Display
 * Renders HTML for options page. Most methods accept the same arguments array, which is constructed when
 * class in instantated.
 *
 * Uses:
 *     CMB2_Page_Utils                 Static class, utility functions
 *
 * Applies CMB2 Filters:
 *     'cmb2_options_page_before'      Insert HTML before <form> tag
 *     'cmb2_options_page_after'       Insert HTML after </form> tag
 *     'cmb2_options_form_id'          Adjust the options form id
 *     'cmb2_options_form_top'         Insert HTML just after <form> tag
 *     'cmb2_options_form_bottom'      Insert HTML just before </form> tag
 *     'cmb2_options_page_save_html'   Manipulate the HTML containing the Save and Reset buttons
 *
 * Public methods:
 *     render()                        Get notices, adds needed CSS/JS, calls page_html()
 *
 * Public methods accessed via callback: None
 *
 * Protected methods:
 *     merge_default_args()            Called by constructor to merge defaults with values in CMB2_Page instance
 *     merge_inserted_args()           Called by all methods which accept args array, merges passed into defaults
 *     page_html()                     Page wrapper. Calls page_form() and save_button()
 *     page_form()                     Adds form. If page type is 'post', calls page_form_post()
 *     page_form_post()                'post' style form, adds metaboxes, etc. Calls _sidebar(), _nonces()
 *     page_form_post_sidebar()        'post' style sidebar and 'side' metaboxes, if configured
 *     page_form_post_nonces()         'post' metabox nonces needed by WP
 *     save_button()                   Generates 'save' and 'reset' buttons with optional wrapper.
 *
 * Private methods: None
 *
 * Magic methods:
 *     __get()                         Magic getter which passes back a reference
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read array      $default_args
 * @property-read \CMB2_Page  $page
 */
class CMB2_Page_Display {
	
	/**
	 * Page Instance
	 *
	 * @since 2.XXX
	 * @var   CMB2_Page
	 */
	protected $page;
	
	/**
	 * Default arguments used by page() and page_form(). Reconciled with $shared on construct
	 *
	 * @since 2.XXX
	 * @var   array
	 */
	protected $default_args = array(
		'checks'         => array(
			'context'   => array( 'edit_form_after_title', ),
			'metaboxes' => array( NULL, array( 'side', 'normal', 'advanced' ), ),
		),
		'option_key'     => '',
		'page_format'    => 'simple',
		'simple_action'  => 'cmb2_options_simple_page',
		'page_nonces'    => TRUE,
		'page_columns'   => 1,
		'page_metaboxes' => array(
			'top'      => 'edit_form_after_title',
			'side'     => 'side',
			'normal'   => 'normal',
			'advanced' => 'advanced',
		),
		'save_button'    => '',
		'reset_button'   => '',
		'button_wrap'    => TRUE,
		'title'          => '',
		'page_id'        => '',
	);
	
	/**
	 * CMB2_Options_Display constructor.
	 *
	 * @since 2.XXX
	 * @param CMB2_Page $page  Page slug (also used by menu)
	 */
	public function __construct( CMB2_Page $page ) {
		
		$this->page         = $page;
		$this->default_args = $this->merge_default_args();
	}
	
	/**
	 * Public accessor to this class
	 *
	 * @since 2.XXX
	 * @return string
	 */
	public function render() {
		
		$notices = CMB2_Page_Utils::do_void_action( array( $this->page->option_key . '-notices' ), 'settings_errors' );
		
		// Use first hookup in our array to trigger the style/js
		$hookup = reset( $this->page->hookups );
		
		if ( $this->page->shared['cmb_styles'] ) {
			$hookup::enqueue_cmb_css();
		}
		if ( $this->page->shared['enqueue_js'] ) {
			$hookup::enqueue_cmb_js();
		}
		
		return $notices . $this->page_html();
	}
	
	
	/**
	 * Merges default args. Does not set $this->default_args!
	 *
	 * @since  2.XXX
	 * @param  array $args
	 *
	 * @return array
	 */
	protected function merge_default_args( $args = array() ) {
		
		$merged  = array(
			'option_key'   => $this->page->option_key,
			'page_format'  => $this->page->shared['page_format'],
			'page_columns' => $this->page->shared['page_columns'],
			'save_button'  => $this->page->shared['save_button'],
			'reset_button' => $this->page->shared['reset_button'],
			'title'        => $this->page->shared['title'],
			'page_id'      => $this->page->page_id,
		);
		
		// insert the values from the hookup object into the defaults
		$merged = CMB2_Page_Utils::array_replace_recursive_strict( $this->default_args, $merged );
		
		// merge any inserted arguments
		if ( ! empty( $args ) && is_array( $args ) ) {
			$merged = CMB2_Page_Utils::array_replace_recursive_strict( $merged, $args );
		}
		
		return $merged;
	}
	
	/**
	 * Merges inserted page arguments with defaults.
	 *
	 * @since 2.XXX
	 * @param array $inserted
	 * @param array $defaults
	 * @return array
	 */
	protected function merge_inserted_args( $inserted = array(), $defaults = array() ) {
		
		$inserted = ! is_array( $inserted ) ? (array) $inserted : $inserted;
		$defaults = ! is_array( $defaults ) || empty( $defaults ) ? $this->default_args : $defaults;
		
		return CMB2_Page_Utils::array_replace_recursive_strict( $defaults, $inserted );
	}
	
	/**
	 * Display options-page output.
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args  Inserted argument array; keys should be in
	 * @return string                Formatted HTML
	 */
	protected function page_html( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		
		$before_html = $after_html = '';
		
		// add formatting class to non-post-type options pages
		$wrapclass = $args['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = '<div class="wrap' . $wrapclass . ' options-' . $args['option_key'] . '">';
		
		if ( $args['title'] ) {
			$html .= '<h1 class="wp-heading-inline">' . wp_kses_post( $args['title'] ) . '</h1>';
		}
		
		/**
		 * 'cmb2_options_page_before' filter.
		 * Allows inserting content before the page form, below the title. Any content passed in
		 * should be formatted HTML to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_before', $before_html, $this );
		
		// the form itself, built potentially from multiple option boxes
		$html .= $this->page_form( $args );
		
		/**
		 * 'cmb2_options_page_after' filter.
		 * Allows inserting content after the page form. Content should be formatted HTML to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_after', $after_html, $this );
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Options page form. Adds post-style structure for pages where that is desired.
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows injecting the page_form arguments
	 * @return string               Formatted HTML
	 */
	protected function page_form( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		
		$id       = 'cmb2-option-' . $args['option_key'];
		$top_html = $bottom_html = '';
		
		/**
		 * 'cmb2_options_form_id' filter
		 * Change the ID of the form. If returned empty, will revert to default.
		 *
		 * @since 2.XXX
		 */
		$form_id = apply_filters( 'cmb2_options_form_id', $id, $this );
		$form_id = empty( $form_id ) ? $id : $form_id;
		
		$html = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="' . esc_attr( $form_id ) . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		/**
		 * 'cmb2_options_form_top' filter
		 * Insert HTML content just after form opening tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_top', $top_html, $this );
		
		$html .= '<input type="hidden" name="action" value="' . esc_attr( $args['option_key'] ) . '">';
		
		$html .= $args['page_format'] !== 'post' ?
			CMB2_Page_Utils::do_void_action( array( $args['simple_action'] ) ) : $this->page_form_post( $args );
		
		$html .= $this->save_button( $args );
		
		/**
		 * 'cmb2_options_form_bottom' filter
		 * Insert HTML content just before form closing tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_bottom', $bottom_html, $this );
		
		$html .= '</form>';
		
		return $html;
	}
	
	/**
	 * Post-editor style options page.
	 * Contexts 'before_permalink', 'after_title', 'after_editor' not supported.
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows injecting the page_form arguments
	 * @return string                Formatted HTML
	 */
	protected function page_form_post( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		$html = '';

		// nonce fields
		$html .= $this->page_form_post_nonces( $args );
		
		// form_top context boxes
		$html .= CMB2_Page_Utils::do_void_action( array( $args['page_metaboxes']['top'], ), $args['checks']['context'] );
		
		// main post area
		$html .= '<div id="poststuff">';
		$html .= '<div id="post-body" class="metabox-holder columns-' . $args['page_columns'] . '">';
		
		// optional sidebar
		$html .= $this->page_form_post_sidebar( $args );
		
		// main column
		$html .= '<div id="postbox-container-' . $args['page_columns'] . '" class="postbox-container">';
		
		$html .= CMB2_Page_Utils::do_void_action(
			array( $args['page_id'], $args['page_metaboxes']['normal'], NULL ),
			$args['checks']['metaboxes'],
			'do_meta_boxes'
		);
		$html .= CMB2_Page_Utils::do_void_action(
			array( $args['page_id'], $args['page_metaboxes']['advanced'], NULL ),
			$args['checks']['metaboxes'],
			'do_meta_boxes'
		);
		
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Adds optional sidebar to post-style form
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows inserting page arguments
	 * @return string                Formatted HTML
	 */
	protected function page_form_post_sidebar( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		
		$html = '';
		
		if ( $args['page_columns'] == 2 ) {
			
			$html .= '<div id="postbox-container-1" class="postbox-container">';
			
			$html .= CMB2_Page_Utils::do_void_action(
				array( $args['page_id'], $args['page_metaboxes']['side'], NULL ),
				$args['checks']['metaboxes'],
				'do_meta_boxes'
			);
			
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Returns WP nonce fields for post form
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows inserting page arguments
	 * @return string                Formatted HTML
	 */
	protected function page_form_post_nonces( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		
		$html = '';
		
		$html .= $args['page_nonces'] ? wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE ) : '';
		$html .= $args['page_nonces'] ? wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE ) : '';
		
		return $html;
	}
	
	/**
	 * Save button. Optionally adds a reset button. Can set either or both.
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows inserting page arguments
	 * @return string                Formatted HTML
	 */
	protected function save_button( $inserted_args = array() ) {
		
		$args = $this->merge_inserted_args( $inserted_args );
		
		$save_button  = $args['save_button'];
		$reset_button = $args['reset_button'];
		$button_wrap  = $args['button_wrap'];
		
		$save_button = empty( $save_button ) ? $this->shared['save_button'] : $save_button;
		$save_button = is_string( $save_button ) && ! empty( $save_button ) ? __( $save_button, 'cmb2' ) : '';
		
		$reset_button = empty( $reset_button ) ? $this->shared['reset_button'] : $reset_button;
		$reset_button = is_string( $reset_button ) && ! empty( $reset_button ) ? __( $reset_button, 'cmb2' ) : '';
		
		if ( ! $save_button && ! $reset_button ) {
			return '';
		}
		
		// Bundle pieces to be nice to users of filter below
		$pieces = array(
			'button_wrap'  => $button_wrap ?
				'<p class="cmb-submit-wrap clear">%s%s</p>' : '%s%s',
			'reset_button' => $reset_button ?
				get_submit_button( esc_attr( $reset_button ), 'secondary', 'reset-cmb', FALSE ) : '',
			'save_button'  => $save_button ?
				get_submit_button( esc_attr( $save_button ), 'primary', 'submit-cmb', FALSE ) : '',
		);
		
		// final HTML
		$html = sprintf( $pieces['button_wrap'], $pieces['reset_button'], $pieces['save_button'] );
		
		/**
		 * 'cmb2_options_page_save_html' filter
		 * Allows filtering of the save button HTML. Include pieces for easier parsing of the concatenated
		 * html string.
		 *
		 * @since 2.XXX
		 */
		$html = apply_filters( 'cmb2_options_page_save_html', $html, $pieces, $this->page_id );
		
		return is_string( $html ) && ! empty( $html ) ? $html : '';
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