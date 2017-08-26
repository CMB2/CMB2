<?php

/**
 * Handles hooking CMB2 forms/metaboxes into WordPress admin "options" pages.
 * This class deals exclusively with actions on individual CMB2 boxes, it assigns the box to a specific
 * CMB_Options_Page_Hookup object based on the value of $this->page_id
 *
 * @since     2.XXX Now allows multiple metaboxes on an options page; rewrite to separate concerns
 * @since     2.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property  CMB2 $cmb  Allow IDEs, etc. to reconize property accessed via magic method.
 */
class CMB2_Options_Hookup extends CMB2_hookup {
	
	/**
	 * CMB2 instance, added to avoid implicit assignment in constructor
	 *
	 * @var \CMB2
	 * @since 2.XXX
	 */
	protected $cmb;
	
	/**
	 * Options page key.
	 *
	 * @var   string
	 * @since 2.2.5
	 */
	protected $option_key = '';
	
	/**
	 * Page ID in admin
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $page_id = '';
	
	/**
	 * Page hookup object
	 *
	 * @var \CMB2_Options_Page_Hookup
	 * @since 2.XXX
	 */
	protected $page;
	
	/**
	 * The object type we are performing the hookup for
	 *
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'options-page';
	
	/**
	 * Constructor
	 *
	 * @since 2.XXX                         Sets $this->page_id value, allows passing of default hooks and page
	 * @since 2.0.0
	 * @param CMB2                          $cmb        The CMB2 object to hookup
	 * @param string                        $option_key Option key box is displaying values for
	 * @param string                        $page_id    Value of _GET['page'], normally set automatically
	 * @param CMB2_Options_Page_Hookup|null $page
	 */
	public function __construct( CMB2 $cmb, $option_key, $page_id = '', $page = null ) {
		
		$this->cmb        = $cmb;
		$this->option_key = (string) $option_key;
		$this->page_id    = empty( $page_id ) ? $this->set_page() : (string) $page_id;
		
		if ( $page !== null && $page instanceOf CMB2_Options_Page_Hookup ) {
			$this->page = $page;
		}
	}
	
	/**
	 * Default hooks.
	 *
	 * @since  2.XXX       Complete re-write to use close to normal CMB actions
	 * @since  2.?.?       Method undocumented
	 * @return array|bool  Array of hooks added
	 */
	public function hooks() {
		
		// Optional 'network_admin_menu' value can be passed here
		$wp_menu_hook = $this->cmb->prop( 'admin_menu_hook', 'admin_menu' );
		
		// get the options page and add $this->cmb to it. Options page handles hooking into menus, etc.
		$this->page = $this->get_options_page_and_add_box( $wp_menu_hook );
		
		// get the box context
		$context = $this->cmb->prop( 'context' ) ? $this->cmb->prop( 'context' ) : 'normal';
		
		// Hooks array: 'hook' will be set to 'id' if not configured.
		$hooks = array(
			array(
				'id' => 'admin_page_hooks',
				'call' => array( $this->page, 'hooks' ),
				'hook' => 'admin_page_hooks'
			),
			array(
				'id' => 'add_meta_boxes',
				'call' => array( $this, 'add_metaboxes' ),
				'hook' => 'add_meta_boxes_' . $this->page_id,
				'only_if' => $context == 'normal',
			),
			array(
				'id' => 'add_context_metaboxes',
				'call' => array( $this, 'add_context_metaboxes' ),
				'hook' => 'edit_form_top',
				'only_if' => $context == 'form_top',
			),
			array(
				'id'   => 'save_options',
				'call' => array( $this, 'save_options' ),
				'hook' => 'admin_post_' . $this->option_key,
			),
			array(
				'id'      => 'cmb2_override_option_get',
				'call'    => array( $this, 'network_get_override' ),
				'hook'    => 'cmb2_override_option_get_' . $this->option_key,
				'only_if' => 'network_admin_menu' == $wp_menu_hook,
				'type'    => 'filter',
				'args'    => 2,
			),
			array(
				'id'      => 'cmb2_override_option_save',
				'call'    => array( $this, 'network_update_override' ),
				'hook'      => 'cmb2_override_option_save_' . $this->option_key,
				'only_if' => 'network_admin_menu' == $wp_menu_hook,
				'type'    => 'filter',
				'args'    => 2,
			),
		);
		
		// set of hooks to be called; passing them through this prep filter to aid devs calling filter
		$hooks = CMB2_Utils::prepare_hooks_array( $hooks, $wp_menu_hook );
		
		/**
		 * 'cmb2_options_metabox_hooks' filter.
		 *
		 * Allows adding or modifying calls to hooks called by this page.
		 *
		 * @since    2.XXX
		 * @internal array               $hooks          Array of hook config arrays
		 * @internal string              $this->page_id  Menu slug ($_GET['page']) value
		 * @internal \CMB2_Options_Hookup $this          Instance of this class
		 */
		$filtered = apply_filters( 'cmb2_options_metabox_hooks', $hooks, $this->page_id, $this );
		$hooks = $hooks != $filtered ? $filtered : $hooks;
		
		// add the hooks
		return ! empty( $hooks ) ? CMB2_Utils::add_wp_hooks_from_config_array( $hooks ) : FALSE;
	}
	
	/**
	 * Options display class will uses "do metaboxes" call.
	 *
	 * @since 2.XXX
	 * @return bool
	 */
	public function add_metaboxes() {
		
		if ( ! $this->show_on() || ! $this->cmb->prop( 'title' ) ) {
			return false;
		}
		
		add_filter( "postbox_classes_{$this->page_id}_{$this->cmb->cmb_id}", array( $this, 'postbox_classes' ) );
		
		add_meta_box( 
			$this->cmb->cmb_id, 
			$this->cmb->prop( 'title' ), 
			array( $this, 'metabox_callback' ), 
			$this->object_type,
			$this->cmb->prop( 'context' ), 
			$this->cmb->prop( 'priority' ) 
		);
		
		return true;
	}
	
	/**
	 * Clone of metabox_callback without demanding a post or comment ID. Also returns a value for testing/non-echo
	 * future uses.
	 *
	 * @since  2.XXX
	 */
	public function metabox_callback() {
		
		ob_start();
		$returned = $this->cmb->show_form( 0, $this->object_type );
		$echoed   = ob_get_clean();
		
		if ( $echoed ) {
			echo $echoed;
			$returned = true;
		};
		
		return $returned;
	}
	
	/**
	 * Gets the options page and adds this metabox to it.
	 *
	 * @since  2.XXX
	 * @param  string $wp_menu_hook  The first box to call the page hookup constructor will set it.
	 * @return \CMB2_Options_Page_Hookup
	 */
	public function get_options_page_and_add_box( $wp_menu_hook = '' ) {
		
		$admin_page = CMB2_Options_Pages::get( $this->page_id );
		
		if ( $admin_page === NULL ) {
			$admin_page = new CMB2_Options_Page_Hookup( $this->page_id, $this->option_key, $wp_menu_hook );
			CMB2_options_Pages::add( $admin_page );
		}
		
		$admin_page->add_hookup( $this );
		
		return $admin_page;
	}
	
	/**
	 * Get the menu slug. We cannot pass a fallback to CMB2 as it will prevent additional pages from being generated.
	 * Menu slugs must ALWAYS be accompianied by an explicit declaration of the 'option_key' box parameter.
	 *
	 * @since  2.XXX
	 * @return string
	 */
	public function set_page() {
		
		$menu_slug = $this->cmb->prop( 'menu_slug' );
		
		$page = empty( $menu_slug ) || ( $menu_slug && ! $this->cmb->prop( 'option_key' ) ) ?
			$this->option_key : $menu_slug;
		
		return $page;
	}
	
	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Checks multiple boxes
	 *               Calls $this->can_box_save() instead of $this->can_save()
	 *               Allows options to be reset
	 * @since  2.2.5
	 * @return void
	 */
	public function save_options() {
		
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}
		
		// set the option and action
		$action  = isset( $_POST['submit-cmb'] ) ? 'save' : ( isset( $_POST['reset-cmb'] ) ? 'reset' : FALSE );
		$option  = isset( $_POST['action'] ) ? $_POST['action'] : FALSE;
		$updated = FALSE;
		
		if ( $action && $option && $this->can_save() && $this->option_key === $option ) {
			
			if ( $action == 'reset' ) {
				$this->field_values_to_default();
				$updated = 'reset';
			}
			
			$up = $this->cmb->save_fields( $this->option_key, $this->cmb->object_type(), $_POST )->was_updated();
			
			$updated = $updated ? $updated : $up;
		}
		
		$url = add_query_arg( 'updated', var_export( $updated, TRUE ), $url );
		
		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		exit;
	}
	
	/**
	 * Changes _POST values for box fields to default values.
	 *
	 * @since 2.XXX
	 * @internal \CMB2_Field $field
	 */
	public function field_values_to_default() {
		
		$fields = $this->cmb->prop( 'fields' );
		
		foreach ( $fields as $fid => $field ) {
			$f             = $this->cmb->get_field( $field );
			$_POST[ $fid ] = $this->cmb->prop( 'reset_action' ) == 'remove' ? '' : $f->get_default();
		}
	}
	
	/**
	 * Replaces get_option with get_site_option
	 *
	 * @since 2.XXX   Added test for expected value of test before returning site option
	 * @since 2.2.5
	 * @param string $test should be 'cmb2_no_override_option_get'
	 * @param mixed  $default
	 * @return mixed  Value set for the network option.
	 */
	public function network_get_override( $test, $default = FALSE ) {
		
		return $test == 'cmb2_no_override_option_get' ?
			get_site_option( $this->option_key, $default ) : $test;
	}
	
	/**
	 * Replaces update_option with update_site_option
	 *
	 * @since 2.XXX Added test for expected value of test
	 * @since 2.2.5
	 * @param string $test should be 'cmb2_no_override_option_save'
	 * @param mixed  $option_value
	 * @return bool Success/Failure
	 */
	public function network_update_override( $test, $option_value ) {
		
		return $test == 'cmb2_no_override_option_save' ?
			update_site_option( $this->option_key, $option_value ) : $test;
	}
	
	/**
	 * Returns property, allows checking state of class
	 *
	 * @since 2.XXX
	 * @param string $property Class property to fetch
	 * @return mixed|null
	 */
	public function __get( $property ) {
		
		return isset( $this->{$property} ) ? $this->{$property} : NULL;
	}
}