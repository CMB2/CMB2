<?php
/**
 * CMB2 field display base.
 *
 * @since 2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Field_Display {

	/**
	 * A CMB field object
	 *
	 * @var   CMB2_Field object
	 * @since 2.2.2
	 */
	public $field;

	/**
	 * The CMB field object's value.
	 *
	 * @var   mixed
	 * @since 2.2.2
	 */
	public $value;

	/**
	 * Get the corresponding display class for the field type.
	 *
	 * @since  2.2.2
	 * @param  CMB2_Field $field Requested field type.
	 * @return CMB2_Field_Display
	 */
	public static function get( CMB2_Field $field ) {
		$fieldtype          = $field->type();
		$display_class_name = $field->args( 'display_class' );

		if ( empty( $display_class_name ) ) {
			switch ( $fieldtype ) {
				case 'text_url':
					$display_class_name = 'CMB2_Display_Text_Url';
					break;
				case 'text_money':
					$display_class_name = 'CMB2_Display_Text_Money';
					break;
				case 'colorpicker':
					$display_class_name = 'CMB2_Display_Colorpicker';
					break;
				case 'checkbox':
					$display_class_name = 'CMB2_Display_Checkbox';
					break;
				case 'wysiwyg':
				case 'textarea_small':
					$display_class_name = 'CMB2_Display_Textarea';
					break;
				case 'textarea_code':
					$display_class_name = 'CMB2_Display_Textarea_Code';
					break;
				case 'text_time':
					$display_class_name = 'CMB2_Display_Text_Time';
					break;
				case 'text_date':
				case 'text_date_timestamp':
				case 'text_datetime_timestamp':
					$display_class_name = 'CMB2_Display_Text_Date';
					break;
				case 'text_datetime_timestamp_timezone':
					$display_class_name = 'CMB2_Display_Text_Date_Timezone';
					break;
				case 'select':
				case 'radio':
				case 'radio_inline':
					$display_class_name = 'CMB2_Display_Select';
					break;
				case 'multicheck':
				case 'multicheck_inline':
					$display_class_name = 'CMB2_Display_Multicheck';
					break;
				case 'taxonomy_radio':
				case 'taxonomy_radio_inline':
				case 'taxonomy_select':
				case 'taxonomy_select_hierarchical':
				case 'taxonomy_radio_hierarchical':
					$display_class_name = 'CMB2_Display_Taxonomy_Radio';
					break;
				case 'taxonomy_multicheck':
				case 'taxonomy_multicheck_inline':
				case 'taxonomy_multicheck_hierarchical':
					$display_class_name = 'CMB2_Display_Taxonomy_Multicheck';
					break;
				case 'file':
					$display_class_name = 'CMB2_Display_File';
					break;
				case 'file_list':
					$display_class_name = 'CMB2_Display_File_List';
					break;
				case 'oembed':
					$display_class_name = 'CMB2_Display_oEmbed';
					break;
				default:
					$display_class_name = __CLASS__;
					break;
			}// End switch.
		}

		if ( has_action( "cmb2_display_class_{$fieldtype}" ) ) {

			/**
			 * Filters the custom field display class used for displaying the field. Class is required to extend CMB2_Type_Base.
			 *
			 * The dynamic portion of the hook name, $fieldtype, refers to the (custom) field type.
			 *
			 * @since 2.2.4
			 *
			 * @param string $display_class_name The custom field display class to use.
			 * @param object $field              The `CMB2_Field` object.
			 */
			$display_class_name = apply_filters( "cmb2_display_class_{$fieldtype}", $display_class_name, $field );
		}

		return new $display_class_name( $field );
	}

	/**
	 * Setup our class vars
	 *
	 * @since 2.2.2
	 * @param CMB2_Field $field A CMB2 field object.
	 */
	public function __construct( CMB2_Field $field ) {
		$this->field = $field;
		$this->value = $this->field->value;
	}

	/**
	 * Catchall method if field's 'display_cb' is NOT defined, or field type does
	 * not have a corresponding display method
	 *
	 * @since 2.2.2
	 */
	public function display() {
		// If repeatable.
		if ( $this->field->args( 'repeatable' ) ) {

			// And has a repeatable value.
			if ( is_array( $this->field->value ) ) {

				// Then loop and output.
				echo '<ul class="cmb2-' . esc_attr( sanitize_html_class( str_replace( '_', '-', $this->field->type() ) ) ) . '">';
				foreach ( $this->field->value as $val ) {
					$this->value = $val;
					echo '<li>', $this->_display(), '</li>';
					;
				}
				echo '</ul>';
			}
		} else {
			$this->_display();
		}
	}

	/**
	 * Default fallback display method.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		print_r( $this->value );
	}
}

class CMB2_Display_Text_Url extends CMB2_Field_Display {
	/**
	 * Display url value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo make_clickable( esc_url( $this->value ) );
	}
}

class CMB2_Display_Text_Money extends CMB2_Field_Display {
	/**
	 * Display text_money value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		$this->value = $this->value ? $this->value : '0';
		echo ( ! $this->field->get_param_callback_result( 'before_field' ) ? '$' : ' ' ), $this->value;
	}
}

class CMB2_Display_Colorpicker extends CMB2_Field_Display {
	/**
	 * Display color picker value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo '<span class="cmb2-colorpicker-swatch"><span style="background-color:', esc_attr( $this->value ), '"></span> ', esc_html( $this->value ), '</span>';
	}
}

class CMB2_Display_Checkbox extends CMB2_Field_Display {
	/**
	 * Display multicheck value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->value === 'on' ? 'on' : 'off';
	}
}

class CMB2_Display_Select extends CMB2_Field_Display {
	/**
	 * Display select value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		$options = $this->field->options();

		$fallback = $this->field->args( 'show_option_none' );
		if ( ! $fallback && isset( $options[''] ) ) {
			$fallback = $options[''];
		}
		if ( ! $this->value && $fallback ) {
			echo $fallback;
		} elseif ( isset( $options[ $this->value ] ) ) {
			echo $options[ $this->value ];
		} else {
			echo esc_attr( $this->value );
		}
	}
}

class CMB2_Display_Multicheck extends CMB2_Field_Display {
	/**
	 * Display multicheck value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) || ! is_array( $this->value ) ) {
			return;
		}

		$options = $this->field->options();

		$output = array();
		foreach ( $this->value as $val ) {
			if ( isset( $options[ $val ] ) ) {
				$output[] = $options[ $val ];
			} else {
				$output[] = esc_attr( $val );
			}
		}

		echo implode( ', ', $output );
	}
}

class CMB2_Display_Textarea extends CMB2_Field_Display {
	/**
	 * Display textarea value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo wpautop( wp_kses_post( $this->value ) );
	}
}

class CMB2_Display_Textarea_Code extends CMB2_Field_Display {
	/**
	 * Display textarea_code value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo '<xmp class="cmb2-code">' . print_r( $this->value, true ) . '</xmp>';
	}
}

class CMB2_Display_Text_Time extends CMB2_Field_Display {
	/**
	 * Display text_time value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->field->get_timestamp_format( 'time_format', $this->value );
	}
}

class CMB2_Display_Text_Date extends CMB2_Field_Display {
	/**
	 * Display text_date value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->field->get_timestamp_format( 'date_format', $this->value );
	}
}

class CMB2_Display_Text_Date_Timezone extends CMB2_Field_Display {
	/**
	 * Display text_datetime_timestamp_timezone value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) ) {
			return;
		}

		$datetime = CMB2_Utils::get_datetime_from_value( $this->value );
		if ( ! $datetime || ! $datetime instanceof DateTime ) {
			return;
		}

		$date     = $datetime->format( stripslashes( $this->field->args( 'date_format' ) ) );
		$time     = $datetime->format( stripslashes( $this->field->args( 'time_format' ) ) );
		$timezone = $datetime->getTimezone()->getName();

		echo $date;
		if ( $time ) {
			echo ' ' . $time;
		}
		if ( $timezone ) {
			echo ', ' . $timezone;
		}
	}
}

class CMB2_Display_Taxonomy_Radio extends CMB2_Field_Display {
	/**
	 * Display single taxonomy value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		$taxonomy = $this->field->args( 'taxonomy' );
		$types    = new CMB2_Types( $this->field );
		$type     = $types->get_new_render_type( $this->field->type(), 'CMB2_Type_Taxonomy_Radio' );
		$terms    = $type->get_object_terms();
		$term     = false;

		if ( is_wp_error( $terms ) || empty( $terms ) && ( $default = $this->field->get_default() ) ) {
			$term = get_term_by( 'slug', $default, $taxonomy );
		} elseif ( ! empty( $terms ) ) {
			$term = $terms[ key( $terms ) ];
		}

		if ( $term ) {
			$link = get_edit_term_link( $term->term_id, $taxonomy );
			echo '<a href="', esc_url( $link ), '">', esc_html( $term->name ), '</a>';
		}
	}
}

class CMB2_Display_Taxonomy_Multicheck extends CMB2_Field_Display {
	/**
	 * Display taxonomy values.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		$taxonomy = $this->field->args( 'taxonomy' );
		$types    = new CMB2_Types( $this->field );
		$type     = $types->get_new_render_type( $this->field->type(), 'CMB2_Type_Taxonomy_Multicheck' );
		$terms    = $type->get_object_terms();

		if ( is_wp_error( $terms ) || empty( $terms ) && ( $default = $this->field->get_default() ) ) {
			$terms = array();
			if ( is_array( $default ) ) {
				foreach ( $default as $slug ) {
					$terms[] = get_term_by( 'slug', $slug, $taxonomy );
				}
			} else {
				$terms[] = get_term_by( 'slug', $default, $taxonomy );
			}
		}

		if ( is_array( $terms ) ) {

			$links = array();
			foreach ( $terms as $term ) {
				$link = get_edit_term_link( $term->term_id, $taxonomy );
				$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a>';
			}
			// Then loop and output.
			echo '<div class="cmb2-taxonomy-terms-', esc_attr( sanitize_html_class( $taxonomy ) ), '">';
			echo implode( ', ', $links );
			echo '</div>';
		}
	}
}

class CMB2_Display_File extends CMB2_Field_Display {
	/**
	 * Display file value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) ) {
			return;
		}

		$this->value = esc_url_raw( $this->value );

		$types = new CMB2_Types( $this->field );
		$type  = $types->get_new_render_type( $this->field->type(), 'CMB2_Type_File_Base' );

		$id = $this->field->get_field_clone( array(
			'id' => $this->field->_id( '', false ) . '_id',
		) )->escaped_value( 'absint' );

		$this->file_output( $this->value, $id, $type );
	}

	protected function file_output( $url_value, $id, CMB2_Type_File_Base $field_type ) {
		// If there is no ID saved yet, try to get it from the url.
		if ( $url_value && ! $id ) {
			$id = CMB2_Utils::image_id_from_url( esc_url_raw( $url_value ) );
		}

		if ( $field_type->is_valid_img_ext( $url_value ) ) {
			$img_size = $this->field->args( 'preview_size' );

			if ( $id ) {
				$image = wp_get_attachment_image( $id, $img_size, null, array(
					'class' => 'cmb-image-display',
				) );
			} else {
				$size = is_array( $img_size ) ? $img_size[0] : 200;
				$image = '<img class="cmb-image-display" style="max-width: ' . absint( $size ) . 'px; width: 100%; height: auto;" src="' . esc_url( $url_value ) . '" alt="" />';
			}

			echo $image;

		} else {

			printf( '<div class="file-status"><span>%1$s <strong><a href="%2$s">%3$s</a></strong></span></div>',
				esc_html( $field_type->_text( 'file_text', __( 'File:', 'cmb2' ) ) ),
				esc_url( $url_value ),
				esc_html( CMB2_Utils::get_file_name_from_path( $url_value ) )
			);

		}
	}
}

class CMB2_Display_File_List extends CMB2_Display_File {
	/**
	 * Display file_list value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) || ! is_array( $this->value ) ) {
			return;
		}

		$types = new CMB2_Types( $this->field );
		$type  = $types->get_new_render_type( $this->field->type(), 'CMB2_Type_File_Base' );

		echo '<ul class="cmb2-display-file-list">';
		foreach ( $this->value as $id => $fullurl ) {
			echo '<li>', $this->file_output( esc_url_raw( $fullurl ), $id, $type ), '</li>';
		}
		echo '</ul>';
	}
}

class CMB2_Display_oEmbed extends CMB2_Field_Display {
	/**
	 * Display oembed value.
	 *
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( ! $this->value ) {
			return;
		}

		cmb2_do_oembed( array(
			'url'         => $this->value,
			'object_id'   => $this->field->object_id,
			'object_type' => $this->field->object_type,
			'oembed_args' => array(
				'width' => '300',
			),
			'field_id'    => $this->field->id(),
		) );
	}
}
