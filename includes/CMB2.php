<?php
/**
 * CMB2 - The core metabox object
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property-read string $cmb_id
 * @property-read array $meta_box
 * @property-read array $updated
 * @property-read bool  $has_columns
 * @property-read array $tax_metaboxes_to_remove
 */

/**
 * The main CMB2 object for storing box data/properties.
 */
class CMB2 extends CMB2_Base {

	/**
	 * The object properties name.
	 *
	 * @var   string
	 * @since 2.2.3
	 */
	protected $properties_name = 'meta_box';

	/**
	 * Metabox Config array
	 *
	 * @var   array
	 * @since 0.9.0
	 */
	protected $meta_box = array();

	/**
	 * Type of object registered for metabox. (e.g., post, user, or comment)
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	protected $mb_object_type = null;

	/**
	 * List of fields that are changed/updated on save
	 *
	 * @var   array
	 * @since 1.1.0
	 */
	protected $updated = array();

	/**
	 * Metabox Defaults
	 *
	 * @var   array
	 * @since 1.0.1
	 */
	protected $mb_defaults = array(
		'id'                      => '',
		'title'                   => '',
		// Post type slug, or 'user', 'term', 'comment', or 'options-page'.
		'object_types'            => array(),

		/**
		 * The context within the screen where the boxes should display. Available contexts vary
		 * from screen to screen. Post edit screen contexts include 'normal', 'side', and 'advanced'.
		 *
		 * For placement in locations outside of a metabox, other options include:
		 * 'form_top', 'before_permalink', 'after_title', 'after_editor'
		 *
		 * Comments screen contexts include 'normal' and 'side'. Default is 'normal'.
		 */
		'context'                 => 'normal',
		'priority'                => 'high',
		'show_names'              => true, // Show field names on the left.
		'show_on_cb'              => null, // Callback to determine if metabox should display.
		'show_on'                 => array(), // Post IDs or page templates to display this metabox. overrides 'show_on_cb'.
		'cmb_styles'              => true, // Include CMB2 stylesheet.
		'enqueue_js'              => true, // Include CMB2 JS.
		'fields'                  => array(),

		/**
		 * Handles hooking CMB2 forms/metaboxes into the post/attachement/user/options-page screens
		 * and handles hooking in and saving those fields.
		 */
		'hookup'                  => true,
		'save_fields'             => true, // Will not save during hookup if false.
		'closed'                  => false, // Default metabox to being closed.
		'taxonomies'              => array(),
		'new_user_section'        => 'add-new-user', // or 'add-existing-user'.
		'new_term_section'        => true,
		'show_in_rest'            => false,
		'classes'                 => null, // Optionally add classes to the CMB2 wrapper.
		'classes_cb'              => '', // Optionally add classes to the CMB2 wrapper (via a callback).

		/*
		 * The following parameter is for post alternate-context metaboxes only.
		 *
		 * To output the fields 'naked' (without a postbox wrapper/style), then
		 * add a `'remove_box_wrap' => true` to your metabox registration array.
		 */
		'remove_box_wrap'         => false,

		/*
		 * The following parameter is any additional arguments passed as $callback_args
		 * to add_meta_box, if/when applicable.
		 *
		 * CMB2 does not use these arguments in the add_meta_box callback, however, these args
		 * are parsed for certain special properties, like determining Gutenberg/block-editor
		 * compatibility.
		 *
		 * Examples:
		 *
		 * - Make sure default editor is used as metabox is not compatible with block editor
		 *      [ '__block_editor_compatible_meta_box' => false/true ]
		 *
		 * - Or declare this box exists for backwards compatibility
		 *      [ '__back_compat_meta_box' => false ]
		 *
		 * More: https://wordpress.org/gutenberg/handbook/extensibility/meta-box/
		 */
		'mb_callback_args'        => null,

		/*
		 * The following parameters are for options-page metaboxes,
		 * and several are passed along to add_menu_page()/add_submenu_page()
		 */

		// 'menu_title'           => null, // Falls back to 'title' (above). Do not define here so we can set a fallback.
		'message_cb'              => '', // Optionally define the options-save message (via a callback).
		'option_key'              => '', // The actual option key and admin menu page slug.
		'parent_slug'             => '', // Used as first param in add_submenu_page().
		'capability'              => 'manage_options', // Cap required to view options-page.
		'icon_url'                => '', // Menu icon. Only applicable if 'parent_slug' is left empty.
		'position'                => null, // Menu position. Only applicable if 'parent_slug' is left empty.

		'admin_menu_hook'         => 'admin_menu', // Alternately 'network_admin_menu' to add network-level options page.
		'display_cb'              => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
		'save_button'             => '', // The text for the options-page save button. Defaults to 'Save'.
		'disable_settings_errors' => false, // On settings pages (not options-general.php sub-pages), allows disabling.
		'tab_group'               => '', // Tab-group identifier, enables options page tab navigation.
		// 'tab_title'            => null, // Falls back to 'title' (above). Do not define here so we can set a fallback.
		// 'autoload'             => true, // Defaults to true, the options-page option will be autloaded.
	);

	/**
	 * Metabox field objects
	 *
	 * @var   array
	 * @since 2.0.3
	 */
	protected $fields = array();

	/**
	 * An array of hidden fields to output at the end of the form
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	protected $hidden_fields = array();

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 *
	 * @var   string
	 * @since 2.0.0
	 */
	protected $generated_nonce = '';

	/**
	 * Whether there are fields to be shown in columns. Set in CMB2::add_field().
	 *
	 * @var   bool
	 * @since 2.2.2
	 */
	protected $has_columns = false;

	/**
	 * If taxonomy field is requesting to remove_default, we store the taxonomy here.
	 *
	 * @var   array
	 * @since 2.2.3
	 */
	protected $tax_metaboxes_to_remove = array();

	/**
	 * Get started
	 *
	 * @since 0.4.0
	 * @param array   $config    Metabox config array.
	 * @param integer $object_id Optional object id.
	 */
	public function __construct( $config, $object_id = 0 ) {

		if ( empty( $config['id'] ) ) {
			wp_die( esc_html__( 'Metabox configuration is required to have an ID parameter.', 'cmb2' ) );
		}

		$this->cmb_id = $config['id'];
		$this->meta_box = wp_parse_args( $config, $this->mb_defaults );
		$this->meta_box['fields'] = array();

		// Ensures object_types is an array.
		$this->set_prop( 'object_types', $this->box_types() );
		$this->object_id( $object_id );

		if ( $this->is_options_page_mb() ) {
			$this->init_options_mb();
		}

		$this->mb_object_type();

		if ( ! empty( $config['fields'] ) && is_array( $config['fields'] ) ) {
			$this->add_fields( $config['fields'] );
		}

		CMB2_Boxes::add( $this );

		/**
		 * Hook during initiation of CMB2 object
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is this meta_box id.
		 *
		 * @param array $cmb This CMB2 object
		 */
		do_action( "cmb2_init_{$this->cmb_id}", $this );

		// Hook in the hookup... how meta.
		add_action( "cmb2_init_hookup_{$this->cmb_id}", array( 'CMB2_hookup', 'maybe_init_and_hookup' ) );

		// Hook in the rest api functionality.
		add_action( "cmb2_init_hookup_{$this->cmb_id}", array( 'CMB2_REST', 'maybe_init_and_hookup' ) );
	}

	/**
	 * Loops through and displays fields
	 *
	 * @since 1.0.0
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Type of object being saved. (e.g., post, user, or comment).
	 *
	 * @return CMB2
	 */
	public function show_form( $object_id = 0, $object_type = '' ) {
		$this->render_form_open( $object_id, $object_type );

		foreach ( $this->prop( 'fields' ) as $field_args ) {
			$this->render_field( $field_args );
		}

		return $this->render_form_close( $object_id, $object_type );
	}

	/**
	 * Outputs the opening form markup and runs corresponding hooks:
	 * 'cmb2_before_form' and "cmb2_before_{$object_type}_form_{$this->cmb_id}"
	 *
	 * @since  2.2.0
	 * @param  integer $object_id   Object ID.
	 * @param  string  $object_type Object type.
	 *
	 * @return CMB2
	 */
	public function render_form_open( $object_id = 0, $object_type = '' ) {
		$object_type = $this->object_type( $object_type );
		$object_id = $this->object_id( $object_id );

		echo "\n<!-- Begin CMB2 Fields -->\n";

		$this->nonce_field();

		/**
		 * Hook before form table begins
		 *
		 * @param array  $cmb_id      The current box ID.
		 * @param int    $object_id   The ID of the current object.
		 * @param string $object_type The type of object you are working with.
		 *                            Usually `post` (this applies to all post-types).
		 *                            Could also be `comment`, `user` or `options-page`.
		 * @param array  $cmb         This CMB2 object.
		 */
		do_action( 'cmb2_before_form', $this->cmb_id, $object_id, $object_type, $this );

		/**
		 * Hook before form table begins
		 *
		 * The first dynamic portion of the hook name, $object_type, is the type of object
		 * you are working with. Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * The second dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * @param array  $cmb_id      The current box ID
		 * @param int    $object_id   The ID of the current object
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_before_{$object_type}_form_{$this->cmb_id}", $object_id, $this );

		echo '<div class="', esc_attr( $this->box_classes() ), '"><div id="cmb2-metabox-', sanitize_html_class( $this->cmb_id ), '" class="cmb2-metabox cmb-field-list">';

		return $this;
	}

	/**
	 * Defines the classes for the CMB2 form/wrap.
	 *
	 * @since  2.0.0
	 * @return string Space concatenated list of classes
	 */
	public function box_classes() {

		$classes = array( 'cmb2-wrap', 'form-table' );

		// Use the callback to fetch classes.
		if ( $added_classes = $this->get_param_callback_result( 'classes_cb' ) ) {
			$added_classes = is_array( $added_classes ) ? $added_classes : array( $added_classes );
			$classes = array_merge( $classes, $added_classes );
		}

		if ( $added_classes = $this->prop( 'classes' ) ) {
			$added_classes = is_array( $added_classes ) ? $added_classes : array( $added_classes );
			$classes = array_merge( $classes, $added_classes );
		}

		/**
		 * Add our context classes for non-standard metaboxes.
		 *
		 * @since 2.2.4
		 */
		if ( $this->is_alternate_context_box() ) {
			$context = array();

			// Include custom class if requesting no title.
			if ( ! $this->prop( 'title' ) && ! $this->prop( 'remove_box_wrap' ) ) {
				$context[] = 'cmb2-context-wrap-no-title';
			}

			// Include a generic context wrapper.
			$context[] = 'cmb2-context-wrap';

			// Include a context-type based context wrapper.
			$context[] = 'cmb2-context-wrap-' . $this->prop( 'context' );

			// Include an ID based context wrapper as well.
			$context[] = 'cmb2-context-wrap-' . $this->prop( 'id' );

			// And merge all the classes back into the array.
			$classes = array_merge( $classes, $context );
		}

		/**
		 * Globally filter box wrap classes
		 *
		 * @since 2.2.2
		 *
		 * @param string $classes Array of classes for the cmb2-wrap.
		 * @param CMB2   $cmb     This CMB2 object.
		 */
		$classes = apply_filters( 'cmb2_wrap_classes', $classes, $this );

		$split = array();
		foreach ( array_filter( $classes ) as $class ) {
			foreach ( explode( ' ', $class ) as $_class ) {
				// Clean up & sanitize.
				$split[] = sanitize_html_class( strip_tags( $_class ) );
			}
		}
		$classes = $split;

		// Remove any duplicates.
		$classes = array_unique( $classes );

		// Make it a string.
		return implode( ' ', $classes );
	}

	/**
	 * Outputs the closing form markup and runs corresponding hooks:
	 * 'cmb2_after_form' and "cmb2_after_{$object_type}_form_{$this->cmb_id}"
	 *
	 * @since  2.2.0
	 * @param  integer $object_id   Object ID.
	 * @param  string  $object_type Object type.
	 *
	 * @return CMB2
	 */
	public function render_form_close( $object_id = 0, $object_type = '' ) {
		$object_type = $this->object_type( $object_type );
		$object_id = $this->object_id( $object_id );

		echo '</div></div>';

		$this->render_hidden_fields();

		/**
		 * Hook after form form has been rendered
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * The first dynamic portion of the hook name, $object_type, is the type of object
		 * you are working with. Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_after_{$object_type}_form_{$this->cmb_id}", $object_id, $this );

		/**
		 * Hook after form form has been rendered
		 *
		 * @param array  $cmb_id      The current box ID.
		 * @param int    $object_id   The ID of the current object.
		 * @param string $object_type The type of object you are working with.
		 *                            Usually `post` (this applies to all post-types).
		 *                            Could also be `comment`, `user` or `options-page`.
		 * @param array  $cmb         This CMB2 object.
		 */
		do_action( 'cmb2_after_form', $this->cmb_id, $object_id, $object_type, $this );

		echo "\n<!-- End CMB2 Fields -->\n";

		return $this;
	}

	/**
	 * Renders a field based on the field type
	 *
	 * @since  2.2.0
	 * @param  array $field_args A field configuration array.
	 * @return mixed CMB2_Field object if successful.
	 */
	public function render_field( $field_args ) {
		$field_args['context'] = $this->prop( 'context' );

		if ( 'group' === $field_args['type'] ) {

			if ( ! isset( $field_args['show_names'] ) ) {
				$field_args['show_names'] = $this->prop( 'show_names' );
			}
			$field = $this->render_group( $field_args );

		} elseif ( 'hidden' === $field_args['type'] && $this->get_field( $field_args )->should_show() ) {
			// Save rendering for after the metabox.
			$field = $this->add_hidden_field( $field_args );

		} else {

			$field_args['show_names'] = $this->prop( 'show_names' );

			// Render default fields.
			$field = $this->get_field( $field_args )->render_field();
		}

		return $field;
	}

	/**
	 * Render a group of fields.
	 *
	 * @param array|CMB2_Field $args Array of field arguments for a group field parent or the group parent field.
	 * @return CMB2_Field|null Group field object.
	 */
	public function render_group( $args ) {
		$field_group = false;

		if ( $args instanceof CMB2_Field ) {
			$field_group = 'group' === $args->type() ? $args : false;
		} elseif ( isset( $args['id'], $args['fields'] ) && is_array( $args['fields'] ) ) {
			$field_group = $this->get_field( $args );
		}

		if ( ! $field_group ) {
			return;
		}

		$field_group->render_context = 'edit';
		$field_group->peform_param_callback( 'render_row_cb' );

		return $field_group;
	}

	/**
	 * The default callback to render a group of fields.
	 *
	 * @since  2.2.6
	 *
	 * @param  array      $field_args  Array of field arguments for the group field parent.
	 * @param  CMB2_Field $field_group The CMB2_Field group object.
	 *
	 * @return CMB2_Field|null Group field object.
	 */
	public function render_group_callback( $field_args, $field_group ) {

		// If field is requesting to be conditionally shown.
		if ( ! $field_group || ! $field_group->should_show() ) {
			return;
		}

		$field_group->index = 0;

		$field_group->peform_param_callback( 'before_group' );

		$desc      = $field_group->args( 'description' );
		$label     = $field_group->args( 'name' );
		$group_val = (array) $field_group->value();

		echo '<div class="cmb-row cmb-repeat-group-wrap ', esc_attr( $field_group->row_classes() ), '" data-fieldtype="group"><div class="cmb-td"><div data-groupid="', esc_attr( $field_group->id() ), '" id="', esc_attr( $field_group->id() ), '_repeat" ', $this->group_wrap_attributes( $field_group ), '>';

		if ( $desc || $label ) {
			$class = $desc ? ' cmb-group-description' : '';
			echo '<div class="cmb-row', $class, '"><div class="cmb-th">';
			if ( $label ) {
				echo '<h2 class="cmb-group-name">', $label, '</h2>';
			}
			if ( $desc ) {
				echo '<p class="cmb2-metabox-description">', $desc, '</p>';
			}
			echo '</div></div>';
		}

		if ( ! empty( $group_val ) ) {
			foreach ( $group_val as $group_key => $field_id ) {
				$this->render_group_row( $field_group );
				$field_group->index++;
			}
		} else {
			$this->render_group_row( $field_group );
		}

		if ( $field_group->args( 'repeatable' ) ) {
			echo '<div class="cmb-row"><div class="cmb-td"><p class="cmb-add-row"><button type="button" data-selector="', esc_attr( $field_group->id() ), '_repeat" data-grouptitle="', esc_attr( $field_group->options( 'group_title' ) ), '" class="cmb-add-group-row button-secondary">', $field_group->options( 'add_button' ), '</button></p></div></div>';
		}

		echo '</div></div></div>';

		$field_group->peform_param_callback( 'after_group' );

		return $field_group;
	}

	/**
	 * Get the group wrap attributes, which are passed through a filter.
	 *
	 * @since  2.2.3
	 * @param  CMB2_Field $field_group The group CMB2_Field object.
	 * @return string                  The attributes string.
	 */
	public function group_wrap_attributes( $field_group ) {
		$classes = 'cmb-nested cmb-field-list cmb-repeatable-group';
		$classes .= $field_group->options( 'sortable' ) ? ' sortable' : ' non-sortable';
		$classes .= $field_group->args( 'repeatable' ) ? ' repeatable' : ' non-repeatable';

		$group_wrap_attributes = array(
			'class' => $classes,
			'style' => 'width:100%;',
		);

		/**
		 * Allow for adding additional HTML attributes to a group wrapper.
		 *
		 * The attributes will be an array of key => value pairs for each attribute.
		 *
		 * @since 2.2.2
		 *
		 * @param string     $group_wrap_attributes Current attributes array.
		 * @param CMB2_Field $field_group           The group CMB2_Field object.
		 */
		$group_wrap_attributes = apply_filters( 'cmb2_group_wrap_attributes', $group_wrap_attributes, $field_group );

		$atts = array();
		foreach ( $group_wrap_attributes as $att => $att_value ) {
			if ( ! CMB2_Utils::is_data_attribute( $att ) ) {
				$att_value = htmlspecialchars( $att_value );
			}

			$atts[ sanitize_html_class( $att ) ] = sanitize_text_field( $att_value );
		}

		return CMB2_Utils::concat_attrs( $atts );
	}

	/**
	 * Render a repeatable group row
	 *
	 * @since  1.0.2
	 * @param  CMB2_Field $field_group     CMB2_Field group field object.
	 *
	 * @return CMB2
	 */
	public function render_group_row( $field_group ) {

		$field_group->peform_param_callback( 'before_group_row' );
		$closed_class     = $field_group->options( 'closed' ) ? ' closed' : '';
		$confirm_deletion = $field_group->options( 'remove_confirm' );
		$confirm_deletion = ! empty( $confirm_deletion ) ? $confirm_deletion : '';

		echo '
		<div id="cmb-group-', $field_group->id(), '-', $field_group->index, '" class="postbox cmb-row cmb-repeatable-grouping', $closed_class, '" data-iterator="', $field_group->index, '">';

		if ( $field_group->args( 'repeatable' ) ) {
			echo '<button type="button" data-selector="', $field_group->id(), '_repeat" data-confirm="', esc_attr( $confirm_deletion ), '" class="dashicons-before dashicons-no-alt cmb-remove-group-row" title="', esc_attr( $field_group->options( 'remove_button' ) ), '"></button>';
		}

			echo '
			<div class="cmbhandle" title="' , esc_attr__( 'Click to toggle', 'cmb2' ), '"><br></div>
			<h3 class="cmb-group-title cmbhandle-title"><span>', $field_group->replace_hash( $field_group->options( 'group_title' ) ), '</span></h3>

			<div class="inside cmb-td cmb-nested cmb-field-list">';
				// Loop and render repeatable group fields.
		foreach ( array_values( $field_group->args( 'fields' ) ) as $field_args ) {
			if ( 'hidden' === $field_args['type'] ) {

				// Save rendering for after the metabox.
				$this->add_hidden_field( $field_args, $field_group );

			} else {

				$field_args['show_names'] = $field_group->args( 'show_names' );
				$field_args['context']    = $field_group->args( 'context' );

				$this->get_field( $field_args, $field_group )->render_field();
			}
		}

		if ( $field_group->args( 'repeatable' ) ) {
			echo '
					<div class="cmb-row cmb-remove-field-row">
						<div class="cmb-remove-row">
							<button type="button" data-selector="', $field_group->id(), '_repeat" data-confirm="', esc_attr( $confirm_deletion ), '" class="cmb-remove-group-row cmb-remove-group-row-button alignright button-secondary">', $field_group->options( 'remove_button' ), '</button>
						</div>
					</div>
					';
		}
			echo '
			</div>
		</div>
		';

		$field_group->peform_param_callback( 'after_group_row' );

		return $this;
	}

	/**
	 * Add a hidden field to the list of hidden fields to be rendered later.
	 *
	 * @since 2.0.0
	 *
	 * @param array           $field_args  Array of field arguments to be passed to CMB2_Field.
	 * @param CMB2_Field|null $field_group CMB2_Field group field object.
	 * @return CMB2_Field
	 */
	public function add_hidden_field( $field_args, $field_group = null ) {
		if ( isset( $field_args['field_args'] ) ) {
			// For back-compatibility.
			$field = new CMB2_Field( $field_args );
		} else {
			$field = $this->get_new_field( $field_args, $field_group );
		}

		$types = new CMB2_Types( $field );

		if ( $field_group ) {
			$types->iterator = $field_group->index;
		}

		$this->hidden_fields[] = $types;

		return $field;
	}

	/**
	 * Loop through and output hidden fields
	 *
	 * @since  2.0.0
	 *
	 * @return CMB2
	 */
	public function render_hidden_fields() {
		if ( ! empty( $this->hidden_fields ) ) {
			foreach ( $this->hidden_fields as $hidden ) {
				$hidden->render();
			}
		}

		return $this;
	}

	/**
	 * Returns array of sanitized field values (without saving them)
	 *
	 * @since  2.0.3
	 * @param  array $data_to_sanitize Array of field_id => value data for sanitizing (likely $_POST data).
	 * @return mixed
	 */
	public function get_sanitized_values( array $data_to_sanitize ) {
		$this->data_to_save = $data_to_sanitize;
		$stored_id          = $this->object_id();

		// We do this So CMB will sanitize our data for us, but not save it.
		$this->object_id( '_' );

		// Ensure temp. data store is empty.
		cmb2_options( 0 )->set();

		// We want to get any taxonomy values back.
		add_filter( "cmb2_return_taxonomy_values_{$this->cmb_id}", '__return_true' );

		// Process/save fields.
		$this->process_fields();

		// Put things back the way they were.
		remove_filter( "cmb2_return_taxonomy_values_{$this->cmb_id}", '__return_true' );

		// Get data from temp. data store.
		$sanitized_values = cmb2_options( 0 )->get_options();

		// Empty out temp. data store again.
		cmb2_options( 0 )->set();

		// Reset the object id.
		$this->object_id( $stored_id );

		return $sanitized_values;
	}

	/**
	 * Loops through and saves field data
	 *
	 * @since  1.0.0
	 * @param  int    $object_id    Object ID.
	 * @param  string $object_type  Type of object being saved. (e.g., post, user, or comment).
	 * @param  array  $data_to_save Array of key => value data for saving. Likely $_POST data.
	 *
	 * @return CMB2
	 */
	public function save_fields( $object_id = 0, $object_type = '', $data_to_save = array() ) {

		// Fall-back to $_POST data.
		$this->data_to_save = ! empty( $data_to_save ) ? $data_to_save : $_POST;
		$object_id = $this->object_id( $object_id );
		$object_type = $this->object_type( $object_type );

		$this->process_fields();

		// If options page, save the updated options.
		if ( 'options-page' === $object_type ) {
			cmb2_options( $object_id )->set();
		}

		return $this->after_save();
	}

	/**
	 * Process and save form fields
	 *
	 * @since  2.0.0
	 *
	 * @return CMB2
	 */
	public function process_fields() {

		$this->pre_process();

		// Remove the show_on properties so saving works.
		$this->prop( 'show_on', array() );

		// save field ids of those that are updated.
		$this->updated = array();

		foreach ( $this->prop( 'fields' ) as $field_args ) {
			$this->process_field( $field_args );
		}

		return $this;
	}

	/**
	 * Process and save a field
	 *
	 * @since  2.0.0
	 * @param  array $field_args Array of field arguments.
	 *
	 * @return CMB2
	 */
	public function process_field( $field_args ) {

		switch ( $field_args['type'] ) {

			case 'group':
				if ( $this->save_group( $field_args ) ) {
					$this->updated[] = $field_args['id'];
				}

				break;

			case 'title':
				// Don't process title fields.
				break;

			default:
				$field = $this->get_new_field( $field_args );

				if ( $field->save_field_from_data( $this->data_to_save ) ) {
					$this->updated[] = $field->id();
				}

				break;
		}

		return $this;
	}

	/**
	 * Fires the "cmb2_{$object_type}_process_fields_{$cmb_id}" action hook.
	 *
	 * @since 2.2.2
	 *
	 * @return CMB2
	 */
	public function pre_process() {
		$object_type = $this->object_type();

		/**
		 * Fires before fields have been processed/saved.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the
		 * metabox/form's object type
		 *    Usually `post` (this applies to all post-types).
		 *    Could also be `comment`, `user` or `options-page`.
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * @param array $cmb       This CMB2 object
		 * @param int   $object_id The ID of the current object
		 */
		do_action( "cmb2_{$object_type}_process_fields_{$this->cmb_id}", $this, $this->object_id() );

		return $this;
	}

	/**
	 * Fires the "cmb2_save_{$object_type}_fields" and
	 * "cmb2_save_{$object_type}_fields_{$cmb_id}" action hooks.
	 *
	 * @since  2.x.x
	 *
	 * @return CMB2
	 */
	public function after_save() {
		$object_type = $this->object_type();
		$object_id   = $this->object_id();

		/**
		 * Fires after all fields have been saved.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the metabox/form's object type
		 * Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param array  $cmb_id      The current box ID
		 * @param string $updated     Array of field ids that were updated.
		 *                            Will only include field ids that had values change.
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_save_{$object_type}_fields", $object_id, $this->cmb_id, $this->updated, $this );

		/**
		 * Fires after all fields have been saved.
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the metabox/form's object type
		 * Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param string $updated     Array of field ids that were updated.
		 *                            Will only include field ids that had values change.
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_save_{$object_type}_fields_{$this->cmb_id}", $object_id, $this->updated, $this );

		return $this;
	}

	/**
	 * Save a repeatable group
	 *
	 * @since  1.x.x
	 * @param  array $args Field arguments array.
	 * @return mixed        Return of CMB2_Field::update_data().
	 */
	public function save_group( $args ) {
		if ( ! isset( $args['id'], $args['fields'] ) || ! is_array( $args['fields'] ) ) {
			return;
		}

		return $this->save_group_field( $this->get_new_field( $args ) );
	}

	/**
	 * Save a repeatable group
	 *
	 * @since  1.x.x
	 * @param  CMB2_Field $field_group CMB2_Field group field object.
	 * @return mixed                   Return of CMB2_Field::update_data().
	 */
	public function save_group_field( $field_group ) {
		$base_id = $field_group->id();

		if ( ! isset( $this->data_to_save[ $base_id ] ) ) {
			return;
		}

		$old        = $field_group->get_data();
		// Check if group field has sanitization_cb.
		$group_vals = $field_group->sanitization_cb( $this->data_to_save[ $base_id ] );
		$saved      = array();

		$field_group->index = 0;
		$field_group->data_to_save = $this->data_to_save;

		foreach ( array_values( $field_group->fields() ) as $field_args ) {
			if ( 'title' === $field_args['type'] ) {
				// Don't process title fields.
				continue;
			}

			$field  = $this->get_new_field( $field_args, $field_group );
			$sub_id = $field->id( true );
			if ( empty( $saved[ $field_group->index ] ) ) {
				$saved[ $field_group->index ] = array();
			}

			foreach ( (array) $group_vals as $field_group->index => $post_vals ) {

				// Get value.
				$new_val = isset( $group_vals[ $field_group->index ][ $sub_id ] )
					? $group_vals[ $field_group->index ][ $sub_id ]
					: false;

				// Sanitize.
				$new_val = $field->sanitization_cb( $new_val );

				if ( is_array( $new_val ) && $field->args( 'has_supporting_data' ) ) {
					if ( $field->args( 'repeatable' ) ) {
						$_new_val = array();
						foreach ( $new_val as $group_index => $grouped_data ) {
							// Add the supporting data to the $saved array stack.
							$saved[ $field_group->index ][ $grouped_data['supporting_field_id'] ][] = $grouped_data['supporting_field_value'];
							// Reset var to the actual value.
							$_new_val[ $group_index ] = $grouped_data['value'];
						}
						$new_val = $_new_val;
					} else {
						// Add the supporting data to the $saved array stack.
						$saved[ $field_group->index ][ $new_val['supporting_field_id'] ] = $new_val['supporting_field_value'];
						// Reset var to the actual value.
						$new_val = $new_val['value'];
					}
				}

				// Get old value.
				$old_val = is_array( $old ) && isset( $old[ $field_group->index ][ $sub_id ] )
					? $old[ $field_group->index ][ $sub_id ]
					: false;

				$is_updated = ( ! CMB2_Utils::isempty( $new_val ) && $new_val !== $old_val );
				$is_removed = ( CMB2_Utils::isempty( $new_val ) && ! CMB2_Utils::isempty( $old_val ) );

				// Compare values and add to `$updated` array.
				if ( $is_updated || $is_removed ) {
					$this->updated[] = $base_id . '::' . $field_group->index . '::' . $sub_id;
				}

				// Add to `$saved` array.
				$saved[ $field_group->index ][ $sub_id ] = $new_val;

			}// End foreach.

			$saved[ $field_group->index ] = CMB2_Utils::filter_empty( $saved[ $field_group->index ] );
		}// End foreach.

		$saved = CMB2_Utils::filter_empty( $saved );

		return $field_group->update_data( $saved, true );
	}

	/**
	 * Get object id from global space if no id is provided
	 *
	 * @since  1.0.0
	 * @param  integer|string $object_id Object ID.
	 * @return integer|string $object_id Object ID.
	 */
	public function object_id( $object_id = 0 ) {
		global $pagenow;

		if ( $object_id ) {
			$this->object_id = $object_id;
			return $this->object_id;
		}

		if ( $this->object_id ) {
			return $this->object_id;
		}

		// Try to get our object ID from the global space.
		switch ( $this->object_type() ) {
			case 'user':
				$object_id = isset( $_REQUEST['user_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user_id'] ) ) : $object_id;
				$object_id = ! $object_id && 'user-new.php' !== $pagenow && isset( $GLOBALS['user_ID'] ) ? $GLOBALS['user_ID'] : $object_id;
				break;

			case 'comment':
				$object_id = isset( $_REQUEST['c'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['c'] ) ) : $object_id;
				$object_id = ! $object_id && isset( $GLOBALS['comments']->comment_ID ) ? $GLOBALS['comments']->comment_ID : $object_id;
				break;

			case 'term':
				$object_id = isset( $_REQUEST['tag_ID'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tag_ID'] ) ) : $object_id;
				break;

			case 'options-page':
				$key = $this->doing_options_page();
				if ( ! empty( $key ) ) {
					$object_id = $key;
				}
				break;

			default:
				$object_id = isset( $GLOBALS['post']->ID ) ? $GLOBALS['post']->ID : $object_id;
				$object_id = isset( $_REQUEST['post'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post'] ) ) : $object_id;
				break;
		}

		// reset to id or 0.
		$this->object_id = $object_id ? $object_id : 0;

		return $this->object_id;
	}

	/**
	 * Sets the $object_type based on metabox settings
	 *
	 * @since  1.0.0
	 * @return string Object type.
	 */
	public function mb_object_type() {
		if ( null !== $this->mb_object_type ) {
			return $this->mb_object_type;
		}

		if ( $this->is_options_page_mb() ) {
			$this->mb_object_type = 'options-page';
			return $this->mb_object_type;
		}

		$registered_types = $this->box_types();

		$type = '';

		// if it's an array of one, extract it.
		if ( 1 === count( $registered_types ) ) {
			$last = end( $registered_types );
			if ( is_string( $last ) ) {
				$type = $last;
			}
		} elseif ( ( $curr_type = $this->current_object_type() ) && in_array( $curr_type, $registered_types, true ) ) {
			$type = $curr_type;
		}

		// Get our object type.
		switch ( $type ) {

			case 'user':
			case 'comment':
			case 'term':
				$this->mb_object_type = $type;
				break;

			default:
				$this->mb_object_type = 'post';
				break;
		}

		return $this->mb_object_type;
	}

	/**
	 * Gets the box 'object_types' array based on box settings.
	 *
	 * @since  2.2.3
	 * @param  array $fallback Fallback value.
	 *
	 * @return array Object types.
	 */
	public function box_types( $fallback = array() ) {
		return CMB2_Utils::ensure_array( $this->prop( 'object_types' ), $fallback );
	}

	/**
	 * Initates the object types and option key for an options page metabox.
	 *
	 * @since  2.2.5
	 *
	 * @return void
	 */
	public function init_options_mb() {
		$keys  = $this->options_page_keys();
		$types = $this->box_types();

		if ( empty( $keys ) ) {
			$keys = '';
			$types = $this->deinit_options_mb( $types );
		} else {

			// Make sure 'options-page' is one of the object types.
			$types[] = 'options-page';
		}

		// Set/Reset the option_key property.
		$this->set_prop( 'option_key', $keys );

		// Reset the object types.
		$this->set_prop( 'object_types', array_unique( $types ) );
	}

	/**
	 * If object-page initiation failed, remove traces options page setup.
	 *
	 * @since  2.2.5
	 *
	 * @param array $types Array of types.
	 * @return array
	 */
	protected function deinit_options_mb( $types ) {
		if ( isset( $this->meta_box['show_on']['key'] ) && 'options-page' === $this->meta_box['show_on']['key'] ) {
			unset( $this->meta_box['show_on']['key'] );
		}

		if ( array_key_exists( 'options-page', $this->meta_box['show_on'] ) ) {
			unset( $this->meta_box['show_on']['options-page'] );
		}

		$index = array_search( 'options-page', $types );

		if ( false !== $index ) {
			unset( $types[ $index ] );
		}

		return $types;
	}

	/**
	 * Determines if metabox is for an options page
	 *
	 * @since  1.0.1
	 * @return boolean True/False.
	 */
	public function is_options_page_mb() {
		return (
			// 'show_on' values checked for back-compatibility.
			$this->is_old_school_options_page_mb()
			|| in_array( 'options-page', $this->box_types() )
		);
	}

	/**
	 * Determines if metabox uses old-schoold options page config.
	 *
	 * @since  2.2.5
	 * @return boolean True/False.
	 */
	public function is_old_school_options_page_mb() {
		return (
			// 'show_on' values checked for back-compatibility.
			isset( $this->meta_box['show_on']['key'] ) && 'options-page' === $this->meta_box['show_on']['key']
			|| array_key_exists( 'options-page', $this->meta_box['show_on'] )
		);
	}

	/**
	 * Determine if we are on an options page (or saving the options page).
	 *
	 * @since  2.2.5
	 *
	 * @return bool
	 */
	public function doing_options_page() {
		$found_key = false;
		$keys = $this->options_page_keys();

		if ( empty( $keys ) ) {
			return $found_key;
		}

		if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], $keys ) ) {
			$found_key = $_GET['page'];
		}

		if ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $keys ) ) {
			$found_key = $_POST['action'];
		}

		return $found_key ? $found_key : false;
	}

	/**
	 * Get the options page key.
	 *
	 * @since  2.2.5
	 * @return string|array
	 */
	public function options_page_keys() {
		$key = '';
		if ( ! $this->is_options_page_mb() ) {
			return $key;
		}

		$values = null;
		if ( ! empty( $this->meta_box['show_on']['value'] ) ) {
			$values = $this->meta_box['show_on']['value'];
		} elseif ( ! empty( $this->meta_box['show_on']['options-page'] ) ) {
			$values = $this->meta_box['show_on']['options-page'];
		} elseif ( $this->prop( 'option_key' ) ) {
			$values = $this->prop( 'option_key' );
		}

		if ( $values ) {
			$key = $values;
		}

		if ( ! is_array( $key ) ) {
			$key = array( $key );
		}

		return $key;
	}

	/**
	 * Returns the object type
	 *
	 * @since  1.0.0
	 * @param string $object_type Type of object being saved. (e.g., post, user, or comment). Optional.
	 * @return string Object type.
	 */
	public function object_type( $object_type = '' ) {
		if ( $object_type ) {
			$this->object_type = $object_type;
			return $this->object_type;
		}

		if ( $this->object_type ) {
			return $this->object_type;
		}

		$this->object_type = $this->current_object_type();

		return $this->object_type;
	}

	/**
	 * Get the object type for the current page, based on the $pagenow global.
	 *
	 * @since  2.2.2
	 * @return string  Page object type name.
	 */
	public function current_object_type() {
		global $pagenow;
		$type = 'post';

		if ( in_array( $pagenow, array( 'user-edit.php', 'profile.php', 'user-new.php' ), true ) ) {
			$type = 'user';
		}

		if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php' ), true ) ) {
			$type = 'comment';
		}

		if ( in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
			$type = 'term';
		}

		if ( defined( 'DOING_AJAX' ) && isset( $_POST['action'] ) && 'add-tag' === $_POST['action'] ) {
			$type = 'term';
		}

		if (
			in_array( $pagenow, array( 'admin.php', 'admin-post.php' ), true )
			&& $this->doing_options_page()
		) {
			$type = 'options-page';
		}

		return $type;
	}

	/**
	 * Set metabox property.
	 *
	 * @since  2.2.2
	 * @param  string $property Metabox config property to retrieve.
	 * @param  mixed  $value    Value to set if no value found.
	 * @return mixed            Metabox config property value or false.
	 */
	public function set_prop( $property, $value ) {
		$this->meta_box[ $property ] = $value;

		return $this->prop( $property );
	}

	/**
	 * Get metabox property and optionally set a fallback
	 *
	 * @since  2.0.0
	 * @param  string $property Metabox config property to retrieve.
	 * @param  mixed  $fallback Fallback value to set if no value found.
	 * @return mixed            Metabox config property value or false.
	 */
	public function prop( $property, $fallback = null ) {
		if ( array_key_exists( $property, $this->meta_box ) ) {
			return $this->meta_box[ $property ];
		} elseif ( $fallback ) {
			return $this->meta_box[ $property ] = $fallback;
		}
	}

	/**
	 * Get a field object
	 *
	 * @since  2.0.3
	 * @param  string|array|CMB2_Field $field        Metabox field id or field config array or CMB2_Field object.
	 * @param  CMB2_Field|null         $field_group  (optional) CMB2_Field object (group parent).
	 * @param  bool                    $reset_cached (optional) Reset the internal cache for this field object.
	 *                                               Use sparingly.
	 *
	 * @return CMB2_Field|false                     CMB2_Field object (or false).
	 */
	public function get_field( $field, $field_group = null, $reset_cached = false ) {
		if ( $field instanceof CMB2_Field ) {
			return $field;
		}

		$field_id = is_string( $field ) ? $field : $field['id'];

		$parent_field_id = ! empty( $field_group ) ? $field_group->id() : '';
		$ids = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		$index = implode( '', $ids ) . ( $field_group ? $field_group->index : '' );

		if ( array_key_exists( $index, $this->fields ) && ! $reset_cached ) {
			return $this->fields[ $index ];
		}

		$this->fields[ $index ] = new CMB2_Field( $this->get_field_args( $field_id, $field, $sub_field_id, $field_group ) );

		return $this->fields[ $index ];
	}

	/**
	 * Handles determining which type of arguments to pass to CMB2_Field
	 *
	 * @since  2.0.7
	 * @param  mixed           $field_id     Field (or group field) ID.
	 * @param  mixed           $field_args   Array of field arguments.
	 * @param  mixed           $sub_field_id Sub field ID (if field_group exists).
	 * @param  CMB2_Field|null $field_group  If a sub-field, will be the parent group CMB2_Field object.
	 * @return array                         Array of CMB2_Field arguments.
	 */
	public function get_field_args( $field_id, $field_args, $sub_field_id, $field_group ) {

		// Check if group is passed and if fields were added in the old-school fields array.
		if ( $field_group && ( $sub_field_id || 0 === $sub_field_id ) ) {

			// Update the fields array w/ any modified properties inherited from the group field.
			$this->meta_box['fields'][ $field_id ]['fields'][ $sub_field_id ] = $field_args;

			return $this->get_default_args( $field_args, $field_group );
		}

		if ( is_array( $field_args ) ) {
			$this->meta_box['fields'][ $field_id ] = array_merge( $field_args, $this->meta_box['fields'][ $field_id ] );
		}

		return $this->get_default_args( $this->meta_box['fields'][ $field_id ] );
	}

	/**
	 * Get default field arguments specific to this CMB2 object.
	 *
	 * @since  2.2.0
	 * @param  array      $field_args  Metabox field config array.
	 * @param  CMB2_Field $field_group (optional) CMB2_Field object (group parent).
	 * @return array                   Array of field arguments.
	 */
	protected function get_default_args( $field_args, $field_group = null ) {
		if ( $field_group ) {
			$args = array(
				'field_args'  => $field_args,
				'group_field' => $field_group,
			);
		} else {
			$args = array(
				'field_args'  => $field_args,
				'object_type' => $this->object_type(),
				'object_id'   => $this->object_id(),
				'cmb_id'      => $this->cmb_id,
			);
		}

		return $args;
	}

	/**
	 * When fields are added in the old-school way, intitate them as they should be
	 *
	 * @since 2.1.0
	 * @param array $fields          Array of fields to add.
	 * @param mixed $parent_field_id Parent field id or null.
	 *
	 * @return CMB2
	 */
	protected function add_fields( $fields, $parent_field_id = null ) {
		foreach ( $fields as $field ) {

			$sub_fields = false;
			if ( array_key_exists( 'fields', $field ) ) {
				$sub_fields = $field['fields'];
				unset( $field['fields'] );
			}

			$field_id = $parent_field_id
				? $this->add_group_field( $parent_field_id, $field )
				: $this->add_field( $field );

			if ( $sub_fields ) {
				$this->add_fields( $sub_fields, $field_id );
			}
		}

		return $this;
	}

	/**
	 * Add a field to the metabox
	 *
	 * @since  2.0.0
	 * @param  array $field    Metabox field config array.
	 * @param  int   $position (optional) Position of metabox. 1 for first, etc.
	 * @return string|false    Field id or false.
	 */
	public function add_field( array $field, $position = 0 ) {
		if ( ! array_key_exists( 'id', $field ) ) {
			return false;
		}

		$this->_add_field_to_array(
			$field,
			$this->meta_box['fields'],
			$position
		);

		return $field['id'];
	}

	/**
	 * Add a field to a group
	 *
	 * @since  2.0.0
	 * @param  string $parent_field_id The field id of the group field to add the field.
	 * @param  array  $field           Metabox field config array.
	 * @param  int    $position        (optional) Position of metabox. 1 for first, etc.
	 * @return mixed                   Array of parent/field ids or false.
	 */
	public function add_group_field( $parent_field_id, array $field, $position = 0 ) {
		if ( ! array_key_exists( $parent_field_id, $this->meta_box['fields'] ) ) {
			return false;
		}

		$parent_field = $this->meta_box['fields'][ $parent_field_id ];

		if ( 'group' !== $parent_field['type'] ) {
			return false;
		}

		if ( ! isset( $parent_field['fields'] ) ) {
			$this->meta_box['fields'][ $parent_field_id ]['fields'] = array();
		}

		$this->_add_field_to_array(
			$field,
			$this->meta_box['fields'][ $parent_field_id ]['fields'],
			$position
		);

		return array( $parent_field_id, $field['id'] );
	}

	/**
	 * Perform some field-type-specific initiation actions.
	 *
	 * @since  2.7.0
	 * @param  array $field Metabox field config array.
	 * @return void
	 */
	protected function field_actions( $field ) {
		switch ( $field['type'] ) {
			case 'file':
			case 'file_list':

				// Initiate attachment JS hooks.
				add_filter( 'wp_prepare_attachment_for_js', array( 'CMB2_Type_File_Base', 'prepare_image_sizes_for_js' ), 10, 3 );
				break;

			case 'oembed':
				// Initiate oembed Ajax hooks.
				cmb2_ajax();
				break;

			case 'group':
				if ( empty( $field['render_row_cb'] ) ) {
					$field['render_row_cb'] = array( $this, 'render_group_callback' );
				}
				break;
			case 'colorpicker':

				// https://github.com/JayWood/CMB2_RGBa_Picker
				// Dequeue the rgba_colorpicker custom field script if it is used,
				// since we now enqueue our own more current version.
				add_action( 'admin_enqueue_scripts', array( 'CMB2_Type_Colorpicker', 'dequeue_rgba_colorpicker_script' ), 99 );
				break;
		}

		if ( isset( $field['column'] ) && false !== $field['column'] ) {
			$field = $this->define_field_column( $field );
		}

		if ( isset( $field['taxonomy'] ) && ! empty( $field['remove_default'] ) ) {
			$this->tax_metaboxes_to_remove[ $field['taxonomy'] ] = $field['taxonomy'];
		}

		return $field;
	}

	/**
	 * Defines a field's column if requesting to be show in admin columns.
	 *
	 * @since  2.2.3
	 * @param  array $field Metabox field config array.
	 * @return array         Modified metabox field config array.
	 */
	protected function define_field_column( array $field ) {
		$this->has_columns = true;

		$column = is_array( $field['column'] ) ? $field['column'] : array();

		$field['column'] = wp_parse_args( $column, array(
			'name'     => isset( $field['name'] ) ? $field['name'] : '',
			'position' => false,
		) );

		return $field;
	}

	/**
	 * Add a field array to a fields array in desired position
	 *
	 * @since 2.0.2
	 * @param array   $field    Metabox field config array.
	 * @param array   $fields   Array (passed by reference) to append the field (array) to.
	 * @param integer $position Optionally specify a position in the array to be inserted.
	 */
	protected function _add_field_to_array( $field, &$fields, $position = 0 ) {
		$field = $this->field_actions( $field );

		if ( $position ) {
			CMB2_Utils::array_insert( $fields, array( $field['id'] => $field ), $position );
		} else {
			$fields[ $field['id'] ] = $field;
		}
	}

	/**
	 * Remove a field from the metabox
	 *
	 * @since 2.0.0
	 * @param  string $field_id        The field id of the field to remove.
	 * @param  string $parent_field_id (optional) The field id of the group field to remove field from.
	 * @return bool                    True if field was removed.
	 */
	public function remove_field( $field_id, $parent_field_id = '' ) {
		$ids = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		unset( $this->fields[ implode( '', $ids ) ] );

		if ( ! $sub_field_id ) {
			unset( $this->meta_box['fields'][ $field_id ] );
			return true;
		}

		if ( isset( $this->fields[ $field_id ]->args['fields'][ $sub_field_id ] ) ) {
			unset( $this->fields[ $field_id ]->args['fields'][ $sub_field_id ] );
		}
		if ( isset( $this->meta_box['fields'][ $field_id ]['fields'][ $sub_field_id ] ) ) {
			unset( $this->meta_box['fields'][ $field_id ]['fields'][ $sub_field_id ] );
		}

		return true;
	}

	/**
	 * Update or add a property to a field
	 *
	 * @since  2.0.0
	 * @param  string $field_id        Field id.
	 * @param  string $property        Field property to set/update.
	 * @param  mixed  $value           Value to set the field property.
	 * @param  string $parent_field_id (optional) The field id of the group field to remove field from.
	 * @return mixed                   Field id. Strict compare to false, as success can return a falsey value (like 0).
	 */
	public function update_field_property( $field_id, $property, $value, $parent_field_id = '' ) {
		$ids = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		if ( ! $sub_field_id ) {
			$this->meta_box['fields'][ $field_id ][ $property ] = $value;
			return $field_id;
		}

		$this->meta_box['fields'][ $field_id ]['fields'][ $sub_field_id ][ $property ] = $value;
		return $field_id;
	}

	/**
	 * Check if field ids match a field and return the index/field id
	 *
	 * @since  2.0.2
	 * @param  string $field_id        Field id.
	 * @param  string $parent_field_id (optional) Parent field id.
	 * @return mixed                    Array of field/parent ids, or false.
	 */
	public function get_field_ids( $field_id, $parent_field_id = '' ) {
		$sub_field_id = $parent_field_id ? $field_id : '';
		$field_id     = $parent_field_id ? $parent_field_id : $field_id;
		$fields       =& $this->meta_box['fields'];

		if ( ! array_key_exists( $field_id, $fields ) ) {
			$field_id = $this->search_old_school_array( $field_id, $fields );
		}

		if ( false === $field_id ) {
			return false;
		}

		if ( ! $sub_field_id ) {
			return array( $field_id, $sub_field_id );
		}

		if ( 'group' !== $fields[ $field_id ]['type'] ) {
			return false;
		}

		if ( ! array_key_exists( $sub_field_id, $fields[ $field_id ]['fields'] ) ) {
			$sub_field_id = $this->search_old_school_array( $sub_field_id, $fields[ $field_id ]['fields'] );
		}

		return false === $sub_field_id ? false : array( $field_id, $sub_field_id );
	}

	/**
	 * When using the old array filter, it is unlikely field array indexes will be the field id.
	 *
	 * @since  2.0.2
	 * @param  string $field_id The field id.
	 * @param  array  $fields   Array of fields to search.
	 * @return mixed            Field index or false.
	 */
	public function search_old_school_array( $field_id, $fields ) {
		$ids = wp_list_pluck( $fields, 'id' );
		$index = array_search( $field_id, $ids );
		return false !== $index ? $index : false;
	}

	/**
	 * Handles metabox property callbacks, and passes this $cmb object as property.
	 *
	 * @since 2.2.3
	 * @param  callable $cb                The callback method/function/closure.
	 * @param  mixed    $additional_params Any additoinal parameters which should be passed to the callback.
	 * @return mixed                       Return of the callback function.
	 */
	public function do_callback( $cb, $additional_params = null ) {
		return call_user_func( $cb, $this, $additional_params );
	}

	/**
	 * Generate a unique nonce field for each registered meta_box
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function nonce_field() {
		wp_nonce_field( $this->nonce(), $this->nonce(), false, true );
	}

	/**
	 * Generate a unique nonce for each registered meta_box
	 *
	 * @since  2.0.0
	 * @return string unique nonce string.
	 */
	public function nonce() {
		if ( ! $this->generated_nonce ) {
			$this->generated_nonce = sanitize_html_class( 'nonce_' . basename( __FILE__ ) . $this->cmb_id );
		}

		return $this->generated_nonce;
	}

	/**
	 * Checks if field-saving updated any fields.
	 *
	 * @since  2.2.5
	 *
	 * @return bool
	 */
	public function was_updated() {
		return ! empty( $this->updated );
	}

	/**
	 * Whether this box is an "alternate context" box. This means the box has a 'context' property defined as:
	 * 'form_top', 'before_permalink', 'after_title', or 'after_editor'.
	 *
	 * @since  2.2.4
	 * @return bool
	 */
	public function is_alternate_context_box() {
		return $this->prop( 'context' ) && in_array( $this->prop( 'context' ), array( 'form_top', 'before_permalink', 'after_title', 'after_editor' ), true );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param  string $property Object property.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			case 'updated':
			case 'has_columns':
			case 'tax_metaboxes_to_remove':
				return $this->{$property};
			default:
				return parent::__get( $property );
		}
	}

}
