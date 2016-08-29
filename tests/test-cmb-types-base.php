<?php
/**
 * CMB2_Types tests
 *
 * @package   Tests_CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */

require_once( 'cmb-tests-base.php' );

abstract class Test_CMB2_Types_Base extends Test_CMB2 {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		parent::setUp();

		$this->cmb_id = 'test';

		$this->text_type_field = array(
			'name' => 'Name',
			'desc' => 'This is a description',
			'id'   => 'field_test_field',
			'type' => 'text',
		);

		$this->field_test = array(
			'id' => 'field_test',
			'fields' => array(
				$this->text_type_field,
			),
		);

		$this->attributes_test = array(
			'id' => 'attributes_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'desc' => 'This is a description',
					'id'   => 'attributes_test_field',
					'type' => 'text',
					'attributes' => array(
						'type'      => 'number',
						'disabled'  => 'disabled',
						'id'        => 'arbitrary-id',
						'data-test' => json_encode( array(
							'one'   => 'One',
							'two'   => 'Two',
							'true'  => true,
							'false' => false,
							'array' => array(
								'nested_data' => true,
							),
						) ),
					),
				),
			),
		);

		$this->options_test = array(
			'id' => 'options_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_test_field',
					'type' => 'select',
					'options' => array(
						'one'   => 'One',
						'two'   => 'Two',
						'true'  => true,
						'false' => false,
					),
				),
			),
		);

		$this->options_cb_test = array(
			'id' => 'options_cb_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_cb_test_field',
					'type' => 'select',
					'options_cb' => array( __CLASS__, 'options_cb' ),
				),
			),
		);

		$this->options_cb_and_array_test = array(
			'id' => 'options_cb_and_array_test',
			'fields' => array(
				array(
					'name' => 'Name',
					'description' => 'This is a description',
					'id'   => 'options_cb_and_array_test_field',
					'type' => 'select',
					'options' => array(
						'one'   => 'One',
						'two'   => 'Two',
						'true'  => true,
						'false' => false,
					),
					'options_cb' => array( __CLASS__, 'options_cb' ),
				),
			),
		);

		$this->post_id = $this->factory->post->create();
		$this->term = $this->factory->term->create( array( 'taxonomy' => 'category', 'name' => 'test_category' ) );
		$this->term2 = $this->factory->term->create( array( 'taxonomy' => 'category', 'name' => 'number_2' ) );

		wp_set_object_terms( $this->post_id, 'test_category', 'category' );

		$this->img_name = 'image.jpg';
		$this->attachment_id = $this->factory->attachment->create_object( $this->img_name, $this->post_id, array(
			'post_mime_type' => 'image/jpeg',
			'post_type' => 'attachment'
		) );
		$this->attachment_id2 = $this->factory->attachment->create_object( '2nd-'.$this->img_name, $this->post_id, array(
			'post_mime_type' => 'image/jpeg',
			'post_type' => 'attachment'
		) );
	}

	public function tearDown() {
		parent::tearDown();
	}

	protected function check_box_assertion( $output, $checked = false ) {
		$checked = $checked ? ' checked="checked"' : '';
		$this->assertHTMLstringsAreEqual(
			'<input type="checkbox" class="cmb2-option cmb2-list" name="field_test_field" id="field_test_field" value="on"'. $checked .'/><label for="field_test_field"><span class="cmb2-metabox-description">This is a description</span></label>',
			is_string( $output ) ? $output : $this->capture_render( $output )
		);
	}

	protected function file_sprintf( $args ) {
		return sprintf( '<li class="file-status"><span>' . esc_html__( 'File:', 'cmb2' ) . ' <strong>%1$s</strong></span>&nbsp;&nbsp; (<a href="%3$s" target="_blank" rel="external">' . esc_html__( 'Download','cmb2' ) . '</a> / <a href="#" class="cmb2-remove-file-button">' . esc_html__( 'Remove', 'cmb2' ) . '</a>)<input type="hidden" name="field_test_field[%2$d]" id="filelist-%2$d" value="%3$s" data-id=\'%2$d\'/></li>',
			$args['file_name'],
			$args['attachment_id'],
			$args['url']
		);
	}

	/**
	 * Test Callbacks
	 */

	public static function options_cb( $field ) {
		return array(
			'one'         => 'One',
			'two'         => 'Two',
			'true'        => true,
			'false'       => false,
			'post_id'     => $field->object_id,
			'object_type' => $field->object_type,
			'type'        => $field->args( 'type' ),
		);
	}

	/**
	 * Test_CMB2_Types helper methods
	 */

	protected function get_field_object( $type = '' ) {
		$args = $this->text_type_field;

		if ( $type ) {
			if ( is_string( $type ) ) {
				$args['type'] = $type;
			} elseif ( is_array( $type ) ) {
				$args = wp_parse_args( $type, $args );
			}
		}

		return new CMB2_Field( array(
			'field_args' => $args,
			'object_id' => $this->post_id,
		) );
	}

	protected function get_field_type_object( $args = '' ) {
		$field = is_a( $args, 'CMB2_Field' ) ? $args : $this->get_field_object( $args );
		return new CMB2_Types( $field );
	}

}

/**
 * Simply allows access to the dependencies protected property (for testing)
 */
class Test_CMB2_JS extends CMB2_JS {
	public static function dependencies() {
		return parent::$dependencies;
	}
}
