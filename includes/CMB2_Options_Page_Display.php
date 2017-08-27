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
	 * @param string $key   The options key
	 * @param string $page  Page slug (also used by menu)
	 * @param array  $props Shared cmb2->prop() values used by the page itself
	 *
	 * @since 2.XXX
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
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page() {
		
		// top part of page
		$html = $this->options_page_output_open();
		
		// the form itself, built potentially from multiple option boxes
		$html .= $this->options_page_form();
		
		// closing part of page
		$html .= $this->options_page_output_close();
		
		return $html;
	}
	
	/**
	 * Opening HTML for options page.
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page_output_open() {
		
		$before = '';
		
		// add formatting class to non-post-type options pages
		$wrapclass = $this->shared['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = "\n" . '<div class="wrap' . $wrapclass . ' options-' . $this->option_key . '">';
		
		if ( $this->shared['title'] ) {
			$html .= "\n" . '<h1 class="wp-heading-inline">'
			         . wp_kses_post( $this->shared['title'] ) . '</h1>';
		}
		
		/**
		 * 'cmb2_options_page_before' filter.
		 *
		 * Allows inserting content before the page form, below the title. Any content passed in should be formatted
		 * HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 *
		 * @var string                     $before Empty string default, can contain HTML from previous calls to filter
		 * @var string                     $this   ->page  Menu slug ($_GET['page']) value
		 * @var \CMB2_Options_Page_Display $this   Instance of this class
		 */
		$html .= "\n" . apply_filters( 'cmb2_options_page_before', $before, $this->page, $this );
		
		return $html;
	}
	
	/**
	 * Closing HTML for options page
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page_output_close() {
		
		$after = '';
		
		/**
		 * 'cmb2_options_page_after' filter.
		 *
		 * Allows inserting content after the page form. Content should be formatted HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 *
		 * @var string                     $before Empty string default, can contain HTML from previous calls to filter
		 * @var string                     $this   ->page  Menu slug ($_GET['page']) value
		 * @var \CMB2_Options_Page_Display $this   Instance of this class
		 */
		$html = "\n" . apply_filters( 'cmb2_options_page_after', $after, $this->page, $this );
		
		// close wrapper
		$html .= "\n" . '</div>' . "\n";
		
		return $html;
	}
	
	/**
	 * Options page form. Adds post-style structure for pages where that is desired.
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page_form() {
		
		$id = 'cmb2-option-' . $this->option_key;
		$top = $bottom = '';
		
		/**
		 * 'cmb2_options_form_id' filter: Change the ID of the form. If returned empty, will revert to default.
		 *
		 * @since 2.XXX
		 *
		 * @var      string                     $this ->page      Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Page_Display $this Instance of this class
		 */
		$form_id = apply_filters( 'cmb2_options_form_id', $id, $this->page, $this );
		
		// No empty IDs
		$form_id = empty( $form_id ) ? $id : $form_id;
		
		// opening form tag
		$html = "\n" . '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="' . esc_attr( $form_id ) . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		/**
		 * 'cmb2_options_form_top' filter: Insert HTML content just after form opening tag
		 *
		 * @since 2.XXX
		 *
		 * @var      string                     $this ->page      Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Page_Display $this Instance of this class
		 */
		$html .= apply_filters( 'cmb2_options_form_top', $top, $this->page, $this );
		
		// action input
		$html .= "\n" . '<input type="hidden" name="action" value="' . esc_attr( $this->option_key ) . '">';
		
		// allows'WP post editor' layout
		$html .= $this->shared['page_format'] !== 'post' ?
			$this->form_simple() : $this->form_post();
		
		// Allow save button to be hidden/assign value/assign default value
		$html .= $this->save_button();
		
		/**
		 * 'cmb2_options_form_bottom' filter: Insert HTML content just before form closing tag
		 *
		 * @since 2.XXX
		 *
		 * @var      string                     $this ->page      Menu slug ($_GET['page']) value
		 * @var      \CMB2_Options_Page_Display $this Instance of this class
		 */
		$html .= apply_filters( 'cmb2_options_form_bottom', $bottom, $this->page, $this );
		
		// close form
		$html .= "\n" . '</form>' . "\n";
		
		return $html;
	}
	
	/**
	 * Simple options page layout; the format originally output by CMB2_Options_Hookup.
	 * Note multiple metaboxes in this format visually appear as one large metabox
	 *
	 * @todo: can this call do_metaboxes or do simple format page boxes need to be given a special context?
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function form_simple() {
		
		$html = '';
		
		ob_start();
		do_action( 'cmb2_options_simple_page', 'options-page' );
		$html .= ob_get_clean();
		
		return $html;
	}
	
	/**
	 * Post-editor style options page.
	 *
	 * Contexts 'before_permalink', 'after_title', 'after_editor' not supported.
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function form_post() {
		
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
		$html .= "\n" . '<div id="poststuff">';
		$html .= "\n" . '<div id="post-body" class="metabox-holder columns-' . $columns . '">';
		
		// optional sidebar
		if ( $columns == 2 ) {
			
			$html .= "\n" . '<div id="postbox-container-1" class="postbox-container">';
			
			ob_start();
			do_meta_boxes( $this->page, 'side', NULL );
			$side = ob_get_clean();
			
			if ( $side ) {
				$html .= $side;
			}
			
			$html .= "\n" . '</div>';
		}
		
		// main column
		$html .= "\n" . '<div id="postbox-container-' . $columns . '" class="postbox-container">';
		
		ob_start();
		do_meta_boxes( $this->page, 'normal', NULL );
		$normal = ob_get_clean();
		
		if ( $normal ) {
			$html .= $normal;
		}
		
		ob_start();
		do_meta_boxes( $this->page, 'advanced', NULL );
		$advanced = ob_get_clean();
		
		if ( $advanced ) {
			$html .= $advanced;
		}
		
		$html .= "\n" . '</div>';
		$html .= "\n" . '</div>';
		
		return $html;
	}
	
	/**
	 * Save button. Optionally adds a reset button.
	 *
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
		
		$html .= "\n" . '<p class="cmb-submit-wrap clear">';
		
		$html .= $res ? get_submit_button( esc_attr( $res ), 'secondary', 'reset-cmb', false ) : '';
		$html .= get_submit_button( esc_attr( $sub ), 'primary', 'submit-cmb', false );
		
		$html .= '</p>';
		
		return $html;
	}
	
	/**
	 * Determines whether to remove the box wrap
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2 $box
	 *
	 * @return bool
	 */
	public function remove_wrap( CMB2 $box ) {
		
		$post_contexts = array( 'normal', 'advanced', 'side' );
		
		// if there is no title, remove
		$remove = ! $box->prop( 'title' );
		
		// if remove_box_wrap is set and the context is not normal post context, remove wrap
		$remove = ! $remove ?
			$box->prop( 'remove_box_wrap' ) === TRUE && ( ! in_array( $box->prop( 'context' ), $post_contexts ) ) :
			TRUE;
		
		// if this is a 'simple' form, remove the wrap
		$remove = ! $remove ? $this->shared['page_format'] !== 'post' : TRUE;
		
		return $remove;
	}
}