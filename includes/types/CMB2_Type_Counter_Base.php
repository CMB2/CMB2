<?php
/**
 * CMB base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
abstract class CMB2_Type_Counter_Base extends CMB2_Type_Base {

	/**
	 * Whether this type has the counter added.
	 *
	 * @since  2.7.0
	 *
	 * @var boolean
	 */
	public $has_counter = false;

	/**
	 * Return character counter markup for this field.
	 *
	 * @since  2.7.0
	 *
	 * @param  string $val The actual value of this field.
	 *
	 * @return string
	 */
	public function char_counter_markup( $val ) {
		$markup = '';

		if ( ! $this->field->args( 'char_counter' ) ) {
			return $markup;
		}

		$type     = (string) $this->field->args( 'char_counter' );
		$field_id = $this->_id( '', false );
		$max      = (int) $this->field->args( 'char_max' );
		$data_max = $max ? ' data-max="' . esc_attr( (string) $max ) . '"' : '';

		switch ( $type ) {
			case 'words':
				$label = $max
					? $this->_text( 'words_left_text', esc_html__( 'Words left', 'cmb2' ) )
					: $this->_text( 'words_text', esc_html__( 'Words', 'cmb2' ) );
				break;
			default:
				$type  = 'characters';
				$label = $max
					? $this->_text( 'characters_left_text', esc_html__( 'Characters left', 'cmb2' ) )
					: $this->_text( 'characters_text', esc_html__( 'Characters', 'cmb2' ) );
				break;
		}

		$msg = $max
			? sprintf( '<span class="cmb2-char-max-msg">%s</span>', $this->_text( 'characters_truncated_text', esc_html__( 'Your text may be truncated.', 'cmb2' ) ) )
			: '';

		$length = strlen( $val );
		$width  = $length > 1 ? ( 8 * strlen( (string) $length ) ) + 15 : false;
		$style  = $width ? sprintf( ' style="width:%dpx;"', $width ) : '';

		$markup .= '<p class="cmb2-char-counter-wrap">';
		$markup .= sprintf(
			'<span class="cmb2-char-counter-label">%2$s:</span> <span id="%1$s" data-field-id="%3$s" data-counter-type="%4$s"%5$s class="cmb2-char-counter" aria-live="polite"%6$s>%7$s</span>%8$s',
			esc_attr( 'char-counter-' . $field_id ),
			$label,
			esc_attr( $field_id ),
			esc_attr( $type ),
			$data_max,
			$style,
			esc_html( (string) $length ),
			$msg
		);
		$markup .= '</p>';

		// Enqueue the required JS.
		$this->field->add_js_dependencies( array(
			'word-count',
			'wp-util',
			'cmb2-char-counter',
		) );

		$this->has_counter = true;

		return $markup;
	}

	/**
	 * Maybe update attributes for the character counter.
	 *
	 * @since  2.7.0
	 *
	 * @param  array  $attributes Array of parsed attributes.
	 *
	 * @return array              Potentially modified attributes.
	 */
	public function maybe_update_attributes_for_char_counter( $attributes ) {
		$char_counter = $this->char_counter_markup( $attributes['value'] );

		// Has character counter?
		if ( $char_counter ) {
			$attributes['class'] = ! empty( $attributes['class'] ) ? $attributes['class'] . ' cmb2-count-chars' : ' cmb2-count-chars';

			// Enforce max chars?
			$max = $this->enforce_max();
			if ( $max ) {
				$attributes['maxlength'] = $max;
			}
			$attributes['desc'] = $char_counter . $attributes['desc'];
		}

		return $attributes;
	}

	/**
	 * Enforce max chars?
	 *
	 * @since  2.7.0
	 *
	 * @return bool Whether to enforce max characters.
	 */
	public function enforce_max() {
		$char_max = (int) $this->field->args( 'char_max' );

		// Enforce max chars?
		return ( $this->field->args( 'char_max_enforce' ) && $char_max > 0
			&& 'words' !== $this->field->args( 'char_counter' ) )
			? $char_max
			: false;
	}

}
