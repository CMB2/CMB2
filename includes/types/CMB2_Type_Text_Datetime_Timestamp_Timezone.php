<?php
/**
 * CMB text_datetime_timestamp_timezone field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Text_Datetime_Timestamp_Timezone extends CMB2_Type_Base {

	public function render() {
		$field = $this->field;

		$args = wp_parse_args( $this->args, array(
			'value'                   => $field->escaped_value(),
			'desc'                    => $this->_desc( true ),
			'text_datetime_timestamp' => array(),
			'select_timezone'         => array(),
		) );

		$args['value'] = $field->escaped_value();
		if ( is_array( $args['value'] ) ) {
			$args['value'] = '';
		}

		$datetime = maybe_unserialize( $args['value'] );
		$value = $tzstring = '';

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz       = $datetime->getTimezone();
			$tzstring = $tz->getName();
			$value    = $datetime->getTimestamp();

			/**
			 * Work out the offset of the time here to stop storing incorrect data.
			 */
			$formatted_date = date( DateTime::ATOM, $value );
			$utc_datetime = new DateTime( $formatted_date, timezone_open( 'utc' ) );
			$offst_tz = timezone_offset_get( $tz, $utc_datetime );

			$value += $offst_tz;
		}

		$timestamp_args = wp_parse_args( $args['text_datetime_timestamp'], array(
			'desc'     => '',
			'value'    => $value,
			'rendered' => true,
		) );
		$datetime_timestamp = $this->types->text_datetime_timestamp( $timestamp_args );

		$timezone_select_args = wp_parse_args( $args['select_timezone'], array(
			'class'    => 'cmb2_select cmb2-select-timezone',
			'name'     => $this->_name( '[timezone]' ),
			'id'       => $this->_id( '_timezone' ),
			'options'  => wp_timezone_choice( $tzstring ),
			'desc'     => $args['desc'],
			'rendered' => true,
		) );
		$select = $this->types->select( $timezone_select_args );

		return $this->rendered(
			$datetime_timestamp . "\n" . $select
		);
	}

}
