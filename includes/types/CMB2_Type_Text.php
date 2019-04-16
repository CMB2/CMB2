<?php
/**
 * CMB text field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Type_Text extends CMB2_Type_Base {

	/**
	 * The type of field
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param CMB2_Types $types
	 * @param array      $args
	 */
	public function __construct( CMB2_Types $types, $args = array(), $type = '' ) {
		parent::__construct( $types, $args );
		$this->type = $type ? $type : $this->type;
	}

	/**
	 * Handles outputting an 'input' element
	 *
	 * @since  1.1.0
	 * @param  array $args Override arguments
	 * @return string       Form input element
	 */
	public function render( $args = array() ) {
		$args = empty( $args ) ? $this->args : $args;
		$a = $this->parse_args( $this->type, array(
			'type'            => 'text',
			'class'           => 'regular-text',
			'name'            => $this->_name(),
			'id'              => $this->_id(),
			'value'           => $this->field->escaped_value(),
			'desc'            => $this->_desc( true ),
			'js_dependencies' => array(),
		), $args );

		// Add character counter?
		$char_counter_markup = '';
		if ( ! empty( $this->field->args['char_counter'] ) ) :

			$char_counter_markup = $this->char_counter_markup();
			$this->field->add_js_dependencies( 'cmb2-char-counter' );
			$a['class'] .= ' cmb2-count-chars';

		endif;

		return $this->rendered(
			sprintf( '<input%s/>%s%s', $this->concat_attrs( $a, array( 'desc' ) ), $char_counter_markup, $a['desc'] )
		);
	}
}
