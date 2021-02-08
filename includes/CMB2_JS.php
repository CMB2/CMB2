<?php
/**
 * Handles the dependencies and enqueueing of the CMB2 JS scripts
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_JS {

	/**
	 * The CMB2 JS handle
	 *
	 * @var   string
	 * @since 2.0.7
	 */
	protected static $handle = 'cmb2-scripts';

	/**
	 * The CMB2 JS variable name
	 *
	 * @var   string
	 * @since 2.0.7
	 */
	protected static $js_variable = 'cmb2_l10';

	/**
	 * Array of CMB2 JS dependencies
	 *
	 * @var   array
	 * @since 2.0.7
	 */
	protected static $dependencies = array(
		'jquery' => 'jquery',
	);

	/**
	 * Array of CMB2 fields model data for JS.
	 *
	 * @var   array
	 * @since 2.4.0
	 */
	protected static $fields = array();

	/**
	 * Add a dependency to the array of CMB2 JS dependencies
	 *
	 * @since 2.0.7
	 * @param array|string $dependencies Array (or string) of dependencies to add.
	 */
	public static function add_dependencies( $dependencies ) {
		foreach ( (array) $dependencies as $dependency ) {
			self::$dependencies[ $dependency ] = $dependency;
		}
	}

	/**
	 * Add field model data to the array for JS.
	 *
	 * @since 2.4.0
	 *
	 * @param CMB2_Field $field Field object.
	 */
	public static function add_field_data( CMB2_Field $field ) {
		$hash = $field->hash_id();
		if ( ! isset( self::$fields[ $hash ] ) ) {
			self::$fields[ $hash ] = $field->js_data();
		}
	}

	/**
	 * Enqueue the CMB2 JS
	 *
	 * @since  2.0.7
	 */
	public static function enqueue() {
		// Filter required script dependencies.
		$dependencies = self::$dependencies = apply_filters( 'cmb2_script_dependencies', self::$dependencies );

		// Only use minified files if SCRIPT_DEBUG is off.
		$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		$min = $debug ? '' : '.min';

		// if colorpicker.
		if ( isset( $dependencies['wp-color-picker'] ) ) {
			if ( ! is_admin() ) {
				self::colorpicker_frontend();
			}

			// Enqueue colorpicker
			if ( ! wp_script_is( 'wp-color-picker', 'enqueued' ) ) {
				wp_enqueue_script( 'wp-color-picker' );
			}

			if ( isset( $dependencies['wp-color-picker-alpha'] ) ) {
				self::register_colorpicker_alpha();
			}
		}

		// if file/file_list.
		if ( isset( $dependencies['media-editor'] ) ) {
			wp_enqueue_media();
			CMB2_Type_File_Base::output_js_underscore_templates();
		}

		// if timepicker.
		if ( isset( $dependencies['jquery-ui-datetimepicker'] ) ) {
			self::register_datetimepicker();
		}

		// if cmb2-wysiwyg.
		$enqueue_wysiwyg = isset( $dependencies['cmb2-wysiwyg'] ) && $debug;
		unset( $dependencies['cmb2-wysiwyg'] );

		// if cmb2-char-counter.
		$enqueue_char_counter = isset( $dependencies['cmb2-char-counter'] ) && $debug;
		unset( $dependencies['cmb2-char-counter'] );

		// Enqueue cmb JS.
		wp_enqueue_script( self::$handle, CMB2_Utils::url( "js/cmb2{$min}.js" ), array_values( $dependencies ), CMB2_VERSION, true );

		// if SCRIPT_DEBUG, we need to enqueue separately.
		if ( $enqueue_wysiwyg ) {
			wp_enqueue_script( 'cmb2-wysiwyg', CMB2_Utils::url( 'js/cmb2-wysiwyg.js' ), array( 'jquery', 'wp-util' ), CMB2_VERSION );
		}
		if ( $enqueue_char_counter ) {
			wp_enqueue_script( 'cmb2-char-counter', CMB2_Utils::url( 'js/cmb2-char-counter.js' ), array( 'jquery', 'wp-util' ), CMB2_VERSION );
		}

		self::localize( $debug );

		do_action( 'cmb2_footer_enqueue' );
	}

	/**
	 * Register or enqueue the wp-color-picker-alpha script.
	 *
	 * @since  2.2.7
	 *
	 * @param  boolean $enqueue Whether or not to enqueue.
	 *
	 * @return void
	 */
	public static function register_colorpicker_alpha( $enqueue = false ) {
		// Only use minified files if SCRIPT_DEBUG is off.
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$func = $enqueue ? 'wp_enqueue_script' : 'wp_register_script';
		$func( 'wp-color-picker-alpha', CMB2_Utils::url( "js/wp-color-picker-alpha{$min}.js" ), array( 'wp-color-picker' ), '2.1.3' );
	}

	/**
	 * Register or enqueue the jquery-ui-datetimepicker script.
	 *
	 * @since  2.2.7
	 *
	 * @param  boolean $enqueue Whether or not to enqueue.
	 *
	 * @return void
	 */
	public static function register_datetimepicker( $enqueue = false ) {
		$func = $enqueue ? 'wp_enqueue_script' : 'wp_register_script';
		$func( 'jquery-ui-datetimepicker', CMB2_Utils::url( 'js/jquery-ui-timepicker-addon.min.js' ), array( 'jquery-ui-slider' ), '1.5.0' );
	}

	/**
	 * We need to register colorpicker on the front-end
	 *
	 * @since  2.0.7
	 */
	protected static function colorpicker_frontend() {
		wp_register_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), CMB2_VERSION );
		wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), CMB2_VERSION );
		wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', array(
			'clear'         => esc_html__( 'Clear', 'cmb2' ),
			'defaultString' => esc_html__( 'Default', 'cmb2' ),
			'pick'          => esc_html__( 'Select Color', 'cmb2' ),
			'current'       => esc_html__( 'Current Color', 'cmb2' ),
		) );
	}

	/**
	 * Localize the php variables for CMB2 JS
	 *
	 * @since  2.0.7
	 *
	 * @param mixed $debug Whether or not we are debugging.
	 */
	protected static function localize( $debug ) {
		static $localized = false;
		if ( $localized ) {
			return;
		}

		$localized = true;
		$l10n = array(
			'fields'            => self::$fields,
			'ajax_nonce'        => wp_create_nonce( 'ajax_nonce' ),
			'ajaxurl'           => admin_url( '/admin-ajax.php' ),
			'script_debug'      => $debug,
			'up_arrow_class'    => 'dashicons dashicons-arrow-up-alt2',
			'down_arrow_class'  => 'dashicons dashicons-arrow-down-alt2',
			'user_can_richedit' => user_can_richedit(),
			'defaults'          => array(
				'code_editor'  => false,
				'color_picker' => false,
				'date_picker'  => array(
					'changeMonth'     => true,
					'changeYear'      => true,
					'dateFormat'      => _x( 'mm/dd/yy', 'Valid formatDate string for jquery-ui datepicker', 'cmb2' ),
					'dayNames'        => explode( ',', esc_html__( 'Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday', 'cmb2' ) ),
					'dayNamesMin'     => explode( ',', esc_html__( 'Su, Mo, Tu, We, Th, Fr, Sa', 'cmb2' ) ),
					'dayNamesShort'   => explode( ',', esc_html__( 'Sun, Mon, Tue, Wed, Thu, Fri, Sat', 'cmb2' ) ),
					'monthNames'      => explode( ',', esc_html__( 'January, February, March, April, May, June, July, August, September, October, November, December', 'cmb2' ) ),
					'monthNamesShort' => explode( ',', esc_html__( 'Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec', 'cmb2' ) ),
					'nextText'        => esc_html__( 'Next', 'cmb2' ),
					'prevText'        => esc_html__( 'Prev', 'cmb2' ),
					'currentText'     => esc_html__( 'Today', 'cmb2' ),
					'closeText'       => esc_html__( 'Done', 'cmb2' ),
					'clearText'       => esc_html__( 'Clear', 'cmb2' ),
				),
				'time_picker'  => array(
					'timeOnlyTitle' => esc_html__( 'Choose Time', 'cmb2' ),
					'timeText'      => esc_html__( 'Time', 'cmb2' ),
					'hourText'      => esc_html__( 'Hour', 'cmb2' ),
					'minuteText'    => esc_html__( 'Minute', 'cmb2' ),
					'secondText'    => esc_html__( 'Second', 'cmb2' ),
					'currentText'   => esc_html__( 'Now', 'cmb2' ),
					'closeText'     => esc_html__( 'Done', 'cmb2' ),
					'timeFormat'    => _x( 'hh:mm TT', 'Valid formatting string, as per http://trentrichardson.com/examples/timepicker/', 'cmb2' ),
					'controlType'   => 'select',
					'stepMinute'    => 5,
				),
			),
			'strings' => array(
				'upload_file'  => esc_html__( 'Use this file', 'cmb2' ),
				'upload_files' => esc_html__( 'Use these files', 'cmb2' ),
				'remove_image' => esc_html__( 'Remove Image', 'cmb2' ),
				'remove_file'  => esc_html__( 'Remove', 'cmb2' ),
				'file'         => esc_html__( 'File:', 'cmb2' ),
				'download'     => esc_html__( 'Download', 'cmb2' ),
				'check_toggle' => esc_html__( 'Select / Deselect All', 'cmb2' ),
			),
		);

		if ( isset( self::$dependencies['code-editor'] ) && function_exists( 'wp_enqueue_code_editor' ) ) {
			$l10n['defaults']['code_editor'] = wp_enqueue_code_editor( array(
				'type' => 'text/html',
			) );
		}

		wp_localize_script( self::$handle, self::$js_variable, apply_filters( 'cmb2_localized_data', $l10n ) );
	}

}
