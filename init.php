<?php
/*
Script Name:  Custom Metaboxes and Fields
Contributors: WebDevStudios (@webdevstudios / webdevstudios.com)
              Justin Sternberg (@jtsternberg / dsgnwrks.pro)
              Jared Atchison (@jaredatch / jaredatchison.com)
              Bill Erickson (@billerickson / billerickson.net)
              Andrew Norcross (@norcross / andrewnorcross.com)
Description:  This will create metaboxes with custom fields that will blow your mind.
Version:      1.2.0
*/

/**
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/************************************************************************
		You should not edit the code below or things might explode!
*************************************************************************/

// Autoload helper classes
spl_autoload_register('cmb_Meta_Box::autoload_helpers');

$meta_boxes = array();
$meta_boxes = apply_filters( 'cmb_meta_boxes', $meta_boxes );
foreach ( $meta_boxes as $meta_box ) {
	$my_box = new cmb_Meta_Box( $meta_box );
}

define( 'CMB_META_BOX_URL', cmb_Meta_Box::get_meta_box_url() );

/**
 * Create meta boxes
 */
class cmb_Meta_Box {

	/**
	 * Current version number
	 * @var   string
	 * @since 1.0.0
	 */
	const CMB_VERSION = '1.2.0';

	/**
	 * Metabox Config array
	 * @var   array
	 * @since 0.9.0
	 */
	protected $_meta_box;

	/**
	 * Metabox Defaults
	 * @var   array
	 * @since 1.0.1
	 */
	protected static $mb_defaults = array(
		'id'         => '',
		'title'      => '',
		'type'       => '',
		'pages'      => array(), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'show_on'    => array( 'key' => false, 'value' => false ), // Specific post IDs or page templates to display this metabox
		'cmb_styles' => true, // Include cmb bundled stylesheet
		'fields'     => array(),
	);

	/**
	 * Metabox Form ID
	 * @var   string
	 * @since 0.9.4
	 */
	protected $form_id = 'post';

	/**
	 * Current field config array
	 * @var   array
	 * @since 1.0.0
	 */
	public static $field = array();

	/**
	 * Object ID for metabox meta retrieving/saving
	 * @var   int
	 * @since 1.0.0
	 */
	protected static $object_id = 0;

	/**
	 * Type of object being saved. (e.g., post, user, or comment)
	 * @var   string
	 * @since 1.0.0
	 */
	protected static $object_type = '';

	/**
	 * Whether scripts/styles have been enqueued yet
	 * @var   bool
	 * @since 1.0.0
	 */
	protected static $is_enqueued = false;

	/**
	 * Whether CMB nonce has been added to the page. (oly add once)
	 * @var   bool
	 * @since 1.2.0
	 */
	protected static $nonce_added = false;

	/**
	 * Type of object specified by the metabox Config
	 * @var   string
	 * @since 1.0.0
	 */
	protected static $mb_object_type = 'post';

	/**
	 * Array of all options from manage-options metaboxes
	 * @var   array
	 * @since 1.0.0
	 */
	protected static $options = array();

	/**
	 * List of fields that are changed/updated on save
	 * @var   array
	 * @since 1.1.0
	 */
	protected static $updated = array();

	/**
	 * Get started
	 */
	function __construct( $meta_box ) {

		$meta_box = self::set_mb_defaults( $meta_box );

		$allow_frontend = apply_filters( 'cmb_allow_frontend', true, $meta_box );

		if ( ! is_admin() && ! $allow_frontend )
			return;

		$this->_meta_box = $meta_box;

		self::set_mb_type( $meta_box );

		$types = wp_list_pluck( $meta_box['fields'], 'type' );
		$upload = in_array( 'file', $types ) || in_array( 'file_list', $types );

		global $pagenow;

		$show_filters = 'cmb_Meta_Box_Show_Filters';
		foreach ( get_class_methods( $show_filters ) as $filter ) {
			add_filter( 'cmb_show_on', array( $show_filters, $filter ), 10, 2 );
		}

		// register our scripts and styles for cmb
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 8 );

		if ( self::get_object_type() == 'post' ) {
			add_action( 'admin_menu', array( $this, 'add_metaboxes' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

			if ( $upload && in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) ) {
				add_action( 'admin_head', array( $this, 'add_post_enctype' ) );
			}

		}
		if ( self::get_object_type() == 'user' ) {

			$priority = 10;
			if ( isset( $meta_box['priority'] ) ) {
				if ( is_numeric( $meta_box['priority'] ) )
					$priority = $meta_box['priority'];
				elseif ( $meta_box['priority'] == 'high' )
					$priority = 5;
				elseif ( $meta_box['priority'] == 'low' )
					$priority = 20;
			}
			add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );

			add_action( 'personal_options_update', array( $this, 'save_user' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
			if ( $upload && in_array( $pagenow, array( 'profile.php', 'user-edit.php' ) ) ) {
				$this->form_id = 'your-profile';
				add_action( 'admin_head', array( $this, 'add_post_enctype' ) );
			}
		}

	}

	/**
	 * Autoloads files with classes when needed
	 * @since  1.0.0
	 * @param  string $class_name Name of the class being requested
	 */
	public static function autoload_helpers( $class_name ) {
		if ( class_exists( $class_name, false ) )
			return;

		// for PHP versions < 5.3
		$dir = dirname( __FILE__ );

		$file = "$dir/helpers/$class_name.php";
		if ( file_exists( $file ) )
			@include( $file );
	}

	/**
	 * Registers scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function register_scripts() {

		// Should only be run once
		if ( self::$is_enqueued )
			return;

		global $wp_version;
		// Only use minified files if SCRIPT_DEBUG is off
		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		// scripts required for cmb
		$scripts = array( 'jquery', 'jquery-ui-core', 'cmb-datepicker', /*'media-upload', */'cmb-timepicker' );
		// styles required for cmb
		$styles = array();

		// if we're 3.5 or later, user wp-color-picker
		if ( 3.5 <= $wp_version ) {
			$scripts[] = 'wp-color-picker';
			$styles[] = 'wp-color-picker';
			if ( ! is_admin() ) {
				// we need to register colorpicker on the front-end
			   wp_register_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), self::CMB_VERSION );
		   	wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), self::CMB_VERSION );
				wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', array(
					'clear'         => __( 'Clear' ),
					'defaultString' => __( 'Default' ),
					'pick'          => __( 'Select Color' ),
					'current'       => __( 'Current Color' ),
				) );
			}
		} else {
			// otherwise use the older 'farbtastic'
			$scripts[] = 'farbtastic';
			$styles[] = 'farbtastic';
		}
		wp_register_script( 'cmb-datepicker', CMB_META_BOX_URL . 'js/jquery.datePicker.min.js' );
		wp_register_script( 'cmb-timepicker', CMB_META_BOX_URL . 'js/jquery.timePicker.min.js' );
		wp_register_script( 'cmb-scripts', CMB_META_BOX_URL .'js/cmb'. $min .'.js', $scripts, self::CMB_VERSION );

		wp_enqueue_media();

		wp_localize_script( 'cmb-scripts', 'cmb_l10', apply_filters( 'cmb_localized_data', array(
			'ajax_nonce'      => wp_create_nonce( 'ajax_nonce' ),
			'script_debug'    => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
			'new_admin_style' => version_compare( $wp_version, '3.7', '>' ),
			'object_type'     => self::get_object_type(),
			'upload_file'     => 'Use this file',
			'remove_image'    => 'Remove Image',
			'remove_file'     => 'Remove',
			'file'            => 'File:',
			'download'        => 'Download',
			'ajaxurl'         => admin_url( '/admin-ajax.php' ),
			'up_arrow'        => '[ ↑ ]&nbsp;',
			'down_arrow'      => '&nbsp;[ ↓ ]',
			'check_toggle'    => __( 'Select / Deselect All', 'cmb' ),
		) ) );

		wp_register_style( 'cmb-styles', CMB_META_BOX_URL . 'style'. $min .'.css', $styles );

		// Ok, we've enqueued our scripts/styles
		self::$is_enqueued = true;
	}

	/**
	 * Enqueues scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function do_scripts( $hook ) {
		// only enqueue our scripts/styles on the proper pages
		if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
			wp_enqueue_script( 'cmb-scripts' );

			// default is to show cmb styles on post pages
			if ( $this->_meta_box['cmb_styles'] )
				wp_enqueue_style( 'cmb-styles' );
		}
	}

	/**
	 * Add encoding attribute
	 */
	public function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#'. $this->form_id .'").attr("enctype", "multipart/form-data");
			jQuery("#'. $this->form_id .'").attr("encoding", "multipart/form-data");
		});
		</script>';
	}

	/**
	 * Add metaboxes (to 'post' object type)
	 */
	public function add_metaboxes() {

		foreach ( $this->_meta_box['pages'] as $page ) {
			if ( apply_filters( 'cmb_show_on', true, $this->_meta_box ) )
				add_meta_box( $this->_meta_box['id'], $this->_meta_box['title'], array( $this, 'post_metabox' ), $page, $this->_meta_box['context'], $this->_meta_box['priority']) ;
		}
	}

	/**
	 * Display metaboxes for a post object
	 * @since  1.0.0
	 */
	public function post_metabox() {
		if ( ! $this->_meta_box )
			return;

		self::show_form( $this->_meta_box, get_the_ID(), 'post' );

	}

	/**
	 * Display metaboxes for a user object
	 * @since  1.0.0
	 */
	public function user_metabox() {
		if ( ! $this->_meta_box )
			return;

		if ( 'user' != self::set_mb_type( $this->_meta_box ) )
			return;

		if ( ! apply_filters( 'cmb_show_on', true, $this->_meta_box ) )
			return;

		wp_enqueue_script( 'cmb-scripts' );

		// default is to NOT show cmb styles on user profile page
		if ( $this->_meta_box['cmb_styles'] != false )
			wp_enqueue_style( 'cmb-styles' );

		self::show_form( $this->_meta_box );

	}

	/**
	 * Loops through and displays fields
	 * @since  1.0.0
	 * @param  array  $meta_box    Metabox config array
	 * @param  int    $object_id   Object ID
	 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
	 */
	public static function show_form( $meta_box, $object_id = 0, $object_type = '' ) {
		$meta_box = self::set_mb_defaults( $meta_box );
		// Set/get type
		$object_type = self::set_object_type( $object_type ? $object_type : self::set_mb_type( $meta_box ) );
		// Set/get ID
		$object_id = self::set_object_id( $object_id ? $object_id : self::get_object_id() );

		// Add nonce only once per page.
		if ( ! self::$nonce_added ) {
			wp_nonce_field( self::nonce(), 'wp_meta_box_nonce', false, true );
			self::$nonce_added = true;
		}

		// Use nonce for verification
		echo "\n<!-- Begin CMB Fields -->\n";
		do_action( 'cmb_before_table', $meta_box, $object_id, $object_type );
		echo '<table class="form-table cmb_metabox">';

		foreach ( $meta_box['fields'] as $field_args ) {

			$field_args['context'] = $meta_box['context'];

			if ( 'group' == $field_args['type'] ) {

				if ( ! isset( $field_args['show_names'] ) ) {
					$field_args['show_names'] = $meta_box['show_names'];
				}
				self::render_group( $field_args );
			} else {

				$field_args['show_names'] = $meta_box['show_names'];
				// Render default fields
				$field = new cmb_Meta_Box_field( $field_args );
				$field->render_field();
			}
		}
		echo '</table>';
		do_action( 'cmb_after_table', $meta_box, $object_id, $object_type );
		echo "\n<!-- End CMB Fields -->\n";

	}

	/**
	 * Render a repeatable group
	 */
	public static function render_group( $args ) {
		if ( ! isset( $args['id'], $args['fields'] ) || ! is_array( $args['fields'] ) )
			return;

		$args['count']   = 0;
		$field_group     = new cmb_Meta_Box_field( $args );
		$desc            = $field_group->args( 'description' );
		$label           = $field_group->args( 'name' );
		$sortable        = $field_group->options( 'sortable' ) ? ' sortable' : '';
		$group_val       = (array) $field_group->value();
		$nrows           = count( $group_val );
		$remove_disabled = $nrows <= 1 ? 'disabled="disabled" ' : '';

		echo '<tr><td colspan="2"><table id="', $field_group->id(), '_repeat" class="repeatable-group'. $sortable .'" style="width:100%;">';
		if ( $desc || $label ) {
			echo '<tr><th>';
				if ( $label )
					echo '<h2 class="cmb-group-name">'. $label .'</h2>';
				if ( $desc )
					echo '<p class="cmb_metabox_description">'. $desc .'</p>';
			echo '</th></tr>';
		}

		if ( ! empty( $group_val ) ) {

			foreach ( $group_val as $iterator => $field_id ) {
				self::render_group_row( $field_group, $remove_disabled );
			}
		} else {
			self::render_group_row( $field_group, $remove_disabled );
		}

		echo '<tr><td><p class="add-row"><button data-selector="', $field_group->id() ,'_repeat" data-grouptitle="', $field_group->options( 'group_title' ) ,'" class="add-group-row button">'. $field_group->options( 'add_button' ) .'</button></p></td></tr>';

		echo '</table></td></tr>';

	}

	public static function render_group_row( $field_group, $remove_disabled ) {

		echo '
		<tr class="repeatable-grouping" data-iterator="'. $field_group->count() .'">
			<td>
				<table class="cmb-nested-table" style="width: 100%;">';
				if ( $field_group->options( 'group_title' ) ) {
					echo '
					<tr class="cmb-group-title">
						<th colspan="2">
							', sprintf( '<h4>%1$s</h4>', $field_group->replace_hash( $field_group->options( 'group_title' ) ) ), '
						<th>
					</tr>
					';
				}
				// Render repeatable group fields
				foreach ( array_values( $field_group->args( 'fields' ) ) as $field_args ) {
					$field_args['show_names'] = $field_group->args( 'show_names' );
					$field_args['context'] = $field_group->args( 'context' );
					$field = new cmb_Meta_Box_field( $field_args, $field_group );
					$field->render_field();
				}
				echo '
					<tr>
						<td class="remove-row" colspan="2">
							<button '. $remove_disabled .'data-selector="'. $field_group->id() .'_repeat" class="button remove-group-row alignright">'. $field_group->options( 'remove_button' ) .'</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		';

		$field_group->args['count']++;
	}

	/**
	 * Save data from metabox
	 */
	public function save_post( $post_id, $post = false ) {

		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		// check permissions
		if (
			// check nonce
			! isset( $_POST['wp_meta_box_nonce'] )
			|| ! wp_verify_nonce( $_POST['wp_meta_box_nonce'], self::nonce() )
			// check if autosave
			|| defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE
			// check user editing permissions
			|| ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) )
			|| ! current_user_can( 'edit_post', $post_id )
			// get the metabox post_types & compare it to this post_type
			|| ! in_array( $post_type, $this->_meta_box['pages'] )
		)
			return $post_id;

		self::save_fields( $this->_meta_box, $post_id, 'post' );
	}

	/**
	 * Save data from metabox
	 */
	public function save_user( $user_id )  {

		// check permissions
		// @todo more hardening?
		if (
			// check nonce
			! isset( $_POST['wp_meta_box_nonce'] )
			|| ! wp_verify_nonce( $_POST['wp_meta_box_nonce'], self::nonce() )
		)
			return $user_id;

		self::save_fields( $this->_meta_box, $user_id, 'user' );
	}

	/**
	 * Loops through and saves field data
	 * @since  1.0.0
	 * @param array   $meta_box    Metabox config array
	 * @param  int    $object_id   Object ID
	 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
	 */
	public static function save_fields( $meta_box, $object_id, $object_type = '' ) {
		$meta_box = self::set_mb_defaults( $meta_box );

		$meta_box['show_on'] = empty( $meta_box['show_on'] ) ? array( 'key' => false, 'value' => false ) : $meta_box['show_on'];

		self::set_object_id( $object_id );
		// Set/get type
		$object_type = self::set_object_type( $object_type ? $object_type	: self::set_mb_type( $meta_box ) );

		if ( ! apply_filters( 'cmb_show_on', true, $meta_box ) )
			return;

		// save field ids of those that are updated
		self::$updated = array();

		foreach ( $meta_box['fields'] as $field_args ) {

			if ( 'group' == $field_args['type'] ) {
				self::save_group( $field_args );
			} else {
				// Save default fields
				$field = new cmb_Meta_Box_field( $field_args );
				self::save_field( self::sanitize_field( $field ), $field );
			}

		}

		// If options page, save the updated options
		if ( $object_type == 'options-page' )
			self::save_option( $object_id );

		do_action( "cmb_save_{$object_type}_fields", $object_id, $meta_box['id'], self::$updated, $meta_box );

	}

	/**
	 * Save a repeatable group
	 */
	public static function save_group( $args ) {
		if ( ! isset( $args['id'], $args['fields'], $_POST[ $args['id'] ] ) || ! is_array( $args['fields'] ) )
			return;

		$field_group        = new cmb_Meta_Box_field( $args );
		$base_id            = $field_group->id();
		$old                = $field_group->get_data();
		$group_vals         = $_POST[ $base_id ];
		$saved              = array();
		$is_updated         = false;
		$field_group->index = 0;

		// $group_vals[0]['color'] = '333';
		foreach ( array_values( $field_group->fields() ) as $field_args ) {
			$field = new cmb_Meta_Box_field( $field_args, $field_group );
			$sub_id = $field->id( true );

			foreach ( (array) $group_vals as $field_group->index => $post_vals ) {

				// Get value
				$new_val = isset( $group_vals[ $field_group->index ][ $sub_id ] )
					? $group_vals[ $field_group->index ][ $sub_id ]
					: false;

				// Sanitize
				$new_val = self::sanitize_field( $field, $new_val, $field_group->index );

				if ( 'file' == $field->type() && is_array( $new_val ) ) {
					// Add image ID to the array stack
					$saved[ $field_group->index ][ $new_val['field_id'] ] = $new_val['attach_id'];
					// Reset var to url string
					$new_val = $new_val['url'];
				}

				// Get old value
				$old_val = is_array( $old ) && isset( $old[ $field_group->index ][ $sub_id ] )
					? $old[ $field_group->index ][ $sub_id ]
					: false;

				$is_updated = ( ! empty( $new_val ) && $new_val != $old_val );
				$is_removed = ( empty( $new_val ) && ! empty( $old_val ) );
				// Compare values and add to `$updated` array
				if ( $is_updated || $is_removed )
					self::$updated[] = $base_id .'::'. $field_group->index .'::'. $sub_id;

				// Add to `$saved` array
				$saved[ $field_group->index ][ $sub_id ] = $new_val;

			}
			$saved[ $field_group->index ] = array_filter( $saved[ $field_group->index ] );
		}
		$saved = array_filter( $saved );

		$field_group->update_data( $saved, true );
	}

	public static function sanitize_field( $field, $new_value = null ) {

		$new_value = null !== $new_value
			? $new_value
			: ( isset( $_POST[ $field->id( true ) ] ) ? $_POST[ $field->id( true ) ] : null );

		if ( $field->args( 'repeatable' ) && is_array( $new_value ) ) {
			// Remove empties
			$new_value = array_filter( $new_value );
		}

		// Check if this metabox field has a registered validation callback, or perform default sanitization
		return $field->sanitization_cb( $new_value );
	}

	public static function save_field( $new_value, $field ) {
		$name = $field->id();
		$old  = $field->get_data();

		// if ( $field->args( 'multiple' ) && ! $field->args( 'repeatable' ) && ! $field->group ) {
		// 	$field->remove_data();
		// 	if ( ! empty( $new_value ) ) {
		// 		foreach ( $new_value as $add_new ) {
		// 			self::$updated[] = $name;
		// 			$field->update_data( $add_new, $name, false );
		// 		}
		// 	}
		// } else
		if ( ! empty( $new_value ) && $new_value != $old  ) {
			self::$updated[] = $name;
			return $field->update_data( $new_value );
		} elseif ( empty( $new_value ) ) {
			if ( ! empty( $old ) )
				self::$updated[] = $name;
			return $field->remove_data();
		}
	}

	/**
	 * Get object id from global space if no id is provided
	 * @since  1.0.0
	 * @param  integer $object_id Object ID
	 * @return integer $object_id Object ID
	 */
	public static function get_object_id( $object_id = 0 ) {

		if ( $object_id )
			return $object_id;

		if ( self::$object_id )
			return self::$object_id;

		// Try to get our object ID from the global space
		switch ( self::get_object_type() ) {
			case 'user':
				$object_id = isset( $GLOBALS['user_ID'] ) ? $GLOBALS['user_ID'] : $object_id;
				$object_id = isset( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : $object_id;
				break;

			default:
				$object_id = isset( $GLOBALS['post']->ID ) ? $GLOBALS['post']->ID : $object_id;
				$object_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : $object_id;
				break;
		}

		// reset to id or 0
		self::set_object_id( $object_id ? $object_id : 0 );

		return self::$object_id;
	}

	/**
	 * Explicitly Set object id
	 * @since  1.0.0
	 * @param  integer $object_id Object ID
	 * @return integer $object_id Object ID
	 */
	public static function set_object_id( $object_id ) {
		return self::$object_id = $object_id;
	}

	/**
	 * Sets the $object_type based on metabox settings
	 * @since  1.0.0
	 * @param  array|string $meta_box Metabox config array or explicit setting
	 * @return string       Object type
	 */
	public static function set_mb_type( $meta_box ) {

		if ( is_string( $meta_box ) ) {
			self::$mb_object_type = $meta_box;
			return self::get_mb_type();
		}

		if ( ! isset( $meta_box['pages'] ) )
			return self::get_mb_type();

		$type = false;
		// check if 'pages' is a string
		if ( self::is_options_page_mb( $meta_box ) )
			$type = 'options-page';
		// check if 'pages' is a string
		elseif ( is_string( $meta_box['pages'] ) )
			$type = $meta_box['pages'];
		// if it's an array of one, extract it
		elseif ( is_array( $meta_box['pages'] ) && count( $meta_box['pages'] === 1 ) )
			$type = is_string( end( $meta_box['pages'] ) ) ? end( $meta_box['pages'] ) : false;

		if ( !$type )
			return self::get_mb_type();

		// Get our object type
		if ( 'user' == $type )
			self::$mb_object_type = 'user';
		elseif ( 'comment' == $type )
			self::$mb_object_type = 'comment';
		elseif ( 'options-page' == $type )
			self::$mb_object_type = 'options-page';
		else
			self::$mb_object_type = 'post';

		return self::get_mb_type();
	}

	/**
	 * Determines if metabox is for an options page
	 * @since  1.0.1
	 * @param  array   $meta_box Metabox config array
	 * @return boolean           True/False
	 */
	public static function is_options_page_mb( $meta_box ) {
		return ( isset( $meta_box['show_on']['key'] ) && 'options-page' === $meta_box['show_on']['key'] );
	}

	/**
	 * Returns the object type
	 * @since  1.0.0
	 * @return string Object type
	 */
	public static function get_object_type() {
		if ( self::$object_type )
			return self::$object_type;

		global $pagenow;

		if (
			$pagenow == 'user-edit.php'
			|| $pagenow == 'profile.php'
		)
			self::set_object_type( 'user' );

		elseif (
			$pagenow == 'edit-comments.php'
			|| $pagenow == 'comment.php'
		)
			self::set_object_type( 'comment' );
		else
			self::set_object_type( 'post' );

		return self::$object_type;
	}

	/**
	 * Sets the object type
	 * @since  1.0.0
	 * @return string Object type
	 */
	public static function set_object_type( $object_type ) {
		return self::$object_type = $object_type;
	}

	/**
	 * Returns the object type
	 * @since  1.0.0
	 * @return string Object type
	 */
	public static function get_mb_type() {
		return self::$mb_object_type;
	}

	/**
	 * Returns the nonce value for wp_meta_box_nonce
	 * @since  1.0.0
	 * @return string Nonce value
	 */
	public static function nonce() {
		return basename( __FILE__ );
	}

	/**
	 * Defines the url which is used to load local resources.
	 * This may need to be filtered for local Window installations.
	 * If resources do not load, please check the wiki for details.
	 * @since  1.0.1
	 * @return string URL to CMB resources
	 */
	public static function get_meta_box_url() {

		if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
			// Windows
			$content_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
			$content_url = str_replace( $content_dir, WP_CONTENT_URL, dirname(__FILE__) );
			$cmb_url = str_replace( DIRECTORY_SEPARATOR, '/', $content_url );

		} else {
		  $cmb_url = str_replace(
				array(WP_CONTENT_DIR, WP_PLUGIN_DIR),
				array(WP_CONTENT_URL, WP_PLUGIN_URL),
				dirname( __FILE__ )
			);
		}

		return trailingslashit( apply_filters('cmb_meta_box_url', $cmb_url ) );
	}

	/**
	 * Fills in empty metabox parameters with defaults
	 * @since  1.0.1
	 * @param  array $meta_box Metabox config array
	 * @return array           Modified Metabox config array
	 */
	public static function set_mb_defaults( $meta_box ) {
		return wp_parse_args( $meta_box, self::$mb_defaults );
	}

	/**
	 * Removes an option from an option array
	 * @since  1.0.1
	 * @param  string  $option_key Option key
	 * @param  string  $field_id   Option array field key
	 * @return array               Modified options
	 */
	public static function remove_option( $option_key, $field_id ) {

		self::$options[ $option_key ] = ! isset( self::$options[ $option_key ] ) || empty( self::$options[ $option_key ] ) ? self::_get_option( $option_key ) : self::$options[ $option_key ];

		if ( isset( self::$options[ $option_key ][ $field_id ] ) )
			unset( self::$options[ $option_key ][ $field_id ] );

		return self::$options[ $option_key ];
	}

	/**
	 * Retrieves an option from an option array
	 * @since  1.0.1
	 * @param  string  $option_key Option key
	 * @param  string  $field_id   Option array field key
	 * @return array               Options array or specific field
	 */
	public static function get_option( $option_key, $field_id = '' ) {

		self::$options[ $option_key ] = ! isset( self::$options[ $option_key ] ) || empty( self::$options[ $option_key ] ) ? self::_get_option( $option_key ) : self::$options[ $option_key ];

		if ( $field_id ) {
			return isset( self::$options[ $option_key ][ $field_id ] ) ? self::$options[ $option_key ][ $field_id ] : false;
		}

		return self::$options[ $option_key ];
	}

	/**
	 * Updates Option data
	 * @since  1.0.1
	 * @param  string  $option_key Option key
	 * @param  string  $field_id   Option array field key
	 * @param  mixed   $value      Value to update data with
	 * @param  bool    $single     Whether data should be an array
	 * @return array               Modified options
	 */
	public static function update_option( $option_key, $field_id, $value, $single = true ) {

		if ( ! $single ) {
			// If multiple, add to array
			self::$options[ $option_key ][ $field_id ][] = $value;
		} else {
			self::$options[ $option_key ][ $field_id ] = $value;
		}

		return self::$options[ $option_key ];
	}

	/**
	 * Retrieve option value based on name of option.
	 * @uses apply_filters() Calls 'cmb_override_option_get_$option_key' hook to allow
	 * 	overwriting the option value to be retrieved.
	 *
	 * @since  1.0.1
	 * @param  string $option  Name of option to retrieve. Expected to not be SQL-escaped.
	 * @param  mixed  $default Optional. Default value to return if the option does not exist.
	 * @return mixed           Value set for the option.
	 */
	public static function _get_option( $option_key, $default = false ) {

		$test_get = apply_filters( "cmb_override_option_get_$option_key", 'cmb_no_override_option_get', $default );

		if ( $test_get !== 'cmb_no_override_option_get' )
			return $test_get;

		// If no override, get the option
		return get_option( $option_key, $default );
	}

	/**
	 * Saves the option array
	 * Needs to be run after finished using remove/update_option
	 * @uses apply_filters() Calls 'cmb_override_option_save_$option_key' hook to allow
	 * 	overwriting the option value to be stored.
	 *
	 * @since  1.0.1
	 * @param  string  $option_key Option key
	 * @return boolean             Success/Failure
	 */
	public static function save_option( $option_key ) {

		$to_save = self::get_option( $option_key );

		$test_save = apply_filters( "cmb_override_option_save_$option_key", 'cmb_no_override_option_save', $to_save );

		if ( $test_save !== 'cmb_no_override_option_save' )
			return $test_save;

		// If no override, update the option
		return update_option( $option_key, $to_save );
	}

	/**
	 * Utility method that returns a timezone string representing the default timezone for the site.
	 *
	 * Roughly copied from WordPress, as get_option('timezone_string') will return
	 * and empty string if no value has beens set on the options page.
	 * A timezone string is required by the wp_timezone_choice() used by the
	 * select_timezone field.
	 *
	 * @since  1.0.0
	 * @return string Timezone string
	 */
	public static function timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
			if ( 0 == $current_offset )
				$tzstring = 'UTC+0';
			elseif ( $current_offset < 0 )
				$tzstring = 'UTC' . $current_offset;
			else
				$tzstring = 'UTC+' . $current_offset;
		}

		return $tzstring;
	}

	/**
	 * Utility method that returns time string offset by timezone
	 * @since  1.0.0
	 * @param  string $tzstring Time string
	 * @return string           Offset time string
	 */
	public static function timezone_offset( $tzstring ) {
		if ( ! empty( $tzstring ) && is_string( $tzstring ) ) {
			if ( substr( $tzstring, 0, 3 ) === 'UTC' ) {
				$tzstring = str_replace( array( ':15',':30',':45' ), array( '.25','.5','.75' ), $tzstring );
				return intval( floatval( substr( $tzstring, 3 ) ) * HOUR_IN_SECONDS );
			}

			$date_time_zone_selected = new DateTimeZone( $tzstring );
			$tz_offset = timezone_offset_get( $date_time_zone_selected, date_create() );

			return $tz_offset;
		}

		return 0;
	}

	/**
	 * Utility method that attempts to get an attachment's ID by it's url
	 * @since  1.0.0
	 * @param  string  $img_url Attachment url
	 * @return mixed            Attachment ID or false
	 */
	public static function image_id_from_url( $img_url ) {
		global $wpdb;

		$img_url = esc_url_raw( $img_url );
		// Get just the file name
		if ( false !== strpos( $img_url, '/' ) ) {
			$explode = explode( '/', $img_url );
			$img_url = end( $explode );
		}

		// And search for a fuzzy match of the file name
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid LIKE '%%%s%%' LIMIT 1;", $img_url ) );

		// If we found an attachement ID, return it
		if ( !empty( $attachment ) && is_array( $attachment ) )
			return $attachment[0];

		// No luck
		return false;
	}

}

// Handle oembed Ajax
add_action( 'wp_ajax_cmb_oembed_handler', array( 'cmb_Meta_Box_ajax', 'oembed_handler' ) );
add_action( 'wp_ajax_nopriv_cmb_oembed_handler', array( 'cmb_Meta_Box_ajax', 'oembed_handler' ) );

/**
 * A helper function to get an option from a CMB options array
 * @since  1.0.1
 * @param  string  $option_key Option key
 * @param  string  $field_id   Option array field key
 * @return array               Options array or specific field
 */
function cmb_get_option( $option_key, $field_id = '' ) {
	return cmb_Meta_Box::get_option( $option_key, $field_id );
}

/**
 * Get a CMB field object.
 * @since  1.1.0
 * @param  array  $field_args  Field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
 * @return object              cmb_Meta_Box_field object
 */
function cmb_get_field( $field_args, $object_id = 0, $object_type = 'post' ) {
	// Default to the loop post ID
	$object_id = $object_id ? $object_id : get_the_ID();
	cmb_Meta_Box::set_object_id( $object_id );
	cmb_Meta_Box::set_object_type( $object_type );
	// Send back field object
	return new cmb_Meta_Box_field( $field_args );
}

/**
 * Get a field's value.
 * @since  1.1.0
 * @param  array  $field_args  Field arguments
 * @param  int    $object_id   Object ID
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page)
 * @return mixed               Maybe escaped value
 */
function cmb_get_field_value( $field_args, $object_id = 0, $object_type = 'post' ) {
	$field = cmb_get_field( $field_args, $object_id, $object_type );
	return $field->escaped_value();
}

/**
 * Loop and output multiple metaboxes
 * @since 1.0.0
 * @param array $meta_boxes Metaboxes config array
 * @param int   $object_id  Object ID
 */
function cmb_print_metaboxes( $meta_boxes, $object_id ) {
	foreach ( (array) $meta_boxes as $meta_box ) {
		cmb_print_metabox( $meta_box, $object_id );
	}
}

/**
 * Output a metabox
 * @since 1.0.0
 * @param array $meta_box  Metabox config array
 * @param int   $object_id Object ID
 */
function cmb_print_metabox( $meta_box, $object_id ) {
	$cmb = new cmb_Meta_Box( $meta_box );
	if ( $cmb ) {

		cmb_Meta_Box::set_object_id( $object_id );

		if ( ! wp_script_is( 'cmb-scripts', 'registered' ) )
			$cmb->register_scripts();

		wp_enqueue_script( 'cmb-scripts' );

		// default is to show cmb styles
		if ( $meta_box['cmb_styles'] != false )
			wp_enqueue_style( 'cmb-styles' );

		cmb_Meta_Box::show_form( $meta_box );
	}

}

/**
 * Saves a particular metabox's fields
 * @since 1.0.0
 * @param array $meta_box  Metabox config array
 * @param int   $object_id Object ID
 */
function cmb_save_metabox_fields( $meta_box, $object_id ) {
	cmb_Meta_Box::save_fields( $meta_box, $object_id );
}

/**
 * Display a metabox form & save it on submission
 * @since  1.0.0
 * @param  array   $meta_box  Metabox config array
 * @param  int     $object_id Object ID
 * @param  boolean $return    Whether to return or echo form
 * @return string             CMB html form markup
 */
function cmb_metabox_form( $meta_box, $object_id, $echo = true ) {

	$meta_box = cmb_Meta_Box::set_mb_defaults( $meta_box );

	// Make sure form should be shown
	if ( ! apply_filters( 'cmb_show_on', true, $meta_box ) )
		return '';

	// Make sure that our object type is explicitly set by the metabox config
	cmb_Meta_Box::set_object_type( cmb_Meta_Box::set_mb_type( $meta_box ) );

	// Save the metabox if it's been submitted
	// check permissions
	// @todo more hardening?
	if (
		// check nonce
		isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST['wp_meta_box_nonce'] )
		&& wp_verify_nonce( $_POST['wp_meta_box_nonce'], cmb_Meta_Box::nonce() )
		&& $_POST['object_id'] == $object_id
	)
		cmb_save_metabox_fields( $meta_box, $object_id );

	// Show specific metabox form

	// Get cmb form
	ob_start();
	cmb_print_metabox( $meta_box, $object_id );
	$form = ob_get_contents();
	ob_end_clean();

	$form_format = apply_filters( 'cmb_frontend_form_format', '<form class="cmb-form" method="post" id="%s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%s">%s<input type="submit" name="submit-cmb" value="%s" class="button-primary"></form>', $object_id, $meta_box, $form );

	$form = sprintf( $form_format, $meta_box['id'], $object_id, $form, __( 'Save' ) );

	if ( $echo )
		echo $form;

	return $form;
}

// End. That's it, folks! //
