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
 */
class CMB2 extends CMB2_Field_Group {

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
	 * Current field's ID
	 *
	 * @var   string
	 * @since 2.0.0
	 */
	protected $cmb_id = '';

	protected $title = '';

	protected $type = '';

	// Post type
	protected $object_types = array();

	protected $context = 'normal';

	protected $priority = 'high';

	// Show field names on the left
	protected $show_names = true;

	// Callback to determine if metabox should display.
	protected $show_on_cb = null;

	// Post IDs or page templates to display this metabox. overrides 'show_on_cb'
	protected $show_on = array();

	// Include CMB2 stylesheet
	protected $cmb_styles = true;

	// Include CMB2 JS
	protected $enqueue_js = true;

	protected $hookup = true;

	// Will not save during hookup if false
	protected $save_fields = true;

	// Default to metabox being closed?
	protected $closed = false;

	// or 'add-existing-user'
	protected $new_user_section = 'add-new-user';

	/**
	 * Array of key => value data for saving. Likely $_POST data.
	 * @var   string
	 * @since 2.0.0
	 */
	protected $generated_nonce = '';

	protected $custom_properties = array();

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
		'show_on'      => array(), // Post IDs or page templates to display this metabox. overrides 'show_on_cb'
		'show_on_cb'   => null, // Callback to determine if metabox should display.
		'cmb_styles'   => true, // Include CMB2 stylesheet
		'enqueue_js'   => true, // Include CMB2 JS
		'fields'       => array(),
		'hookup'       => true,
		'save_fields'  => true, // Will not save during hookup if false
		'closed'       => false, // Default to metabox being closed?
		'new_user_section' => 'add-new-user', // or 'add-existing-user'
	);

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

		$meta_box = wp_parse_args( $meta_box, $this->mb_defaults );
		$this->object_id( $object_id );

		$this->set_cmb_id( $meta_box[ 'id' ] );
		$this->set_title( $meta_box[ 'title' ] );
		$this->set_type( $meta_box[ 'type' ] );
		$this->set_object_types( $meta_box[ 'object_types' ] );
		$this->set_context( $meta_box[ 'context' ] );
		$this->set_priority( $meta_box[ 'priority' ] );
		$this->set_show_names( $meta_box[ 'show_names' ] );
		$this->set_show_on( $meta_box[ 'show_on' ] );
		$this->set_show_on_cb( $meta_box[ 'show_on_cb' ] );
		$this->set_cmb_styles( $meta_box[ 'cmb_styles' ] );
		$this->set_enqueue_js( $meta_box[ 'enqueue_js' ] );
		$this->set_fields( $meta_box[ 'fields' ] );
		$this->set_hookup( $meta_box[ 'hookup' ] );
		$this->set_save_fields( $meta_box[ 'save_fields' ] );
		$this->set_closed( $meta_box[ 'closed' ] );
		$this->set_new_user_section( $meta_box[ 'new_user_section' ] );

		$this->mb_object_type();

		CMB2_Boxes::add( $this );

		/**
		 * Hook during initiation of CMB2 object
		 *
		 * The dynamic portion of the hook name, $this->cmb_id, is this meta_box id.
		 *
		 * @param array $cmb This CMB2 object
		 */
		do_action( "cmb2_init_{$this->get_cmb_id()}", $this );
	}

	/**
	 * @return string
	 */
	public function get_cmb_id() {

		return $this->cmb_id;
	}

	/**
	 * @param string $cmb_id
	 *
	 * @return string
	 */
	public function set_cmb_id( $cmb_id ) {

		$this->cmb_id = $cmb_id;
		return $cmb_id;
	}

	/**
	 * @return string
	 */
	public function get_title() {

		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return string
	 */
	public function set_title( $title ) {

		$this->title = $title;
		return $title;
	}

	/**
	 * @return string
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	public function set_type( $type ) {

		$this->type = $type;
		return $type;
	}

	/**
	 * @return array
	 */
	public function get_object_types() {

		return $this->object_types;
	}

	/**
	 * @param array $object_types
	 *
	 * @return array
	 */
	public function set_object_types( $object_types ) {

		$this->object_types = $object_types;
		return $object_types;
	}

	/**
	 * @return string
	 */
	public function get_context() {

		return $this->context;
	}

	/**
	 * @param string $context
	 *
	 * @return string
	 */
	public function set_context( $context ) {

		$this->context = $context;
		return $context;
	}

	/**
	 * @return string
	 */
	public function get_priority() {

		return $this->priority;
	}

	/**
	 * @param string $priority
	 *
	 * @return string
	 */
	public function set_priority( $priority ) {

		$this->priority = $priority;
		return $priority;
	}

	/**
	 * @return boolean
	 */
	public function get_show_names() {

		return $this->show_names;
	}

	/**
	 * @param boolean $show_names
	 *
	 * @return bool
	 */
	public function set_show_names( $show_names ) {

		$this->show_names = $show_names;
		return $show_names;
	}

	/**
	 * @return null
	 */
	public function get_show_on_cb() {

		return $this->show_on_cb;
	}

	/**
	 * @param null $show_on_cb
	 *
	 * @return null
	 */
	public function set_show_on_cb( $show_on_cb ) {

		$this->show_on_cb = $show_on_cb;
		return $show_on_cb;
	}

	/**
	 * @return array
	 */
	public function get_show_on() {

		return $this->show_on;
	}

	/**
	 * @param array $show_on
	 *
	 * @return array
	 */
	public function set_show_on( $show_on ) {

		$this->show_on = $show_on;
		return $show_on;
	}

	/**
	 * @return boolean
	 */
	public function get_cmb_styles() {

		return $this->cmb_styles;
	}

	/**
	 * @param boolean $cmb_styles
	 *
	 * @return bool
	 */
	public function set_cmb_styles( $cmb_styles ) {

		$this->cmb_styles = $cmb_styles;
		return $cmb_styles;
	}

	/**
	 * @return boolean
	 */
	public function get_enqueue_js() {

		return $this->enqueue_js;
	}

	/**
	 * @param boolean $enqueue_js
	 *
	 * @return bool
	 */
	public function set_enqueue_js( $enqueue_js ) {

		$this->enqueue_js = $enqueue_js;
		return $enqueue_js;
	}

	/**
	 * @return boolean
	 */
	public function get_hookup() {

		return $this->hookup;
	}

	/**
	 * @param boolean $hookup
	 *
	 * @return bool
	 */
	public function set_hookup( $hookup ) {

		$this->hookup = $hookup;
		return $hookup;
	}

	/**
	 * @return boolean
	 */
	public function get_save_fields() {

		return $this->save_fields;
	}

	/**
	 * @param boolean $save_fields
	 *
	 * @return bool
	 */
	public function set_save_fields( $save_fields ) {

		$this->save_fields = $save_fields;
		return $save_fields;
	}

	/**
	 * @return boolean
	 */
	public function get_closed() {

		return $this->closed;
	}

	/**
	 * @param boolean $closed
	 *
	 * @return bool
	 */
	public function set_closed( $closed ) {

		$this->closed = $closed;
		return $closed;
	}

	/**
	 * @return string
	 */
	public function get_new_user_section() {

		return $this->new_user_section;
	}

	/**
	 * @param string $new_user_section
	 *
	 * @return string
	 */
	public function set_new_user_section( $new_user_section ) {

		$this->new_user_section = $new_user_section;
		return $new_user_section;
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
		do_action( 'cmb2_before_form', $this->get_cmb_id(), $object_id, $object_type, $this );

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
		do_action( "cmb2_before_{$object_type}_form_{$this->get_cmb_id()}", $object_id, $this );

		echo '<div class="cmb2-wrap form-table"><div id="cmb2-metabox-', sanitize_html_class( $this->get_cmb_id() ), '" class="cmb2-metabox cmb-field-list">';

		foreach ( $this->get_fields() as $field_args ) {

			$field_args['context'] = $this->get_context();

			if ( 'group' == $field_args['type'] ) {

				if ( ! isset( $field_args['show_names'] ) ) {
					$field_args['show_names'] = $this->get_show_names();
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

				$field_args['show_names'] = $this->get_show_names();

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
		do_action( 'cmb2_after_form', $this->get_cmb_id(), $object_id, $object_type, $this );

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
		do_action( "cmb2_after_{$object_type}_form_{$this->get_cmb_id()}", $object_id, $this );

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

		if ( ! $this->get_object_types() ) {
			$this->mb_object_type = 'post';
			return $this->mb_object_type;
		}

		$type = false;
		// check if 'object_types' is a string
		if ( is_string( $this->get_object_types() ) ) {
			$type = $this->get_object_types();
		}
		// if it's an array of one, extract it
		elseif ( is_array( $this->get_object_types() ) && 1 === count( $this->get_object_types() ) ) {
			$cpts = $this->get_object_types();
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

		return ( isset(
		            $this->get_show_on()['key'] )
		            && 'options-page' === $this->get_show_on()['key']
		            || array_key_exists( 'options-page', $this->get_show_on()
				) );
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

		switch ( $property ) {
			case 'id':
				return $this->get_cmb_id();

			case 'title':
				return $this->get_title();

			case 'type':
				return $this->get_type();

			case 'object_types':
				return $this->get_object_types();

			case 'context':
				return $this->get_context();

			case 'priority':
				return $this->get_priority();

			case 'show_names':
				return $this->get_show_names();

			case 'show_on_cb':
				return $this->get_show_on_cb();

			case 'show_on':
				return $this->get_show_on();

			case 'cmb_styles':
				return $this->get_cmb_styles();

			case 'enqueue_js':
				return $this->get_enqueue_js();

			case 'fields':
				return $this->get_fields();

			case 'hookup':
				return $this->get_hookup();

			case 'save_fields':
				return $this->get_save_fields();

			case 'closed':
				return $this->get_closed();

			case 'new_user_section':
				return $this->get_new_user_section();

			default:
				if( array_key_exists( $property, $this->custom_properties ) ) {
					return $this->custom_properties[ $property ];
				} elseif ( ! is_null( $fallback ) ) {
					return $this->custom_properties[ $property ] = $fallback;
				}
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
		if ( is_callable( $this->get_show_on_cb() ) ) {
			$show = (bool) call_user_func( $this->get_show_on_cb(), $this );
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
		$this->generated_nonce = sanitize_html_class( 'nonce_' . basename( __FILE__ ) . $this->get_cmb_id() );
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
				return $this->get_cmb_id();

			case 'meta_box':
				return array_merge(
					array(
						'id'           => $this->get_cmb_id(),
						'title'        => $this->get_title(),
						'type'         => $this->get_type(),
						'object_types' => $this->get_object_types(),
						'context'      => $this->get_context(),
						'priority'     => $this->get_priority(),
						'show_names'   => $this->get_show_names(),
						'show_on'      => $this->get_show_on(),
						'show_on_cb'   => $this->get_show_on_cb(),
						'cmb_styles'   => $this->get_cmb_styles(),
						'enqueue_js'   => $this->get_enqueue_js(),
						'fields'       => $this->get_fields(),
						'hookup'       => $this->get_hookup(),
						'save_fields'  => $this->get_save_fields(),
						'closed'       => $this->get_closed(),
						'new_user_section' => $this->get_new_user_section(),
					),
					$this->custom_properties
				);

			default:
				return parent::__get( $field );
		}
	}

}
