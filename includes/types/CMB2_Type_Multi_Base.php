<?php
/**
 * CMB Multi base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
abstract class CMB2_Type_Multi_Base extends CMB2_Type_Base {

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
		$a = $this->parse_args( 'list_input', array(
			'type'  => 'radio',
			'class' => 'cmb2-option',
			'name'  => $this->_name(),
			'id'    => $this->_id( $i ),
			'value' => $this->field->escaped_value(),
			'label' => '',
		), $args );

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
	 * Generates html for concatenated items
	 * @since  1.1.0
	 * @param  array   $args Optional arguments
	 * @return string        Concatenated html items
	 */
	public function concat_items( $args = array() ) {
		$field = $this->field;

		$method = isset( $args['method'] ) ? $args['method'] : 'select_option';
		unset( $args['method'] );

		$value = null !== $field->escaped_value()
			? $field->escaped_value()
			: $field->get_default();

		if ( is_numeric( $value ) ) {
			$value = intval( $value );
		}

		$concatenated_items = ''; $i = 1;

		$options = array();
		if ( $option_none = $field->args( 'show_option_none' ) ) {
			$options[ '' ] = $option_none;
		}
		$options = $options + (array) $field->options();
		foreach ( $options as $opt_value => $opt_label ) {

			// Clone args & modify for just this item
			$a = $args;

			$a['value'] = $opt_value;
			$a['label'] = $opt_label;

			// Check if this option is the value of the input
			if ( $value === $opt_value ) {
				$a['checked'] = 'checked';
			}

			$concatenated_items .= $this->$method( $a, $i++ );
		}

		return $concatenated_items;
	}

}
