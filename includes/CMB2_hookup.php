<?php
/**
 * Handles hooking CMB2 forms/metaboxes into the post/attachement/user screens
 * and handles hooking in and saving those fields.
 *
 * @since  2.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
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
	 * @var   bool
	 * @since 2.0.7
	 */
	protected static $js_registration_done = false;

	/**
	 * Only allow CSS registration once
	 * @var   bool
	 * @since 2.0.7
	 */
	protected static $css_registration_done = false;

	/**
	 * Metabox Form ID
	 * @var   CMB2 object
	 * @since 2.0.2
	 */
	protected $cmb;

	public function __construct( CMB2 $cmb ) {
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
		global $pagenow;

		// register our scripts and styles for cmb
		$this->once( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 8 );

		$type = $this->cmb->mb_object_type();
		if ( 'post' == $type ) {
			add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

		} elseif ( 'user' == $type ) {

			$priority = $this->cmb->prop( 'priority' );

			if ( ! is_numeric( $priority ) ) {
				switch ( $priority ) {

					case 'high':
						$priority = 5;
						break;

					case 'low':
						$priority = 20;
						break;

					default:
						$priority = 10;
						break;
				}
			}

			add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );
			add_action( 'user_new_form', array( $this, 'user_new_metabox' ), $priority );

			add_action( 'personal_options_update', array( $this, 'save_user' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
			add_action( 'user_register', array( $this, 'save_user' ) );
		}
	}

	/**
	 * Registers styles for CMB2
	 * @since 2.0.7
	 */
	protected static function register_styles() {
		if ( self::$css_registration_done ) {
			return;
		}

		// Only use minified files if SCRIPT_DEBUG is off
		$min   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$front = is_admin() ? '' : '-front';

		// Filter required styles and register stylesheet
		$styles = apply_filters( 'cmb2_style_dependencies', array() );
		wp_register_style( 'cmb2-styles', cmb2_utils()->url( "css/cmb2{$front}{$min}.css" ), $styles );

		self::$css_registration_done = true;
	}

	/**
	 * Registers scripts for CMB2
	 * @since  2.0.7
	 */
	protected static function register_js() {
		if ( self::$js_registration_done ) {
			return;
		}

		$hook = is_admin() ? 'admin_footer' : 'wp_footer';
		add_action( $hook, array( 'CMB2_JS', 'enqueue' ), 8 );

		self::$js_registration_done = true;
	}

	/**
	 * Registers scripts and styles for CMB2
	 * @since  1.0.0
	 */
	public static function register_scripts() {
		self::register_styles();
		self::register_js();
	}

	/**
	 * Enqueues scripts and styles for CMB2
	 * @since  1.0.0
	 */
	public function do_scripts( $hook ) {
		// only enqueue our scripts/styles on the proper pages
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'page-new.php', 'page.php' ), true ) ) {
			if ( $this->cmb->prop( 'cmb_styles' ) ) {
				self::enqueue_cmb_css();
			}
			if ( $this->cmb->prop( 'enqueue_js' ) ) {
				self::enqueue_cmb_js();
			}
		}
	}

	/**
	 * Add metaboxes (to 'post' object type)
	 */
	public function add_metaboxes() {

		if ( ! $this->show_on() ) {
			return;
		}

		foreach ( $this->cmb->prop( 'object_types' ) as $post_type ) {

			if ( $this->cmb->prop( 'closed' ) ) {
				add_filter( "postbox_classes_{$post_type}_{$this->cmb->cmb_id}", array( $this, 'close_metabox_class' ) );
			}

			/**
			 * To keep from registering an actual post-screen metabox,
			 * omit the 'title' attribute from the metabox registration array.
			 *
			 * (WordPress will not display metaboxes without titles anyway)
			 *
			 * This is a good solution if you want to output your metaboxes
			 * Somewhere else in the post-screen
			 */
			if ( $this->cmb->prop( 'title' ) ) {
				add_meta_box( $this->cmb->cmb_id, $this->cmb->prop( 'title' ), array( $this, 'post_metabox' ), $post_type, $this->cmb->prop( 'context' ), $this->cmb->prop( 'priority' ) );
			}
		}
	}

	/**
	 * Add 'closed' class to metabox
	 * @since  2.0.0
	 * @param  array  $classes Array of classes
	 * @return array           Modified array of classes
	 */
	public function close_metabox_class( $classes ) {
		$classes[] = 'closed';
		return $classes;
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
			$object_id = $this->cmb->object_id();
			$this->cmb->object_id( isset( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : $object_id );
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
		if ( $this->cmb->prop( 'enqueue_js' ) ) {
			self::enqueue_cmb_js();
		}

		$this->cmb->show_form( 0, 'user' );
	}

	/**
	 * Save data from metabox
	 */
	public function save_post( $post_id, $post = false ) {

		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		$do_not_pass_go = (
			! $this->cmb->prop( 'save_fields' )
			// check nonce
			|| ! isset( $_POST[ $this->cmb->nonce() ] )
			|| ! wp_verify_nonce( $_POST[ $this->cmb->nonce() ], $this->cmb->nonce() )
			// check if autosave
			|| defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE
			// check user editing permissions
			|| ( 'page' == $post_type && ! current_user_can( 'edit_page', $post_id ) )
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
	public function save_user( $user_id ) {
		// check permissions
		if (
			! $this->cmb->prop( 'save_fields' )
			// check nonce
			|| ! isset( $_POST[ $this->cmb->nonce() ] )
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
	 * @return bool Whether metabox should be added/shown
	 */
	public function show_on() {
		$show = true;

		// If metabox is requesting to be conditionally shown
		if ( is_callable( $this->cmb->prop( 'show_on_cb' ) ) ) {
			$show = (bool) call_user_func( $this->cmb->prop( 'show_on_cb' ), $this->cmb );
		}

		/**
		 * Filter to determine if metabox should show. Default is true
		 *
		 * @param array  $show          Default is true, show the metabox
		 * @param mixed  $meta_box_args Array of the metabox arguments
		 * @param mixed  $cmb           The CMB2 instance
		 */
		$show = (bool) apply_filters( 'cmb2_show_on', $show, $this->cmb->meta_box, $this->cmb );

		return $show;
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
	 * Includes CMB2 styles
	 * @since  2.0.0
	 */
	public static function enqueue_cmb_css() {
		if ( ! apply_filters( 'cmb2_enqueue_css', true ) ) {
			return false;
		}

		self::register_styles();
		return wp_enqueue_style( 'cmb2-styles' );
	}

	/**
	 * Includes CMB2 JS
	 * @since  2.0.0
	 */
	public static function enqueue_cmb_js() {
		if ( ! apply_filters( 'cmb2_enqueue_js', true ) ) {
			return false;
		}

		self::register_js();
		return true;
	}

}
