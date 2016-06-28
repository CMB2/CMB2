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
	 * @var   CMB2 object
	 * @since 2.0.2
	 */
	protected $cmb;

	/**
	 * CMB taxonomies array for term meta
	 * @var   array
	 * @since 2.2.0
	 */
	protected $taxonomies = array();

	/**
	 * Custom field columns.
	 * @var   array
	 * @since 2.2.2
	 */
	protected $columns = array();

	/**
	 * The object type we are performing the hookup for
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'post';

	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
		$this->object_type = $this->cmb->mb_object_type();

		$this->universal_hooks();

		if ( is_admin() ) {

			switch ( $this->object_type ) {
				case 'post':
					return $this->post_hooks();
				case 'comment':
					return $this->comment_hooks();
				case 'user':
					return $this->user_hooks();
				case 'term':
					return $this->term_hooks();
			}

		}
	}

	public function universal_hooks() {
		foreach ( get_class_methods( 'CMB2_Show_Filters' ) as $filter ) {
			add_filter( 'cmb2_show_on', array( 'CMB2_Show_Filters', $filter ), 10, 3 );
		}

		if ( is_admin() ) {
			// register our scripts and styles for cmb
			$this->once( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 8 );
			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

			$this->maybe_enqueue_column_display_styles();
		}
	}

	public function post_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		add_action( 'add_attachment', array( $this, 'save_post' ) );
		add_action( 'edit_attachment', array( $this, 'save_post' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		if ( $this->cmb->has_columns ) {
			foreach ( $this->cmb->prop( 'object_types' ) as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'register_column_headers' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'column_display' ), 10, 2 );
			}
		}
	}

	public function comment_hooks() {
		add_action( 'add_meta_boxes_comment', array( $this, 'add_metaboxes' ) );
		add_action( 'edit_comment', array( $this, 'save_comment' ) );

		if ( $this->cmb->has_columns ) {
			add_filter( 'manage_edit-comments_columns', array( $this, 'register_column_headers' ) );
			add_action( 'manage_comments_custom_column', array( $this, 'column_display'  ), 10, 3 );
		}
	}

	public function user_hooks() {
		$priority = $this->get_priority();

		add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
		add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );
		add_action( 'user_new_form', array( $this, 'user_new_metabox' ), $priority );

		add_action( 'personal_options_update', array( $this, 'save_user' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
		add_action( 'user_register', array( $this, 'save_user' ) );

		if ( $this->cmb->has_columns ) {
			add_filter( 'manage_users_columns', array( $this, 'register_column_headers' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'return_column_display'  ), 10, 3 );
		}
	}

	public function term_hooks() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			wp_die( __( 'Term Metadata is a WordPress > 4.4 feature. Please upgrade your WordPress install.', 'cmb2' ) );
		}

		if ( ! $this->cmb->prop( 'taxonomies' ) ) {
			wp_die( __( 'Term metaboxes configuration requires a \'taxonomies\' parameter', 'cmb2' ) );
		}

		$this->taxonomies = (array) $this->cmb->prop( 'taxonomies' );
		$show_on_term_add = $this->cmb->prop( 'new_term_section' );
		$priority         = $this->get_priority( 8 );

		foreach ( $this->taxonomies as $taxonomy ) {
			// Display our form data
			add_action( "{$taxonomy}_edit_form", array( $this, 'term_metabox' ), $priority, 2 );

			$show_on_add = is_array( $show_on_term_add )
				? in_array( $taxonomy, $show_on_term_add )
				: (bool) $show_on_term_add;

			$show_on_add = apply_filters( "cmb2_show_on_term_add_form_{$this->cmb->cmb_id}", $show_on_add, $this->cmb );

			// Display form in add-new section (unless specified not to)
			if ( $show_on_add ) {
				add_action( "{$taxonomy}_add_form_fields", array( $this, 'term_metabox' ), $priority, 2 );
			}

			if ( $this->cmb->has_columns ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'register_column_headers' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'return_column_display'  ), 10, 3 );
			}
		}

		add_action( 'created_term', array( $this, 'save_term' ), 10, 3 );
		add_action( 'edited_terms', array( $this, 'save_term' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'delete_term' ), 10, 3 );

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
		$rtl   = is_rtl() ? '-rtl' : '';

		// Filter required styles and register stylesheet
		$dependencies = apply_filters( 'cmb2_style_dependencies', array() );
		wp_register_style( 'cmb2-styles', cmb2_utils()->url( "css/cmb2{$front}{$rtl}{$min}.css" ), $dependencies );
		wp_register_style( 'cmb2-display-styles', cmb2_utils()->url( "css/cmb2-display{$rtl}{$min}.css" ), $dependencies );

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
	 * Enqueues scripts and styles for CMB2 in admin_head.
	 * @since  1.0.0
	 */
	public function do_scripts( $hook ) {
		$hooks = array(
			'post.php',
			'post-new.php',
			'page-new.php',
			'page.php',
			'comment.php',
			'edit-tags.php',
			'term.php',
			'user-new.php',
			'profile.php',
			'user-edit.php',
		);
		// only pre-enqueue our scripts/styles on the proper pages
		// show_form_for_type will have us covered if we miss something here.
		if ( in_array( $hook, $hooks, true ) ) {
			if ( $this->cmb->prop( 'cmb_styles' ) ) {
				self::enqueue_cmb_css();
			}
			if ( $this->cmb->prop( 'enqueue_js' ) ) {
				self::enqueue_cmb_js();
			}
		}
	}

	/**
	 * Register the CMB2 field column headers.
	 * @since 2.2.2
	 */
	public function register_column_headers( $columns ) {
		$fields = $this->cmb->prop( 'fields' );

		foreach ( $fields as $key => $field ) {
			if ( ! isset( $field['column'] ) ) {
				continue;
			}

			$column = $field['column'];

			if ( false === $column['position'] ) {

				$columns[ $field['id'] ] = $column['name'];

			} else {

				$before = array_slice( $columns, 0, absint( $column['position'] ) );
				$before[ $field['id'] ] = $column['name'];
				$columns = $before + $columns;
			}

			$column['field'] = $field;
			$this->columns[ $field['id'] ] = $column;
		}

		return $columns;
	}

	/**
	 * The CMB2 field column display output.
	 * @since 2.2.2
	 */
	public function column_display( $column_name, $object_id ) {
		if ( isset( $this->columns[ $column_name ] ) ) {
 			$field = new CMB2_Field( array(
				'field_args'  => $this->columns[ $column_name ]['field'],
				'object_type' => $this->object_type,
				'object_id'   => $this->cmb->object_id( $object_id ),
				'cmb_id'      => $this->cmb->cmb_id,
			) );

			$this->cmb->get_field( $field )->render_column();
		}
	}

	/**
	 * Returns the column display.
	 * @since 2.2.2
	 */
	public function return_column_display( $empty, $custom_column, $object_id ) {
		ob_start();
		$this->column_display( $custom_column, $object_id );
		$column = ob_get_clean();

		return $column ? $column : $empty;
	}

	/**
	 * Add metaboxes (to 'post' or 'comment' object types)
	 * @since 1.0.0
	 */
	public function add_metaboxes() {

		if ( ! $this->show_on() ) {
			return;
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
		if ( ! $this->cmb->prop( 'title' ) ) {
			return;
		}

		foreach ( $this->cmb->prop( 'object_types' ) as $post_type ) {
			if ( $this->cmb->prop( 'closed' ) ) {
				add_filter( "postbox_classes_{$post_type}_{$this->cmb->cmb_id}", array( $this, 'close_metabox_class' ) );
			}

			add_meta_box( $this->cmb->cmb_id, $this->cmb->prop( 'title' ), array( $this, 'metabox_callback' ), $post_type, $this->cmb->prop( 'context' ), $this->cmb->prop( 'priority' ) );
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
	 * Display metaboxes for a post or comment object
	 * @since  1.0.0
	 */
	public function metabox_callback() {
		$object_id = 'comment' == $this->object_type ? get_comment_ID() : get_the_ID();
		$this->cmb->show_form( $object_id, $this->object_type );
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
		$this->show_form_for_type( 'user' );
	}

	/**
	 * Display metaboxes for a taxonomy term object
	 * @since  2.2.0
	 */
	public function term_metabox() {
		$this->show_form_for_type( 'term' );
	}

	/**
	 * Display metaboxes for an object type
	 * @since  2.2.0
	 * @param  string $type Object type
	 * @return void
	 */
	public function show_form_for_type( $type ) {
		if ( $type != $this->cmb->mb_object_type() ) {
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

		$this->cmb->show_form( 0, $type );
	}

	/**
	 * Determines if metabox should be shown in current context
	 * @since  2.0.0
	 * @return bool Whether metabox should be added/shown
	 */
	public function show_on() {
		// If metabox is requesting to be conditionally shown
		$show = $this->cmb->should_show();

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
	 * Get the CMB priority property set to numeric hook priority.
	 * @since  2.2.0
	 * @param  integer $default Default display hook priority.
	 * @return integer          Hook priority.
	 */
	public function get_priority( $default = 10 ) {
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
					$priority = $default;
					break;
			}
		}

		return $priority;
	}

	/**
	 * Save data from post metabox
	 * @since  1.0.0
	 * @param  int    $post_id Post ID
	 * @param  mixed  $post    Post object
	 * @return null
	 */
	public function save_post( $post_id, $post = false ) {

		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		$do_not_pass_go = (
			! $this->can_save( $post_type )
			// check user editing permissions
			|| ( 'page' == $post_type && ! current_user_can( 'edit_page', $post_id ) )
			|| ! current_user_can( 'edit_post', $post_id )
		);

		if ( $do_not_pass_go ) {
			// do not collect $200
			return;
		}

		// take a trip to reading railroad – if you pass go collect $200
		$this->cmb->save_fields( $post_id, 'post', $_POST );
	}

	/**
	 * Save data from comment metabox
	 * @since  2.0.9
	 * @param  int    $comment_id Comment ID
	 * @return null
	 */
	public function save_comment( $comment_id ) {

		$can_edit = current_user_can( 'moderate_comments', $comment_id );

		if ( $this->can_save( get_comment_type( $comment_id ) ) && $can_edit ) {
			$this->cmb->save_fields( $comment_id, 'comment', $_POST );
		}
	}

	/**
	 * Save data from user fields
	 * @since  1.0.x
	 * @param  int   $user_id  User ID
	 * @return null
	 */
	public function save_user( $user_id ) {
		// check permissions
		if ( $this->can_save( 'user' ) ) {
			$this->cmb->save_fields( $user_id, 'user', $_POST );
		}
	}

	/**
	 * Save data from term fields
	 * @since  2.2.0
	 * @param  int    $term_id  Term ID
	 * @param  int    $tt_id    Term Taxonomy ID
	 * @param  string $taxonomy Taxonomy
	 * @return null
	 */
	public function save_term( $term_id, $tt_id, $taxonomy = '' ) {
		$taxonomy = $taxonomy ? $taxonomy : $tt_id;

		// check permissions
		if ( $this->taxonomy_can_save( $taxonomy ) && $this->can_save( 'term' ) ) {
			$this->cmb->save_fields( $term_id, 'term', $_POST );
		}
	}

	/**
	 * Delete term meta when a term is deleted.
	 * @since  2.2.0
	 * @param  int    $term_id  Term ID
	 * @param  int    $tt_id    Term Taxonomy ID
	 * @param  string $taxonomy Taxonomy
	 * @return null
	 */
	public function delete_term( $term_id, $tt_id, $taxonomy = '' ) {
		if ( $this->taxonomy_can_save( $taxonomy ) ) {

			foreach ( $this->cmb->prop( 'fields' ) as $field ) {
				$data_to_delete[ $field['id'] ] = '';
			}

			$this->cmb->save_fields( $term_id, 'term', $data_to_delete );
		}
	}

	/**
	 * Determines if the current object is able to be saved
	 * @since  2.0.9
	 * @param  string  $type Current post_type or comment_type
	 * @return bool          Whether object can be saved
	 */
	public function can_save( $type = '' ) {
		return (
			$this->cmb->prop( 'save_fields' )
			// check nonce
			&& isset( $_POST[ $this->cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $this->cmb->nonce() ], $this->cmb->nonce() )
			// check if autosave
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			// get the metabox types & compare it to this type
			&& ( $type && in_array( $type, $this->cmb->prop( 'object_types' ) ) )
		);
	}

	/**
	 * Determine if taxonomy of term being modified is cmb2-editable.
	 * @since  2.2.0
	 * @param  string $taxonomy Taxonomy of term being modified.
	 * @return bool             Whether taxonomy is editable.
	 */
	public function taxonomy_can_save( $taxonomy ) {
		if ( empty( $this->taxonomies ) || ! in_array( $taxonomy, $this->taxonomies ) ) {
			return false;
		}

		$taxonomy_object = get_taxonomy( $taxonomy );
		// Can the user edit this term?
		if ( ! isset( $taxonomy_object->cap ) || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
			return false;
		}

		return true;
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
	 * Enqueues the 'cmb2-display-styles' if the conditions match (has columns, on the right page, etc).
	 * @since  2.2.2.1
	 */
	protected function maybe_enqueue_column_display_styles() {
		global $pagenow;
		if (
			$pagenow
			&& $this->cmb->has_columns
			&& $this->cmb->prop( 'cmb_styles' )
			&& in_array( $pagenow, array( 'edit.php', 'users.php', 'edit-comments.php', 'edit-tags.php' ), 1 )
			) {
			self::enqueue_cmb_css( 'cmb2-display-styles' );
		}
	}

	/**
	 * Includes CMB2 styles
	 * @since  2.0.0
	 */
	public static function enqueue_cmb_css( $handle = 'cmb2-styles' ) {
		if ( ! apply_filters( 'cmb2_enqueue_css', true ) ) {
			return false;
		}

		self::register_styles();

		/*
		 * White list the options as this method can be used as a hook callback
		 * and have a different argument passed.
		 */
		return wp_enqueue_style( 'cmb2-display-styles' === $handle ? $handle : 'cmb2-styles' );
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
