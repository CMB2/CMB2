<?php

/**
 * Helper function to provide directory path to CMB
 * @since  2.0.0
 * @param  string  $path Path to append
 * @return string        Directory with optional path appended
 */
function cmb2_dir( $path = '' ) {
	return trailingslashit( dirname( __FILE__ ) ) . $path;
}

require_once cmb2_dir( 'includes/helper-functions.php' );

$meta_boxes_config = apply_filters( 'cmb2_meta_boxes', array() );
foreach ( (array) $meta_boxes_config as $meta_box ) {
	$box = new CMB2( $meta_box );
	$box->hooks();
}

class CMB2_Base {
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
	 * Array of all metabox objects
	 * @var   array
	 * @since 2.0.0
	 */
	protected static $meta_boxes = array();

	/**
	 * Array of all hooks done (to be run once)
	 * @var   array
	 * @since 2.0.0
	 */
	protected static $done = array();

	public static function get_metabox( $meta_box_id ) {
		if ( empty( self::$meta_boxes ) || empty( self::$meta_boxes[ $meta_box_id ] ) ) {
			return false;
		}

		return self::$meta_boxes[ $meta_box_id ];
	}

	public function once( $action, $hook, $priority = 10, $accepted_args = 1 ) {
		$key = md5( serialize( func_get_arg() ) );

		if ( in_array( $key, self::$done ) ) {
			return;
		}

		self::$done[] = $key;
		add_filter( $action, $hook, $priority, $accepted_args );
	}

}

/**
 * Create meta boxes
 */
class CMB2 extends CMB2_Base {

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
	 * List of fields that are changed/updated on save
	 * @var   array
	 * @since 1.1.0
	 */
	protected static $updated = array();

	/**
	 * Get started
	 */
	function __construct( $meta_box ) {
		$this->meta_box = $this->set_mb_defaults( $meta_box );
		$this->box_id    = $meta_box['id'];
		parent::$meta_boxes[ $meta_box['id'] ] = $this;
	}

	public function once() {

	}

	public function hooks() {
		$allow_frontend = apply_filters( 'cmb2_allow_frontend', true, $meta_box );

		if ( ! is_admin() && ! $allow_frontend ) {
			return;
		}


		foreach ( get_class_methods( 'CMB2_Show_Filters' ) as $filter ) {
			add_filter( 'cmb2_show_on', array( 'CMB2_Show_Filters', $filter ), 10, 2 );
		}

		if ( is_admin() ) {
			$this->admin_hooks();
		}
	}

	public function admin_hooks() {

		$field_types = wp_list_pluck( $meta_box['fields'], 'type' );

		$upload = in_array( 'file', $field_types ) || in_array( 'file_list', $field_types );

		global $pagenow;

		// register our scripts and styles for cmb
		$this->once( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 8 );

		if ( self::get_object_type() == 'post' ) {
			add_action( 'admin_menu', array( $this, 'add_metaboxes' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

			if ( $upload && in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) ) {
				$this->once( 'admin_head', array( $this, 'add_post_enctype' ) );
			}

		}
		if ( self::get_object_type() == 'user' ) {

			$priority = isset( $meta_box['priority'] ) ? $meta_box['priority'] : 10;

			if ( ! is_numeric( $priority ) ) {
				if ( $priority == 'high' ) {
					$priority = 5;
				} elseif ( $priority == 'low' ) {
					$priority = 20;
				} else {
					$priority = 10;
				}
			}

			add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'user_new_form', array( $this, 'user_metabox' ), $priority );

			add_action( 'personal_options_update', array( $this, 'save_user' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
			add_action( 'user_register', array( $this, 'save_user' ) );
			if ( $upload && in_array( $pagenow, array( 'profile.php', 'user-edit.php', 'user-add.php' ) ) ) {
				$this->form_id = 'your-profile';
				$this->once( 'admin_head', array( $this, 'add_post_enctype' ) );
			}
		}
	}

	/**
	 * Registers scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function register_scripts() {
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
			   wp_register_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), CMB2_VERSION );
		   	wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), CMB2_VERSION );
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
		wp_register_script( 'cmb-datepicker', cmb2_utils()->url( 'js/jquery.datePicker.min.js' ) );
		wp_register_script( 'cmb-timepicker', cmb2_utils()->url( 'js/jquery.timePicker.min.js' ) );
		wp_register_script( 'cmb-scripts', cmb2_utils()->url( "js/cmb{$min}.js" ), $scripts, CMB2_VERSION );

		wp_enqueue_media();

		wp_localize_script( 'cmb-scripts', 'cmb2_l10', apply_filters( 'cmb2_localized_data', array(
			'ajax_nonce'      => wp_create_nonce( 'ajax_nonce' ),
			'ajaxurl'         => admin_url( '/admin-ajax.php' ),
			'script_debug'    => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG,
			'new_admin_style' => version_compare( $wp_version, '3.7', '>' ),
			'object_type'     => self::get_object_type(),
			'upload_file'     => __( 'Use this file', 'cmb' ),
			'remove_image'    => __( 'Remove Image', 'cmb' ),
			'remove_file'     => __( 'Remove', 'cmb' ),
			'file'            => __( 'File:', 'cmb' ),
			'download'        => __( 'Download', 'cmb' ),
			'up_arrow'        => __( '[ ↑ ]&nbsp;', 'cmb' ),
			'down_arrow'      => __( '&nbsp;[ ↓ ]', 'cmb' ),
			'check_toggle'    => __( 'Select / Deselect All', 'cmb' ),
			'defaults'        => array(
				'date_picker'  => false,
				'color_picker' => false,
				'time_picker'  => array(
					'startTime'   => '00:00',
					'endTime'     => '23:59',
					'show24Hours' => false,
					'separator'   => ':',
					'step'        => 30
				),
			),
		) ) );

		wp_register_style( 'cmb-styles', cmb2_utils()->url( "css/style{$min}.css" ), $styles );

	}

	/**
	 * Enqueues scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function do_scripts( $hook ) {
		// only enqueue our scripts/styles on the proper pages
		if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
			wp_enqueue_script( 'cmb-scripts' );

			cmb2_utils()->enqueue_cmb_css( $this->meta_box );
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

		foreach ( $this->meta_box['pages'] as $page ) {
			if ( apply_filters( 'cmb2_show_on', true, $this->meta_box ) ) {
				add_meta_box( $this->meta_box['id'], $this->meta_box['title'], array( $this, 'post_metabox' ), $page, $this->meta_box['context'], $this->meta_box['priority']) ;
			}
		}
	}

	/**
	 * Display metaboxes for a post object
	 * @since  1.0.0
	 */
	public function post_metabox() {
		if ( ! $this->meta_box ) {
			return;
		}

		$this->show_form( get_the_ID(), 'post' );

	}

	/**
	 * Display metaboxes for a user object
	 * @since  1.0.0
	 */
	public function user_metabox() {
		if ( ! $this->meta_box ) {
			return;
		}

		if ( 'user' != $this->mb_object_type() ) {
			return;
		}

		if ( ! apply_filters( 'cmb2_show_on', true, $this->meta_box ) )
			return;

		wp_enqueue_script( 'cmb-scripts' );

		// default is to NOT show cmb styles on user profile page
		cmb2_utils()->enqueue_cmb_css( $this->meta_box );

		$this->show_form();

	}

	/**
	 * Loops through and displays fields
	 * @since  1.0.0
	 * @param  array  $meta_box    Metabox config array
	 * @param  int    $object_id   Object ID
	 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
	 */
	public function show_form( $object_id = 0, $object_type = '' ) {
		$object_type = $this->set_object_type( $object_type ? $object_type : $this->mb_object_type() );
		// Set/get ID
		$object_id = $this->get_object_id( $object_id );

		wp_nonce_field( $this->nonce(), 'wp_meta_box_nonce', false, true );

		echo "\n<!-- Begin CMB Fields -->\n";
		/**
		 * Hook before form table begins
		 *
		 * @param array  $meta_box    Metabox config array
		 * @param int    $object_id   The ID of the current object
		 * @param string $object_type The type of object you are working with.
		 *	                           Usually `post` (this applies to all post-types).
		 *	                           Could also be `comment`, `user` or `options-page`.
		 */
		do_action( 'cmb2_before_table', $meta_box, $object_id, $object_type );
		echo '<table class="form-table cmb2_metabox">';

		foreach ( $meta_box['fields'] as $field_args ) {

			$field_args['context'] = $meta_box['context'];

			if ( 'group' == $field_args['type'] ) {

				if ( ! isset( $field_args['show_names'] ) ) {
					$field_args['show_names'] = $meta_box['show_names'];
				}
				$this->render_group( $field_args );
			} else {

				$field_args['show_names'] = $meta_box['show_names'];
				// Render default fields
				$field = new CMB2_field( $field_args );
				$field->render_field();
			}
		}
		echo '</table>';
		/**
		 * Hook after form table has been rendered
		 *
		 * @param array  $meta_box    Metabox config array
		 * @param int    $object_id   The ID of the current object
		 * @param string $object_type The type of object you are working with.
		 *	                           Usually `post` (this applies to all post-types).
		 *	                           Could also be `comment`, `user` or `options-page`.
		 */
		do_action( 'cmb2_after_table', $meta_box, $object_id, $object_type );
		echo "\n<!-- End CMB Fields -->\n";

	}

	/**
	 * Render a repeatable group
	 */
	public function render_group( $args ) {
		if ( ! isset( $args['id'], $args['fields'] ) || ! is_array( $args['fields'] ) ) {
			return;
		}

		$args['count']   = 0;
		$field_group     = new CMB2_field( $args );
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
					echo '<p class="cmb2_metabox_description">'. $desc .'</p>';
			echo '</th></tr>';
		}

		if ( ! empty( $group_val ) ) {

			foreach ( $group_val as $iterator => $field_id ) {
				$this->render_group_row( $field_group, $remove_disabled );
			}
		} else {
			$this->render_group_row( $field_group, $remove_disabled );
		}

		echo '<tr><td><p class="add-row"><button data-selector="', $field_group->id() ,'_repeat" data-grouptitle="', $field_group->options( 'group_title' ) ,'" class="add-group-row button">'. $field_group->options( 'add_button' ) .'</button></p></td></tr>';

		echo '</table></td></tr>';

	}

	public function render_group_row( $field_group, $remove_disabled ) {

		echo '
		<tr class="repeatable-grouping" data-iterator="'. $field_group->count() .'">
			<td>
				<table class="cmb-nested-table" style="width: 100%;">';
				if ( $field_group->options( 'group_title' ) ) {
					echo '
					<tr class="cmb-group-title">
						<th colspan="2">
							', sprintf( '<h4>%1$s</h4>', $field_group->replace_hash( $field_group->options( 'group_title' ) ) ), '
						</th>
					</tr>
					';
				}
				// Render repeatable group fields
				foreach ( array_values( $field_group->args( 'fields' ) ) as $field_args ) {
					$field_args['show_names'] = $field_group->args( 'show_names' );
					$field_args['context'] = $field_group->args( 'context' );
					$field = new CMB2_field( $field_args, $field_group );
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
			|| ! wp_verify_nonce( $_POST['wp_meta_box_nonce'], $this->nonce() )
			// check if autosave
			|| defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE
			// check user editing permissions
			|| ( 'page' ==  $post_type && ! current_user_can( 'edit_page', $post_id ) )
			|| ! current_user_can( 'edit_post', $post_id )
			// get the metabox post_types & compare it to this post_type
			|| ! in_array( $post_type, $this->meta_box['pages'] )
		) {
			return $post_id;
		}

		$this->save_fields( $post_id, 'post' );
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
			|| ! wp_verify_nonce( $_POST['wp_meta_box_nonce'], $this->nonce() )
		) {
			return $user_id;
		}

		$this->save_fields( $user_id, 'user' );
	}

	/**
	 * Loops through and saves field data
	 * @since  1.0.0
	 * @param array   $meta_box    Metabox config array
	 * @param  int    $object_id   Object ID
	 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
	 */
	public function save_fields( $object_id = 0, $object_type = '' ) {

		$this->meta_box['show_on'] = empty( $this->meta_box['show_on'] ) ? array( 'key' => false, 'value' => false ) : $this->meta_box['show_on'];

		$this->object_id = $object_id ? $object_id : get_the_ID();
		// Set/get type
		$object_type = $this->set_object_type( $object_type ? $object_type : $this->mb_object_type() );

		if ( ! apply_filters( 'cmb2_show_on', true, $this->meta_box ) )
			return;

		// save field ids of those that are updated
		$this->updated = array();

		foreach ( $this->meta_box['fields'] as $field_args ) {

			if ( 'group' == $field_args['type'] ) {
				$this->save_group( $field_args );
			} else {
				// Save default fields
				$field = new CMB2_field( $field_args );
				$this->save_field( $this->sanitize_field( $field ), $field );
			}

		}

		// If options page, save the updated options
		if ( $object_type == 'options-page' ) {
			cmb2_options( $object_id )->set();
		}

		/**
		 * Fires after all fields have been saved.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the metabox/form's object type
		 * 	Usually `post` (this applies to all post-types).
		 *  	Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param array  $meta_box_id Metabox's id parameter
		 * @param string $updated     All fields that were updated.
		 *                            Will only include fields that had values change.
		 * @param string $meta_box    The metabox config array.
		 */
		do_action( "cmb2_save_{$object_type}_fields", $object_id, $this->meta_box['id'], $this->updated, $this->meta_box );

	}

	/**
	 * Save a repeatable group
	 */
	public function save_group( $args ) {
		if ( ! isset( $args['id'], $args['fields'], $_POST[ $args['id'] ] ) || ! is_array( $args['fields'] ) )
			return;

		$field_group        = new CMB2_field( $args );
		$base_id            = $field_group->id();
		$old                = $field_group->get_data();
		$group_vals         = $_POST[ $base_id ];
		$saved              = array();
		$is_updated         = false;
		$field_group->index = 0;

		// $group_vals[0]['color'] = '333';
		foreach ( array_values( $field_group->fields() ) as $field_args ) {
			$field = new CMB2_field( $field_args, $field_group );
			$sub_id = $field->id( true );

			foreach ( (array) $group_vals as $field_group->index => $post_vals ) {

				// Get value
				$new_val = isset( $group_vals[ $field_group->index ][ $sub_id ] )
					? $group_vals[ $field_group->index ][ $sub_id ]
					: false;

				// Sanitize
				$new_val = $this->sanitize_field( $field, $new_val, $field_group->index );

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
					$this->updated[] = $base_id .'::'. $field_group->index .'::'. $sub_id;

				// Add to `$saved` array
				$saved[ $field_group->index ][ $sub_id ] = $new_val;

			}
			$saved[ $field_group->index ] = array_filter( $saved[ $field_group->index ] );
		}
		$saved = array_filter( $saved );

		$field_group->update_data( $saved, true );
	}

	public function sanitize_field( $field, $new_value = null ) {

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

	public function save_field( $new_value, $field ) {
		$name = $field->id();
		$old  = $field->get_data();

		// if ( $field->args( 'multiple' ) && ! $field->args( 'repeatable' ) && ! $field->group ) {
		// 	$field->remove_data();
		// 	if ( ! empty( $new_value ) ) {
		// 		foreach ( $new_value as $add_new ) {
		// 			$this->updated[] = $name;
		// 			$field->update_data( $add_new, $name, false );
		// 		}
		// 	}
		// } else
		if ( ! empty( $new_value ) && $new_value !== $old  ) {
			$this->updated[] = $name;
			return $field->update_data( $new_value );
		} elseif ( empty( $new_value ) ) {
			if ( ! empty( $old ) )
				$this->updated[] = $name;
			return $field->remove_data();
		}
	}

	/**
	 * Get object id from global space if no id is provided
	 * @since  1.0.0
	 * @param  integer $object_id Object ID
	 * @return integer $object_id Object ID
	 */
	public function get_object_id( $object_id = 0 ) {

		if ( $object_id ) {
			$this->object_id = $object_id;
			return $this->object_id;
		}

		if ( isset( $this->object_id ) ) {
			return $this->object_id;
		}

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
		$this->object_id = $object_id ? $object_id : 0;

		return $this->object_id;
	}

	/**
	 * Sets the $object_type based on metabox settings
	 * @since  1.0.0
	 * @param  array|string $meta_box Metabox config array or explicit setting
	 * @return string       Object type
	 */
	public static function mb_object_type( $meta_box ) {

		if ( isset( $this->mb_object_type ) ) {
			return $this->mb_object_type;
		}
		if ( is_string( $this->meta_box ) ) {
			$this->mb_object_type = $this->meta_box;
			return $this->mb_object_type;
		}

		if ( ! isset( $this->meta_box['pages'] ) ) {
			return $this->mb_object_type;
		}

		$type = false;
		// check if 'pages' is a string
		if ( $this->is_options_page_mb() )
			$type = 'options-page';
		// check if 'pages' is a string
		elseif ( is_string( $this->meta_box['pages'] ) )
			$type = $this->meta_box['pages'];
		// if it's an array of one, extract it
		elseif ( is_array( $this->meta_box['pages'] ) && count( $this->meta_box['pages'] === 1 ) )
			$type = is_string( end( $this->meta_box['pages'] ) ) ? end( $this->meta_box['pages'] ) : false;

		if ( !$type ) {
			return $this->mb_object_type;
		}

		// Get our object type
		if ( 'user' == $type )
			$this->mb_object_type = 'user';
		elseif ( 'comment' == $type )
			$this->mb_object_type = 'comment';
		elseif ( 'options-page' == $type )
			$this->mb_object_type = 'options-page';
		else
			$this->mb_object_type = 'post';

		return $this->mb_object_type;
	}

	/**
	 * Determines if metabox is for an options page
	 * @since  1.0.1
	 * @param  array   $meta_box Metabox config array
	 * @return boolean           True/False
	 */
	public function is_options_page_mb() {
		return ( isset( $this->meta_box['show_on']['key'] ) && 'options-page' === $this->meta_box['show_on']['key'] );
	}

	/**
	 * Returns the object type
	 * @since  1.0.0
	 * @return string Object type
	 */
	public function type() {
		if ( $this->object_type ) {
			return $this->object_type;
		}

		global $pagenow;

		if (
			$pagenow == 'user-edit.php'
			|| $pagenow == 'profile.php'
			|| $pagenow == 'user-new.php'
		)
			$this->set_object_type( 'user' );

		elseif (
			$pagenow == 'edit-comments.php'
			|| $pagenow == 'comment.php'
		)
			$this->set_object_type( 'comment' );
		else
			$this->set_object_type( 'post' );

		return $this->object_type;
	}

	/**
	 * Sets the object type
	 * @since  1.0.0
	 * @return string Object type
	 */
	public function set_object_type( $object_type ) {
		return $this->object_type = $object_type;
	}

	/**
	 * Fills in empty metabox parameters with defaults
	 * @since  1.0.1
	 * @param  array $meta_box Metabox config array
	 * @return array           Modified Metabox config array
	 */
	public function set_mb_defaults( $meta_box ) {
		$this->meta_box = wp_parse_args( $meta_box, parent::$mb_defaults );
		$this->mb_object_type();
	}

	public function nonce() {
		return md5( serialize( $this->metabox ) );
	}

}

// Handle oembed Ajax
add_action( 'wp_ajax_cmb2_oembed_handler', array( 'CMB2_ajax', 'oembed_handler' ) );
add_action( 'wp_ajax_nopriv_cmb2_oembed_handler', array( 'CMB2_ajax', 'oembed_handler' ) );

// End. That's it, folks! //
