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
class CMB2_Types {

	/**
	 * An iterator value for repeatable fields
	 * @var   integer
	 * @since 1.0.0
	 */
	public $iterator = 0;

	/**
	 * Current CMB2_Field field object
	 * @var   array
	 * @since 1.0.0
	 */
	public $field;

	public function __construct( CMB2_Field $field ) {
		$this->field = $field;
	}

	/**
	 * Default fallback. Allows rendering fields via "cmb2_render_$name" hook
	 * @since  1.0.0
	 * @param  string $name      Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function __call( $name, $arguments ) {
		/**
		 * Pass non-existent field types through an action
		 *
		 * The dynamic portion of the hook name, $name, refers to the field type.
		 *
		 * @param array  $field              The passed in `CMB2_Field` object
		 * @param mixed  $escaped_value      The value of this field escaped.
		 *                                   It defaults to `sanitize_text_field`.
		 *                                   If you need the unescaped value, you can access it
		 *                                   via `$field->value()`
		 * @param int    $object_id          The ID of the current object
		 * @param string $object_type        The type of object you are working with.
		 *                                   Most commonly, `post` (this applies to all post-types),
		 *                                   but could also be `comment`, `user` or `options-page`.
		 * @param object $field_type_object  This `CMB2_Types` object
		 */
		do_action( "cmb2_render_$name", $this->field, $this->field->escaped_value(), $this->field->object_id, $this->field->object_type, $this );
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
			if ( $cached ) {
				return $cached;
			}

			$cached = wp_get_object_terms( $object_id, $taxonomy );
			// Do our own (minimal) caching. Long enough for a page-load.
			$set = set_transient( $cache_key, $cached, 60 );
			return $cached;
		}

		// WP caches internally so it's better to use
		return get_the_terms( $post, $taxonomy );

	}

	/**
	 * Retrieve text parameter from field's options array (if it has one), or use fallback text
	 * @since  2.0.0
	 * @param  string  $option_key Key in field's options array
	 * @param  string  $fallback   Fallback text
	 * @return string              Text
	 */
	public function _text( $option_key, $fallback ) {
		$options = (array) $this->field->args( 'options' );
		return isset( $options[ $option_key ] ) ? $options[ $option_key ] : $fallback;
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
			? (array) apply_filters( 'cmb2_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) )
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
		return wp_parse_args( apply_filters( "cmb2_{$element}_attributes", $this->field->maybe_set_attributes( $args ), $defaults, $this->field, $this ), $defaults );
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
			if ( ! in_array( $attr, (array) $attr_exclude, true ) ) {
				$attributes .= sprintf( ' %s="%s"', $attr, $val );
			}
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
				if ( $is_current ) {
					$this_args['checked'] = 'checked';
				}

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
			'class' => 'cmb2_option',
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

		$this->_desc( true, true, true );
		?>

		<div id="<?php echo $table_id; ?>" class="cmb-repeat-table cmb-nested">
			<ul class="cmb-tbody">
				<?php $this->repeatable_rows(); ?>
			</ul>
		</div>
		<p class="add-row">
			<a data-selector="<?php echo $table_id; ?>" class="add-row-button button" href="#"><?php echo esc_html( $this->_text( 'add_row_text', __( 'Add Row', 'cmb2' ) ) ); ?></a>
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
		$meta_value = array_filter( (array) $this->field->escaped_value() );
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
			$count = count( $meta_value );
			foreach ( (array) $meta_value as $val ) {
				$this->field->escaped_value = $val;
				$this->repeat_row( $count < 2 );
				$this->iterator++;
			}
		} else {
			// Otherwise add one row
			$this->repeat_row( true );
		}

		// Then add an empty row
		$this->field->escaped_value = '';
		$this->iterator = $this->iterator ? $this->iterator : 1;
		$this->repeat_row( false, 'empty-row hidden' );
	}

	/**
	 * Generates a repeatable row's markup
	 * @since  1.1.0
	 * @param  string  $disable_remover Whether remove button should be disabled
	 * @param  string  $class Repeatable table row's class
	 */
	protected function repeat_row( $disable_remover = false, $class = 'repeat-row' ) {
		$disabled = $disable_remover ? 'disabled="disabled"' : '';
		?>

		<li class="cmb-row <?php echo $class; ?>">
			<div class="cmb-td">
				<?php $this->_render(); ?>
			</div>
			<div class="cmb-td remove-row">
				<a class="button remove-row-button" <?php echo $disabled; ?> href="#"><?php echo esc_html( $this->_text( 'remove_row_text', __( 'Remove', 'cmb2' ) ) ); ?></a>
			</div>
		</li>

		<?php
	}

	/**
	 * Generates description markup
	 * @since  1.0.0
	 * @param  boolean $paragraph Paragraph tag or span
	 * @param  boolean $echo      Whether to echo description or only return it
	 * @return string             Field's description markup
	 */
	public function _desc( $paragraph = false, $echo = false, $repeat_group = false ) {
		// Prevent description from printing multiple times for repeatable fields
		if ( ! $repeat_group && ( $this->field->args( 'repeatable' ) || $this->iterator > 0 ) ) {
			return '';
		}
		$tag = $paragraph ? 'p' : 'span';
		$desc = "\n<$tag class=\"cmb2_metabox_description\">{$this->field->args( 'description' )}</$tag>\n";
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
			'class' => 'cmb2_textarea',
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

	public function hidden() {
		return $this->input( array( 'type' => 'hidden', 'desc' => '', 'class' => '' ) );
	}

	public function text_small() {
		return $this->input( array( 'class' => 'cmb2_text_small', 'desc' => $this->_desc() ) );
	}

	public function text_medium() {
		return $this->input( array( 'class' => 'cmb2_text_medium', 'desc' => $this->_desc() ) );
	}

	public function text_email() {
		return $this->input( array( 'class' => 'cmb2_text_email cmb2_text_medium', 'type' => 'email' ) );
	}

	public function text_url() {
		return $this->input( array( 'class' => 'cmb2_text_url cmb2_text_medium regular-text', 'value' => $this->field->escaped_value( 'esc_url' ) ) );
	}

	public function text_date() {
		return $this->input( array( 'class' => 'cmb2_text_small cmb2_datepicker', 'desc' => $this->_desc() ) );
	}

	public function text_time() {
		return $this->input( array( 'class' => 'cmb2_timepicker text_time', 'desc' => $this->_desc() ) );
	}

	public function text_money() {
		return ( ! $this->field->args( 'before' ) ? '$ ' : ' ' ) . $this->input( array( 'class' => 'cmb2_text_money', 'desc' => $this->_desc() ) );
	}

	public function textarea_small() {
		return $this->textarea( array( 'class' => 'cmb2_textarea_small', 'rows' => 4 ) );
	}

	public function textarea_code() {
		return sprintf( '<pre>%s', $this->textarea( array( 'class' => 'cmb2_textarea_code', 'desc' => '</pre>' . $this->_desc( true ) )  ) );
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
		return $this->input( array( 'class' => 'cmb2_text_small cmb2_datepicker', 'value' => $value ) );
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
				'class' => 'cmb2_text_small cmb2_datepicker',
				'name'  => $this->_name( '[date]' ),
				'id'    => $this->_id( '_date' ),
				'value' => ! empty( $meta_value ) && ! is_array( $meta_value ) ? date( $this->field->args( 'date_format' ), $meta_value ) : '',
				'desc'  => '',
			) ),
			$this->input( array(
				'class' => 'cmb2_timepicker text_time',
				'name'  => $this->_name( '[time]' ),
				'id'    => $this->_id( '_time' ),
				'value' => ! empty( $meta_value ) && ! is_array( $meta_value ) ? date( $this->field->args( 'time_format' ), $meta_value ) : '',
				'desc'  => $desc,
			) )
		);

		return implode( "\n", $inputs );
	}

	public function text_datetime_timestamp_timezone() {
		$meta_value = $this->field->escaped_value();
		if ( is_array( $meta_value ) ) {
			$meta_value = '';
		}
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
			: cmb2_utils()->timezone_string();

		$meta_value = $this->field->escaped_value();

		return '<select name="'. $this->_name() .'" id="'. $this->_id() .'">'. wp_timezone_choice( $meta_value ) .'</select>';
	}

	public function colorpicker() {
		$meta_value = $this->field->escaped_value();
		$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
		if ( preg_match( '/^' . $hex_color . '/i', $meta_value ) ) { // Value is just 123abc, so prepend #.
			$meta_value = '#' . $meta_value;
		}
		elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta_value ) ) { // Value doesn't match #123abc, so sanitize to just #.
			$meta_value = "#";
		}
		return $this->input( array( 'class' => 'cmb2_colorpicker cmb2_text_small', 'value' => $meta_value ) );
	}

	public function title() {
		extract( $this->parse_args( array(), 'title', array(
			'tag'   => $this->field->object_type == 'post' ? 'h5' : 'h3',
			'class' => 'cmb2_metabox_title',
			'name'  => $this->field->args( 'name' ),
			'desc'  => $this->_desc( true ),
		) ) );

		return sprintf( '<%1$s class="%2$s">%3$s</%1$s>%4$s', $tag, $class, $name, $desc );
	}

	public function select( $args = array() ) {
		$args = $this->parse_args( $args, 'select', array(
			'class'   => 'cmb2_select',
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

		$option_none  = $this->field->args( 'show_option_none' );
		if( ! empty( $option_none ) ) {
			$option_none_value = apply_filters( 'cmb2_taxonomy_select_default_value', '' );
			$option_none_value = apply_filters( "cmb2_taxonomy_select_{$this->_id()}_default_value", $option_none_value );
			$selected = $saved_term == $option_none_value;
			$options .= $this->option( $option_none, $option_none_value, $selected );
		}

		foreach ( $terms as $term ) {
			$selected = $saved_term == $term->slug;
			$options .= $this->option( $term->name, $term->slug, $selected );
		}

		return $this->select( array( 'options' => $options ) );
	}

	public function radio( $args = array(), $type = 'radio' ) {
		extract( $this->parse_args( $args, $type, array(
			'class'   => 'cmb2_radio_list cmb2_list',
			'options' => $this->concat_options( array( 'label' => 'test' ) ),
			'desc'    => $this->_desc( true ),
		) ) );

		return sprintf( '<ul class="%s">%s</ul>%s', $class, $options, $desc );
	}

	public function radio_inline() {
		return $this->radio( array(), 'radio_inline' );
	}

	public function multicheck( $type = 'checkbox' ) {

		$classes = false === $this->field->args( 'select_all_button' )
			? 'cmb2_checkbox_list no_select_all cmb2_list'
			: 'cmb2_checkbox_list cmb2_list';

		return $this->radio( array( 'class' => $classes, 'options' => $this->concat_options( array( 'type' => 'checkbox', 'name' => $this->_name() .'[]' ), 'list_input_checkbox' ) ), $type );
	}

	public function multicheck_inline() {
		$this->multicheck( 'multicheck_inline' );
	}

	public function checkbox() {
		$meta_value = $this->field->escaped_value();
		$args = array( 'type' => 'checkbox', 'class' => 'cmb2_option cmb2_list', 'value' => 'on', 'desc' => '' );
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
			$options .= '<li><label>'. esc_html( $this->_text( 'no_terms_text', __( 'No terms', 'cmb2' ) ) ) .'</label></li>';
		} else {
			$option_none  = $this->field->args( 'show_option_none' );
			if( ! empty( $option_none ) ) {
				$option_none_value = apply_filters( "cmb2_taxonomy_radio_{$this->_id()}_default_value", apply_filters( 'cmb2_taxonomy_radio_default_value', '' ) );
				$args = array(
					'value' => $option_none_value,
					'label' => $option_none,
				);
				if( $saved_term == $option_none_value ) {
					$args['checked'] = 'checked';
				}
				$options .= $this->list_input( $args, $i );
				$i++;
			}

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

		$names       = $this->get_object_terms();
		$saved_terms = is_wp_error( $names ) || empty( $names )
			? $this->field->args( 'default' )
			: wp_list_pluck( $names, 'slug' );
		$terms       = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$name        = $this->_name() .'[]';
		$options     = ''; $i = 1;

		if ( ! $terms ) {
			$options .= '<li><label>'. esc_html( $this->_text( 'no_terms_text', __( 'No terms', 'cmb2' ) ) ) .'</label></li>';
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

		$classes = false === $this->field->args( 'select_all_button' )
			? 'cmb2_checkbox_list no_select_all cmb2_list'
			: 'cmb2_checkbox_list cmb2_list';

		return $this->radio( array( 'class' => $classes, 'options' => $options ), 'taxonomy_multicheck' );
	}

	public function taxonomy_multicheck_inline() {
		$this->taxonomy_multicheck();
	}

	public function file_list() {
		$meta_value = $this->field->escaped_value();

		$name = $this->_name();

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb2_upload_file cmb2_upload_list',
			'size'  => 45, 'desc'  => '', 'value'  => '',
		) ),
		$this->input( array(
			'type'  => 'button',
			'class' => 'cmb2_upload_button button cmb2_upload_list',
			'value'  => esc_html( $this->_text( 'add_upload_file_text', __( 'Add or Upload File', 'cmb2' ) ) ),
			'name'  => '', 'id'  => '',
		) );

		echo '<ul id="', $this->_id( '_status' ) ,'" class="cmb2_media_status attach_list">';

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
						'<p class="cmb2_remove_wrapper"><a href="#" class="cmb2_remove_file_button">'. esc_html( $this->_text( 'remove_image_text', __( 'Remove Image', 'cmb2' ) ) ) .'</a></p>
						'. $id_input .'
					</li>';

				} else {
					$parts = explode( '/', $fullurl );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo
					'<li>',
						esc_html( $this->_text( 'file_text', __( 'File:', 'cmb2' ) ) ) ,' <strong>', $title ,'</strong>&nbsp;&nbsp;&nbsp; (<a href="', $fullurl ,'" target="_blank" rel="external">', esc_html( $this->_text( 'file_download_text', __( 'Download', 'cmb2' ) ) ) ,'</a> / <a href="#" class="cmb2_remove_file_button">', esc_html( $this->_text( 'remove_text', __( 'Remove', 'cmb2' ) ) ) ,'</a>)
						', $id_input ,'
					</li>';
				}
			}
		}

		echo '</ul>';
	}

	public function file() {
		$meta_value = $this->field->escaped_value();
		$options    = (array) $this->field->args( 'options' );

		// if options array and 'url' => false, then hide the url field
		$input_type = array_key_exists( 'url', $options ) && false === $options['url'] ? 'hidden' : 'text';

		echo $this->input( array(
			'type'  => $input_type,
			'class' => 'cmb2_upload_file',
			'size'  => 45,
			'desc'  => '',
		) ),
		'<input class="cmb2_upload_button button" type="button" value="'. esc_attr( $this->_text( 'add_upload_file_text', __( 'Add or Upload File', 'cmb2' ) ) ) .'" />',
		$this->_desc( true );

		$cached_id = $this->_id();
		// Reset field args for attachment ID
		$args = $this->field->args();
		$args['id'] = $args['_id'] . '_id';
		unset( $args['_id'], $args['_name'] );

		// And get new field object
		$this->field = new CMB2_Field( array(
			'field_args'  => $args,
			'group_field' => $this->field->group,
			'object_type' => $this->field->object_type(),
			'object_id'   => $this->field->object_id(),
		) );

		// Get ID value
		$_id_value = $this->field->escaped_value( 'absint' );

		// If there is no ID saved yet, try to get it from the url
		if ( $meta_value && ! $_id_value ) {
			$_id_value = cmb2_utils()->image_id_from_url( esc_url_raw( $meta_value ) );
		}

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb2_upload_file_id',
			'value' => $_id_value,
			'desc'  => '',
		) ),
		'<div id="', $this->_id( '_status' ) ,'" class="cmb2_media_status">';
			if ( ! empty( $meta_value ) ) {

				if ( $this->is_valid_img_ext( $meta_value ) ) {
					echo '<div class="img_status">';
					echo '<img style="max-width: 350px; width: 100%; height: auto;" src="', $meta_value, '" alt="" />';
					echo '<p class="cmb2_remove_wrapper"><a href="#" class="cmb2_remove_file_button" rel="', $cached_id, '">'. esc_html( $this->_text( 'remove_image_text', __( 'Remove Image', 'cmb2' ) ) ) .'</a></p>';
					echo '</div>';
				} else {
					// $file_ext = $this->get_file_ext( $meta_value );
					$parts = explode( '/', $meta_value );
					for ( $i = 0; $i < count( $parts ); ++$i ) {
						$title = $parts[$i];
					}
					echo esc_html( $this->_text( 'file_text', __( 'File:', 'cmb2' ) ) ), ' <strong>', $title ,'</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta_value ,'" target="_blank" rel="external">', esc_html( $this->_text( 'file_download_text', __( 'Download', 'cmb2' ) ) ) ,'</a> / <a href="#" class="cmb2_remove_file_button" rel="', $cached_id, '">', esc_html( $this->_text( 'remove_text', __( 'Remove', 'cmb2' ) ) ) ,'</a>)';
				}
			}
		echo '</div>';
	}

	public function oembed() {
		echo $this->input( array(
			'class'           => 'cmb2_oembed regular-text',
			'data-objectid'   => $this->field->object_id,
			'data-objecttype' => $this->field->object_type
		) ),
		'<p class="cmb-spinner spinner" style="display:none;"><img src="'. admin_url( '/images/wpspin_light.gif' ) .'" alt="spinner"/></p>',
		'<div id="',$this->_id( '_status' ) ,'" class="cmb2_media_status ui-helper-clearfix embed_wrap">';

			if ( $meta_value = $this->field->escaped_value() ) {
				echo cmb2_get_oembed( array(
					'url'         => $meta_value,
					'object_id'   => $this->field->object_id,
					'object_type' => $this->field->object_type,
					'oembed_args' => array( 'width' => '640' ),
					'field_id'    => $this->_id(),
				) );
			}

		echo '</div>';
	}

}
