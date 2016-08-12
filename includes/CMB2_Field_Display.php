<?php
/**
 * CMB2 field display base.
 *
 * @since 2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Field_Display {

	/**
	 * A CMB field object
	 * @var   CMB2_Field object
	 * @since 2.2.2
	 */
	public $field;

	/**
	 * The CMB field object's value.
	 * @var   mixed
	 * @since 2.2.2
	 */
	public $value;

	/**
	 * Get the corresponding display class for the field type.
	 * @since  2.2.2
	 * @param  CMB2_Field $field
	 * @return CMB2_Field_Display
	 */
	public static function get( CMB2_Field $field ) {
		switch ( $field->type() ) {
			case 'text_url':
				$type = new CMB2_Display_Text_Url( $field );
				break;
			case 'text_money':
				$type = new CMB2_Display_Text_Money( $field );
				break;
			case 'colorpicker':
				$type = new CMB2_Display_Colorpicker( $field );
				break;
			case 'checkbox':
				$type = new CMB2_Display_Checkbox( $field );
				break;
			case 'wysiwyg':
			case 'textarea_small':
				$type = new CMB2_Display_Textarea( $field );
				break;
			case 'textarea_code':
				$type = new CMB2_Display_Textarea_Code( $field );
				break;
			case 'text_time':
				$type = new CMB2_Display_Text_Time( $field );
				break;
			case 'text_date':
			case 'text_date_timestamp':
			case 'text_datetime_timestamp':
				$type = new CMB2_Display_Text_Date( $field );
				break;
			case 'text_datetime_timestamp_timezone':
				$type = new CMB2_Display_Text_Date_Timezone( $field );
				break;
			case 'select':
			case 'radio':
			case 'radio_inline':
				$type = new CMB2_Display_Select( $field );
				break;
			case 'multicheck':
			case 'multicheck_inline':
				$type = new CMB2_Display_Multicheck( $field );
				break;
			case 'taxonomy_radio':
			case 'taxonomy_radio_inline':
			case 'taxonomy_select':
				$type = new CMB2_Display_Taxonomy_Radio( $field );
				break;
			case 'taxonomy_multicheck':
			case 'taxonomy_multicheck_inline':
				$type = new CMB2_Display_Taxonomy_Multicheck( $field );
				break;
			case 'file':
				$type = new CMB2_Display_File( $field );
				break;
			case 'file_list':
				$type = new CMB2_Display_File_List( $field );
				break;
			case 'oembed':
				$type = new CMB2_Display_oEmbed( $field );
				break;
			default:
				$type = new self( $field );
				break;
		}

		return $type;
	}

	/**
	 * Setup our class vars
	 * @since 2.2.2
	 * @param CMB2_Field $field A CMB2 field object
	 */
	public function __construct( CMB2_Field $field ) {
		$this->field = $field;
		$this->value = $this->field->value;
	}

	/**
	 * Catchall method if field's 'display_cb' is NOT defined, or field type does
	 * not have a corresponding display method
	 * @since 2.2.2
	 * @param  string $method    Non-existent method name
	 * @param  array  $arguments All arguments passed to the method
	 */
	public function display() {
		// If repeatable
		if ( $this->field->args( 'repeatable' ) ) {

			// And has a repeatable value
			if ( is_array( $this->field->value ) ) {

				// Then loop and output.
				echo '<ul class="cmb2-'. str_replace( '_', '-', $this->field->type() ) .'">';
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
	 * @since 2.2.2
	 */
	protected function _display() {
		print_r( $this->value );
	}
}

class CMB2_Display_Text_Url extends CMB2_Field_Display {
	/**
	 * Display url value.
	 * @since 2.2.2
	 */
	protected function _display() {
		echo make_clickable( esc_url( $this->value ) );
	}
}

class CMB2_Display_Text_Money extends CMB2_Field_Display {
	/**
	 * Display text_money value.
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
	 * @since 2.2.2
	 */
	protected function _display() {
		echo '<span class="cmb2-colorpicker-swatch"><span style="background-color:', esc_attr( $this->value ), '"></span> ', esc_html( $this->value ), '</span>';
	}
}

class CMB2_Display_Checkbox extends CMB2_Field_Display {
	/**
	 * Display multicheck value.
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->value === 'on' ? 'on' : 'off';
	}
}

class CMB2_Display_Select extends CMB2_Field_Display {
	/**
	 * Display select value.
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
	 * @since 2.2.2
	 */
	protected function _display() {
		echo wpautop( wp_kses_post( $this->value ) );
	}
}

class CMB2_Display_Textarea_Code extends CMB2_Field_Display {
	/**
	 * Display textarea_code value.
	 * @since 2.2.2
	 */
	protected function _display() {
		echo '<xmp class="cmb2-code">'. print_r( $this->value, true ) .'</xmp>';
	}
}

class CMB2_Display_Text_Time extends CMB2_Field_Display {
	/**
	 * Display text_time value.
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->field->get_timestamp_format( 'time_format', $this->value );
	}
}

class CMB2_Display_Text_Date extends CMB2_Field_Display {
	/**
	 * Display text_date value.
	 * @since 2.2.2
	 */
	protected function _display() {
		echo $this->field->get_timestamp_format( 'date_format', $this->value );
	}
}

class CMB2_Display_Text_Date_Timezone extends CMB2_Field_Display {
	/**
	 * Display text_datetime_timestamp_timezone value.
	 * @since 2.2.2
	 */
	protected function _display() {
		$field = $this->field;

		if ( empty( $this->value ) ) {
			return;
		}

		$datetime = maybe_unserialize( $this->value );
		$this->value = $tzstring = '';

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz       = $datetime->getTimezone();
			$tzstring = $tz->getName();
			$this->value    = $datetime->getTimestamp();
		}

		$date = $this->field->get_timestamp_format( 'date_format', $this->value );
		$time = $this->field->get_timestamp_format( 'time_format', $this->value );

		echo $date, ( $time ? ' ' . $time : '' ), ( $tzstring ? ', ' . $tzstring : '' );
	}
}

class CMB2_Display_Taxonomy_Radio extends CMB2_Field_Display {
	/**
	 * Display single taxonomy value.
	 * @since 2.2.2
	 */
	protected function _display() {
		$taxonomy   = $this->field->args( 'taxonomy' );
		$field_type = new CMB2_Type_Taxonomy_Radio( new CMB2_Types( $this->field ) );
		$terms      = $field_type->get_object_terms();
		$term       = false;

		if ( is_wp_error( $terms ) || empty( $terms ) && ( $default = $this->field->get_default() ) ) {
			$term = get_term_by( 'slug', $default, $taxonomy );
		} elseif ( ! empty( $terms ) ) {
			$term = $terms[key( $terms )];
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
	 * @since 2.2.2
	 */
	protected function _display() {
		$taxonomy   = $this->field->args( 'taxonomy' );
		$field_type = new CMB2_Type_Taxonomy_Multicheck( new CMB2_Types( $this->field ) );
		$terms      = $field_type->get_object_terms();

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
				$links[] = '<a href="'. esc_url( $link ) .'">'. esc_html( $term->name ) .'</a>';
			}
			// Then loop and output.
			echo '<div class="cmb2-taxonomy-terms-', esc_attr( $taxonomy ), '">';
			echo implode( ', ', $links );
			echo '</div>';
		}
	}
}

class CMB2_Display_File extends CMB2_Field_Display {
	/**
	 * Display file value.
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) ) {
			return;
		}

		$this->value = esc_url_raw( $this->value );

		$field_type = new CMB2_Type_File_Base( new CMB2_Types( $this->field ) );

		$id = $this->field->get_field_clone( array(
			'id' => $field_type->_id() . '_id',
		) )->escaped_value( 'absint' );

		$this->file_output( $this->value, $id, $field_type );
	}

	protected function file_output( $url_value, $id, CMB2_Type_File_Base $field_type ) {
		// If there is no ID saved yet, try to get it from the url
		if ( $url_value && ! $id ) {
			$id = CMB2_Utils::image_id_from_url( esc_url_raw( $url_value ) );
		}

		if ( $field_type->is_valid_img_ext( $url_value ) ) {
			$img_size = $this->field->args( 'preview_size' );

			if ( $id ) {
				$image = wp_get_attachment_image( $id, $img_size, null, array( 'class' => 'cmb-image-display' ) );
			} else {
				$size = is_array( $img_size ) ? $img_size[0] : 200;
				$image = '<img class="cmb-image-display" style="max-width: ' . absint( $size ) . 'px; width: 100%; height: auto;" src="' . $url_value . '" alt="" />';
			}

			echo $image;

		} else {

			printf( '<div class="file-status"><span>%1$s <strong><a href="%2$s">%3$s</a></strong></span></div>',
				esc_html( $field_type->_text( 'file_text', esc_html__( 'File:', 'cmb2' ) ) ),
				$url_value,
				CMB2_Utils::get_file_name_from_path( $url_value )
			);

		}
	}
}

class CMB2_Display_File_List extends CMB2_Display_File {
	/**
	 * Display file_list value.
	 * @since 2.2.2
	 */
	protected function _display() {
		if ( empty( $this->value ) || ! is_array( $this->value ) ) {
			return;
		}

		$field_type = new CMB2_Type_File_Base( new CMB2_Types( $this->field ) );

		echo '<ul class="cmb2-display-file-list">';
		foreach ( $this->value as $id => $fullurl ) {
			echo '<li>', $this->file_output( esc_url_raw( $fullurl ), $id, $field_type ), '</li>';
		}
		echo '</ul>';
	}
}

class CMB2_Display_oEmbed extends CMB2_Field_Display {
	/**
	 * Display oembed value.
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
			'oembed_args' => array( 'width' => '300' ),
			'field_id'    => $this->field->id(),
		) );
	}
}
