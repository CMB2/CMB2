<?php

/**
 * Creates and displays an options page.
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
	 * @param string $key
	 * @param string $page
	 * @param array  $boxes
	 * @param array  $props
	 *
	 * @since 2.XXX
	 */
	public function __construct( $key,  $page, $boxes, $props ) {
		$this->option_key = $key;
		$this->page = $page;
		$this->boxes = $boxes;
		$this->shared_properties = $props;
	}
	
	/**
	 * Display options-page output. Called from CMB2_Options_Hookup.
	 *
	 * @since  2.XXX
	 *
	 * @return string
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
	 * Opening HTML for options page
	 *
	 * @since 2.XXX
	 *
	 * @return string
	 */
	public function options_page_output_open() {
		
		// add formatting class to non-post-type options pages
		$wrapclass = $this->shared_properties['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = "\n" . '<div class="wrap' . $wrapclass . ' options-' . $this->option_key . '">';
		
		if ( $this->shared_properties['title'] ) {
			$html .= "\n" . '<h1 class="wp-heading-inline">' . wp_kses_post( $this->shared_properties['title'] ) . '</h1>';
		}
		
		// Allow html content to be inserted before the form
		$before = '';
		$html   .= "\n" . apply_filters( 'cmb2_options_page_before', $before, $this->page, $this );
		
		return $html;
	}
	
	/**
	 * Closing HTML for options page
	 *
	 * @since 2.XXX
	 *
	 * @return string
	 */
	public function options_page_output_close() {
		
		// allow content to be placed after the form
		$after = '';
		$html  = "\n" . apply_filters( 'cmb2_options_page_after', $after, $this->page, $this );
		
		// close wrapper
		$html .= "\n" . '</div>' . "\n";
		
		return $html;
	}
	
	/**
	 * Options page form. Adds post-style structure for pages where that is desired.
	 *
	 * @since 2.XXX
	 *
	 * @return string
	 */
	public function options_page_form() {

		// opening form tag
		$html = "\n" . '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="cmb2-option-' . $this->option_key . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		// action input
		$html .= "\n" . '<input type="hidden" name="action" value="' . esc_attr( $this->option_key ) . '">';
		
		// allows mimicing WP post editor layout
		$html .= $this->shared_properties['page_format'] !== 'post' ? $this->form_simple() : $this->form_post();
		
		// Allow save button to be hidden
		$html .= $this->save_button();
		
		// close form
		$html .= "\n" . '</form>' . "\n";
		
		return $html;
	}
	
	/**
	 * Simple options page layout. Note multiple metaboxes in this format visually appear as one large metabox
	 *
	 * @since 2.XXX
	 *
	 * @return string
	 */
	public function form_simple() {
		
		$html = '';
		
		foreach ( $this->boxes as $box ) {
			$html .= $this->options_page_metabox( $box );
		}
		
		return $html;
	}
	
	/**
	 * Post-editor style options page. Note that the contexts 'before_permalink', 'after_title', and 'after_editor'
	 * are not supported as they have no relevance on this page, those elements are missing.
	 *
	 * @since 2.XXX
	 *
	 * @return string
	 */
	public function form_post() {
		
		// determine number of columns for post-style layout
		$columns = $this->find_page_columns();
		
		// array to hold boxes, sorted by context
		$sorted_boxes = array( 'side' => array(), 'normal' => array(), 'advanced' => array(), 'form_top' => array() );
		
		// sort boxes
		foreach ( $this->boxes as $box ) {
			$sorted_boxes[ $box->prop( 'context' ) ][] = $box;
		}
		
		// nonce fields for postboxes
		$html = wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE );
		$html .= wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE );
		
		// form_top context boxes
		foreach ( $sorted_boxes['form_top'] as $box ) {
			$html .= $this->options_page_metabox( $box, true );
		}
		
		// open main post area
		$html .= "\n" . '<div id="poststuff">';
		$html .= "\n" . '<div id="post-body" class="metabox-holder columns-' . $columns . '">';
		
		// if there is a sidebar
		if ( $columns == 2 ) {
			
			$html .= "\n" . '<div id="postbox-container-1" class="postbox-container">';
			$html .= "\n" . '<div id="side-sortables" class="meta-box-sortables ui-sortable">';
			
			foreach ( $sorted_boxes['side'] as $box ) {
				$html .= $this->options_page_metabox( $box, false );
			}
			
			$html .= "\n" . '</div>';
			$html .= "\n" . '</div>';
		}
		
		// main column
		$html .= "\n" . '<div id="postbox-container-' . $columns . '" class="postbox-container">';
		$html .= "\n" . '<div id="normal-sortables" class="meta-box-sortables ui-sortable">';
		
		// normal boxes
		foreach ( $sorted_boxes['normal'] as $box ) {
			$html .= $this->options_page_metabox( $box, false );
		}
		
		$html .= "\n" . '</div>';
		$html .= "\n" . '<div id="advanced-sortables" class="meta-box-sortables ui-sortable">';
		
		// advanced boxes
		foreach ( $sorted_boxes['advanced'] as $box ) {
			$html .= $this->options_page_metabox( $box, false );
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
	 * @since  2.2.5
	 *
	 * @param \CMB2 $box
	 * @param bool  $con Whether to add context box classes
	 *
	 * @return string
	 */
	public function options_page_metabox( CMB2 $box, $con = false ) {
		
		if ( ! (bool) apply_filters( 'cmb2_show_on', $box->should_show(), $box->meta_box, $box ) ) {
			return '';
		}
		
		// tests if wrap should be removed
		$remove = $this->remove_wrap( $box );
		
		// opening box html
		$html   = $this->box_open( $box, $remove, $con );
		
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
	 * @param bool  $remove  Whether to remove the wrapper
	 * @param bool  $con     Whether to add context classes
	 *
	 * @return string
	 */
	public function box_open( CMB2 $box, $remove, $con = false ) {
		
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
	 * @param bool  $remove
	 *
	 * @return string
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
	 * Save button. Default is
	 *
	 * @return string
	 */
	public function save_button() {
		
		// get the save button if configured
		$sub = $this->shared_properties['save_button'];
		
		// If this is a string, allow it to be translated
		$sub = is_string( $sub ) ? __( $sub, 'cmb2' ) : $sub;
		
		return $sub ? "\n" . '<br class="clear">' . get_submit_button( esc_attr( $sub ), 'primary', 'submit-cmb' ) : '';
	}
	
	/**
	 * Determines how many columns for a post-style page
	 *
	 * @return int
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
	 * Adds filter to postbox_classes. This is a little kludgy but easiest way to pass a specific box, via closure
	 *
	 * @since 2.XXX
	 *
	 * @param \CMB2 $box
	 * @param bool $con
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
	 * @param \CMB2 $box
	 *
	 * @return bool
	 */
	public function remove_wrap( CMB2 $box ) {
		
		// if there is no title, remove
		$remove = ! $box->prop( 'title' );
		
		// if remove_box_wrap is set and the context is not normal post context, remove wrap
		$remove = ! $remove ? $box->prop( 'remove_box_wrap' ) === true  &&
				( ! in_array( $box->prop( 'context' ), array( 'normal', 'advanced', 'side' ) ) ) : true;
		
		// if this is a 'simple' form, remove the wrap
		$remove = ! $remove ? $this->shared_properties['page_format'] !== 'post' : true;
		
		return $remove;
	}
}