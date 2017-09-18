<?php
/**
 * CMB2_Types tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'test-cmb-types-base.php' );

class Test_CMB2_Types_Display extends Test_CMB2_Types_Base {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	// public function test_repeatable_field() {}

	public function test_text() {
		$this->assertDisplayFieldMatches( 'text', __FUNCTION__ );
	}

	public function test_hidden() {
		$this->assertDisplayFieldMatches( 'hidden', __FUNCTION__ );
	}

	public function test_text_small() {
		$this->assertDisplayFieldMatches( 'text_small', __FUNCTION__ );
	}

	public function test_text_medium() {
		$this->assertDisplayFieldMatches( 'text_medium', __FUNCTION__ );
	}

	public function test_text_email() {
		$this->assertDisplayFieldMatches( 'text_email', __FUNCTION__ );
	}

	public function test_text_url() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'text_url', $value, '<a href="http://' . $value . '" rel="nofollow">http://' . $value . '</a>' );
	}

	public function test_text_money() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'text_money', $value, '$' . $value );
	}

	public function test_textarea() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'textarea_small', $value, '<p>' . $value . '</p>' );
	}

	public function test_textarea_small() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'textarea_small', $value, '<p>' . $value . '</p>' );
	}

	public function test_textarea_code() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'textarea_code', $value, '<xmp class="cmb2-code">' . $value . '</xmp>' );
	}

	public function test_wysiwyg() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'textarea_small', $value, '<p>' . $value . '</p>' );
	}

	public function test_text_date() {
		$value = time();
		$this->assertDisplayFieldMatches( 'text_date', $value, date( 'm/d/Y', $value ) );
	}

	public function test_text_date_timestamp() {
		$value = time();
		$this->assertDisplayFieldMatches( 'text_date_timestamp', $value, date( 'm/d/Y', $value ) );
	}

	public function test_text_time() {
		$value = time();
		$this->assertDisplayFieldMatches( 'text_time', $value, date( 'h:i A', $value ) );
	}

	public function test_text_datetime_timestamp() {
		$value = time();
		$this->assertDisplayFieldMatches( 'text_datetime_timestamp', $value, date( 'm/d/Y', $value ) );
	}

	public function test_text_datetime_timestamp_timezone() {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			$this->assertTrue( true );
			return;
		}

		$time = time();

		$sanitizer = new CMB2_Sanitize( $this->get_field_object( 'text_datetime_timestamp_timezone' ), array(
			'date' => date( 'm/d/Y', $time ),
			'time' => date( 'h:i A', $time ),
			'timezone' => date( 'e', $time ),
		) );

		$saved_value = $sanitizer->text_datetime_timestamp_timezone();

		$datetime = unserialize( $saved_value );
		$tz       = $datetime->getTimezone();
		$tzstring = $tz->getName();

		$expected = date( 'm/d/Y h:i A', $time ) . ', ' . $tzstring;

		$this->assertDisplayFieldMatches( 'text_datetime_timestamp_timezone', $saved_value, $expected );
	}

	public function test_select_timezone() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'select_timezone', $value );
	}

	public function test_colorpicker() {
		$value = __FUNCTION__;
		$this->assertDisplayFieldMatches( 'colorpicker', $value, '<span class="cmb2-colorpicker-swatch"><span style="background-color:' . $value . '"></span> ' . $value . '</span>' );
	}

	public function test_title() {
		$this->assertDisplayFieldMatches( 'title', '' );
	}

	public function test_select() {
		$this->assertOptionDisplayFieldMatches( 'select', 'two' );
	}

	public function test_taxonomy_select() {
		$this->text_type_field['taxonomy'] = 'category';
		$this->assertDisplayFieldMatches( 'taxonomy_select', '', '<a href="">test_category</a>' );
	}

	public function test_radio() {
		$this->assertOptionDisplayFieldMatches( 'radio', 'two' );
	}

	public function test_radio_inline() {
		$this->assertOptionDisplayFieldMatches( 'radio_inline', 'two', false, '<div class="cmb-column cmb-type-radio-inline cmb2-id-options-test-field cmb-inline" data-fieldtype="radio_inline">Two</div>' );
	}

	public function test_multicheck() {
		$this->assertOptionDisplayFieldMatches( 'multicheck', array( 'one', 'two' ), 'One, Two' );
	}

	public function test_multicheck_inline() {
		$this->assertOptionDisplayFieldMatches( 'multicheck_inline', array( 'one', 'two' ), true, '<div class="cmb-column cmb-type-multicheck-inline cmb2-id-options-test-field cmb-inline" data-fieldtype="multicheck_inline">One, Two</div>' );
	}

	public function test_checkbox() {
		$value = 'on';
		$this->assertDisplayFieldMatches( 'checkbox', $value );
	}

	public function test_taxonomy_radio() {
		$this->text_type_field['taxonomy'] = 'category';
		$this->assertDisplayFieldMatches( 'taxonomy_radio', '', '<a href="">test_category</a>' );
	}

	public function test_taxonomy_radio_inline() {
		$this->text_type_field['taxonomy'] = 'category';
		$this->assertDisplayFieldMatches( 'taxonomy_radio_inline', '', true, '<div class="cmb-column cmb-type-taxonomy-radio-inline cmb2-id-field-test-field cmb-inline" data-fieldtype="taxonomy_radio_inline"><a href="">test_category</a></div>' );
	}

	public function test_taxonomy_multicheck() {
		$this->text_type_field['taxonomy'] = 'category';
		$this->assertDisplayFieldMatches( 'taxonomy_multicheck', '', '<div class="cmb2-taxonomy-terms-category"><a href="">test_category</a></div>' );
	}

	public function test_taxonomy_multicheck_inline() {
		$this->text_type_field['taxonomy'] = 'category';
		$this->assertDisplayFieldMatches( 'taxonomy_multicheck_inline', '', true, '<div class="cmb-column cmb-type-taxonomy-multicheck-inline cmb2-id-field-test-field cmb-inline" data-fieldtype="taxonomy_multicheck_inline"><div class="cmb2-taxonomy-terms-category"><a href="">test_category</a></div></div>' );
	}

	/**
	 * @group cmb2-ajax-embed
	 */
	public function test_oembed() {
		$vid = 'EOfy5LDpEHo';
		$value = 'https://www.youtube.com/watch?v=' . $vid;
			update_post_meta( $this->post_id, $this->text_type_field['id'], $value );

			$expected_field = $this->is_connected()
				? '<div class="cmb-column cmb-type-oembed cmb2-id-field-test-field" data-fieldtype="oembed"><div class="cmb2-oembed"><iframe width="300" height="169" src="www.youtube.com/embed/' . $vid . '?feature=oembed" frameborder="0" allowfullscreen></iframe></div></div>'
				: '<div class="cmb-column cmb-type-oembed cmb2-id-field-test-field" data-fieldtype="oembed"><p class="ui-state-error-text">' . sprintf( esc_html__( 'No oEmbed Results Found for %1$s. View more info at %2$s.', 'cmb2' ), '<a href="www.youtube.com/watch?v=' . $vid . '">www.youtube.com/watch?v=' . $vid . '</a>', '<a href="codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>' ) . '</p></div>';

			$actual_field = $this->capture_render( array( $this->get_field_object( 'oembed' ), 'render_column' ) );

		if ( ! $this->is_connected() ) {
			$expected_field = '<div class="cmb-column cmb-type-oembed cmb2-id-field-test-field" data-fieldtype="oembed"><p class="ui-state-error-text">No oEmbed Results Found for <a href="www.youtube.com/watch?v=' . $vid . '">www.youtube.com/watch?v=' . $vid . '</a>. View more info at <a href="codex.wordpress.org/Embeds" target="_blank">codex.wordpress.org/Embeds</a>.</p></div>';
		}

		$this->assertHTMLstringsAreEqual(
			preg_replace( '~https?://~', '', $expected_field ), // normalize http differences
			preg_replace( '~https?://~', '', $actual_field ) // normalize http differences
		);
	}

	public function test_file_list() {
		$images = get_attached_media( 'image', $this->post_id );
		$attach_1_url = get_permalink( $this->attachment_id );
		$attach_2_url = get_permalink( $this->attachment_id2 );

		$this->assertDisplayFieldMatches(
			'file_list',
			array(
				$this->attachment_id => $attach_1_url,
				$this->attachment_id2 => $attach_2_url,
			),
			'<ul class="cmb2-display-file-list"><li><div class="file-status"><span>File: <strong><a href="' . $attach_1_url . '">' . CMB2_Utils::get_file_name_from_path( $attach_1_url ) . '</a></strong></span></div></li><li><div class="file-status"><span>File: <strong><a href="' . $attach_2_url . '">' . CMB2_Utils::get_file_name_from_path( $attach_2_url ) . '</a></strong></span></div></li></ul>'
		);
	}

	public function test_file() {
		$images = get_attached_media( 'image', $this->post_id );
		$attach_1_url = get_permalink( $this->attachment_id );

		$field_id = $this->text_type_field['id'];
		update_post_meta( $this->post_id, $field_id . '_id', $this->attachment_id );

		$this->assertDisplayFieldMatches(
			'file',
			$attach_1_url,
			'<div class="file-status"><span>File: <strong><a href="' . $attach_1_url . '">' . CMB2_Utils::get_file_name_from_path( $attach_1_url ) . '</a></strong></span></div>'
		);
	}

	protected function assertOptionDisplayFieldMatches( $type, $value, $value_display = false, $string = false ) {
		$args = $this->options_test['fields'][0];
		$field_id = $args['id'];
		update_post_meta( $this->post_id, $field_id, $value );

		$value_display = $value_display ? $value_display : $args['options'][ $value ];
		$args['type'] = $type;

		$args['column'] = true;

		$field = new CMB2_Field( array(
			'field_args' => $args,
			'object_id' => $this->post_id,
		) );

		$string = $string ? $string : '<div class="cmb-column cmb-type-' . str_replace( '_', '-', $type ) . ' cmb2-id-' . str_replace( '_', '-', $field_id ) . '" data-fieldtype="' . $type . '">' . $value_display . '</div>';

		$this->assertHTMLstringsAreEqual(
			$string,
			$this->capture_render( array( $field, 'render_column' ) )
		);

	}
	protected function assertDisplayFieldMatches( $type, $value, $value_display = false, $string = false ) {
		$field_id = $this->text_type_field['id'];
		if ( false !== $value ) {
			update_post_meta( $this->post_id, $field_id, $value );
		}

		$value_display = $value_display ? $value_display : $value;
		$string = $string ? $string : '<div class="cmb-column cmb-type-' . str_replace( '_', '-', $type ) . ' cmb2-id-' . str_replace( '_', '-', $field_id ) . '" data-fieldtype="' . $type . '">' . $value_display . '</div>';

		$this->assertHTMLstringsAreEqual(
			$string,
			$this->capture_render( array( $this->get_field_object( $type ), 'render_column' ) )
		);
	}

	protected function get_field_object( $type = '' ) {
		$args = $this->text_type_field;

		if ( $type ) {
			if ( is_string( $type ) ) {
				$args['type'] = $type;
			} elseif ( is_array( $type ) ) {
				$args = wp_parse_args( $type, $args );
			}
		}

		$args['column'] = true;

		return new CMB2_Field( array(
			'field_args' => $args,
			'object_id' => $this->post_id,
		) );
	}
}
