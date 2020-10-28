<?php
/**
 * CMB checkbox field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Checkbox extends CMB2_Type_Text {

	/**
	 * If checkbox is checked
	 *
	 * @var mixed
	 */
	public $is_checked = null;

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param CMB2_Types $types      Object for the field type.
	 * @param array      $args       Array of arguments for the type.
	 * @param mixed      $is_checked Whether or not the field is checked, or default value.
	 */
	public function __construct( CMB2_Types $types, $args = array(), $is_checked = null ) {
		parent::__construct( $types, $args );
		$this->is_checked = $is_checked;
	}

	/**
	 * Render the field for the field type.
	 *
	 * @since 2.2.2
	 *
	 * @param array $args Array of arguments for the rendering.
	 * @return CMB2_Type_Base|string
	 */
	public function render( $args = array() ) {
		$defaults = array(
			'type'  => 'checkbox',
			'class' => 'cmb2-option cmb2-list',
			'value' => 'on',
			'desc'  => '',
		);

		$meta_value = $this->field->escaped_value();

		$is_checked = null === $this->is_checked
			? ! empty( $meta_value )
			: $this->is_checked;

		if ( $is_checked ) {
			$defaults['checked'] = 'checked';
		}

		$args = $this->parse_args( 'checkbox', $defaults );

		return $this->rendered(
			sprintf(
				'%s <label for="%s">%s</label>',
				parent::render( $args ),
				$this->_id( '', false ),
				$this->_desc()
			)
		);
	}

}
