<?php
/**
 * CMB2 core tests
 *
 * @package   Tests_CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */

require_once( 'cmb-rest-tests-base.php' );

/**
 * Test the REST endpoints
 *
 * @group cmb2-rest-api
 *
 * @link https://pantheon.io/blog/test-coverage-your-wp-rest-api-project
 * @link https://github.com/danielbachhuber/pantheon-rest-api-demo/blob/master/tests/test-rest-api-demo.php
 */
class Test_CMB2_REST extends Test_CMB2_Rest_Base {

	/**
	 * Set up the test fixture
	 */
	public function setUp() {
		$this->set_up_and_init( array(
			'id' => strtolower( __CLASS__ ),
			'show_in_rest' => WP_REST_Server::ALLMETHODS,
			'fields' => array(
				'rest_test' => array(
					'name'        => 'Name',
					'id'          => 'rest_test',
					'type'        => 'text',
				),
				'rest_test2' => array(
					'name'        => 'Name',
					'id'          => 'rest_test2',
					'type'        => 'text',
				),
			),
		) );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_construction() {
		$this->assertTrue( $this->rest_box->cmb instanceof CMB2 );
	}

	public function test_declare_read_edit_fields() {
		$this->rest_box->declare_read_edit_fields();
		$this->assertEquals( 2, count( $this->rest_box->read_fields ) );
		$this->assertEquals( 2, count( $this->rest_box->edit_fields ) );
	}

	public function test_is_readable() {
		$this->do_scenarios_test( $this->get_read_scenarios(), array( 'CMB2_REST', 'is_readable' ) );
	}

	public function test_is_editable() {
		$this->do_scenarios_test( $this->get_edit_scenarios(), array( 'CMB2_REST', 'is_editable' ) );
	}

	protected function do_scenarios_test( $scenarios, $function ) {
		foreach ( $scenarios as $label => $check ) {
			$is = call_user_func( $function, $check['val'] );
			$this->assertTrue( $check['eval'] === $is, $this->scenario_fail_message( $label, $is, $check['eval'] ) );
		}
	}

	public function test_field_is_readable() {
		$rest_box = new Test_CMB2_REST_Object( new CMB2( array(
			'id' => $this->cmb_id,
			'show_in_rest' => WP_REST_Server::READABLE, // Show the fields by default.
		) ) );
		$this->read_assertions( $rest_box );
		$this->edit_assertions( $rest_box );
	}

	public function test_field_is_editable() {
		$rest_box = new Test_CMB2_REST_Object( new CMB2( array(
			'id' => $this->cmb_id,
			'show_in_rest' => WP_REST_Server::EDITABLE, // Only allow writing fields by default.
		) ) );

		$this->read_assertions( $rest_box );
		$this->edit_assertions( $rest_box );
	}

	public function test_field_is_readable_and_editable() {
		$rest_box = new Test_CMB2_REST_Object( new CMB2( array(
			'id' => $this->cmb_id,
			'show_in_rest' => WP_REST_Server::ALLMETHODS, // Only allow writing fields by default.
		) ) );

		$this->read_assertions( $rest_box );
		$this->edit_assertions( $rest_box );
	}

	protected function read_assertions( $rest_box ) {
		// If readable box, check the fallback (null) value matches.
		$this->assertEquals( $rest_box->rest_read, $rest_box->can_read( null ) );

		// Yep, show the field.
		$this->assertTrue( $rest_box->can_read( WP_REST_Server::READABLE ) );
		$this->assertTrue( $rest_box->can_read( 'string' ) );
		$this->assertTrue( $rest_box->can_read( 1 ) );
		$this->assertTrue( $rest_box->can_read( true ) );
		$this->assertTrue( $rest_box->can_read( WP_REST_Server::ALLMETHODS ) );

		// Nope, don't show the field.
		$this->assertFalse( $rest_box->can_read( false ) );
		$this->assertFalse( $rest_box->can_read( 0 ) );
		$this->assertFalse( $rest_box->can_read( '' ) );
		$this->assertFalse( $rest_box->can_read( WP_REST_Server::CREATABLE ) );
		$this->assertFalse( $rest_box->can_read( WP_REST_Server::EDITABLE ) );
		$this->assertFalse( $rest_box->can_read( WP_REST_Server::DELETABLE ) );
	}

	protected function edit_assertions( $rest_box ) {
		// If editable box, check the fallback (null) value matches.
		$this->assertEquals( $rest_box->rest_edit, $rest_box->can_edit( null ) );

		// Yep, can edit the field.
		$this->assertTrue( $rest_box->can_edit( WP_REST_Server::ALLMETHODS ) );
		$this->assertTrue( $rest_box->can_edit( WP_REST_Server::EDITABLE ) );

		// Nope, can't edit the field.
		$this->assertFalse( $rest_box->can_edit( WP_REST_Server::READABLE ) );
		$this->assertFalse( $rest_box->can_edit( 'string' ) );
		$this->assertFalse( $rest_box->can_edit( 1 ) );
		$this->assertFalse( $rest_box->can_edit( true ) );
		$this->assertFalse( $rest_box->can_edit( false ) );
		$this->assertFalse( $rest_box->can_edit( 0 ) );
		$this->assertFalse( $rest_box->can_edit( '' ) );
		$this->assertFalse( $rest_box->can_edit( WP_REST_Server::CREATABLE ) );
		$this->assertFalse( $rest_box->can_edit( WP_REST_Server::DELETABLE ) );
	}

	public function test_field_can_read() {
		$this->rest_box->declare_read_edit_fields();
		$this->assertInstanceOf( 'CMB2_Field', $this->rest_box->field_can_read( 'rest_test', true ) );
	}

	public function test_field_can_edit() {
		$this->rest_box->declare_read_edit_fields();
		$this->assertInstanceOf( 'CMB2_Field', $this->rest_box->field_can_edit( 'rest_test', true ) );
	}

	public function test_get_object_id() {
		$object = (object) array(
			'ID' => 1,
		);
		$this->assertEquals( $object->ID, Test_CMB2_REST_Object::get_object_id( $object, 'post' ) );

		$object = (object) array(
			'comment_ID' => 1,
		);
		$this->assertEquals( $object->comment_ID, Test_CMB2_REST_Object::get_object_id( $object, 'comment' ) );

		$object = (object) array(
			'term_id' => 1,
		);
		$this->assertEquals( $object->term_id, Test_CMB2_REST_Object::get_object_id( $object, 'term' ) );

		$object = array(
			'term_id' => 1,
		);
		$this->assertEquals( $object['term_id'], Test_CMB2_REST_Object::get_object_id( $object, 'term' ) );

		$this->assertEquals( false, Test_CMB2_REST_Object::get_object_id( array() ) );
	}

	public function test_get_rest_box() {
		$this->assertInstanceOf( 'CMB2_REST', CMB2_REST::get_rest_box( strtolower( __CLASS__ ) ) );
	}

	public function test_get_rest_values() {
		$expected = array();
		foreach ( $this->metabox_array['fields'] as $field ) {
			$expected[ $field['id'] ] = md5( $field['id'] );
		}

		$this->confirm_get_rest_values( $expected );
	}

	public function test_update_rest_values() {
		$fields = $this->metabox_array['fields'];

		$test_santizing_value = "'ello mate<script>XSS</script>";

		$counter = 0;
		foreach ( $fields as $key => $field ) {
			if ( $counter ) {
				$fields[ $key ] = ++$counter;
			} else {
				$test_santizing_key = $key;
				$fields[ $key ] = $test_santizing_value;
				$counter++;
			}
		}

		$new_values = array(
			$this->cmb_id => $fields,
		);

		$values = CMB2_REST::update_post_rest_values( $new_values, (object) array(
			'ID' => $this->post_id,
		), 'cmb2', new WP_REST_Request, 'post' );

		$this->assertEquals( count( $fields ), count( $values[ $this->cmb_id ] ) );
		foreach ( $values[ $this->cmb_id ] as $value ) {
			$this->assertTrue( $value );
		}

		$fields[ $test_santizing_key ] = sanitize_text_field( $test_santizing_value );

		$this->confirm_get_rest_values( $fields );
	}

	protected function confirm_get_rest_values( $expected ) {
		$values = CMB2_REST::get_post_rest_values( array(
			'id' => $this->post_id,
		), 'cmb2', new WP_REST_Request, 'post' );
		$expected = array(
			$this->cmb_id => $expected,
		);
		$this->assertEquals( $expected, $values );
	}

	protected function scenario_fail_message( $label, $is_readable, $check ) {
		ob_start();
		var_dump( $is_readable );
		$is_readable = ob_get_clean();

		ob_start();
		var_dump( $check );
		$check = ob_get_clean();

		return 'Checked and ' . $label . ': ' . $is_readable . ' is NOT equal to: ' . $check;
	}

	protected function get_read_scenarios() {
		return array(
			'false' => array(
				'val' => false,
				'eval' => false,
			),
			'true' => array(
				'val' => true,
				'eval' => true,
			),
			'WP_REST_Server::READABLE' => array(
				'val' => WP_REST_Server::READABLE,
				'eval' => true,
			),
			'WP_REST_Server::CREATABLE' => array(
				'val' => WP_REST_Server::CREATABLE,
				'eval' => false,
			),
			'WP_REST_Server::EDITABLE' => array(
				'val' => WP_REST_Server::EDITABLE,
				'eval' => false,
			),
			'WP_REST_Server::DELETABLE' => array(
				'val' => WP_REST_Server::DELETABLE,
				'eval' => false,
			),
			'WP_REST_Server::ALLMETHODS' => array(
				'val' => WP_REST_Server::ALLMETHODS,
				'eval' => true,
			),
		);
	}

	protected function get_edit_scenarios() {
		$scenarios = $this->get_read_scenarios();
		$scenarios['false']['eval'] = false;
		$scenarios['true']['eval'] = false;
		$scenarios['WP_REST_Server::READABLE']['eval'] = false;
		$scenarios['WP_REST_Server::CREATABLE']['eval'] = false;
		$scenarios['WP_REST_Server::EDITABLE']['eval'] = true;
		$scenarios['WP_REST_Server::DELETABLE']['eval'] = false;
		$scenarios['WP_REST_Server::ALLMETHODS']['eval'] = true;
		return $scenarios;
	}

}

