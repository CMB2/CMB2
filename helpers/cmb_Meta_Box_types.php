<?php

/**
 * CMB field types
 *
 * @todo test taxonomy methods with non-post objects
 * @todo test all methods with non-post objects
 * @todo Date/Time fields should store date format as data attribute for JS
 *
 * @since  1.0.0
 */
class cmb_Meta_Box_types {

	/**
	 * An iterator value for repeatable fields
	 * @var   integer
	 * @since 1.0.0
	 */
	public $iterator = 0;

	/**
	 * Current field
	 * @var   array
	 * @since 1.0.0
	 */
	public $field;

	public function __construct( $field ) {
		$this->field = $field;
	}

	/**
	 * Default fallback. Allows rendering fields via "cmb_render_$name" hook
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		// When a non-registered field is called, send it through an action.
		do_action( "cmb_render_$name", $this->field->args(), $this->field->escaped_value(), $this->field->object_id, $this->field->object_type, $this );
	}

	/**
	 * Render a field (and handle repeatable)
	 * @since  1.1.0
	 */
	public function render() {
		if ( $this->field->args( 'repeatable' ) ) {
			$this->render_repeatable_field();
		} else {
			$this->_render();
		}
	}

	/**
	 * Render a field type
	 * @since  1.1.0
	 */
	protected function _render() {
		echo $this->{$this->field->type()}();
	}

	/**
	 * Checks if we can get a post object, and if so, uses `get_the_terms` which utilizes caching
	 * @since  1.0.2
	 * @return mixed Array of terms on success
	 */
	public function get_object_terms() {
		$object_id = $this->field->object_id;
		$taxonomy = $this->field->args( 'taxonomy' );

		if ( ! $post = get_post( $object_id ) ) {

			$cache_key = 'cmb-cache-'. $taxonomy .'-'. $object_id;

			// Check cache
			$cached = $test = get_transient( $cache_key );
			if ( $cached )
				return $cached;

			$cached = wp_get_object_terms( $object_id, $taxonomy );
			// Do our own (minimal) caching. Long enough for a page-load.
			$set = set_transient( $cache_key, $cached, 60 );
			return $cached;
		}

		// WP caches internally so it's better to use
		return get_the_terms( $post, $taxonomy );

	}

	/**
	 * Determine a file's extension
	 * @since  1.0.0
	 * @param  string       $file File url
	 * @return string|false       File extension or false
	 */
	public function get_file_ext( $file ) {
		$parsed = @parse_url( $file, PHP_URL_PATH );
		return $parsed ? strtolower( pathinfo( $parsed, PATHINFO_EXTENSION ) ) : false;
	}

	/**
	 * Determines if a file has a valid image extension
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return bool         Whether file has a valid image extension
	 */
	public function is_valid_img_ext( $file ) {
		$file_ext = $this->get_file_ext( $file );

		$this->valid = empty( $this->valid )
			? (array) apply_filters( 'cmb_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) )
			: $this->valid;

		return ( $file_ext && in_array( $file_ext, $this->valid ) );
	}

	/**
	 * Handles parsing and filtering attributes while preserving any passed in via field config.
	 * @since  1.1.0
	 * @param  array  $args     Override arguments
	 * @param  string $element  Element for filter
	 * @param  array  $defaults Default arguments
	 * @return array            Parsed and filtered arguments
	 */
	public function parse_args( $args, $element, $defaults ) {
		return wp_parse_args( apply_filters( "cmb_{$element}_attributes", $this->field->maybe_set_attributes( $args ), $this->field, $this ), $defaults );
	}

	/**
	 * Combines attributes into a string for a form element
	 * @since  1.1.0
	 * @param  array  $attrs        Attributes to concatenate
	 * @param  array  $attr_exclude Attributes that should NOT be concatenated
	 * @return string               String of attributes for form element
	 */
	public function concat_attrs( $attrs, $attr_exclude = array() ) {
		$attributes = '';
		foreach ( $attrs as $attr => $val ) {
			if ( ! in_array( $attr, (array) $attr_exclude, true ) )
				$attributes .= sprintf( ' %s="%s"', $attr, $val );
		}
		return $attributes;
	}

	/**
	 * Generates html for an option element
	 * @since  1.1.0
	 * @param  string  $opt_label Option label
	 * @param  string  $opt_value Option value
	 * @param  mixed   $selected  Selected attribute if option is selected
	 * @return string             Generated option element html
	 */
	public function option( $opt_label, $opt_value, $selected ) {
		return sprintf( "\t".'<option value="%s" %s>%s</option>', $opt_value, selected( $selected, true, false ), $opt_label )."\n";
	}

	/**
	 * Generates options html
	 * @since  1.1.0
	 * @param  array   $args   Optional arguments
	 * @param  string  $method Method to generate individual option item
	 * @return string          Concatenated html options
	 */
	public function concat_options( $args = array(), $method = 'list_input' ) {

		$options     = (array) $this->field->args( 'options' );
		$saved_value = $this->field->escaped_value();
		$value       = $saved_value ? $saved_value : $this->field->args( 'default' );

		$_options = ''; $i = 1;
		foreach ( $options as $option_key => $option ) {

			// Check for the "old" way
			$opt_label  = is_array( $option ) && array_key_exists( 'name', $option ) ? $option['name'] : $option;
			$opt_value  = is_array( $option ) && array_key_exists( 'value', $option ) ? $option['value'] : $option_key;
			// Check if this option is the value of the input
			$is_current = $value == $opt_value;

			if ( ! empty( $args ) ) {
				// Clone args & modify for just this item
				$this_args = $args;
				$this_args['value'] = $opt_value;
				$this_args['label'] = $opt_label;
				if ( $is_current )
					$this_args['checked'] = 'checked';

				$_options .= $this->$method( $this_args, $i );
			} else {
				$_options .= $this->option( $opt_label, $opt_value, $is_current );
			}
			$i++;
		}
		return $_options;
	}

	/**
	 * Generates html for list item with input
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @param  int    $i    Iterator value
	 * @return string       Gnerated list item html
	 */
	public function list_input( $args = array(), $i ) {
		$args = $this->parse_args( $args, 'list_input', array(
			'type'  => 'radio',
			'class' => 'cmb_option',
			'name'  => $this->_name(),
			'id'    => $this->_id( $i ),
			'value' => $this->field->escaped_value(),
			'label' => '',
		) );

		return sprintf( "\t".'<li><input%s/> <label for="%s">%s</label></li>'."\n", $this->concat_attrs( $args, 'label' ), $args['id'], $args['label'] );
	}

	/**
	 * Generates html for list item with checkbox input
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @param  int    $i    Iterator value
	 * @return string       Gnerated list item html
	 */
	public function list_input_checkbox( $args, $i ) {
		unset( $args['selected'] );
		$saved_value = $this->field->escaped_value();
		if ( is_array( $saved_value ) && in_array( $args['value'], $saved_value ) ) {
			$args['checked'] = 'checked';
		}
		return $this->list_input( $args, $i );
	}

	/**
	 * Generates repeatable field table markup
	 * @since  1.0.0
	 */
	public function render_repeatable_field() {
		$table_id = $this->field->id() .'_repeat';

		$this->_desc( true, true );
		?>

		<table id="<?php echo $table_id; ?>" class="cmb-repeat-table">
			<tbody>
				<?php $this->repeatable_rows(); ?>
			</tbody>
		</table>
		<p class="add-row">
			<a data-selector="<?php echo $table_id; ?>" class="add-row-button button" href="#"><?php _e( 'Add Row', 'cmb' ); ?></a>
		</p>

		<?php
		// reset iterator
		$this->iterator = 0;
	}

	/**
	 * Generates repeatable field rows
	 * @since  1.1.0
	 */
	public function repeatable_rows() {
		$meta_value = $this->field->escaped_value();
		// check for default content
		$default    = $this->field->args( 'default' );

		// check for saved data
		if ( ! empty( $meta_value ) ) {
			$meta_value = is_array( $meta_value ) ? array_filter( $meta_value ) : $meta_value;
			$meta_value = ! empty( $meta_value ) ? $meta_value : $default;
		} else {
			$meta_value = $default;
		}

		// Loop value array and add a row
		if ( ! empty( $meta_value ) ) {
			foreach ( (array) $meta_value as $val ) {
				$this->field->escaped_value = $val;
				$this->repeat_row();
				$this->iterator++;
			}
		} else {
			// Otherwise add one row
			$this->repeat_row();
		}

		// Then add an empty row
		$this->field->escaped_value = '';
		$this->iterator = $this->iterator ? $this->iterator : 1;
		$this->repeat_row( 'empty-row' );
	}

	/**
	 * Generates a repeatable row's markup
	 * @since  1.1.0
	 * @param  string  $class Repeatable table row's class
	 */
	protected function repeat_row( $class = 'repeat-row' ) {
		?>

		<tr class="<?php echo $class; ?>">
			<td>
				<?php $this->_render(); ?>
			</td>
			<td class="remove-row">
				<a class="button remove-row-button" href="#"><?php _e( 'Remove', 'cmb' ); ?></a>
			</td>
		</tr>

		<?php
	}

	/**
	 * Generates description markup
	 * @since  1.0.0
	 * @param  boolean $paragraph Paragraph tag or span
	 * @param  boolean $echo      Whether to echo description or only return it
	 * @return string             Field's description markup
	 */
	public function _desc( $paragraph = false, $echo = false ) {
		// Prevent description from printing multiple times for repeatable fields
		if ( $this->field->args( 'repeatable' ) || $this->iterator > 0 ) {
			return '';
		}
		$tag = $paragraph ? 'p' : 'span';
		$desc = "\n<$tag class=\"cmb_metabox_description\">{$this->field->args( 'description' )}</$tag>\n";
		if ( $echo )
			echo $desc;
		return $desc;
	}

	/**
	 * Generate field name attribute
	 * @since  1.1.0
	 * @param  string  $suffix For multi-part fields
	 * @return string          Name attribute
	 */
	public function _name( $suffix = '' ) {
		return $this->field->args( '_name' ) . ( $this->field->args( 'repeatable' ) ? '['. $this->iterator .']' : '' ) . $suffix;
	}

	/**
	 * Generate field id attribute
	 * @since  1.1.0
	 * @param  string  $suffix For multi-part fields
	 * @return string          Id attribute
	 */
	public function _id( $suffix = '' ) {
		return $this->field->id() . $suffix . ( $this->field->args( 'repeatable' ) ? '_'. $this->iterator .'" data-iterator="'. $this->iterator : '' );
	}

	/**
	 * Handles outputting an 'input' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form input element
	 */
	public function input( $args = array() ) {
		$args = $this->parse_args( $args, 'input', array(
			'type'  => 'text',
			'class' => 'regular-text',
			'name'  => $this->_name(),
			'id'    => $this->_id(),
			'value' => $this->field->escaped_value(),
			'desc'  => $this->_desc( true ),
		) );

		return sprintf( '<input%s/>%s', $this->concat_attrs( $args, 'desc' ), $args['desc'] );
	}

	/**
	 * Handles outputting an 'textarea' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form textarea element
	 */
	public function textarea( $args = array() ) {
		$args = $this->parse_args( $args, 'textarea', array(
			'class' => 'cmb_textarea',
			'name'  => $this->_name(),
			'id'    => $this->_id(),
			'cols'  => 60,
			'rows'  => 10,
			'value' => $this->field->escaped_value( 'esc_textarea' ),
			'desc'  => $this->_desc( true ),
		) );
		return sprintf( '<textarea%s>%s</textarea>%s', $this->concat_attrs( $args, array( 'desc', 'value' ) ), $args['value'], $args['desc'] );
	}

	/**
	 * Begin Field Types
	 */

	public function text() {
		return $this->input();
	}

	public function text_small() {
		return $this->input( array( 'class' => 'cmb_text_small', 'desc' => $this->_desc() ) );
	}

	public function text_medium() {
		return $this->input( array( 'class' => 'cmb_text_medium', 'desc' => $this->_desc() ) );
	}

	public function text_email() {
		return $this->input( array( 'class' => 'cmb_text_email cmb_text_medium', 'type' => 'email' ) );
	}

	public function text_url() {
		return $this->input( array( 'class' => 'cmb_text_url cmb_text_medium regular-text', 'value' => $this->field->escaped_value( 'esc_url' ) ) );
	}

	public function text_date() {
		return $this->input( array( 'class' => 'cmb_text_small cmb_datepicker', 'desc' => $this->_desc() ) );
	}

	public function text_time() {
		return $this->input( array( 'class' => 'cmb_timepicker text_time', 'desc' => $this->_desc() ) );
	}

	public function text_money() {
		return ( ! $this->field->args( 'before' ) ? '$ ' : ' ' ) . $this->input( array( 'class' => 'cmb_text_money', 'desc' => $this->_desc() ) );
	}

	public function textarea_small() {
		return $this->textarea( array( 'class' => 'cmb_textarea_small', 'rows' => 4 ) );
	}

	public function textarea_code() {
		return sprintf( '<pre>%s</pre>', $this->textarea( array( 'class' => 'cmb_textarea_code' )  ) );
	}

	public function wysiwyg( $args = array() ) {
		extract( $this->parse_args( $args, 'input', array(
			'id'      => $this->_id(),
			'value'   => $this->field->escaped_value( 'stripslashes' ),
			'desc'    => $this->_desc( true ),
			'options' => $this->field->args( 'options' ),
		) ) );

		wp_editor( $value, $id, $options );
		echo $desc;
	}

	public function text_date_timestamp() {
		$meta_value = $this->field->escaped_value();
		$value = ! empty( $meta_value ) ? date( $this->field->args( 'date_format' ), $meta_value ) : '';
		return $this->input( array( 'class' => 'cmb_text_small cmb_datepicker', 'value' => $value ) );
	}

	public function text_datetime_timestamp( $meta_value = '' ) {
		$desc = '';
		if ( ! $meta_value ) {
			$meta_value = $this->field->escaped_value();
			// This will be used if there is a select_timezone set for this field
			$tz_offset = $this->field->field_timezone_offset();
			if ( ! empty( $tz_offset ) ) {
				$meta_value -= $tz_offset;
			}
			$desc = $this->_desc();
		}

		$inputs = array(
			$this->input( array(
				'class' => 'cmb_text_small cmb_datepicker',
				'name'  => $this->_name( '[date]' ),
				'id'    => $this->_id( '_date' ),
				'value' => ! empty( $meta_value ) ? date( $this->field->args( 'date_format' ), $meta_value ) : '',
				'desc'  => '',
			) ),
			$this->input( array(
				'class' => 'cmb_timepicker text_time',
				'name'  => $this->_name( '[time]' ),
				'id'    => $this->_id( '_time' ),
				'value' => ! empty( $meta_value ) ? date( $this->field->args( 'time_format' ), $meta_value ) : '',
				'desc'  => $desc,
			) )
		);

		return implode( "\n", $inputs );
	}

	public function text_datetime_timestamp_timezone() {
		$meta_value = $this->field->escaped_value();
		$datetime   = unserialize( $meta_value );
		$meta_value = $tzstring = false;

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz         = $datetime->getTimezone();
			$tzstring   = $tz->getName();
			$meta_value = $datetime->getTimestamp() + $tz->getOffset( new DateTime( 'NOW' ) );
		}

		$inputs = $this->text_datetime_timestamp( $meta_value );
		$inputs .= '<select name="'. $this->_name( '[timezone]' ) .'" id="'. $this->_id( '_timezone' ) .'">';
		$inputs .= wp_timezone_choice( $tzstring );
		$inputs .= '</select>'. $this->_desc();

		return $inputs;
	}

	public function select_timezone() {
		$this->field->args['default'] = $this->field->args( 'default' )
			? $this->field->args( 'default' )
			: cmb_Meta_Box::timezone_string();

		$meta_value = $this->field->escaped_value();

		return '<select name="'. $this->_name() .'" id="'. $this->_id() .'">'. wp_timezone_choice( $meta_value ) .'</select>';
	}

	public function colorpicker() {
		$meta_value = $this->field->escaped_value();
		$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
		if ( preg_match( '/^' . $hex_color . '/i', $meta_value ) ) // Value is just 123abc, so prepend #.
			$meta_value = '#' . $meta_value;
		elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta_value ) ) // Value doesn't match #123abc, so sanitize to just #.
			$meta_value = "#";

		return $this->input( array( 'class' => 'cmb_colorpicker cmb_text_small', 'value' => $meta_value ) );
	}

	public function title() {
		extract( $this->parse_args( array(), 'title', array(
			'tag'   => $this->field->object_type == 'post' ? 'h5' : 'h3',
			'class' => 'cmb_metabox_title',
			'name'  => $this->field->args( 'name' ),
			'desc'  => $this->_desc( true ),
		) ) );

		return sprintf( '<%1$s class="%2$s">%3$s</%1$s>%4$s', $tag, $class, $name, $desc );
	}

	public function select( $args = array() ) {
		$args = $this->parse_args( $args, 'select', array(
			'class'   => 'cmb_select',
			'name'    => $this->_name(),
			'id'      => $this->_id(),
			'desc'    => $this->_desc( true ),
			'options' => $this->concat_options(),
		) );

		$attrs = $this->concat_attrs( $args, array( 'desc', 'options' ) );
		return sprintf( '<select%s>%s</select>%s', $attrs, $args['options'], $args['desc'] );
	}

	public function taxonomy_select() {

		$names      = $this->get_object_terms();
		$saved_term = is_wp_error( $names ) || empty( $names ) ? $this->field->args( 'default' ) : $names[0]->slug;
		$terms      = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$options    = '';

		foreach ( $terms as $term ) {
			$selected = $saved_term == $term->slug;
			$options .= $this->option( $term->name, $term->slug, $selected );
		}

		return $this->select( array( 'options' => $options ) );
	}

	public function radio( $args = array(), $type = 'radio' ) {
		extract( $this->parse_args( $args, $type, array(
			'class'   => 'cmb_radio_list cmb_list',
			'options' => $this->concat_options( array( 'label' => 'test' ) ),
			'desc'    => $this->_desc( true ),
		) ) );

		return sprintf( '<ul class="%s">%s</ul>%s', $class, $options, $desc );
	}

	public function radio_inline() {
		return $this->radio( array(), 'radio_inline' );
	}

	public function multicheck( $type = 'checkbox' ) {
		return $this->radio( array( 'class' => 'cmb_checkbox_list cmb_list', 'options' => $this->concat_options( array( 'type' => 'checkbox', 'name' => $this->_name() .'[]' ), 'list_input_checkbox' ) ), $type );
	}

	public function multicheck_inline() {
		$this->multicheck( 'multicheck_inline' );
	}

	public function checkbox() {
		$meta_value = $this->field->escaped_value();
		$args = array( 'type' => 'checkbox', 'class' => 'cmb_option cmb_list', 'value' => 'on', 'desc' => '' );
		if ( ! empty( $meta_value ) ) {
			$args['checked'] = 'checked';
		}
		return sprintf( '%s <label for="%s">%s</label>', $this->input( $args ), $this->_id(), $this->_desc() );
	}

	public function taxonomy_radio() {
		$names      = $this->get_object_terms();
		$saved_term = is_wp_error( $names ) || empty( $names ) ? $this->field->args( 'default' ) : $names[0]->slug;
		$terms      = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$options    = ''; $i = 1;

		if ( ! $terms ) {
			$options .= '<li><label>'. __( 'No terms', 'cmb' ) .'</label></li>';
		} else {
			foreach ( $terms as $term ) {
				$args = array(
					'value' => $term->slug,
					'label' => $term->name,
				);

				if ( $saved_term == $term->slug ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->list_input( $args, $i );
				$i++;
			}
		}

		return $this->radio( array( 'options' => $options ), 'taxonomy_radio' );
	}

	public function taxonomy_radio_inline() {
		$this->taxonomy_radio();
	}

	public function taxonomy_multicheck() {

		$names   = $this->get_object_terms();
		$saved_terms   = is_wp_error( $names ) || empty( $names )
			? $this->field->args( 'default' )
			: wp_list_pluck( $names, 'slug' );
		$terms   = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$name    = $this->_name() .'[]';
		$options = ''; $i = 1;

		if ( ! $terms ) {
			$options .= '<li><label>'. __( 'No terms', 'cmb' ) .'</label></li>';
		} else {

			foreach ( $terms as $term ) {
				$args = array(
					'value' => $term->slug,
					'label' => $term->name,
					'type' => 'checkbox',
					'name' => $name,
				);

				if ( is_array( $saved_terms ) && in_array( $term->slug, $saved_terms ) ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->list_input( $args, $i );
				$i++;
			}
		}

		return $this->radio( array( 'class' => 'cmb_checkbox_list cmb_list', 'options' => $options ), 'taxonomy_multicheck' );
	}

	public function taxonomy_multicheck_inline() {
		$this->taxonomy_multicheck();
	}

	public function file_list() {
		$meta_value = $this->field->escaped_value();

		$name = $this->_name();

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb_upload_file cmb_upload_list',
			'size'  => 45, 'desc'  => '', 'value'  => '',
		) ),
		$this->input( array(
			'type'  => 'button',
			'class' => 'cmb_upload_button button cmb_upload_list',
			'value'  => __( 'Add or Upload File', 'cmb' ),
			'name'  => '', 'id'  => '',
		) );

		echo '<ul id="', $this->_id( '_status' ) ,'" class="cmb_media_status attach_list">';

		if ( $meta_value && is_array( $meta_value ) ) {

			foreach ( $meta_value as $id => $fullurl ) {
				$id_input = $this->input( array(
					'type'  => 'hidden',
					'value' => $fullurl,
					'name'  => $name .'['. $id .']',
					'id'    => 'filelist-'. $id,
					'desc'  => '', 'class' => '',
				) );

				if ( $this->is_valid_img_ext( $fullurl ) ) {
					echo
					'<li class="img_status">',
						wp_get_attachment_image( $id, $this->field->args( 'preview_size' ) ),
						'<p class="cmb_remove_wrapper"><a href="#" class="cmb_remove_file_button">'. __( 'Remove Image', 'cmb' ) .'</a></p>
						'. $id_input .'
					</li>';

				} else {
					$parts = explode( '/', $fullurl );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo
					'<li>',
						__( 'File:', 'cmb' ), ' <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $fullurl, '" target="_blank" rel="external">'. __( 'Download', 'cmb' ) .'</a> / <a href="#" class="cmb_remove_file_button">'. __( 'Remove', 'cmb' ) .'</a>)
						'. $id_input .'
					</li>';
				}
			}
		}

		echo '</ul>';
	}

	public function file() {
		$meta_value = $this->field->escaped_value();
		$allow      = $this->field->args( 'allow' );
		$input_type = ( 'url' == $allow || ( is_array( $allow ) && in_array( 'url', $allow ) ) )
			? 'text' : 'hidden';

		echo $this->input( array(
			'type'  => $input_type,
			'class' => 'cmb_upload_file',
			'size'  => 45,
			'desc'  => '',
		) ),
		'<input class="cmb_upload_button button" type="button" value="'. __( 'Add or Upload File', 'cmb' ) .'" />',
		$this->_desc( true );

		$cached_id = $this->_id();
		// Reset field args for attachment ID
		$args = $this->field->args();
		$args['id'] = $args['_id'] . '_id';
		unset( $args['_id'], $args['_name'] );

		// And get new field object
		$this->field = new cmb_Meta_Box_field( $args, $this->field->group );

		// Get ID value
		$_id_value = $this->field->escaped_value( 'absint' );

		// If there is no ID saved yet, try to get it from the url
		if ( $meta_value && ! $_id_value ) {
			$_id_value = cmb_Meta_Box::image_id_from_url( esc_url_raw( $meta_value ) );
		}

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb_upload_file_id',
			'value' => $_id_value,
			'desc'  => '',
		) ),
		'<div id="', $this->_id( '_status' ) ,'" class="cmb_media_status">';
			if ( ! empty( $meta_value ) ) {

				if ( $this->is_valid_img_ext( $meta_value ) ) {
					echo '<div class="img_status">';
					echo '<img style="max-width: 350px; width: 100%; height: auto;" src="', $meta_value, '" alt="" />';
					echo '<p class="cmb_remove_wrapper"><a href="#" class="cmb_remove_file_button" rel="', $cached_id, '">'. __( 'Remove Image', 'cmb' ) .'</a></p>';
					echo '</div>';
				} else {
					// $file_ext = $this->get_file_ext( $meta_value );
					$parts = explode( '/', $meta_value );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo __( 'File:', 'cmb' ), ' <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta_value, '" target="_blank" rel="external">'. __( 'Download', 'cmb' ) .'</a> / <a href="#" class="cmb_remove_file_button" rel="', $cached_id, '">'. __( 'Remove', 'cmb' ) .'</a>)';
				}
			}
		echo '</div>';
	}

	public function oembed() {
		echo $this->input( array(
			'class'           => 'cmb_oembed regular-text',
			'data-objectid'   => $this->field->object_id,
			'data-objecttype' => $this->field->object_type
		) ),
		'<p class="cmb-spinner spinner" style="display:none;"><img src="'. admin_url( '/images/wpspin_light.gif' ) .'" alt="spinner"/></p>',
		'<div id="',$this->_id( '_status' ) ,'" class="cmb_media_status ui-helper-clearfix embed_wrap">';

			if ( $meta_value = $this->field->escaped_value() ) {
				echo cmb_Meta_Box_ajax::get_oembed( $meta_value, $this->field->object_id, array(
					'object_type' => $this->field->object_type,
					'oembed_args' => array( 'width' => '640' ),
					'field_id'    => $this->_id(),
				) );
			}

		echo '</div>';
	}

}
