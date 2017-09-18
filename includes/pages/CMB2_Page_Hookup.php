<?php

/**
 * Handles creating an options page, which may contain multiple metaboxes.
 * The default page format is the same as the old-school CMB2 style. It can mimic the 'post' editor, however.
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Page_Hookup extends CMB2_Page {
	
	/**
	 * WP Options key
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $option_key;
	
	/**
	 * The page slug, equivalent to $_GET['page']
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $page_id;
	
	/**
	 * The first box to call this instance will set the menu hook!
	 *
	 * @since 2.XXX
	 * @var string
	 */
	protected $wp_menu_hook;
	
	/**
	 * CMB2__Page_Hookup constructor.
	 *
	 * @since 2.XXX
	 * @param string $page         The page slug, equivalent to $_GET['page']
	 * @param string $option_key   The WP Options key
	 * @param string $wp_menu_hook The menu hook which was set on the box (usually 'admin_menu')
	 */
	public function __construct( $page, $option_key, $wp_menu_hook ) {
		
		$this->page_id      = $page;
		$this->option_key   = $option_key;
		$this->wp_menu_hook = $wp_menu_hook;
	}
	
	/**
	 * Adds metaboxes to page
	 *
	 * @since 2.XXX
	 * @return bool
	 */
	public function add_metaboxes() {
		
		if ( $this->shared['page_format'] == 'post' ) {
			do_action( 'add_meta_boxes_' . $this->page_id, NULL );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Adds WP JS needed for post-style page.
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_postbox_script() {
		
		wp_enqueue_script( 'postbox' );
		
		return TRUE;
	}
	
	/**
	 * Adds script to admin footer to toggle postboxes
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_postbox_toggle() {
		
		echo '<script>jQuery(document).ready(function()';
		echo '{postboxes.add_postbox_toggles("postbox-container");});</script>';
		
		return TRUE;
	}
	
	/**
	 * Checks to see if the setting is registered, and if not, registers it
	 *
	 * @since  2.XXX
	 * @return bool
	 */
	public function add_registered_setting() {
		
		if ( ! $this->is_setting_registered() ) {
			
			register_setting( 'cmb2', $this->option_key );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Adds page to menu. Allows parameters to be filtered. Returns array containing results, for testing purposes.
	 *
	 * @since 2.XXX
	 * @return array
	 */
	public function add_to_menu() {
		
		$return = array();
		
		// this class shouldn't be working at all if these are true, but just to be sure...
		if ( ! $this->page_id || empty( $this->hookups ) ) {
			return $return;
		}
		
		// get menu parameters
		$params = $this->menu_parameters();
		
		// if parameters return was bogus, exit
		if ( empty( $params ) ) {
			return $return;
		}
		
		// gets menu parameters and adds menu to WP admin menu
		$page_hook = $this->add_to_admin_menu( $params );
		
		// menu not added, exit
		if ( ! $page_hook ) {
			return $return;
		}
		
		// add page_hook token
		$tokens = array( '{PAGEHOOK}' => $page_hook );
		
		// add page hooks which required $page_hook
		$hooks = $this->hooks_array( 'add_to_menu', $this->get_hooks( 'add_to_menu' ), $tokens );
		$hooks = ! empty( $hooks ) ? CMB2_Page_Utils::add_wp_hooks_from_config_array( $hooks ) : FALSE;
		
		return array(
			'type'      => ( empty( $params['parent_slug'] ) ? 'menu' : 'submenu' ),
			'params'    => $params,
			'page_hook' => $page_hook,
			'hooks'     => $hooks,
		);
	}
	
	/**
	 * Adds settings notice if updated.
	 *
	 * @since  2.XXX
	 * @return string
	 */
	public function add_update_notice() {
		
		$up   = $this->is_updated();
		$str  = '';
		$hook = $up === 'true' ? 'updated' : 'notice-warning';
		
		if ( $up === FALSE ) {
			return FALSE;
		}
		
		switch ( $up ) {
			case 'true':
				$str = 'Settings updated.';
				break;
			case 'false':
				$str = 'Nothing to update.';
				break;
			case 'reset':
				$str = 'Options reset to defaults.';
				break;
		}
		
		add_settings_error( $this->option_key . '-notices', '', __( $str, 'cmb2' ), $hook );
		
		return $up;
	}
	
	/**
	 * Adds hooks tied to the creation of this page.
	 *
	 * @since  2.XXX
	 * @return array|bool
	 */
	protected function add_hooks() {
		
		$hooks = $this->hooks_array( 'hooks', $this->get_hooks( 'hooks' ) );
		
		return ! empty( $hooks ) ?
			CMB2_Page_Utils::add_wp_hooks_from_config_array( $hooks, $this->wp_menu_hook ) : FALSE;
	}
	
	/**
	 * Default hooks used by this class. Root keys are method by which they are requested.
	 *
	 * @since  2.XXX
	 * @param  string $key It's possible to get all hooks or just a subset
	 * @return array
	 */
	protected function get_hooks( $key = '' ) {
		
		$hooks = array(
			'hooks'       => array(
				array(
					'id'   => 'menu_hook',
					'hook' => $this->wp_menu_hook,
					'call' => array( $this, 'add_to_menu' ),
				),
				array(
					'id'       => 'updated',
					'priority' => 11,
					'hook'     => $this->wp_menu_hook,
					'call'     => array( $this, 'add_update_notice' ),
				),
				array(
					'id'      => 'postbox',
					'hook'    => 'admin_enqueue_scripts',
					'call'    => array( $this, 'add_postbox_script' ),
					'only_if' => $this->shared['page_format'] !== 'post',
				),
				array(
					'id'      => 'toggle',
					'hook'    => 'admin_print_footer_scripts',
					'call'    => array( $this, 'add_postbox_toggle' ),
					'only_if' => $this->shared['page_format'] !== 'post',
				),
			),
			'add_to_menu' => array(
				array(
					'id'   => 'add_metaboxes',
					'hook' => 'load-{PAGEHOOK}',
					'call' => array( $this, 'add_metaboxes' ),
				),
				array(
					'id'      => 'add_cmb_css_to_head',
					'hook'    => 'admin_print_styles-{PAGEHOOK}',
					'call'    => array( 'CMB2_hookup', 'enqueue_cmb_css' ),
					'only_if' => $this->shared['cmb_styles'] == TRUE,
				),
			),
		);
		
		if ( $key ) {
			$hooks = ! empty( $key ) && array_key_exists( $key, $hooks ) ? $hooks[ $key ] : array();
		}
		
		return $hooks;
	}
	
	/**
	 * Returns the rendered HTML from options display class
	 *
	 * @since 2.XXX
	 * @return string
	 */
	protected function render_page() {
		
		$notices = CMB2_Page_Utils::do_void_action( array( $this->option_key . '-notices' ), 'settings_errors' );
		
		// Use first hookup in our array to trigger the style/js
		$hookup = reset( $this->hookups );
		
		if ( $this->shared['cmb_styles'] ) {
			$hookup::enqueue_cmb_css();
		}
		if ( $this->shared['enqueue_js'] ) {
			$hookup::enqueue_cmb_js();
		}
		
		$display = new CMB2_Page_Display( $this->option_key, $this->page_id, $this->shared );
		
		return $notices . $display->page();
	}
}