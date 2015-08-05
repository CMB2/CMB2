<?php
/**
 * CMB field type objects
 *
 * @todo Date/Time fields should store date format as data attribute for JS
 *
 * @since  1.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
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
	 * @var   CMB2_Field object
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
		$this->field->peform_param_callback( 'before_field' );
		echo $this->{$this->field->type()}();
		$this->field->peform_param_callback( 'after_field' );
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

			$cache_key = "cmb-cache-{$taxonomy}-{$object_id}";

			// Check cache
			$cached = get_transient( $cache_key );
			if ( $cached ) {
				return $cached;
			}

			$cached = wp_get_object_terms( $object_id, $taxonomy );
			// Do our own (minimal) caching. Long enough for a page-load.
			set_transient( $cache_key, $cached, 60 );
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
		$has_string_param = $this->field->options( $option_key );
		return $has_string_param ? $has_string_param : $fallback;
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
	 * Get the file name from a url
	 * @since  2.0.0
	 * @param  string $value File url or path
	 * @return string        File name
	 */
	public function get_file_name_from_path( $value ) {
		$parts = explode( '/', $value );
		return is_array( $parts ) ? end( $parts ) : $value;
	}

	/**
	 * Determines if a file has a valid image extension
	 * @since  1.0.0
	 * @param  string $file File url
	 * @return bool         Whether file has a valid image extension
	 */
	public function is_valid_img_ext( $file ) {
		$file_ext = $this->get_file_ext( $file );

		$is_valid_types = apply_filters( 'cmb2_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ) );
		$is_valid = $file_ext && in_array( $file_ext, (array) $is_valid_types );

		return (bool) apply_filters( 'cmb2_' . $this->field->id() . '_is_valid_img_ext', $is_valid, $file, $file_ext );
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
			$excluded = in_array( $attr, (array) $attr_exclude, true );
			$empty    = false === $val && 'value' !== $attr;
			if ( ! $excluded && ! $empty ) {
				// if data attribute, use single quote wraps, else double
				$quotes = false !== stripos( $attr, 'data-' ) ? "'" : '"';
				$attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, $quotes );
			}
		}
		return $attributes;
	}

	/**
	 * Generates html for concatenated items
	 * @since  1.1.0
	 * @param  array   $args Optional arguments
	 * @return string        Concatenated html items
	 */
	public function concat_items( $args = array() ) {

		$method = isset( $args['method'] ) ? $args['method'] : 'select_option';
		unset( $args['method'] );

		$value = $this->field->escaped_value()
			? $this->field->escaped_value()
			: $this->field->args( 'default' );

		$concatenated_items = ''; $i = 1;

		$options = array();
		if ( $option_none = $this->field->args( 'show_option_none' ) ) {
			$options[ '' ] = $option_none;
		}
		$options = $options + (array) $this->field->options();
		foreach ( $options as $opt_value => $opt_label ) {

			// Clone args & modify for just this item
			$a = $args;

			$a['value'] = $opt_value;
			$a['label'] = $opt_label;

			// Check if this option is the value of the input
			if ( $value == $opt_value ) {
				$a['checked'] = 'checked';
			}

			$concatenated_items .= $this->$method( $a, $i++ );
		}

		return $concatenated_items;
	}

	/**
	 * Generates html for an option element
	 * @since  1.1.0
	 * @param  array  $args Arguments array containing value, label, and checked boolean
	 * @return string       Generated option element html
	 */
	public function select_option( $args = array() ) {
		return sprintf( "\t" . '<option value="%s" %s>%s</option>', $args['value'], selected( isset( $args['checked'] ) && $args['checked'], true, false ), $args['label'] ) . "\n";
	}

	/**
	 * Generates html for list item with input
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @param  int    $i    Iterator value
	 * @return string       Gnerated list item html
	 */
	public function list_input( $args = array(), $i ) {
		$a = $this->parse_args( $args, 'list_input', array(
			'type'  => 'radio',
			'class' => 'cmb2-option',
			'name'  => $this->_name(),
			'id'    => $this->_id( $i ),
			'value' => $this->field->escaped_value(),
			'label' => '',
		) );

		return sprintf( "\t" . '<li><input%s/> <label for="%s">%s</label></li>' . "\n", $this->concat_attrs( $a, array( 'label' ) ), $a['id'], $a['label'] );
	}

	/**
	 * Generates html for list item with checkbox input
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @param  int    $i    Iterator value
	 * @return string       Gnerated list item html
	 */
	public function list_input_checkbox( $args, $i ) {
		$saved_value = $this->field->escaped_value();
		if ( is_array( $saved_value ) && in_array( $args['value'], $saved_value ) ) {
			$args['checked'] = 'checked';
		}
		$args['type'] = 'checkbox';
		return $this->list_input( $args, $i );
	}

	/**
	 * Generates repeatable field table markup
	 * @since  1.0.0
	 */
	public function render_repeatable_field() {
		$table_id = $this->field->id() . '_repeat';

		$this->_desc( true, true, true );
		?>

		<div id="<?php echo $table_id; ?>" class="cmb-repeat-table cmb-nested">
			<div class="cmb-tbody cmb-field-list">
				<?php $this->repeatable_rows(); ?>
			</div>
		</div>
		<p class="cmb-add-row">
			<button data-selector="<?php echo $table_id; ?>" class="cmb-add-row-button button"><?php echo esc_html( $this->_text( 'add_row_text', __( 'Add Row', 'cmb2' ) ) ); ?></button>
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
	 * @since 1.1.0
	 * @param bool   $disable_remover Whether remove button should be disabled
	 * @param string $class Repeatable table row's class
	 */
	protected function repeat_row( $disable_remover = false, $class = 'cmb-repeat-row' ) {
		$disabled = $disable_remover ? ' button-disabled' : '';
		?>

		<div class="cmb-row <?php echo $class; ?>">
			<div class="cmb-td">
				<?php $this->_render(); ?>
			</div>
			<div class="cmb-td cmb-remove-row">
				<button class="button cmb-remove-row-button<?php echo $disabled; ?>"><?php echo esc_html( $this->_text( 'remove_row_text', __( 'Remove', 'cmb2' ) ) ); ?></button>
			</div>
		</div>

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

		$desc = $this->field->args( 'description' );

		if ( ! $desc ) {
			return;
		}

		$tag = $paragraph ? 'p' : 'span';
		$desc = sprintf( "\n" . '<%1$s class="cmb2-metabox-description">%2$s</%1$s>' . "\n", $tag, $desc );

		if ( $echo ) {
			echo $desc;
		}
		return $desc;
	}

	/**
	 * Generate field name attribute
	 * @since  1.1.0
	 * @param  string  $suffix For multi-part fields
	 * @return string          Name attribute
	 */
	public function _name( $suffix = '' ) {
		return $this->field->args( '_name' ) . ( $this->field->args( 'repeatable' ) ? '[' . $this->iterator . ']' : '' ) . $suffix;
	}

	/**
	 * Generate field id attribute
	 * @since  1.1.0
	 * @param  string  $suffix For multi-part fields
	 * @return string          Id attribute
	 */
	public function _id( $suffix = '' ) {
		return $this->field->id() . $suffix . ( $this->field->args( 'repeatable' ) ? '_' . $this->iterator . '" data-iterator="' . $this->iterator : '' );
	}

	/**
	 * Handles outputting an 'input' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form input element
	 */
	public function input( $args = array() ) {
		$a = $this->parse_args( $args, 'input', array(
			'type'  => 'text',
			'class' => 'regular-text',
			'name'  => $this->_name(),
			'id'    => $this->_id(),
			'value' => $this->field->escaped_value(),
			'desc'  => $this->_desc( true ),
		) );

		return sprintf( '<input%s/>%s', $this->concat_attrs( $a, array( 'desc' ) ), $a['desc'] );
	}

	/**
	 * Handles outputting an 'textarea' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form textarea element
	 */
	public function textarea( $args = array() ) {
		$a = $this->parse_args( $args, 'textarea', array(
			'class' => 'cmb2_textarea',
			'name'  => $this->_name(),
			'id'    => $this->_id(),
			'cols'  => 60,
			'rows'  => 10,
			'value' => $this->field->escaped_value( 'esc_textarea' ),
			'desc'  => $this->_desc( true ),
		) );
		return sprintf( '<textarea%s>%s</textarea>%s', $this->concat_attrs( $a, array( 'desc', 'value' ) ), $a['value'], $a['desc'] );
	}

	/**
	 * Begin Field Types
	 */

	public function text() {
		return $this->input();
	}

	public function hidden() {
		return $this->input( array( 'type' => 'hidden', 'desc' => '', 'class' => false ) );
	}

	public function text_small() {
		return $this->input( array( 'class' => 'cmb2-text-small', 'desc' => $this->_desc() ) );
	}

	public function text_medium() {
		return $this->input( array( 'class' => 'cmb2-text-medium', 'desc' => $this->_desc() ) );
	}

	public function text_email() {
		return $this->input( array( 'class' => 'cmb2-text-email cmb2-text-medium', 'type' => 'email' ) );
	}

	public function text_url() {
		return $this->input( array( 'class' => 'cmb2-text-url cmb2-text-medium regular-text', 'value' => $this->field->escaped_value( 'esc_url' ) ) );
	}

	public function text_money() {
		return ( ! $this->field->get_param_callback_result( 'before_field' ) ? '$ ' : ' ' ) . $this->input( array( 'class' => 'cmb2-text-money', 'desc' => $this->_desc() ) );
	}

	public function textarea_small() {
		return $this->textarea( array( 'class' => 'cmb2-textarea-small', 'rows' => 4 ) );
	}

	public function textarea_code() {
		return sprintf( '<pre>%s', $this->textarea( array( 'class' => 'cmb2-textarea-code', 'desc' => '</pre>' . $this->_desc( true ) ) ) );
	}

	public function wysiwyg( $args = array() ) {
		$a = $this->parse_args( $args, 'input', array(
			'id'      => $this->_id(),
			'value'   => $this->field->escaped_value( 'stripslashes' ),
			'desc'    => $this->_desc( true ),
			'options' => $this->field->options(),
		) );

		wp_editor( $a['value'], $a['id'], $a['options'] );
		echo $a['desc'];
	}

	public function text_date( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'class' => 'cmb2-text-small cmb2-datepicker',
			'value' => $this->field->get_timestamp_format(),
			'desc'  => $this->_desc(),
		) );

		CMB2_JS::add_dependencies( array( 'jquery-ui-core', 'jquery-ui-datepicker' ) );

		return $this->input( $args );
	}

	// Alias for text_date
	public function text_date_timestamp( $args = array() ) {
		return $this->text_date( $args );
	}

	public function text_time( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'class' => 'cmb2-timepicker text-time',
			'value' => $this->field->get_timestamp_format( 'time_format' ),
			'desc' => $this->_desc(),
		) );

		CMB2_JS::add_dependencies( array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ) );

		return $this->input( $args );
	}

	public function text_datetime_timestamp( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'value'      => $this->field->escaped_value(),
			'desc'       => $this->_desc(),
			'datepicker' => array(),
			'timepicker' => array(),
		) );

		if ( empty( $args['value'] ) ) {
			$args['value'] = $this->field->escaped_value();
			// This will be used if there is a select_timezone set for this field
			$tz_offset = $this->field->field_timezone_offset();
			if ( ! empty( $tz_offset ) ) {
				$args['value'] -= $tz_offset;
			}
		}

		$has_good_value = ! empty( $args['value'] ) && ! is_array( $args['value'] );

		$date_args = wp_parse_args( $args['datepicker'], array(
			'class' => 'cmb2-text-small cmb2-datepicker',
			'name'  => $this->_name( '[date]' ),
			'id'    => $this->_id( '_date' ),
			'value' => $has_good_value ? $this->field->get_timestamp_format( 'date_format', $args['value'] ) : '',
			'desc'  => '',
		) );

		$time_args = wp_parse_args( $args['timepicker'], array(
			'class' => 'cmb2-timepicker text-time',
			'name'  => $this->_name( '[time]' ),
			'id'    => $this->_id( '_time' ),
			'value' => $has_good_value ? $this->field->get_timestamp_format( 'time_format', $args['value'] ) : '',
			'desc'  => $args['desc'],
		) );

		CMB2_JS::add_dependencies( array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ) );

		return $this->input( $date_args ) . "\n" . $this->input( $time_args );
	}

	public function text_datetime_timestamp_timezone( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'value'                   => $this->field->escaped_value(),
			'desc'                    => $this->_desc( true ),
			'text_datetime_timestamp' => array(),
			'select_timezone'         => array(),
		) );

		$args['value'] = $this->field->escaped_value();
		if ( is_array( $args['value'] ) ) {
			$args['value'] = '';
		}

		$datetime = unserialize( $args['value'] );
		$args['value'] = $tzstring = '';

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz            = $datetime->getTimezone();
			$tzstring      = $tz->getName();
			$args['value'] = $datetime->getTimestamp() + $tz->getOffset( new DateTime( 'NOW' ) );
		}

		$timestamp_args = wp_parse_args( $args['text_datetime_timestamp'], array(
			'desc'  => '',
			'value' => $args['value'],
		) );

		$timezone_args = wp_parse_args( $args['select_timezone'], array(
			'class'   => 'cmb2_select cmb2-select-timezone',
			'name'    => $this->_name( '[timezone]' ),
			'id'      => $this->_id( '_timezone' ),
			'options' => wp_timezone_choice( $tzstring ),
			'desc'    => $args['desc'],
		) );

		return $this->text_datetime_timestamp( $timestamp_args ) . "\n" . $this->select( $timezone_args );
	}

	public function select_timezone() {
		$this->field->args['default'] = $this->field->args( 'default' )
			? $this->field->args( 'default' )
			: cmb2_utils()->timezone_string();

		return $this->select( array(
			'class'   => 'cmb2_select cmb2-select-timezone',
			'options' => wp_timezone_choice( $this->field->escaped_value() ),
			'desc'    => $this->_desc(),
		) );
	}

	public function colorpicker() {
		$meta_value = $this->field->escaped_value();
		$hex_color = '(([a-fA-F0-9]){3}){1,2}$';
		if ( preg_match( '/^' . $hex_color . '/i', $meta_value ) ) {
			// Value is just 123abc, so prepend #
			$meta_value = '#' . $meta_value;
		} elseif ( ! preg_match( '/^#' . $hex_color . '/i', $meta_value ) ) {
			// Value doesn't match #123abc, so sanitize to just #
			$meta_value = '#';
		}

		wp_enqueue_style( 'wp-color-picker' );
		CMB2_JS::add_dependencies( array( 'wp-color-picker' ) );

		return $this->input( array( 'class' => 'cmb2-colorpicker cmb2-text-small', 'value' => $meta_value ) );
	}

	public function title( $args = array() ) {
		$a = $this->parse_args( $args, 'title', array(
			'tag'   => $this->field->object_type == 'post' ? 'h5' : 'h3',
			'class' => 'cmb2-metabox-title',
			'name'  => $this->field->args( 'name' ),
			'desc'  => $this->_desc( true ),
		) );

		return sprintf( '<%1$s class="%2$s">%3$s</%1$s>%4$s', $a['tag'], $a['class'], $a['name'], $a['desc'] );
	}

	public function select( $args = array() ) {
		$a = $this->parse_args( $args, 'select', array(
			'class'   => 'cmb2_select',
			'name'    => $this->_name(),
			'id'      => $this->_id(),
			'desc'    => $this->_desc( true ),
			'options' => $this->concat_items(),
		) );

		$attrs = $this->concat_attrs( $a, array( 'desc', 'options' ) );
		return sprintf( '<select%s>%s</select>%s', $attrs, $a['options'], $a['desc'] );
	}

	public function taxonomy_select() {

		$names      = $this->get_object_terms();
		$saved_term = is_wp_error( $names ) || empty( $names ) ? $this->field->args( 'default' ) : $names[key( $names )]->slug;
		$terms      = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$options    = '';

		$option_none  = $this->field->args( 'show_option_none' );
		if ( ! empty( $option_none ) ) {
			$option_none_value = apply_filters( 'cmb2_taxonomy_select_default_value', '' );
			$option_none_value = apply_filters( "cmb2_taxonomy_select_{$this->_id()}_default_value", $option_none_value );

			$options .= $this->select_option( array(
				'label'   => $option_none,
				'value'   => $option_none_value,
				'checked' => $saved_term == $option_none_value,
			) );
		}

		foreach ( $terms as $term ) {
			$options .= $this->select_option( array(
				'label'   => $term->name,
				'value'   => $term->slug,
				'checked' => $saved_term == $term->slug,
			) );
		}

		return $this->select( array( 'options' => $options ) );
	}

	public function radio( $args = array(), $type = 'radio' ) {
		$a = $this->parse_args( $args, $type, array(
			'class'   => 'cmb2-radio-list cmb2-list',
			'options' => $this->concat_items( array( 'label' => 'test', 'method' => 'list_input' ) ),
			'desc'    => $this->_desc( true ),
		) );

		return sprintf( '<ul class="%s">%s</ul>%s', $a['class'], $a['options'], $a['desc'] );
	}

	public function radio_inline() {
		return $this->radio( array(), 'radio_inline' );
	}

	public function multicheck( $type = 'checkbox' ) {

		$classes = false === $this->field->args( 'select_all_button' )
			? 'cmb2-checkbox-list no-select-all cmb2-list'
			: 'cmb2-checkbox-list cmb2-list';

		return $this->radio( array( 'class' => $classes, 'options' => $this->concat_items( array( 'name' => $this->_name() . '[]', 'method' => 'list_input_checkbox' ) ) ), $type );
	}

	public function multicheck_inline() {
		return $this->multicheck( 'multicheck_inline' );
	}

	public function checkbox() {
		$meta_value = $this->field->escaped_value();
		$args = array( 'type' => 'checkbox', 'class' => 'cmb2-option cmb2-list', 'value' => 'on', 'desc' => '' );
		if ( ! empty( $meta_value ) ) {
			$args['checked'] = 'checked';
		}
		return sprintf( '%s <label for="%s">%s</label>', $this->input( $args ), $this->_id(), $this->_desc() );
	}

	public function taxonomy_radio() {
		$names      = $this->get_object_terms();
		$saved_term = is_wp_error( $names ) || empty( $names ) ? $this->field->args( 'default' ) : $names[key( $names )]->slug;
		$terms      = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$options    = ''; $i = 1;

		if ( ! $terms ) {
			$options .= sprintf( '<li><label>%s</label></li>', esc_html( $this->_text( 'no_terms_text', __( 'No terms', 'cmb2' ) ) ) );
		} else {
			$option_none  = $this->field->args( 'show_option_none' );
			if ( ! empty( $option_none ) ) {
				$option_none_value = apply_filters( "cmb2_taxonomy_radio_{$this->_id()}_default_value", apply_filters( 'cmb2_taxonomy_radio_default_value', '' ) );
				$args = array(
					'value' => $option_none_value,
					'label' => $option_none,
				);
				if ( $saved_term == $option_none_value ) {
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
		return $this->taxonomy_radio();
	}

	public function taxonomy_multicheck() {

		$names       = $this->get_object_terms();
		$saved_terms = is_wp_error( $names ) || empty( $names )
			? $this->field->args( 'default' )
			: wp_list_pluck( $names, 'slug' );
		$terms       = get_terms( $this->field->args( 'taxonomy' ), 'hide_empty=0' );
		$name        = $this->_name() . '[]';
		$options     = ''; $i = 1;

		if ( ! $terms ) {
			$options .= sprintf( '<li><label>%s</label></li>', esc_html( $this->_text( 'no_terms_text', __( 'No terms', 'cmb2' ) ) ) );
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
			? 'cmb2-checkbox-list no-select-all cmb2-list'
			: 'cmb2-checkbox-list cmb2-list';

		return $this->radio( array( 'class' => $classes, 'options' => $options ), 'taxonomy_multicheck' );
	}

	public function taxonomy_multicheck_inline() {
		return $this->taxonomy_multicheck();
	}

	public function oembed() {
		$meta_value = trim( $this->field->escaped_value() );
		$oembed = ! empty( $meta_value )
			? cmb2_get_oembed( array(
				'url'         => $this->field->escaped_value(),
				'object_id'   => $this->field->object_id,
				'object_type' => $this->field->object_type,
				'oembed_args' => array( 'width' => '640' ),
				'field_id'    => $this->_id(),
			) )
			: '';

		echo $this->input( array(
			'class'           => 'cmb2-oembed regular-text',
			'data-objectid'   => $this->field->object_id,
			'data-objecttype' => $this->field->object_type,
		) ),
		'<p class="cmb-spinner spinner" style="display:none;"></p>',
		'<div id="', $this->_id( '-status' ), '" class="cmb2-media-status ui-helper-clearfix embed_wrap">', $oembed, '</div>';
	}

	public function file_list() {
		$meta_value = $this->field->escaped_value();
		$name       = $this->_name();
		$img_size   = $this->field->args( 'preview_size' );
		$query_args = $this->field->args( 'query_args' );

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb2-upload-file cmb2-upload-list',
			'size'  => 45, 'desc'  => '', 'value'  => '',
			'data-previewsize' => is_array( $img_size ) ? sprintf( '[%s]', implode( ',', $img_size ) ) : 50,
			'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
		) ),
		$this->input( array(
			'type'  => 'button',
			'class' => 'cmb2-upload-button button cmb2-upload-list',
			'value'  => esc_html( $this->_text( 'add_upload_files_text', __( 'Add or Upload Files', 'cmb2' ) ) ),
			'name'  => '', 'id'  => '',
		) );

		echo '<ul id="', $this->_id( '-status' ), '" class="cmb2-media-status cmb-attach-list">';

		if ( $meta_value && is_array( $meta_value ) ) {

			foreach ( $meta_value as $id => $fullurl ) {
				$id_input = $this->input( array(
					'type'    => 'hidden',
					'value'   => $fullurl,
					'name'    => $name . '[' . $id . ']',
					'id'      => 'filelist-' . $id,
					'data-id' => $id,
					'desc'    => '',
					'class'   => false,
				) );

				if ( $this->is_valid_img_ext( $fullurl ) ) {

					$this->img_status_output( array(
						'image'    => wp_get_attachment_image( $id, $img_size ),
						'tag'      => 'li',
						'id_input' => $id_input,
					) );

				} else {

					$this->file_status_output( array(
						'value'    => $fullurl,
						'tag'      => 'li',
						'id_input' => $id_input,
					) );

				}
			}
		}

		echo '</ul>';

		CMB2_JS::add_dependencies( 'media-editor' );
	}

	public function file() {
		$meta_value = $this->field->escaped_value();
		$options    = (array) $this->field->options();
		$img_size   = $this->field->args( 'preview_size' );
		$query_args = $this->field->args( 'query_args' );

		// if options array and 'url' => false, then hide the url field
		$input_type = array_key_exists( 'url', $options ) && false === $options['url'] ? 'hidden' : 'text';

		echo $this->input( array(
			'type'  => $input_type,
			'class' => 'cmb2-upload-file regular-text',
			'size'  => 45,
			'desc'  => '',
			'data-previewsize' => is_array( $img_size ) ? '[' . implode( ',', $img_size ) . ']' : 350,
			'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
		) );

		printf( '<input class="cmb2-upload-button button" type="button" value="%s" />', esc_attr( $this->_text( 'add_upload_file_text', __( 'Add or Upload File', 'cmb2' ) ) ) );

		$this->_desc( true, true );

		// If we're looking at a file in a group, we need to get the non-prefixed id
		$cached_id = $this->field->group ? $this->field->args( '_id' ) : $this->_id();

		// Reset field args for attachment ID
		$args = $this->field->args();
		$args['id'] = $cached_id . '_id';
		unset( $args['_id'], $args['_name'] );

		// And get new field object
		$this->field = new CMB2_Field( array(
			'field_args'  => $args,
			'group_field' => $this->field->group,
			'object_type' => $this->field->object_type,
			'object_id'   => $this->field->object_id,
		) );

		// Get ID value
		$_id_value = $this->field->escaped_value( 'absint' );

		// If there is no ID saved yet, try to get it from the url
		if ( $meta_value && ! $_id_value ) {
			$_id_value = cmb2_utils()->image_id_from_url( esc_url_raw( $meta_value ) );
		}

		echo $this->input( array(
			'type'  => 'hidden',
			'class' => 'cmb2-upload-file-id',
			'value' => $_id_value,
			'desc'  => '',
		) ),
		'<div id="', $this->_id( '-status' ), '" class="cmb2-media-status">';
			if ( ! empty( $meta_value ) ) {

				if ( $this->is_valid_img_ext( $meta_value ) ) {

					if ( $_id_value ) {
						$image = wp_get_attachment_image( $_id_value, $img_size, null, array( 'class' => 'cmb-file-field-image' ) );
					} else {
						$size = is_array( $img_size ) ? $img_size[0] : 350;
						$image = '<img style="max-width: ' . absint( $size ) . 'px; width: 100%; height: auto;" src="' . $meta_value . '" alt="" />';
					}

					$this->img_status_output( array(
						'image'     => $image,
						'tag'       => 'div',
						'cached_id' => $cached_id,
					) );

				} else {

					$this->file_status_output( array(
						'value'     => $meta_value,
						'tag'       => 'div',
						'cached_id' => $cached_id,
					) );

				}
			}
		echo '</div>';

		CMB2_JS::add_dependencies( 'media-editor' );
	}

	/**
	 * file/file_list image wrap
	 * @since  2.0.2
	 * @param  array  $args Array of arguments for output
	 * @return string       Image wrap output
	 */
	public function img_status_output( $args ) {
		printf( '<%1$s class="img-status">%2$s<p class="cmb2-remove-wrapper"><a href="#" class="cmb2-remove-file-button"%3$s>%4$s</a></p>%5$s</%1$s>',
			$args['tag'],
			$args['image'],
			isset( $args['cached_id'] ) ? ' rel="' . $args['cached_id'] . '"' : '',
			esc_html( $this->_text( 'remove_image_text', __( 'Remove Image', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

	/**
	 * file/file_list file wrap
	 * @since  2.0.2
	 * @param  array  $args Array of arguments for output
	 * @return string       File wrap output
	 */
	public function file_status_output( $args ) {
		printf( '<%1$s class="file-status"><span>%2$s <strong>%3$s</strong></span>&nbsp;&nbsp; (<a href="%4$s" target="_blank" rel="external">%5$s</a> / <a href="#" class="cmb2-remove-file-button"%6$s>%7$s</a>)%8$s</%1$s>',
			$args['tag'],
			esc_html( $this->_text( 'file_text', __( 'File:', 'cmb2' ) ) ),
			$this->get_file_name_from_path( $args['value'] ),
			$args['value'],
			esc_html( $this->_text( 'file_download_text', __( 'Download', 'cmb2' ) ) ),
			isset( $args['cached_id'] ) ? ' rel="' . $args['cached_id'] . '"' : '',
			esc_html( $this->_text( 'remove_text', __( 'Remove', 'cmb2' ) ) ),
			isset( $args['id_input'] ) ? $args['id_input'] : ''
		);
	}

}
