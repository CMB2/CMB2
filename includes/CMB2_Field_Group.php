<?php
/**
 * Class CMB2_Field_Group
 */
abstract class CMB2_Field_Group {

	/**
	 * Object ID for metabox meta retrieving/saving
	 *
	 * @var   mixed
	 * @since 1.0.0
	 */
	protected $object_id = 0;

	/**
	 * List of fields that are changed/updated on save
	 *
	 * @var   array
	 * @since 1.1.0
	 */
	protected $updated = array();

	/**
	 * Metabox field objects
	 *
	 * @var   array
	 * @since 2.0.3
	 */
	protected $fields = array();

	protected $fields_array = array();

	/**
	 * An array of hidden fields to output at the end of the form
	 *
	 * @var   CMB2_Types[]
	 * @since 2.0.0
	 */
	protected $hidden_fields = array();

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 *
	 * @var   array
	 * @since 2.0.0
	 */
	public $data_to_save = array();

	/**
	 * Get object id from global space if no id is provided
	 * @since  1.0.0
	 * @param  integer $object_id Object ID
	 * @return integer $object_id Object ID
	 */
	public function object_id( $object_id = 0 ) {
		global $pagenow;

		if ( $object_id ) {
			$this->object_id = $object_id;
			return $this->object_id;
		}

		if ( $this->object_id ) {
			return $this->object_id;
		}

		// Try to get our object ID from the global space
		switch ( $this->object_type() ) {
			case 'user':
				$object_id = isset( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : $object_id;
				$object_id = ! $object_id && 'user-new.php' != $pagenow && isset( $GLOBALS['user_ID'] ) ? $GLOBALS['user_ID'] : $object_id;
				break;

			case 'comment':
				$object_id = isset( $_REQUEST['c'] ) ? $_REQUEST['c'] : $object_id;
				$object_id = ! $object_id && isset( $GLOBALS['comments']->comment_ID ) ? $GLOBALS['comments']->comment_ID : $object_id;
				break;

			default:
				$object_id = isset( $GLOBALS['post']->ID ) ? $GLOBALS['post']->ID : $object_id;
				$object_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : $object_id;
				break;
		}

		// reset to id or 0
		$this->object_id = $object_id ? $object_id : 0;

		return $this->object_id;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 *
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'updated':
				return $this->{$field};
			case 'object_id':
				return $this->object_id();
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}