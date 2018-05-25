<?php
/**
 * Handles hooking CMB2 forms/metaboxes into the post/attachement/user screens
 * and handles hooking in and saving those fields.
 *
 * @since  2.0.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Options_Hookup extends CMB2_hookup {

	/**
	 * The object type we are performing the hookup for
	 *
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'options-page';

	/**
	 * Options page key.
	 *
	 * @var   string
	 * @since 2.2.5
	 */
	protected $option_key = '';

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 * @param CMB2 $cmb The CMB2 object to hookup
	 */
	public function __construct( CMB2 $cmb, $option_key ) {
		$this->cmb = $cmb;
		$this->option_key = $option_key;
	}

	public function hooks() {
		if ( empty( $this->option_key ) ) {
			return;
		}

		if ( ! $this->cmb->prop( 'autoload', true ) ) {
			// Disable option autoload if requested.
			add_filter( "cmb2_should_autoload_{$this->option_key}", '__return_false' );
		}

		// Register setting to cmb2 group.
		register_setting( 'cmb2', $this->option_key );

		// Handle saving the data.
		add_action( 'admin_post_' . $this->option_key, array( $this, 'save_options' ) );

		// Optionally network_admin_menu.
		$hook = $this->cmb->prop( 'admin_menu_hook' );

		// Hook in to add our menu.
		add_action( $hook, array( $this, 'options_page_menu_hooks' ) );

		// If in the network admin, need to use get/update_site_option.
		if ( 'network_admin_menu' === $hook ) {
			// Override CMB's getter.
			add_filter( "cmb2_override_option_get_{$this->option_key}", array( $this, 'network_get_override' ), 10, 2 );
			// Override CMB's setter.
			add_filter( "cmb2_override_option_save_{$this->option_key}", array( $this, 'network_update_override' ), 10, 2 );
		}
	}

	/**
	 * Hook up our admin menu item and admin page.
	 *
	 * @since  2.2.5
	 *
	 * @return void
	 */
	public function options_page_menu_hooks() {
		$parent_slug = $this->cmb->prop( 'parent_slug' );
		$title       = $this->cmb->prop( 'title' );
		$menu_title  = $this->cmb->prop( 'menu_title', $title );
		$capability  = $this->cmb->prop( 'capability' );
		$callback    = array( $this, 'options_page_output' );

		if ( $parent_slug ) {
			$page_hook = add_submenu_page(
				$parent_slug,
				$title,
				$menu_title,
				$capability,
				$this->option_key,
				$callback
			);
		} else {
			$page_hook = add_menu_page(
				$title,
				$menu_title,
				$capability,
				$this->option_key,
				$callback,
				$this->cmb->prop( 'icon_url' ),
				$this->cmb->prop( 'position' )
			);
		}

		if ( $this->cmb->prop( 'cmb_styles' ) ) {
			// Include CMB CSS in the head to avoid FOUC
			add_action( "admin_print_styles-{$page_hook}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}

		$this->maybe_register_message();
	}

	/**
	 * If there is a message callback, let it determine how to register the message,
	 * else add a settings message if on this settings page.
	 *
	 * @since  2.2.6
	 *
	 * @return void
	 */
	public function maybe_register_message() {
		$is_options_page = self::is_page( $this->option_key );
		$should_notify   = ! $this->cmb->prop( 'disable_settings_errors' ) && isset( $_GET['settings-updated'] ) && $is_options_page;
		$is_updated      = $should_notify && 'true' === $_GET['settings-updated'];
		$setting         = "{$this->option_key}-notices";
		$code            = '';
		$message         = __( 'Nothing to update.', 'cmb2' );
		$type            = 'notice-warning';

		if ( $is_updated ) {
			$message = __( 'Settings updated.', 'cmb2' );
			$type    = 'updated';
		}

		// Check if parameter has registered a callback.
		if ( $cb = $this->cmb->maybe_callback( 'message_cb' ) ) {

			/**
			 * The 'message_cb' callback will receive the following parameters.
			 * Unless there are other reasons for notifications, the callback should only
			 * `add_settings_error()` if `$args['should_notify']` is truthy.
			 *
			 * @param CMB2  $cmb The CMB2 object.
			 * @param array $args {
			 *     An array of message arguments
			 *
			 *     @type bool   $is_options_page Whether current page is this options page.
			 *     @type bool   $should_notify   Whether options were saved and we should be notified.
			 *     @type bool   $is_updated      Whether options were updated with save (or stayed the same).
			 *     @type string $setting         For add_settings_error(), Slug title of the setting to which
			 *                                   this error applies.
			 *     @type string $code            For add_settings_error(), Slug-name to identify the error.
			 *                                   Used as part of 'id' attribute in HTML output.
			 *     @type string $message         For add_settings_error(), The formatted message text to display
			 *                                   to the user (will be shown inside styled `<div>` and `<p>` tags).
			 *                                   Will be 'Settings updated.' if $is_updated is true, else 'Nothing to update.'
			 *     @type string $type            For add_settings_error(), Message type, controls HTML class.
			 *                                   Accepts 'error', 'updated', '', 'notice-warning', etc.
			 *                                   Will be 'updated' if $is_updated is true, else 'notice-warning'.
			 * }
			 */
			$args = compact( 'is_options_page', 'should_notify', 'is_updated', 'setting', 'code', 'message', 'type' );

			$this->cmb->do_callback( $cb, $args );

		} elseif ( $should_notify ) {

			add_settings_error( $setting, $code, $message, $type );
		}
	}

	/**
	 * Display options-page output. To override, set 'display_cb' box property.
	 *
	 * @since  2.2.5
	 */
	public function options_page_output() {
		$this->maybe_output_settings_notices();

		$callback = $this->cmb->prop( 'display_cb' );
		if ( is_callable( $callback ) ) {
			return $callback( $this );
		}

		$tabs = $this->get_tab_group_tabs();
		?>
		<div class="wrap cmb2-options-page option-<?php echo $this->option_key; ?>">
			<?php if ( $this->cmb->prop( 'title' ) ) : ?>
				<h2><?php echo wp_kses_post( $this->cmb->prop( 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $tabs ) ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
						<a class="nav-tab<?php if ( self::is_page( $option_key ) ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
					<?php endforeach; ?>
				</h2>
			<?php endif; ?>
			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $this->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
				<input type="hidden" name="action" value="<?php echo esc_attr( $this->option_key ); ?>">
				<?php $this->options_page_metabox(); ?>
				<?php submit_button( esc_attr( $this->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Outputs the settings notices if a) not a sub-page of 'options-general.php'
	 * (because settings_errors() already called in wp-admin/options-head.php),
	 * and b) the 'disable_settings_errors' prop is not set or truthy.
	 *
	 * @since  2.2.5
	 * @return void
	 */
	public function maybe_output_settings_notices() {
		global $parent_file;

		// The settings sub-pages will already have settings_errors() called in wp-admin/options-head.php
		if ( 'options-general.php' !== $parent_file ) {
			settings_errors( "{$this->option_key}-notices" );
		}
	}

	/**
	 * Gets navigation tabs array for CMB2 options pages which share the
	 * same tab_group property.
	 *
	 * @since 2.4.0
	 * @return array Array of tab information ($option_key => $tab_title)
	 */
	public function get_tab_group_tabs() {
		$tab_group = $this->cmb->prop( 'tab_group' );
		$tabs      = array();

		if ( $tab_group ) {
			$boxes = CMB2_Boxes::get_by( 'tab_group', $tab_group );

			foreach ( $boxes as $cmb_id => $cmb ) {
				$option_key = $cmb->options_page_keys();

				// Must have an option key, must be an options page box.
				if ( ! isset( $option_key[0] ) || 'options-page' !== $cmb->mb_object_type() ) {
					continue;
				}

				$tabs[ $option_key[0] ] = $cmb->prop( 'tab_title', $cmb->prop( 'title' ) );
			}
		}

		return $tabs;
	}

	/**
	 * Display metaboxes for an options-page object.
	 *
	 * @since  2.2.5
	 */
	public function options_page_metabox() {
		$this->show_form_for_type( 'options-page' );
	}

	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.2.5
	 * @return void
	 */
	public function save_options() {
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}

		if (
			$this->can_save( 'options-page' )
			// check params
			&& isset( $_POST['submit-cmb'], $_POST['action'] )
			&& $this->option_key === $_POST['action']
		) {

			$updated = $this->cmb
				->save_fields( $this->option_key, $this->cmb->object_type(), $_POST )
				->was_updated(); // Will be false if no values were changed/updated.

			$url = add_query_arg( 'settings-updated', $updated ? 'true' : 'false', $url );
		}

		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		exit;
	}

	/**
	 * Replaces get_option with get_site_option
	 * @since 2.2.5
	 * @return mixed Value set for the network option.
	 */
	public function network_get_override( $test, $default = false ) {
		return get_site_option( $this->option_key, $default );
	}

	/**
	 * Replaces update_option with update_site_option
	 * @since 2.2.5
	 * @return bool Success/Failure
	 */
	public function network_update_override( $test, $option_value ) {
		return update_site_option( $this->option_key, $option_value );
	}

	/**
	 * Determines if given page slug matches the 'page' GET query variable.
	 *
	 * @since  2.4.0
	 *
	 * @param  string $page Page slug
	 *
	 * @return boolean
	 */
	public static function is_page( $page ) {
		return isset( $_GET['page'] ) && $page === $_GET['page'];
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'object_type':
			case 'option_key':
			case 'cmb':
				return $this->{$field};
			default:
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s property: %2$s', 'cmb2' ), __CLASS__, $field ) );
		}
	}
}
