<?php
/**
 * CMB radio field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Radio extends CMB2_Type_Multi_Base {

	/**
	 * The type of radio field
	 *
	 * @var string
	 */
	public $type = 'radio';

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

	public function render() {
		$args = $this->parse_args( $this->type, array(
			'class'   => 'cmb2-radio-list cmb2-list',
			'options' => $this->concat_items( array(
				'label'  => 'test',
				'method' => 'list_input'
			) ),
			'desc' => $this->_desc( true ),
		) );

		return $this->rendered( $this->ul( $args ) );
	}

	protected function ul( $a ) {
		return sprintf( '<ul class="%s">%s</ul>%s', $a['class'], $a['options'], $a['desc'] );
	}

}
