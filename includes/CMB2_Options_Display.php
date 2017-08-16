<?php

/**
 * Creates and displays an options page.
 *
 * Properties set on any metabox within page which can affect display:
 *
 *   - 'page_format'  : 'simple', 'post'     : Default 'simple', old school CMB2; 'post' is WP 'post' editor format
 *   - 'page_columns' : 1, 2, 'auto'         : Default 'auto' (two columns if 'side' context box included)
 *   - 'save_button'  : string, false        : If not set, 'Save' is used; false removes the button
 *   - 'reset_button' : string               : If set, a reset button is added next to save with this text on it
 *   - 'reset_action' : 'default' : 'remove' : Default 'default', saves field default values; 'remove' blanks values
 *   - 'page_title'   : string               : If not set, first box's title is used for page title
 *
 * First value encountered in loop is used.
 *
 * @since 2.XXX
 */

class CMB2_Options_Display {
	
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
	 * Array of CMB2 objects used on this page
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $boxes;
	
	/**
	 * Shared cmb->prop values
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $shared_properties;
	
	/**
	 * CMB2_Options_Display constructor.
	 *
	 * @param string $key   The options key
	 * @param string $page  Page slug (also used by menu)
	 * @param array  $boxes Boxes which are displayed on this page
	 * @param array  $props Shared cmb2->prop() values used by the page itself
	 *
	 * @since 2.XXX
	 */
	public function __construct( $key, $page, $boxes, $props ) {
		
		$this->option_key        = $key;
		$this->page              = $page;
		$this->boxes             = $boxes;
		$this->shared_properties = $props;
	}
	
	/**
	 * Display options-page output. Called from CMB2_Options_Hookup.
	 *
	 * @since  2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page_output() {
		
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
		$wrapclass = $this->shared_properties['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = "\n" . '<div class="wrap' . $wrapclass . ' options-' . $this->option_key . '">';
		
		if ( $this->shared_properties['title'] ) {
			$html .= "\n" . '<h1 class="wp-heading-inline">' . wp_kses_post( $this->shared_properties['title'] ) . '</h1>';
		}
		
		/**
		 * 'cmb2_options_page_before' filter.
		 *
		 * Allows inserting content before the page form, below the title. Any content passed in should be formatted
		 * HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 *
		 * @var string                $before Empty string default, can contain HTML from previous calls to filter
		 * @var string                $this   ->page  Menu slug ($_GET['page']) value
		 * @var \CMB2_Options_Display $this   Instance of this class
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
		 * @var string                $before Empty string default, can contain HTML from previous calls to filter
		 * @var string                $this   ->page  Menu slug ($_GET['page']) value
		 * @var \CMB2_Options_Display $this   Instance of this class
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
		
		// opening form tag
		$html = "\n" . '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="cmb2-option-' . $this->option_key . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		// action input
		$html .= "\n" . '<input type="hidden" name="action" value="' . esc_attr( $this->option_key ) . '">';
		
		// allows'WP post editor' layout
		$html .= $this->shared_properties['page_format'] !== 'post' ? $this->form_simple() : $this->form_post();
		
		// Allow save button to be hidden/assign value/assign default value
		$html .= $this->save_button();
		
		// close form
		$html .= "\n" . '</form>' . "\n";
		
		return $html;
	}
	
	/**
	 * Simple options page layout; the format originally output by CMB2_Options_Hookup.
	 * Note multiple metaboxes in this format visually appear as one large metabox
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function form_simple() {
		
		$html = '';
		
		foreach ( $this->boxes as $box ) {
			$html .= $this->options_page_metabox( $box );
		}
		
		return $html;
	}
	
	/**
	 * Post-editor style options page.
	 *
	 * Contexts 'before_permalink', 'after_title', 'after_editor' not supported, they have no equivalent on this page.
	 *
	 * @since 2.XXX
	 *
	 * @return string  Formatted HTML
	 */
	public function form_post() {
		
		// determine number of columns for post-style layout
		$columns = $this->find_page_columns();
		
		// boxes, sorted by context
		$sorted_boxes = array( 'side' => array(), 'normal' => array(), 'advanced' => array(), 'form_top' => array() );
		
		// sort boxes
		foreach ( $this->boxes as $box ) {
			$sorted_boxes[ $box->prop( 'context' ) ][] = $box;
		}
		
		// nonce fields
		$html = wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE );
		$html .= wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE );
		
		// form_top context boxes
		foreach ( $sorted_boxes['form_top'] as $box ) {
			$html .= $this->options_page_metabox( $box, TRUE );
		}
		
		// main post area
		$html .= "\n" . '<div id="poststuff">';
		$html .= "\n" . '<div id="post-body" class="metabox-holder columns-' . $columns . '">';
		
		// optional sidebar
		if ( $columns == 2 ) {
			
			$html .= "\n" . '<div id="postbox-container-1" class="postbox-container">';
			$html .= "\n" . '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
			
			foreach ( $sorted_boxes['side'] as $box ) {
				$html .= $this->options_page_metabox( $box, FALSE );
			}
			
			$html .= "\n" . '</div>';
			$html .= "\n" . '</div>';
		}
		
		// main column
		$html .= "\n" . '<div id="postbox-container-' . $columns . '" class="postbox-container">';
		$html .= "\n" . '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
		
		// 'normal' boxes
		foreach ( $sorted_boxes['normal'] as $box ) {
			$html .= $this->options_page_metabox( $box, FALSE );
		}
		
		$html .= "\n" . '</div>';
		$html .= "\n" . '<div id="advanced-sortables" class="meta-box-sortables ui-sortable">';
		
		// 'advanced' boxes
		foreach ( $sorted_boxes['advanced'] as $box ) {
			$html .= $this->options_page_metabox( $box, FALSE );
		}
		
		$html .= "\n" . '</div>';
		$html .= "\n" . '</div>';
		$html .= "\n" . '</div>';
		
		return $html;
	}
	
	/**
	 * Display metaboxes for an options-page object.
	 *
	 * @since  2.XXX  Pull parent class functionality into here to allow passing multiple boxes; complete rewrite
	 * @since  2.2.5 (Originally within CMB2_Options_Hookup)
	 *
	 * @param \CMB2 $box
	 * @param bool  $con Whether to add context box classes
	 *
	 * @return string  Formatted HTML
	 */
	public function options_page_metabox( CMB2 $box, $con = FALSE ) {
		
		if ( ! (bool) apply_filters( 'cmb2_show_on', $box->should_show(), $box->meta_box, $box ) ) {
			return '';
		}
		
		// tests if wrap should be removed
		$remove = $this->remove_wrap( $box );
		
		// opening box html
		$html = $this->box_open( $box, $remove, $con );
		
		// capture results from methods which only echo results
		ob_start();
		$box->show_form( 0, 'options-page' );
		$html .= ob_get_clean();
		
		// closing box html
		$html .= $this->box_close( $remove );
		
		return $html;
	}
	
	/**
	 * Adds wrap to boxes.
	 * (Note, cannot call parent equivalents as they reference $this->cmb)
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2 $box
	 * @param bool  $remove Whether to remove the wrapper
	 * @param bool  $con    Whether to add context classes
	 *
	 * @return string  Formatted HTML
	 */
	public function box_open( CMB2 $box, $remove, $con = FALSE ) {
		
		// nothing from this method is needed for 'simple' format
		if ( $this->shared_properties['page_format'] !== 'post' ) {
			return '';
		}
		
		$html  = '';
		$title = $box->prop( 'title' );
		
		// add filter to postbox classes
		$this->filter_postbox_classes( $box, $con );
		
		// wrapper
		$html .= "\n" . '<div id="' . $box->cmb_id . '" class="'
		         . trim( postbox_classes( $box->cmb_id, $this->page ) ) . '">';
		
		// add handles, if desired
		if ( ! $remove ) {
			
			$html .= "\n" . '<button type="button" class="handlediv" aria-expanded="true">';
			$html .= '<span class="screen-reader-text">' . sprintf( __( 'Toggle panel: %s' ), $title ) . '</span>';
			$html .= '<span class="toggle-indicator" aria-hidden="true"></span>';
			$html .= '</button>';
			
			$html .= "\n" . '<h2 class="hndle ui-sortable-handle"><span>' . esc_attr( $title ) . '</span></h2>';
			
			$html .= "\n" . '<div class="inside">';
		}
		
		return $html;
	}
	
	/**
	 * Adds closing wrap to boxes
	 *
	 * @since 2.XXX
	 *
	 * @param bool $remove Should the wrap be removed
	 *
	 * @return string  Formatted HTML
	 */
	public function box_close( $remove ) {
		
		// return if not a post box
		if ( $this->shared_properties['page_format'] !== 'post' ) {
			return '';
		}
		
		$html = ! $remove ? "\n" . '</div>' : '';
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
		$sub = $this->shared_properties['save_button'];
		$sub = is_string( $sub ) ? __( $sub, 'cmb2' ) : $sub;
		
		if ( ! $sub ) {
			return $html;
		}
		
		// get the reset button, if configured
		$res = $this->shared_properties['reset_button'];
		$res = is_string( $res ) && ! empty( $res ) ? __( $res, 'cmb2' ) : '';
		
		$html .= "\n" . '<p class="cmb-submit-wrap clear">';
		
		$html .= $res ? get_submit_button( esc_attr( $res ), 'secondary', 'reset-cmb', false ) : '';
		$html .= get_submit_button( esc_attr( $sub ), 'primary', 'submit-cmb', false );
		
		$html .= '</p>';
		
		return $html;
	}
	
	/**
	 * Determines how many columns for a post-style page
	 *
	 * @return int  Value will be '1' or '2'
	 */
	public function find_page_columns() {
		
		// see if a value was passed, default to auto
		$cols = $this->shared_properties['page_columns'];
		
		// a value was passed, it can either be 2 or 1
		if ( $cols !== 'auto' ) {
			return intval( $cols ) !== 2 ? 1 : 2;
		}
		
		// run through the boxes and make an array of all contexts
		$contexts = array();
		foreach ( $this->boxes as $box ) {
			$contexts[] = $box->prop( 'context' );
		}
		$contexts = array_unique( $contexts );
		
		// if the 'side' context is in the array, page has two columns
		return in_array( 'side', $contexts ) ? 2 : 1;
	}
	
	/**
	 * Adds filter to postbox_classes.
	 * This is a little kludgy but easiest way to pass a specific box, via closure
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2 $box
	 * @param bool  $con Whether this is a 'context' box
	 */
	public function filter_postbox_classes( CMB2 $box, $con ) {
		
		add_filter( "postbox_classes_{$this->page}_{$box->cmb_id}", function ( $classes ) use ( $box, $con ) {
			
			if ( ! in_array( 'postbox', $classes ) ) {
				$classes[] = 'postbox';
			}
			
			$classes[] = 'cmb2-postbox';
			
			if ( $con ) {
				$classes[] = 'context-box context-' . $box->prop( 'context' ) . '-box';
			}
			if ( $box->prop( 'closed' ) && ! in_array( 'closed', $classes ) ) {
				$classes[] = 'closed';
			}
			if ( in_array( $box->cmb_id, get_hidden_meta_boxes( get_current_screen() ) ) ) {
				$classes[] = 'hide-if-js';
			}
			
			return $classes;
		} );
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
		$remove = ! $remove ? $this->shared_properties['page_format'] !== 'post' : TRUE;
		
		return $remove;
	}
}