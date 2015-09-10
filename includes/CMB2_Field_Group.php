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
	 * @var   CMB2_Field[]
	 * @since 2.0.3
	 */
	protected $field_objects = array();

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
	 * @return null|string
	 */
	public function get_html_name_attribute() {

		$name = null;

		if ( $this->group ) {
			$parent_path = $this->group->get_html_name_attribute();
			$index = '[' . $this->group->index . ']';
			$id = '[' . $this->id() . ']';
			$name = $parent_path .  $index . $id;
		} else {
			$name = $this->id();
		}

		return $name;
	}

	/**
	 * @return null|string
	 */
	public function get_html_id_attribute() {

		$name = null;

		if ( $this->group ) {
			$parent_path = $this->group->get_html_id_attribute();
			$index = '_' . $this->group->index;
			$id = '_' . $this->id();
			$name = $parent_path .  $index . $id;
		} else {
			$name = $this->id();
		}

		return $name;
	}

	/**
	 * @return array
	 */
	public function get_updated() {

		return $this->updated;
	}

	/**
	 * @param array $updated
	 *
	 * @return array
	 */
	public function set_updated( $updated ) {

		$this->updated = $updated;
		return $updated;
	}

	/**
	 * @return CMB2_Types[]
	 */
	public function get_hidden_fields() {

		return $this->hidden_fields;
	}

	/**
	 * @param CMB2_Types[] $hidden_fields
	 *
	 * @return CMB2_Types[]
	 */
	public function set_hidden_fields( $hidden_fields ) {

		$this->hidden_fields = $hidden_fields;
		return $hidden_fields;
	}

	/**
	 * @return array
	 */
	public function get_data_to_save() {

		return $this->data_to_save;
	}

	/**
	 * @param array $data_to_save
	 *
	 * @return array
	 */
	public function set_data_to_save( $data_to_save ) {

		$this->data_to_save = $data_to_save;
		return $data_to_save;
	}

	public function get_field_data_to_save( $field_name ) {

		$field_data_to_save = null;

		if( isset( $this->data_to_save[ $field_name ] ) ) {
			$field_data_to_save = $this->data_to_save[ $field_name ];
		}

		return $field_data_to_save;
	}

	/**
	 * @return array
	 */
	public function get_fields() {

		$fields_array = array();
		foreach ( $this->get_field_objects() as $key => $field_object ) {

			$fields_array[ $key ] = $field_object->args();
			$nested_fields = array();
			foreach ( $field_object->args( 'fields' ) as $this_field ) {
				$nested_fields[ $this_field[ 'id' ] ] = $this_field;
			}
			$fields_array[ $key ] = array_merge( $fields_array[ $key ], array( 'fields' => $nested_fields) );
		}

		return $fields_array;
	}

	/**
	 * @param array $fields_array
	 *
	 * @return array
	 */
	public function set_fields( $fields_array ) {

		// Clear out any previously stored objects
		$this->field_objects = array();

		foreach( $fields_array as $field_params ) {
			$this->add_field( $field_params );
		}

		return $fields_array;
	}

	/**
	 * @return CMB2_Field[]
	 */
	public function get_field_objects() {

		return $this->field_objects;
	}

	/**
	 * @param CMB2_Field[] $object_array
	 *
	 * @return CMB2_Field[]
	 */
	public function set_field_objects( $object_array ) {

		$this->field_objects = $object_array;
		return $object_array;
	}

	/**
	 * @param string $field_id
	 *
	 * @return CMB2_Field|null
	 */
	public function get_field_object( $field_id ) {

		$field_object = null;

		if ( array_key_exists( $field_id, $this->field_objects ) ) {
			$field_object = $this->field_objects[ $field_id ];
		}

		return $field_object;
	}

	/**
	 * @param string     $field_id
	 * @param CMB2_Field $field_object
	 * @param int        $position Optionally specify a position in the array to be inserted
	 *
	 * @return CMB2_Field[]
	 */
	public function set_field_object( $field_id, $field_object, $position = 0 ) {

		if ( $position ) {
			cmb2_utils()->array_insert( $this->field_objects, array( $field_id => $field_object ), $position );
		} else {
			$this->field_objects[ $field_id ] = $field_object;
		}

		return $field_object;
	}

	/**
	 * @param string $field_id
	 */
	public function remove_field_object( $field_id ) {

		unset( $this->field_objects[ $field_id ] );
	}

	/**
	 * Render a repeatable group
	 *
	 * @param array|CMB2_Field $field Array of field arguments or a field
	 *                                object for a group field parent
	 */
	public function render_group( $field ) {

		// Get a field object if we weren't passed one
		if ( ! is_a( $field, 'CMB2_Field' ) ) {

			$field = (array) $field;
			if ( ! isset( $field[ 'id' ] ) ) {
				return;
			}

			$field = $this->get_field( $field );
		}

		// If field is requesting to be conditionally shown
		if ( ! $field || ! $field->should_show() ) {
			return;
		}

		$desc               = $field->args( 'description' );
		$label              = $field->args( 'name' );
		$sortable           = $field->options( 'sortable' ) ? ' sortable' : ' non-sortable';
		$repeat_class       = $field->args( 'repeatable' ) ? ' repeatable' : ' non-repeatable';
		$group_val          = (array) $field->value();
		$nrows              = count( $group_val );
		$remove_disabled    = $nrows <= 1 ? 'disabled="disabled" ' : '';
		$field->index = 0;

		$field->peform_param_callback( 'before_group' );

		echo '<div class="cmb-row cmb-repeat-group-wrap"><div class="cmb-td"><div id="', $field->get_html_id_attribute(), '_repeat" class="cmb-nested cmb-field-list cmb-repeatable-group', $sortable, $repeat_class, '" style="width:100%;">';

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
				$this->render_group_row( $field, $remove_disabled );
				$field->index ++;
			}
		} else {
			$this->render_group_row( $field, $remove_disabled );
		}

		if ( $field->args( 'repeatable' ) ) {
			echo '<div class="cmb-row"><div class="cmb-td"><p class="cmb-add-row"><button data-selector="', $field->get_html_id_attribute(), '_repeat" data-grouptitle="', $field->options( 'group_title' ), '" class="cmb-add-group-row button">', $field->options( 'add_button' ), '</button></p></div></div>';
		}

		echo '</div></div></div>';

		$field->peform_param_callback( 'after_group' );

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
				echo '<button ', $remove_disabled, 'data-selector="', $field_group->get_html_id_attribute(), '_repeat" class="dashicons-before dashicons-no-alt cmb-remove-group-row"></button>';
			}

			echo '
			<div class="cmbhandle" title="', __( 'Click to toggle', 'cmb2' ), '"><br></div>
			<h3 class="cmb-group-title cmbhandle-title"><span>', $field_group->replace_hash( $field_group->options( 'group_title' ) ), '</span></h3>

			<div class="inside cmb-td cmb-nested cmb-field-list">';
				// Loop and render repeatable group fields
				foreach ( $field_group->get_field_objects() as $field_object ) {
					if ( 'hidden' == $field_object->args[ 'type' ] ) {

						// Save rendering for after the metabox
						$this->add_hidden_field( array(
							'field_args'  => $field_object->args,
							'group_field' => $field_group,
						) );

					} elseif( 'group' == $field_object->args['type'] ) {

						if ( ! isset( $field_object->args['show_names'] ) ) {
							$field_object->args['show_names'] = $this->get_show_names();
						}
						$this->render_group( $field_object );

					} else {

						$field_object->args[ 'show_names' ] = $field_group->args( 'show_names' );
						$field_object->args[ 'context' ]    = $field_group->args( 'context' );

						// Todo: Same pattern as the get_field() hack again
						// With early instantiation we don't need to call get_field() here any more but
						// we do need to call get_data() in order to copy over this row's data for each
						// individual field inside the group field.  But in order for get_data() to work
						// we also need to copy a few other properties to keep them in sync.
						$field_object->object_id = $this->object_id;
						$field_object->object_type = $this->object_type;
						$field_object->escaped_value = null;
						$field_object->value = $field_object->get_data();
						$field_object->render_field();
					}
				}
				if ( $field_group->args( 'repeatable' ) ) {
					echo '
					<div class="cmb-row cmb-remove-field-row">
						<div class="cmb-remove-row">
							<button ', $remove_disabled, 'data-selector="', $field_group->get_html_id_attribute(), '_repeat" class="button cmb-remove-group-row alignright">', $field_group->options( 'remove_button' ), '</button>
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

		$this->get_hidden_fields()[] = new CMB2_Types( new CMB2_Field( $args ) );
	}

	/**
	 * Loop through and output hidden fields
	 *
	 * @since  2.0.0
	 */
	public function render_hidden_fields() {

		if ( ! empty( $this->get_hidden_fields() ) ) {
			foreach ( $this->get_hidden_fields() as $hidden ) {
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

		$this->set_data_to_save( $data_to_sanitize );
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
		$this->set_data_to_save( ! empty( $data_to_save ) ? $data_to_save : $_POST );
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
		do_action( "cmb2_save_{$object_type}_fields", $object_id, $this->get_id(), $this->get_updated(), $this );

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
		do_action( "cmb2_save_{$object_type}_fields_{$this->get_id()}", $object_id, $this->get_updated(), $this );

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
		do_action( "cmb2_{$this->object_type()}_process_fields_{$this->get_id()}", $this, $this->object_id() );

		// Remove the show_on properties so saving works
		$this->set_show_on( array() );

		// save field ids of those that are updated
		$this->set_updated( array() );

		foreach ( $this->get_field_objects() as $field_object ) {
			$this->process_field( $field_object->args );
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

				if ( $field->save_field_from_data( $this->get_data_to_save() ) ) {
					$this->get_updated()[] = $field->id();
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

		$field_data_to_save = $this->get_field_data_to_save( $args[ 'id' ] );
		if ( ! isset( $args[ 'id' ] )  || is_null( $field_data_to_save ) ) {
			return;
		}

		$field_group = $this->get_field_object( $args[ 'id' ] );
		$base_id     = $field_group->id();
		$old         = $field_group->get_data();
		// Check if group field has sanitization_cb
		$group_vals         = $field_group->sanitization_cb( $this->get_field_data_to_save( $base_id ) );
		$saved              = array();
		$field_group->index = 0;

		foreach ( array_values( $field_group->get_field_objects() ) as $field_object ) {
			$sub_id = $field_object->id();

			foreach ( (array) $group_vals as $field_group->index => $post_vals ) {

				// Get value
				$new_val = isset( $group_vals[ $field_group->index ][ $sub_id ] )
					? $group_vals[ $field_group->index ][ $sub_id ]
					: false;

				// Sanitize
				$new_val = $field_object->sanitization_cb( $new_val );

				if ( 'file' == $field_object->type() && is_array( $new_val ) ) {
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
					$this->get_updated()[] = $base_id . '::' . $field_group->index . '::' . $sub_id;
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
		$field_object = $this->get_field_object( $index );
		if ( ! is_null( $field_object ) ) {

			// Todo: Hacked this to ensure field objects get updated if data was changed after instantiation
			$field_object->object_id = $this->object_id;
			$field_object->object_type = $this->object_type;
			$field_object->escaped_value = null;

			// Also Todo: duplication is getting messier and messier without a field object refactor.  Duct tape only here
			if( is_array( $field ) && isset( $field[ 'options' ] ) ) {
				// Here's the rub: we already had a field object tucked away but someone may have gotten an instance,
				// changed some options, and then passed the arg array-- not the field object-- to something that calls
				// through to here.  The expected behavior per unit tests is that those changed options persist, so we
				// make sure to copy them over if they exist.  A better solution is badly needed.
				$field_object->args[ 'options' ] = $field[ 'options' ];
			}

			$field_object->value = $field_object->get_data();
		} else {
			$field_object = new CMB2_Field( $this->get_field_args( $field_id, $field, $sub_field_id, $field_group ) );
			$this->set_field_object( $index, $field_object );
		}

		return $field_object;
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

		$field_object = $this->get_field_object( $field_id );

		// Check if group is passed and if fields were added in the old-school fields array
		if ( $field_group && ( $sub_field_id || 0 === $sub_field_id ) ) {

			// Update the fields array w/ any modified properties inherited from the group field
			$field_object->args[ 'fields' ][ $sub_field_id ] = $field_args;

			return array(
				'field_args'  => $field_args,
				'group_field' => $field_group,
			);

		}

		if ( is_array( $field_args ) ) {
			$field_object->args = array_merge( $field_args, $field_object->args );
		}

		return array(
			'field_args'  => $field_object->args,
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

		$field_id = is_string( $field ) ? $field : $field[ 'id' ];

		$parent_field_id = '';
		$field_group = null;
		if ( is_a( $this, 'CMB2_Field' ) && 'group' == $this->args( 'type' ) ) {
			$parent_field_id = $this->id();

			$new_field = new CMB2_Field( array(
				'field_args'  => $field,
				'group_field' => $this,
				'object_type' => $this->object_type(),
				'object_id'   => $this->object_id()
			) );
		} else {
			$new_field = new CMB2_Field( array(
				'field_args'  => $field,
				'object_type' => $this->object_type(),
				'object_id'   => $this->object_id(),
			) );
		}


		$this->set_field_object( $field_id, $new_field, $position );
		return $field_id;
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

		$parent_field_object = $this->get_field_object( $parent_field_id );
		if ( ! is_a( $parent_field_object, 'CMB2_Field' ) ) {
			return false;
		}

		if ( 'group' !== $parent_field_object->args[ 'type' ] ) {
			return false;
		}

		$new_field_id = $parent_field_object->add_field( $field, $position );

		return array( $parent_field_id, $new_field_id );
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

		if ( ! $sub_field_id ) {
			return true;
		}

		unset( $this->field_objects[ $field_id ]->args[ 'fields' ][ $sub_field_id ] );

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

		$field_object = $this->get_field_object( $field_id );
		if ( ! $sub_field_id ) {
			$field_object->args[ $property ] = $value;
		} else {
			$field_object->args[ $sub_field_id ][ $property ] = $value;
		}

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

		$field_object = $this->get_field_object( $field_id );
		if ( is_null( $field_object ) ) {
			$field_id = $this->search_old_school_array( $field_id, $this->get_fields() );
		}

		if ( false === $field_id ) {
			return false;
		}

		if ( ! $sub_field_id ) {
			return array( $field_id, $sub_field_id );
		}

		if ( 'group' !== $field_object->args[ 'type' ] ) {
			return false;
		}

		if ( ! array_key_exists( $sub_field_id, $field_object->get_field_objects() ) ) {
			$sub_field_id = $this->search_old_school_array( $sub_field_id, $field_object->args( 'fields' ) );
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
				return $this->get_updated();
			case 'object_id':
				return $this->object_id();
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}