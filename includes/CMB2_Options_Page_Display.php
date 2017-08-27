<?php

/**
 * Creates and displays an options page.
 *
 * @since 2.XXX
 */

class CMB2_Options_Page_Display {
	
	/**
	 * Options key
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $option_key;
	
	/**
	 * The page id, same as menu_slug
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $page;
	
	/**
	 * Shared cmb->prop values
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $shared;
	
	/**
	 * CMB2_Options_Display constructor.
	 *
	 * @since 2.XXX
	 * @param string $key   The options key
	 * @param string $page  Page slug (also used by menu)
	 * @param array  $props Shared cmb2->prop() values used by the page itself
	 */
	public function __construct( $key, $page, $props ) {
		
		$this->option_key = $key;
		$this->page       = $page;
		$this->shared     = $props;
	}
	
	/**
	 * Display options-page output. Called from CMB2_Options_Hookup.
	 *
	 * @since  2.XXX
	 * @return string  Formatted HTML
	 */
	public function page() {
		
		$before = $after = '';
		
		// add formatting class to non-post-type options pages
		$wrapclass = $this->shared['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = '<div class="wrap' . $wrapclass . ' options-' . $this->option_key . '">';
		
		if ( $this->shared['title'] ) {
			$html .= '<h1 class="wp-heading-inline">' . wp_kses_post( $this->shared['title'] ) . '</h1>';
		}
		
		/**
		 * 'cmb2_options_page_before' filter.
		 * Allows inserting content before the page form, below the title. Any content passed in should be formatted
		 * HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_before', $before, $this );
		
		// the form itself, built potentially from multiple option boxes
		$html .= $this->page_form();
		
		/**
		 * 'cmb2_options_page_after' filter.
		 * Allows inserting content after the page form. Content should be formatted HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_after', $after, $this );
		
		// close wrapper
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Options page form. Adds post-style structure for pages where that is desired.
	 *
	 * @since 2.XXX
	 * @return string  Formatted HTML
	 */
	public function page_form() {
		
		$id = 'cmb2-option-' . $this->option_key;
		$top = $bottom = '';
		
		/**
		 * 'cmb2_options_form_id' filter
		 * Change the ID of the form. If returned empty, will revert to default.
		 *
		 * @since 2.XXX
		 */
		$form_id = apply_filters( 'cmb2_options_form_id', $id, $this );
		
		// No empty IDs
		$form_id = empty( $form_id ) ? $id : $form_id;
		
		// opening form tag
		$html = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="' . esc_attr( $form_id ) . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		/**
		 * 'cmb2_options_form_top' filter
		 * Insert HTML content just after form opening tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_top', $top, $this );
		
		// action input
		$html .= '<input type="hidden" name="action" value="' . esc_attr( $this->option_key ) . '">';
		
		// allows'WP post editor' layout
		$html .= $this->shared['page_format'] !== 'post' ?
			$this->page_form_simple() : $this->page_form_post();
		
		// Allow save button to be hidden/assign value/assign default value
		$html .= $this->save_button();
		
		/**
		 * 'cmb2_options_form_bottom' filter
		 * Insert HTML content just before form closing tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_bottom', $bottom, $this );
		
		$html .= '</form>';
		
		return $html;
	}
	
	/**
	 * Simple options page layout; the format originally output by CMB2_Options_Hookup.
	 * Note multiple metaboxes in this format visually appear as one large metabox
	 *
	 * @since  2.XXX
	 * @return string  Formatted HTML
	 */
	public function page_form_simple() {
		
		ob_start();
		do_action( 'cmb2_options_simple_page', $this->page );
		
		return ob_get_clean();
	}
	
	/**
	 * Post-editor style options page.
	 *
	 * Contexts 'before_permalink', 'after_title', 'after_editor' not supported.
	 *
	 * @since  2.XXX
	 * @return string  Formatted HTML
	 */
	public function page_form_post() {
		
		// determine number of columns for post-style layout
		$columns = $this->shared['page_columns'];
		
		// nonce fields
		$html = wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE );
		$html .= wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE );
		
		// form_top context boxes
		ob_start();
		do_action( 'edit_form_after_title', $this->page );
		$html .= ob_get_clean();
		
		// main post area
		$html .= '<div id="poststuff">';
		$html .= '<div id="post-body" class="metabox-holder columns-' . $columns . '">';
		
		// optional sidebar
		if ( $columns == 2 ) {
			
			$html .= '<div id="postbox-container-1" class="postbox-container">';
			
			ob_start();
			do_meta_boxes( $this->page, 'side', NULL );
			$html .= ob_get_clean();
			
			$html .= '</div>';
		}
		
		// main column
		$html .= '<div id="postbox-container-' . $columns . '" class="postbox-container">';
		
		ob_start();
		do_meta_boxes( $this->page, 'normal', NULL );
		$html .= ob_get_clean();
		
		ob_start();
		do_meta_boxes( $this->page, 'advanced', NULL );
		$html .= ob_get_clean();
		
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Save button. Optionally adds a reset button.
	 *
	 * @since  2.XXX
	 * @return string  Formatted HTML
	 */
	public function save_button() {
		
		$html = '';
		
		// get the save button, if configured
		$sub = $this->shared['save_button'];
		$sub = is_string( $sub ) ? __( $sub, 'cmb2' ) : $sub;
		
		if ( ! $sub ) {
			return $html;
		}
		
		// get the reset button, if configured
		$res = $this->shared['reset_button'];
		$res = is_string( $res ) && ! empty( $res ) ? __( $res, 'cmb2' ) : '';
		
		$html .= '<p class="cmb-submit-wrap clear">';
		
		$html .= $res ? get_submit_button( esc_attr( $res ), 'secondary', 'reset-cmb', false ) : '';
		$html .= get_submit_button( esc_attr( $sub ), 'primary', 'submit-cmb', false );
		
		$html .= '</p>';
		
		return $html;
	}
}