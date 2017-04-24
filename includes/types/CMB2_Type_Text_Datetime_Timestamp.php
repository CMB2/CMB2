<?php
/**
 * CMB text_datetime_timestamp field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Text_Datetime_Timestamp extends CMB2_Type_Picker_Base {

	public function render() {
		$field = $this->field;

		$args = wp_parse_args( $this->args, array(
			'value'      => $field->escaped_value(),
			'desc'       => $this->_desc(),
			'datepicker' => array(),
			'timepicker' => array(),
		) );

		if ( empty( $args['value'] ) ) {
			$args['value'] = $field->escaped_value();
			// This will be used if there is a select_timezone set for this field
			$tz_offset = $field->field_timezone_offset();
			if ( ! empty( $tz_offset ) ) {
				$args['value'] -= $tz_offset;
			}
		}

		$has_good_value = ! empty( $args['value'] ) && ! is_array( $args['value'] );

		$date_input = parent::render( $this->date_args( $args, $has_good_value ) );
		$time_input = parent::render( $this->time_args( $args, $has_good_value ) );

		return $this->rendered( $date_input . "\n" . $time_input );
	}

	public function date_args( $args, $has_good_value ) {
		$date_args = wp_parse_args( $args['datepicker'], array(
			'class' => 'cmb2-text-small cmb2-datepicker',
			'name'  => $this->_name( '[date]' ),
			'id'    => $this->_id( '_date' ),
			'value' => $has_good_value ? $this->field->get_timestamp_format( 'date_format', $args['value'] ) : '',
			'desc'  => '',
		) );

		$date_args['rendered'] = true;

		// Let's get the date-format, and set it up as a data attr for the field.
		return $this->parse_picker_options( 'date', $date_args );
	}

	public function time_args( $args, $has_good_value ) {
		$time_args = wp_parse_args( $args['timepicker'], array(
			'class' => 'cmb2-timepicker text-time',
			'name'  => $this->_name( '[time]' ),
			'id'    => $this->_id( '_time' ),
			'value' => $has_good_value ? $this->field->get_timestamp_format( 'time_format', $args['value'] ) : '',
			'desc'  => $args['desc'],
			'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
		) );

		$time_args['rendered'] = true;

		// Let's get the time-format, and set it up as a data attr for the field.
		return $this->parse_picker_options( 'time', $time_args );
	}

}
