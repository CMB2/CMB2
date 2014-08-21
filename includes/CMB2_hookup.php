<?php

class CMB2_hookup {

	/**
	 * Metabox Form ID
	 * @var   string
	 * @since 0.9.4
	 */
	protected $form_id = 'post';

	/**
	 * Array of all hooks done (to be run once)
	 * @var   array
	 * @since 2.0.0
	 */
	protected static $hooks_completed = array();

	/**
	 * Only allow JS registration once
	 * @var   array
	 * @since 2.0.0
	 */
	protected static $registration_done = false;

	public function __construct( $cmb ) {
		$this->cmb = $cmb;

		$this->hooks();
		if ( is_admin() ) {
			$this->admin_hooks();
		}
	}

	public function hooks() {
		// Handle oembed Ajax
		$this->once( 'wp_ajax_cmb2_oembed_handler', array( cmb2_ajax(), 'oembed_handler' ) );
		$this->once( 'wp_ajax_nopriv_cmb2_oembed_handler', array( cmb2_ajax(), 'oembed_handler' ) );

		foreach ( get_class_methods( 'CMB2_Show_Filters' ) as $filter ) {
			add_filter( 'cmb2_show_on', array( 'CMB2_Show_Filters', $filter ), 10, 3 );
		}

	}

	public function admin_hooks() {

		$field_types = (array) wp_list_pluck( $this->cmb->prop( 'fields', array() ), 'type' );
		$has_upload = in_array( 'file', $field_types ) || in_array( 'file_list', $field_types );

		global $pagenow;

		// register our scripts and styles for cmb
		$this->once( 'admin_enqueue_scripts', array( $this, '_register_scripts' ), 8 );

		$type = $this->cmb->mb_object_type();
		if ( 'post' == $type ) {
			add_action( 'admin_menu', array( $this, 'add_metaboxes' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

			if ( $has_upload && in_array( $pagenow, array( 'page.php', 'page-new.php', 'post.php', 'post-new.php' ) ) ) {
				$this->once( 'admin_head', array( $this, 'add_post_enctype' ) );
			}

		}
		elseif ( 'user' == $type ) {

			$priority = $this->cmb->prop( 'priority' );

			if ( ! is_numeric( $priority ) ) {
				if ( $priority == 'high' ) {
					$priority = 5;
				} elseif ( $priority == 'low' ) {
					$priority = 20;
				} else {
					$priority = 10;
				}
			}

			add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'user_new_form', array( $this, 'user_new_metabox' ), $priority );

			add_action( 'personal_options_update', array( $this, 'save_user' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
			add_action( 'user_register', array( $this, 'save_user' ) );
			if ( $has_upload && in_array( $pagenow, array( 'profile.php', 'user-edit.php', 'user-add.php' ) ) ) {
				$this->form_id = 'your-profile';
				$this->once( 'admin_head', array( $this, 'add_post_enctype' ) );
			}
		}
	}

	/**
	 * Registers scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function _register_scripts() {
		self::register_scripts( $this->cmb->object_type() );
	}

	/**
	 * Registers scripts and styles for CMB
	 * @since  1.0.0
	 */
	public static function register_scripts() {
		if ( self::$registration_done ) {
			return;
		}

		global $wp_version;

		// Only use minified files if SCRIPT_DEBUG is off
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( ! is_admin() ) {
			// we need to register colorpicker on the front-end
			wp_register_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), CMB2_VERSION );
			wp_register_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), CMB2_VERSION );
			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', array(
				'clear'         => __( 'Clear', 'cmb2' ),
				'defaultString' => __( 'Default', 'cmb2' ),
				'pick'          => __( 'Select Color', 'cmb2' ),
				'current'       => __( 'Current Color', 'cmb2' ),
			) );
		}

		wp_register_script( 'cmb-timepicker', cmb2_utils()->url( 'js/jquery.timePicker.min.js' ) );

		// scripts required for cmb
		$scripts = array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'cmb-timepicker', 'wp-color-picker' );
		// styles required for cmb
		$styles = array( 'wp-color-picker' );

		wp_register_script( 'cmb-scripts', cmb2_utils()->url( "js/cmb2{$min}.js" ), $scripts, CMB2_VERSION );

		wp_enqueue_media();

		wp_localize_script( 'cmb-scripts', 'cmb2_l10', apply_filters( 'cmb2_localized_data', array(
			'ajax_nonce'       => wp_create_nonce( 'ajax_nonce' ),
			'ajaxurl'          => admin_url( '/admin-ajax.php' ),
			'script_debug'     => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
			'new_admin_style'  => version_compare( $wp_version, '3.7', '>' ),
			'up_arrow_class'   => 'dashicons dashicons-arrow-up-alt2',
			'down_arrow_class' => 'dashicons dashicons-arrow-down-alt2',
			'defaults'         => array(
				'color_picker' => false,
				'date_picker'  => array(
					'changeMonth'     => true,
					'changeYear'      => true,
					'dateFormat'      => __( 'mm/dd/yy', 'cmb2' ),
					'dayNames'        => explode( ',', __( 'Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday', 'cmb2' ) ),
					'dayNamesMin'     => explode( ',', __( 'Su, Mo, Tu, We, Th, Fr, Sa', 'cmb2' ) ),
					'dayNamesShort'   => explode( ',', __( 'Sun, Mon, Tue, Wed, Thu, Fri, Sat', 'cmb2' ) ),
					'monthNames'      => explode( ',', __( 'January, February, March, April, May, June, July, August, September, October, November, December', 'cmb2' ) ),
					'monthNamesShort' => explode( ',', __( 'Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec', 'cmb2' ) ),
					'nextText'        => __( 'Next', 'cmb2' ),
					'prevText'        => __( 'Prev', 'cmb2' ),
					'currentText'     => __( 'Today', 'cmb2' ),
					'closeText'       => __( 'Done', 'cmb2' ),
					'clearText'       => __( 'Clear', 'cmb2' ),
				),
				'time_picker'  => array(
					'startTime'   => '00:00',
					'endTime'     => '23:59',
					'show24Hours' => false,
					'separator'   => ':',
					'step'        => 30
				),
			),
			'strings' => array(
				'upload_file'  => __( 'Use this file', 'cmb2' ),
				'remove_image' => __( 'Remove Image', 'cmb2' ),
				'remove_file'  => __( 'Remove', 'cmb2' ),
				'file'         => __( 'File:', 'cmb2' ),
				'download'     => __( 'Download', 'cmb2' ),
				'check_toggle' => __( 'Select / Deselect All', 'cmb2' ),
			),
		) ) );

		wp_register_style( 'cmb-styles', cmb2_utils()->url( "css/cmb2{$min}.css" ), $styles );

		self::$registration_done = true;
	}

	/**
	 * Enqueues scripts and styles for CMB
	 * @since  1.0.0
	 */
	public function do_scripts( $hook ) {
		// only enqueue our scripts/styles on the proper pages
		if ( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
			if ( $this->cmb->prop( 'cmb_styles' ) ) {
				self::enqueue_cmb_css();
			}
			self::enqueue_cmb_js();
		}
	}

	/**
	 * Add encoding attribute
	 */
	public function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			$form = jQuery("#' . $this->form_id . '");
			if ( $form.length ) {
				$form.attr( {
					"enctype" : "multipart/form-data",
					"encoding" : "multipart/form-data"
				} );
			}
		});
		</script>';
	}

	/**
	 * Add metaboxes (to 'post' object type)
	 */
	public function add_metaboxes() {

		if ( ! $this->show_on() ) {
			return;
		}

		foreach ( $this->cmb->prop( 'object_types' ) as $page ) {
			add_meta_box( $this->cmb->cmb_id, $this->cmb->prop( 'title' ), array( $this, 'post_metabox' ), $page, $this->cmb->prop( 'context' ), $this->cmb->prop( 'priority' ) ) ;
		}
	}

	/**
	 * Display metaboxes for a post object
	 * @since  1.0.0
	 */
	public function post_metabox() {
		$this->cmb->show_form( get_the_ID(), 'post' );
	}

	/**
	 * Display metaboxes for new user page
	 * @since  1.0.0
	 */
	public function user_new_metabox( $section ) {
		if ( $section == $this->cmb->prop( 'new_user_section' ) ) {
			$this->cmb->new_user_page = true;
			$this->user_metabox();
		}
	}

	/**
	 * Display metaboxes for a user object
	 * @since  1.0.0
	 */
	public function user_metabox() {

		if ( 'user' != $this->cmb->mb_object_type() ) {
			return;
		}

		if ( ! $this->show_on() ) {
			return;
		}

		if ( $this->cmb->prop( 'cmb_styles' ) ) {
			self::enqueue_cmb_css();
		}
		self::enqueue_cmb_js();

		$this->cmb->show_form( 0, 'user' );
	}

	/**
	 * Save data from metabox
	 */
	public function save_post( $post_id, $post = false ) {

		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		$do_not_pass_go = (
			// check nonce
			! isset( $_POST[ $this->cmb->nonce() ] )
			|| ! wp_verify_nonce( $_POST[ $this->cmb->nonce() ], $this->cmb->nonce() )
			// check if autosave
			|| defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE
			// check user editing permissions
			|| ( 'page' ==  $post_type && ! current_user_can( 'edit_page', $post_id ) )
			|| ! current_user_can( 'edit_post', $post_id )
			// get the metabox post_types & compare it to this post_type
			|| ! in_array( $post_type, $this->cmb->prop( 'object_types' ) )
		);

		if ( $do_not_pass_go ) {
			// do not collect $200
			return;
		}

		// take a trip to reading railroad – if you pass go collect $200
		$this->cmb->save_fields( $post_id, 'post', $_POST );
	}

	/**
	 * Save data from metabox
	 */
	public function save_user( $user_id )  {
		// check permissions
		if (
			// check nonce
			! isset( $_POST[ $this->cmb->nonce() ] )
			|| ! wp_verify_nonce( $_POST[ $this->cmb->nonce() ], $this->cmb->nonce() )
		) {
			// @todo more hardening?
			return;
		}

		$this->cmb->save_fields( $user_id, 'user', $_POST );
	}

	/**
	 * Determines if metabox should be shown in current context
	 * @since  2.0.0
	 * @return bool
	 */
	public function show_on() {
		return (bool) apply_filters( 'cmb2_show_on', true, $this->cmb->meta_box, $this->cmb );
	}

	/**
	 * Ensures WordPress hook only gets fired once
	 * @since  2.0.0
	 * @param string   $action        The name of the filter to hook the $hook callback to.
	 * @param callback $hook          The callback to be run when the filter is applied.
	 * @param integer  $priority      Order the functions are executed
	 * @param int      $accepted_args The number of arguments the function accepts.
	 */
	public function once( $action, $hook, $priority = 10, $accepted_args = 1 ) {
		$key = md5( serialize( func_get_args() ) );

		if ( in_array( $key, self::$hooks_completed ) ) {
			return;
		}

		self::$hooks_completed[] = $key;
		add_filter( $action, $hook, $priority, $accepted_args );
	}

	/**
	 * Includes CMB styles
	 * @since  2.0.0
	 */
	public static function enqueue_cmb_css() {
		CMB2_hookup::register_scripts();

		return wp_enqueue_style( 'cmb-styles' );
	}

	/**
	 * Includes CMB JS
	 * @since  2.0.0
	 */
	public static function enqueue_cmb_js() {
		CMB2_hookup::register_scripts();

		return wp_enqueue_script( 'cmb-scripts' );
	}

}
