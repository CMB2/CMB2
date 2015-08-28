<?php
/**
 * CMB2 - The core metabox object
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 *
 * @property-read string $cmb_id
 * @property-read array $meta_box
 * @property-read array $updated
 */
class CMB2 extends CMB2_Field_Group {

	/**
	 * Current field's ID
	 * @var   string
	 * @since 2.0.0
	 */
	protected $cmb_id = '';

	/**
	 * Metabox Config array
	 * @var   array
	 * @since 0.9.0
	 */
	protected $meta_box = array();

	/**
	 * Type of object being saved. (e.g., post, user, or comment)
	 * @var   string
	 * @since 1.0.0
	 */
	protected $object_type = 'post';

	/**
	 * Type of object registered for metabox. (e.g., post, user, or comment)
	 * @var   string
	 * @since 1.0.0
	 */
	protected $mb_object_type = null;

	/**
	 * Metabox Defaults
	 * @var   array
	 * @since 1.0.1
	 */
	protected $mb_defaults = array(
		'id'           => '',
		'title'        => '',
		'type'         => '',
		'object_types' => array(), // Post type
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left
		'show_on_cb'   => null, // Callback to determine if metabox should display.
		'show_on'      => array(), // Post IDs or page templates to display this metabox. overrides 'show_on_cb'
		'cmb_styles'   => true, // Include CMB2 stylesheet
		'enqueue_js'   => true, // Include CMB2 JS
		'fields'       => array(),
		'hookup'       => true,
		'save_fields'  => true, // Will not save during hookup if false
		'closed'       => false, // Default to metabox being closed?
		'new_user_section' => 'add-new-user', // or 'add-existing-user'
	);

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 * @var   string
	 * @since 2.0.0
	 */
	protected $generated_nonce = '';

	/**
	 * Get started
	 * @since 0.4.0
	 * @param array   $meta_box  Metabox config array
	 * @param integer $object_id Optional object id
	 */
	public function __construct( $meta_box, $object_id = 0 ) {

		if ( empty( $meta_box['id'] ) ) {
			wp_die( __( 'Metabox configuration is required to have an ID parameter', 'cmb2' ) );
		}

		$this->meta_box = wp_parse_args( $meta_box, $this->mb_defaults );
		$this->object_id( $object_id );
		$this->mb_object_type();
		$this->cmb_id = $meta_box['id'];

		CMB2_Boxes::add( $this );

		/**
		 * Hook during initiation of CMB2 object
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is this meta_box id.
		 *
		 * @param array $cmb This CMB2 object
		 */
		do_action( "cmb2_init_{$this->cmb_id}", $this );
	}

	/**
	 * Loops through and displays fields
	 * @since  1.0.0
	 * @param  int    $object_id   Object ID
	 * @param  string $object_type Type of object being saved. (e.g., post, user, or comment)
	 */
	public function show_form( $object_id = 0, $object_type = '' ) {
		$object_type = $this->object_type( $object_type );
		$object_id = $this->object_id( $object_id );

		$this->nonce_field();

		echo "\n<!-- Begin CMB2 Fields -->\n";

		/**
		 * Hook before form table begins
		 *
		 * @param array  $cmb_id      The current box ID
		 * @param int    $object_id   The ID of the current object
		 * @param string $object_type The type of object you are working with.
		 *	                           Usually `post` (this applies to all post-types).
		 *	                           Could also be `comment`, `user` or `options-page`.
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( 'cmb2_before_form', $this->cmb_id, $object_id, $object_type, $this );

		/**
		 * Hook before form table begins
		 *
		 * The first dynamic portion of the hook name, $object_type, is the type of object
		 * you are working with. Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * The second dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * @param array  $cmb_id      The current box ID
		 * @param int    $object_id   The ID of the current object
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_before_{$object_type}_form_{$this->cmb_id}", $object_id, $this );

		echo '<div class="cmb2-wrap form-table"><div id="cmb2-metabox-', sanitize_html_class( $this->cmb_id ), '" class="cmb2-metabox cmb-field-list">';

		foreach ( $this->prop( 'fields' ) as $field_args ) {

			$field_args['context'] = $this->prop( 'context' );

			if ( 'group' == $field_args['type'] ) {

				if ( ! isset( $field_args['show_names'] ) ) {
					$field_args['show_names'] = $this->prop( 'show_names' );
				}
				$this->render_group( $field_args );

			} elseif ( 'hidden' == $field_args['type'] && $this->get_field( $field_args )->should_show() ) {
				// Save rendering for after the metabox
				$this->add_hidden_field( array(
					'field_args'  => $field_args,
					'object_type' => $this->object_type(),
					'object_id'   => $this->object_id(),
				) );

			} else {

				$field_args['show_names'] = $this->prop( 'show_names' );

				// Render default fields
				$field = $this->get_field( $field_args )->render_field();
			}
		}

		echo '</div></div>';

		$this->render_hidden_fields();

		/**
		 * Hook after form form has been rendered
		 *
		 * @param array  $cmb_id      The current box ID
		 * @param int    $object_id   The ID of the current object
		 * @param string $object_type The type of object you are working with.
		 *	                           Usually `post` (this applies to all post-types).
		 *	                           Could also be `comment`, `user` or `options-page`.
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( 'cmb2_after_form', $this->cmb_id, $object_id, $object_type, $this );

		/**
		 * Hook after form form has been rendered
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is the meta_box id.
		 *
		 * The first dynamic portion of the hook name, $object_type, is the type of object
		 * you are working with. Usually `post` (this applies to all post-types).
		 * Could also be `comment`, `user` or `options-page`.
		 *
		 * @param int    $object_id   The ID of the current object
		 * @param array  $cmb         This CMB2 object
		 */
		do_action( "cmb2_after_{$object_type}_form_{$this->cmb_id}", $object_id, $this );

		echo "\n<!-- End CMB2 Fields -->\n";

	}

	/**
	 * Sets the $object_type based on metabox settings
	 * @since  1.0.0
	 * @return string Object type
	 */
	public function mb_object_type() {

		if ( null !== $this->mb_object_type ) {
			return $this->mb_object_type;
		}

		if ( $this->is_options_page_mb() ) {
			$this->mb_object_type = 'options-page';
			return $this->mb_object_type;
		}

		if ( ! $this->prop( 'object_types' ) ) {
			$this->mb_object_type = 'post';
			return $this->mb_object_type;
		}

		$type = false;
		// check if 'object_types' is a string
		if ( is_string( $this->prop( 'object_types' ) ) ) {
			$type = $this->prop( 'object_types' );
		}
		// if it's an array of one, extract it
		elseif ( is_array( $this->prop( 'object_types' ) ) && 1 === count( $this->prop( 'object_types' ) ) ) {
			$cpts = $this->prop( 'object_types' );
			$type = is_string( end( $cpts ) )
				? end( $cpts )
				: false;
		}

		if ( ! $type ) {
			$this->mb_object_type = 'post';
			return $this->mb_object_type;
		}

		// Get our object type
		switch ( $type ) {

			case 'user':
			case 'comment':
				$this->mb_object_type = $type;
				break;

			default:
				$this->mb_object_type = 'post';
				break;
		}

		return $this->mb_object_type;
	}

	/**
	 * Determines if metabox is for an options page
	 * @since  1.0.1
	 * @return boolean True/False
	 */
	public function is_options_page_mb() {
		return ( isset( $this->meta_box['show_on']['key'] ) && 'options-page' === $this->meta_box['show_on']['key'] || array_key_exists( 'options-page', $this->meta_box['show_on'] ) );
	}

	/**
	 * Returns the object type
	 *
	 * @since  1.0.0
	 *
	 * @param string $object_type
	 *
	 * @return string Object type
	 */
	public function object_type( $object_type = '' ) {
		if ( $object_type ) {
			$this->object_type = $object_type;
			return $this->object_type;
		}

		if ( $this->object_type ) {
			return $this->object_type;
		}

		global $pagenow;

		if ( in_array( $pagenow, array( 'user-edit.php', 'profile.php', 'user-new.php' ), true ) ) {
			$this->object_type = 'user';

		} elseif ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php' ), true ) ) {
			$this->object_type = 'comment';

		} else {
			$this->object_type = 'post';
		}

		return $this->object_type;
	}

	/**
	 * Get metabox property and optionally set a fallback
	 * @since  2.0.0
	 * @param  string $property Metabox config property to retrieve
	 * @param  mixed  $fallback Fallback value to set if no value found
	 * @return mixed            Metabox config property value or false
	 */
	public function prop( $property, $fallback = null ) {

		if ( array_key_exists( $property, $this->meta_box ) ) {
			return $this->meta_box[ $property ];
		} elseif ( $fallback ) {
			return $this->meta_box[ $property ] = $fallback;
		}

		return null;
	}

	/**
	 * Determine whether this cmb object should show, based on the 'show_on_cb' callback.
	 *
	 * @since 2.0.9
	 *
	 * @return bool Whether this cmb should be shown.
	 */
	public function should_show() {
		// Default to showing this cmb
		$show = true;

		// Use the callback to determine showing the cmb, if it exists
		if ( is_callable( $this->prop( 'show_on_cb' ) ) ) {
			$show = (bool) call_user_func( $this->prop( 'show_on_cb' ), $this );
		}

		return $show;
	}

	/**
	 * Generate a unique nonce field for each registered meta_box
	 * @since  2.0.0
	 * @return string unique nonce hidden input
	 */
	public function nonce_field() {
		wp_nonce_field( $this->nonce(), $this->nonce(), false, true );
	}

	/**
	 * Generate a unique nonce for each registered meta_box
	 * @since  2.0.0
	 * @return string unique nonce string
	 */
	public function nonce() {
		if ( $this->generated_nonce ) {
			return $this->generated_nonce;
		}
		$this->generated_nonce = sanitize_html_class( 'nonce_' . basename( __FILE__ ) . $this->cmb_id );
		return $this->generated_nonce;
	}

	/**
	 * Magic getter for our object.
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'cmb_id':
			case 'meta_box':
				return $this->{$field};
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

}
