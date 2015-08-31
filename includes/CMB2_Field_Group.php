<?php

/**
 * Class CMB2_Field_Group
 */
abstract class CMB2_Field_Group {

	/**
	 * Object ID for metabox meta retrieving/saving
	 *
	 * @var   mixed
	 * @since 1.0.0
	 */
	protected $object_id = 0;

	/**
	 * List of fields that are changed/updated on save
	 *
	 * @var   array
	 * @since 1.1.0
	 */
	protected $updated = array();

	/**
	 * Metabox field objects
	 *
	 * @var   array
	 * @since 2.0.3
	 */
	protected $fields = array();

	protected $fields_array = array();

	/**
	 * An array of hidden fields to output at the end of the form
	 *
	 * @var   CMB2_Types[]
	 * @since 2.0.0
	 */
	protected $hidden_fields = array();

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	public $data_to_save = array();

	/**
	 * Render a repeatable group
	 *
	 * @param array $args Array of field arguments for a group field parent
	 */
	public function render_group( $args ) {

		if ( ! isset( $args[ 'id' ], $args[ 'fields' ] ) || ! is_array( $args[ 'fields' ] ) ) {
			return;
		}

		$field_group = $this->get_field( $args );

		// If field is requesting to be conditionally shown
		if ( ! $field_group || ! $field_group->should_show() ) {
			return;
		}

		$desc               = $field_group->args( 'description' );
		$label              = $field_group->args( 'name' );
		$sortable           = $field_group->options( 'sortable' ) ? ' sortable' : ' non-sortable';
		$repeat_class       = $field_group->args( 'repeatable' ) ? ' repeatable' : ' non-repeatable';
		$group_val          = (array) $field_group->value();
		$nrows              = count( $group_val );
		$remove_disabled    = $nrows <= 1 ? 'disabled="disabled" ' : '';
		$field_group->index = 0;

		$field_group->peform_param_callback( 'before_group' );

		echo '<div class="cmb-row cmb-repeat-group-wrap"><div class="cmb-td"><div id="', $field_group->id(), '_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group', $sortable, $repeat_class, '" style="width:100%;">';

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
				$this->render_group_row( $field_group, $remove_disabled );
				$field_group->index ++;
			}
		} else {
			$this->render_group_row( $field_group, $remove_disabled );
		}

		if ( $field_group->args( 'repeatable' ) ) {
			echo '<div class="cmb-row"><div class="cmb-td"><p class="cmb-add-row"><button data-selector="', $field_group->id(), '_repeat" data-grouptitle="', $field_group->options( 'group_title' ), '" class="cmb-add-group-row button">', $field_group->options( 'add_button' ), '</button></p></div></div>';
		}

		echo '</div></div></div>';

		$field_group->peform_param_callback( 'after_group' );

	}

	/**
	 * Render a repeatable group row
	 *
	 * @since  1.0.2
	 *
	 * @param  CMB2_Field $field_group     CMB2_Field group field object
	 * @param  string     $remove_disabled Attribute string to disable the
	 *                                     remove button
	 */
	public function render_group_row( $field_group, $remove_disabled ) {

		$field_group->peform_param_callback( 'before_group_row' );
		$closed_class = $field_group->options( 'closed' ) ? ' closed' : '';

		echo '
		<div class="postbox cmb-row cmb-repeatable-grouping', $closed_class, '" data-iterator="', $field_group->index, '">';

		if ( $field_group->args( 'repeatable' ) ) {
			echo '<button ', $remove_disabled, 'data-selector="', $field_group->id(), '_repeat" class="dashicons-before dashicons-no-alt cmb-remove-group-row"></button>';
		}

		echo '
			<div class="cmbhandle" title="', __( 'Click to toggle', 'cmb2' ), '"><br></div>
			<h3 class="cmb-group-title cmbhandle-title"><span>', $field_group->replace_hash( $field_group->options( 'group_title' ) ), '</span></h3>

			<div class="inside cmb-td cmb-nested cmb-field-list">';
		// Loop and render repeatable group fields
		foreach ( array_values( $field_group->args( 'fields' ) ) as $field_args ) {
			if ( 'hidden' == $field_args[ 'type' ] ) {

				// Save rendering for after the metabox
				$this->add_hidden_field( array(
					'field_args'  => $field_args,
					'group_field' => $field_group,
				) );

			} else {

				$field_args[ 'show_names' ] = $field_group->args( 'show_names' );
				$field_args[ 'context' ]    = $field_group->args( 'context' );

				$field = $this->get_field( $field_args, $field_group )->render_field();
			}
		}
		if ( $field_group->args( 'repeatable' ) ) {
			echo '
					<div class="cmb-row cmb-remove-field-row">
						<div class="cmb-remove-row">
							<button ', $remove_disabled, 'data-selector="', $field_group->id(), '_repeat" class="button cmb-remove-group-row alignright">', $field_group->options( 'remove_button' ), '</button>
						</div>
					</div>
					';
		}
		echo '
			</div>
		</div>
		';

		$field_group->peform_param_callback( 'after_group_row' );
	}

	/**
	 * Add a hidden field to the list of hidden fields to be rendered later
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Array of arguments to be passed to CMB2_Field
	 */
	public function add_hidden_field( $args ) {

		$this->hidden_fields[] = new CMB2_Types( new CMB2_Field( $args ) );
	}

	/**
	 * Loop through and output hidden fields
	 *
	 * @since  2.0.0
	 */
	public function render_hidden_fields() {

		if ( ! empty( $this->hidden_fields ) ) {
			foreach ( $this->hidden_fields as $hidden ) {
				$hidden->render();
			}
		}
	}

	/**
	 * Returns array of sanitized field values (without saving them)
	 *
	 * @since  2.0.3
	 *
	 * @param  array $data_to_sanitize Array of field_id => value data for
	 *                                 sanitizing (likely $_POST data).
	 *
	 * @return mixed
	 */
	public function get_sanitized_values( array $data_to_sanitize ) {

		$this->data_to_save = $data_to_sanitize;
		$stored_id          = $this->object_id();

		// We do this So CMB will sanitize our data for us, but not save it
		$this->object_id( '_' );

		// Ensure temp. data store is empty
		cmb2_options( 0 )->set();

		// Process/save fields
		$this->process_fields();

		// Get data from temp. data store
		$sanitized_values = cmb2_options( 0 )->get_options();

		// Empty out temp. data store again
		cmb2_options( 0 )->set();

		// Reset the object id
		$this->object_id( $stored_id );

		return $sanitized_values;
	}

	/**
	 * Loops through and saves field data
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $object_id    Object ID
	 * @param  string $object_type  Type of object being saved. (e.g., post,
	 *                              user, or comment)
	 * @param  array  $data_to_save Array of key => value data for saving.
	 *                              Likely $_POST data.
	 */
	public function save_fields( $object_id = 0, $object_type = '', $data_to_save = array() ) {

		// Fall-back to $_POST data
		$this->data_to_save = ! empty( $data_to_save ) ? $data_to_save : $_POST;
		$object_id          = $this->object_id( $object_id );
		$object_type        = $this->object_type( $object_type );

		$this->process_fields();

		// If options page, save the updated options
		if ( 'options-page' == $object_type ) {
			cmb2_options( $object_id )->set();
		}

		/**
		 * Fires after all fields have been saved.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the metabox/form's object type
		 *    Usually `post` (this applies to all post-types).
		 *    Could also be `comment`, `user` or `options-page`.
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
		 *    Usually `post` (this applies to all post-types).
		 *    Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param string $updated     Array of field ids that were updated.
		 *                            Will only include field ids that had values change.
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_save_{$object_type}_fields_{$this->cmb_id}", $object_id, $this->updated, $this );

	}

	/**
	 * Process and save form fields
	 *
	 * @since  2.0.0
	 */
	public function process_fields() {

		/**
		 * Fires before fields have been processed/saved.
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * The dynamic portion of the hook name, $object_type, refers to the metabox/form's object type
		 *    Usually `post` (this applies to all post-types).
		 *    Could also be `comment`, `user` or `options-page`.
		 *
		 * @param array $cmb       This CMB2 object
		 * @param int   $object_id The ID of the current object
		 */
		do_action( "cmb2_{$this->object_type()}_process_fields_{$this->cmb_id}", $this, $this->object_id() );

		// Remove the show_on properties so saving works
		$this->prop( 'show_on', array() );

		// save field ids of those that are updated
		$this->updated = array();

		foreach ( $this->prop( 'fields' ) as $field_args ) {
			$this->process_field( $field_args );
		}
	}

	/**
	 * Process and save a field
	 *
	 * @since  2.0.0
	 *
	 * @param  array $field_args Array of field arguments
	 */
	public function process_field( $field_args ) {

		switch ( $field_args[ 'type' ] ) {

			case 'group':
				$this->save_group( $field_args );
				break;

			case 'title':
				// Don't process title fields
				break;

			default:

				// Save default fields
				$field = new CMB2_Field( array(
					'field_args'  => $field_args,
					'object_type' => $this->object_type(),
					'object_id'   => $this->object_id(),
				) );

				if ( $field->save_field_from_data( $this->data_to_save ) ) {
					$this->updated[] = $field->id();
				}

				break;
		}

	}

	/**
	 * Save a repeatable group
	 *
	 * @param array $args
	 */
	public function save_group( $args ) {

		if ( ! isset( $args[ 'id' ], $args[ 'fields' ], $this->data_to_save[ $args[ 'id' ] ] ) || ! is_array( $args[ 'fields' ] ) ) {
			return;
		}

		$field_group = new CMB2_Field( array(
			'field_args'  => $args,
			'object_type' => $this->object_type(),
			'object_id'   => $this->object_id(),
		) );
		$base_id     = $field_group->id();
		$old         = $field_group->get_data();
		// Check if group field has sanitization_cb
		$group_vals         = $field_group->sanitization_cb( $this->data_to_save[ $base_id ] );
		$saved              = array();
		$field_group->index = 0;

		foreach ( array_values( $field_group->fields() ) as $field_args ) {
			$field  = new CMB2_Field( array(
				'field_args'  => $field_args,
				'group_field' => $field_group,
			) );
			$sub_id = $field->id( true );

			foreach ( (array) $group_vals as $field_group->index => $post_vals ) {

				// Get value
				$new_val = isset( $group_vals[ $field_group->index ][ $sub_id ] )
					? $group_vals[ $field_group->index ][ $sub_id ]
					: false;

				// Sanitize
				$new_val = $field->sanitization_cb( $new_val );

				if ( 'file' == $field->type() && is_array( $new_val ) ) {
					// Add image ID to the array stack
					$saved[ $field_group->index ][ $new_val[ 'field_id' ] ] = $new_val[ 'attach_id' ];
					// Reset var to url string
					$new_val = $new_val[ 'url' ];
				}

				// Get old value
				$old_val = is_array( $old ) && isset( $old[ $field_group->index ][ $sub_id ] )
					? $old[ $field_group->index ][ $sub_id ]
					: false;

				$is_updated = ( ! empty( $new_val ) && $new_val != $old_val );
				$is_removed = ( empty( $new_val ) && ! empty( $old_val ) );
				// Compare values and add to `$updated` array
				if ( $is_updated || $is_removed ) {
					$this->updated[] = $base_id . '::' . $field_group->index . '::' . $sub_id;
				}

				// Add to `$saved` array
				$saved[ $field_group->index ][ $sub_id ] = $new_val;

			}
			$saved[ $field_group->index ] = array_filter( $saved[ $field_group->index ] );
		}
		$saved = array_filter( $saved );

		$field_group->update_data( $saved, true );
	}

	/**
	 * Get a field object
	 *
	 * @since  2.0.3
	 *
	 * @param  string|array|CMB2_Field $field       Metabox field id or field
	 *                                              config array or CMB2_Field
	 *                                              object
	 * @param  CMB2_Field              $field_group (optional) CMB2_Field
	 *                                              object (group parent)
	 *
	 * @return CMB2_Field|false CMB2_Field object (or false)
	 */
	public function get_field( $field, $field_group = null ) {

		if ( is_a( $field, 'CMB2_Field' ) ) {
			return $field;
		}

		$field_id = is_string( $field ) ? $field : $field[ 'id' ];

		$parent_field_id = ! empty( $field_group ) ? $field_group->id() : '';
		$ids             = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		$index = implode( '', $ids ) . ( $field_group ? $field_group->index : '' );
		if ( array_key_exists( $index, $this->fields ) ) {
			return $this->fields[ $index ];
		}

		$this->fields[ $index ] = new CMB2_Field( $this->get_field_args( $field_id, $field, $sub_field_id, $field_group ) );

		return $this->fields[ $index ];
	}

	/**
	 * Handles determining which type of arguments to pass to CMB2_Field
	 *
	 * @since  2.0.7
	 *
	 * @param  mixed $field_id     Field (or group field) ID
	 * @param  mixed $field_args   Array of field arguments
	 * @param  mixed $sub_field_id Sub field ID (if field_group exists)
	 * @param  mixed $field_group  If a sub-field, will be the parent group
	 *                             CMB2_Field object
	 *
	 * @return array                Array of CMB2_Field arguments
	 */
	public function get_field_args( $field_id, $field_args, $sub_field_id, $field_group ) {

		// Check if group is passed and if fields were added in the old-school fields array
		if ( $field_group && ( $sub_field_id || 0 === $sub_field_id ) ) {

			// Update the fields array w/ any modified properties inherited from the group field
			$this->meta_box[ 'fields' ][ $field_id ][ 'fields' ][ $sub_field_id ] = $field_args;

			return array(
				'field_args'  => $field_args,
				'group_field' => $field_group,
			);

		}

		if ( is_array( $field_args ) ) {
			$this->meta_box[ 'fields' ][ $field_id ] = array_merge( $field_args, $this->meta_box[ 'fields' ][ $field_id ] );
		}

		return array(
			'field_args'  => $this->meta_box[ 'fields' ][ $field_id ],
			'object_type' => $this->object_type(),
			'object_id'   => $this->object_id(),
		);
	}

	/**
	 * Add a field to the metabox
	 *
	 * @since  2.0.0
	 *
	 * @param  array $field    Metabox field config array
	 * @param  int   $position (optional) Position of metabox. 1 for first, etc
	 *
	 * @return mixed                   Field id or false
	 */
	public function add_field( array $field, $position = 0 ) {

		if ( ! is_array( $field ) || ! array_key_exists( 'id', $field ) ) {
			return false;
		}

		$this->_add_field_to_array(
			$field,
			$this->meta_box[ 'fields' ],
			$position
		);

		return $field[ 'id' ];
	}

	/**
	 * Add a field to a group
	 *
	 * @since  2.0.0
	 *
	 * @param  string $parent_field_id The field id of the group field to add
	 *                                 the field
	 * @param  array  $field           Metabox field config array
	 * @param  int    $position        (optional) Position of metabox. 1 for
	 *                                 first, etc
	 *
	 * @return mixed                   Array of parent/field ids or false
	 */
	public function add_group_field( $parent_field_id, array $field, $position = 0 ) {

		if ( ! array_key_exists( $parent_field_id, $this->meta_box[ 'fields' ] ) ) {
			return false;
		}

		$parent_field = $this->meta_box[ 'fields' ][ $parent_field_id ];

		if ( 'group' !== $parent_field[ 'type' ] ) {
			return false;
		}

		if ( ! isset( $parent_field[ 'fields' ] ) ) {
			$this->meta_box[ 'fields' ][ $parent_field_id ][ 'fields' ] = array();
		}

		$this->_add_field_to_array(
			$field,
			$this->meta_box[ 'fields' ][ $parent_field_id ][ 'fields' ],
			$position
		);

		return array( $parent_field_id, $field[ 'id' ] );
	}

	/**
	 * Add a field array to a fields array in desired position
	 *
	 * @since 2.0.2
	 *
	 * @param array   $field    Metabox field config array
	 * @param array   &$fields  Array (passed by reference) to append the field
	 *                          (array) to
	 * @param integer $position Optionally specify a position in the array to
	 *                          be inserted
	 */
	protected function _add_field_to_array( $field, &$fields, $position = 0 ) {

		if ( $position ) {
			cmb2_utils()->array_insert( $fields, array( $field[ 'id' ] => $field ), $position );
		} else {
			$fields[ $field[ 'id' ] ] = $field;
		}
	}

	/**
	 * Remove a field from the metabox
	 *
	 * @since 2.0.0
	 *
	 * @param  string $field_id        The field id of the field to remove
	 * @param  string $parent_field_id (optional) The field id of the group
	 *                                 field to remove field from
	 *
	 * @return bool                    True if field was removed
	 */
	public function remove_field( $field_id, $parent_field_id = '' ) {

		$ids = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		unset( $this->fields[ implode( '', $ids ) ] );

		if ( ! $sub_field_id ) {
			unset( $this->meta_box[ 'fields' ][ $field_id ] );

			return true;
		}

		unset( $this->fields[ $field_id ]->args[ 'fields' ][ $sub_field_id ] );
		unset( $this->meta_box[ 'fields' ][ $field_id ][ 'fields' ][ $sub_field_id ] );

		return true;
	}

	/**
	 * Update or add a property to a field
	 *
	 * @since  2.0.0
	 *
	 * @param  string $field_id        Field id
	 * @param  string $property        Field property to set/update
	 * @param  mixed  $value           Value to set the field property
	 * @param  string $parent_field_id (optional) The field id of the group
	 *                                 field to remove field from
	 *
	 * @return mixed                   Field id. Strict compare to false, as
	 *                                 success can return a falsey value (like
	 *                                 0)
	 */
	public function update_field_property( $field_id, $property, $value, $parent_field_id = '' ) {

		$ids = $this->get_field_ids( $field_id, $parent_field_id );

		if ( ! $ids ) {
			return false;
		}

		list( $field_id, $sub_field_id ) = $ids;

		if ( ! $sub_field_id ) {
			$this->meta_box[ 'fields' ][ $field_id ][ $property ] = $value;

			return $field_id;
		}

		$this->meta_box[ 'fields' ][ $field_id ][ 'fields' ][ $sub_field_id ][ $property ] = $value;

		return $field_id;
	}

	/**
	 * Check if field ids match a field and return the index/field id
	 *
	 * @since  2.0.2
	 *
	 * @param  string $field_id        Field id
	 * @param  string $parent_field_id (optional) Parent field id
	 *
	 * @return mixed                    Array of field/parent ids, or false
	 */
	public function get_field_ids( $field_id, $parent_field_id = '' ) {

		$sub_field_id = $parent_field_id ? $field_id : '';
		$field_id     = $parent_field_id ? $parent_field_id : $field_id;
		$fields       =& $this->meta_box[ 'fields' ];

		if ( ! array_key_exists( $field_id, $fields ) ) {
			$field_id = $this->search_old_school_array( $field_id, $fields );
		}

		if ( false === $field_id ) {
			return false;
		}

		if ( ! $sub_field_id ) {
			return array( $field_id, $sub_field_id );
		}

		if ( 'group' !== $fields[ $field_id ][ 'type' ] ) {
			return false;
		}

		if ( ! array_key_exists( $sub_field_id, $fields[ $field_id ][ 'fields' ] ) ) {
			$sub_field_id = $this->search_old_school_array( $sub_field_id, $fields[ $field_id ][ 'fields' ] );
		}

		return false === $sub_field_id ? false : array(
			$field_id,
			$sub_field_id
		);
	}

	/**
	 * Get object id from global space if no id is provided
	 *
	 * @since  1.0.0
	 *
	 * @param  integer $object_id Object ID
	 *
	 * @return integer $object_id Object ID
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

		// Try to get our object ID from the global space
		switch ( $this->object_type() ) {
			case 'user':
				$object_id = isset( $_REQUEST[ 'user_id' ] ) ? $_REQUEST[ 'user_id' ] : $object_id;
				$object_id = ! $object_id && 'user-new.php' != $pagenow && isset( $GLOBALS[ 'user_ID' ] ) ? $GLOBALS[ 'user_ID' ] : $object_id;
				break;

			case 'comment':
				$object_id = isset( $_REQUEST[ 'c' ] ) ? $_REQUEST[ 'c' ] : $object_id;
				$object_id = ! $object_id && isset( $GLOBALS[ 'comments' ]->comment_ID ) ? $GLOBALS[ 'comments' ]->comment_ID : $object_id;
				break;

			default:
				$object_id = isset( $GLOBALS[ 'post' ]->ID ) ? $GLOBALS[ 'post' ]->ID : $object_id;
				$object_id = isset( $_REQUEST[ 'post' ] ) ? $_REQUEST[ 'post' ] : $object_id;
				break;
		}

		// reset to id or 0
		$this->object_id = $object_id ? $object_id : 0;

		return $this->object_id;
	}

	/**
	 * When using the old array filter, it is unlikely field array indexes will
	 * be the field id
	 *
	 * @since  2.0.2
	 *
	 * @param  string $field_id The field id
	 * @param  array  $fields   Array of fields to search
	 *
	 * @return mixed            Field index or false
	 */
	public function search_old_school_array( $field_id, $fields ) {

		$ids   = wp_list_pluck( $fields, 'id' );
		$index = array_search( $field_id, $ids );

		return false !== $index ? $index : false;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {

		switch ( $field ) {
			case 'updated':
				return $this->{$field};
			case 'object_id':
				return $this->object_id();
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}