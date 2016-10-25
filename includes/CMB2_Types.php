<?php
/**
 * CMB field type objects
 *
 * @since  1.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 *
 * @method string _id()
 * @method string _name()
 * @method string _desc()
 * @method string _text()
 * @method string concat_attrs()
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

	/**
	 * Current CMB2_Type_Base object
	 * @var   CMB2_Type_Base object
	 * @since 2.2.2
	 */
	public $type = null;

	public function __construct( CMB2_Field $field ) {
		$this->field = $field;
	}

	/**
	 * Default fallback. Allows rendering fields via "cmb2_render_$fieldtype" hook
	 * @since 1.0.0
	 * @param string $fieldtype Non-existent field type name
	 * @param array  $arguments All arguments passed to the method
	 */
	public function __call( $fieldtype, $arguments ) {
		$proxied = array(
			'get_object_terms' => array(),
			'is_valid_img_ext' => false,
			'parse_args' => array(),
			'concat_items' => '',
			'select_option' => '',
			'list_input' => '',
			'list_input_checkbox' => '',
			'img_status_output' => '',
			'file_status_output' => '',
			'parse_picker_options' => array(),
		);
		if ( isset( $proxied[ $fieldtype ] ) ) {
			// Proxies the method call to the CMB2_Type_Base object
			return $this->proxy_method( $fieldtype, $proxied[ $fieldtype ], $arguments );
		}

		/**
		 * Pass non-existent field types through an action
		 *
		 * The dynamic portion of the hook name, $fieldtype, refers to the field type.
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
		do_action( "cmb2_render_{$fieldtype}", $this->field, $this->field->escaped_value(), $this->field->object_id, $this->field->object_type, $this );
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
	 * Proxies the method call to the CMB2_Type_Base object, if it exists, otherwise returns a default fallback value.
	 *
	 * @since  2.2.2
	 *
	 * @param  string $method  Method to call on the CMB2_Type_Base object.
	 * @param  mixed  $default Default fallback value if method is not found.
	 *
	 * @return mixed           Results from called method.
	 */
	protected function proxy_method( $method, $default, $args = array() ) {
		if ( ! is_object( $this->type ) ) {
			$this->guess_type_object( $method );
		}

		if ( is_object( $this->type ) && method_exists( $this->type, $method ) ) {

			return empty( $args )
				? $this->type->$method()
				: call_user_func_array( array( $this->type, $method ), $args );
		}

		return $default;
	}

	/**
	 * If no CMB2_Types::$type object is initiated when a proxy method is called, it means
	 * it's a custom field type (which SHOULD be instantiating a Type), but let's try and
	 * guess the type object for them and instantiate it.
	 *
	 * @since  2.2.3
	 *
	 * @param string $method  Method attempting to be called on the CMB2_Type_Base object.
	 */
	protected function guess_type_object( $method ) {

		// Try to "guess" the Type object based on the method requested.
		switch ( $method ) {
			case 'select_option':
			case 'list_input':
			case 'list_input_checkbox':
			case 'concat_items':
				$this->type = new CMB2_Type_Select( $this );
				break;
			case 'is_valid_img_ext':
			case 'img_status_output':
			case 'file_status_output':
				$this->type = new CMB2_Type_File_Base( $this );
				break;
			case 'parse_picker_options':
				$this->type = new CMB2_Type_Text_Date( $this );
				break;
			case 'get_object_terms':
			case 'get_terms':
				$this->type = new CMB2_Type_Taxonomy_Multicheck( $this );
				break;
			case 'date_args':
			case 'time_args':
				$this->type = new CMB2_Type_Text_Datetime_Timestamp( $this );
				break;
			case 'parse_args':
				$this->type = new CMB2_Type_Text( $this );
				break;
		}

		return null !== $this->type;
	}

	/**
	 * Retrieve text parameter from field's options array (if it has one), or use fallback text
	 * @since  2.0.0
	 * @param  string  $text_key Key in field's options array
	 * @param  string  $fallback Fallback text
	 * @return string            Text
	 */
	public function _text( $text_key, $fallback = '' ) {
		return $this->field->get_string( $text_key, $fallback );
	}

	/**
	 * Determine a file's extension
	 * @since  1.0.0
	 * @param  string       $file File url
	 * @return string|false       File extension or false
	 */
	public function get_file_ext( $file ) {
		return CMB2_Utils::get_file_ext( $file );
	}

	/**
	 * Get the file name from a url
	 * @since  2.0.0
	 * @param  string $value File url or path
	 * @return string        File name
	 */
	public function get_file_name_from_path( $value ) {
		return CMB2_Utils::get_file_name_from_path( $value );
	}

	/**
	 * Combines attributes into a string for a form element
	 * @since  1.1.0
	 * @param  array  $attrs        Attributes to concatenate
	 * @param  array  $attr_exclude Attributes that should NOT be concatenated
	 * @return string               String of attributes for form element
	 */
	public function concat_attrs( $attrs, $attr_exclude = array() ) {
		return CMB2_Utils::concat_attrs( $attrs, $attr_exclude );
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
			<button type="button" data-selector="<?php echo $table_id; ?>" class="cmb-add-row-button button"><?php echo esc_html( $this->_text( 'add_row_text', esc_html__( 'Add Row', 'cmb2' ) ) ); ?></button>
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
		$default    = $this->field->get_default();

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

			// If value is empty (including empty array), then clear the value.
			$this->field->escaped_value = $this->field->value = null;

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
				<button type="button" class="button cmb-remove-row-button<?php echo $disabled; ?>"><?php echo esc_html( $this->_text( 'remove_row_text', esc_html__( 'Remove', 'cmb2' ) ) ); ?></button>
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
	 * @param  string $type Field type
	 * @return string       Form input element
	 */
	public function input( $args = array(), $type = __FUNCTION__ ) {
		$this->type = new CMB2_Type_Text( $this, $args, $type );
		return $this->type->render();
	}

	/**
	 * Handles outputting an 'textarea' element
	 * @since  1.1.0
	 * @param  array  $args Override arguments
	 * @return string       Form textarea element
	 */
	public function textarea( $args = array() ) {
		$this->type = new CMB2_Type_Textarea( $this, $args );
		return $this->type->render();
	}

	/**
	 * Begin Field Types
	 */

	public function text() {
		return $this->input();
	}

	public function hidden() {
		$args = array(
			'type' => 'hidden',
			'desc' => '',
			'class' => 'cmb2-hidden',
		);
		if ( $this->field->group ) {
			$args['data-groupid'] = $this->field->group->id();
			$args['data-iterator'] = $this->iterator;
		}
		return $this->input( $args );
	}

	public function text_small() {
		return $this->input( array(
			'class' => 'cmb2-text-small',
			'desc' => $this->_desc(),
		) );
	}

	public function text_medium() {
		return $this->input( array(
			'class' => 'cmb2-text-medium',
			'desc' => $this->_desc(),
		) );
	}

	public function text_email() {
		return $this->input( array(
			'class' => 'cmb2-text-email cmb2-text-medium',
			'type' => 'email',
		) );
	}

	public function text_url() {
		return $this->input( array(
			'class' => 'cmb2-text-url cmb2-text-medium regular-text',
			'value' => $this->field->escaped_value( 'esc_url' ),
		) );
	}

	public function text_money() {
		$input = $this->input( array(
			'class' => 'cmb2-text-money',
			'desc' => $this->_desc(),
		) );
		return ( ! $this->field->get_param_callback_result( 'before_field' ) ? '$ ' : ' ' ) . $input;
	}

	public function textarea_small() {
		return $this->textarea( array(
			'class' => 'cmb2-textarea-small',
			'rows' => 4,
		) );
	}

	public function textarea_code() {
		$this->type = new CMB2_Type_Textarea_Code( $this );
		return $this->type->render();
	}

	public function wysiwyg( $args = array() ) {
		$this->type = new CMB2_Type_Wysiwyg( $this, $args );
		return $this->type->render();
	}

	public function text_date( $args = array() ) {
		$this->type = new CMB2_Type_Text_Date( $this, $args );
		return $this->type->render();
	}

	// Alias for text_date
	public function text_date_timestamp( $args = array() ) {
		return $this->text_date( $args );
	}

	public function text_time( $args = array() ) {
		$this->type = new CMB2_Type_Text_Time( $this, $args );
		return $this->type->render();
	}

	public function text_datetime_timestamp( $args = array() ) {
		$this->type = new CMB2_Type_Text_Datetime_Timestamp( $this, $args );
		return $this->type->render();
	}

	public function text_datetime_timestamp_timezone( $args = array() ) {
		$this->type = new CMB2_Type_Text_Datetime_Timestamp_Timezone( $this, $args );
		return $this->type->render();
	}

	public function select_timezone() {
		$this->type = new CMB2_Type_Select_Timezone( $this );
		return $this->type->render();
	}

	public function colorpicker( $args = array(), $meta_value = '' ) {
		$this->type = new CMB2_Type_Colorpicker( $this, $args, $meta_value );
		return $this->type->render();
	}

	public function title( $args = array() ) {
		$this->type = new CMB2_Type_Title( $this, $args );
		return $this->type->render();
	}

	public function select( $args = array() ) {
		$this->type = new CMB2_Type_Select( $this, $args );
		return $this->type->render();
	}

	public function taxonomy_select() {
		$this->type = new CMB2_Type_Taxonomy_Select( $this );
		return $this->type->render();
	}

	public function radio( $args = array(), $type = __FUNCTION__ ) {
		$this->type = new CMB2_Type_Radio( $this, $args, $type );
		return $this->type->render();
	}

	public function radio_inline() {
		return $this->radio( array(), __FUNCTION__ );
	}

	public function multicheck( $type = 'checkbox' ) {
		$this->type = new CMB2_Type_Multicheck( $this, array(), $type );
		return $this->type->render();
	}

	public function multicheck_inline() {
		return $this->multicheck( 'multicheck_inline' );
	}

	public function checkbox( $args = array(), $is_checked = null ) {
		$this->type = new CMB2_Type_Checkbox( $this, $args, $is_checked );
		return $this->type->render();
	}

	public function taxonomy_radio() {
		$this->type = new CMB2_Type_Taxonomy_Radio( $this );
		return $this->type->render();
	}

	public function taxonomy_radio_inline() {
		return $this->taxonomy_radio();
	}

	public function taxonomy_multicheck() {
		$this->type = new CMB2_Type_Taxonomy_Multicheck( $this );
		return $this->type->render();
	}

	public function taxonomy_multicheck_inline() {
		return $this->taxonomy_multicheck();
	}

	public function oembed() {
		$this->type = new CMB2_Type_Oembed( $this );
		return $this->type->render();
	}

	public function file_list() {
		$this->type = new CMB2_Type_File_List( $this );
		return $this->type->render();
	}

	public function file() {
		$this->type = new CMB2_Type_File( $this );
		return $this->type->render();
	}

}
