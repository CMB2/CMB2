<?php
/**
 * Admin notice announcing the upcoming alignment of CMB2's REST API read
 * permissions for settings data with WordPress core conventions.
 *
 * Shown only when the site actually registers a REST-readable options/settings
 * box and the alignment has not already been enabled, so sites that need no
 * action (or that already opted in) are not nagged.
 *
 * @since 2.12.0
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Rest_Read_Permissions_Notice {

	/**
	 * Default URL for the explainer guide.
	 *
	 * @var   string
	 * @since 2.12.0
	 */
	const GUIDE_URL = 'https://github.com/CMB2/CMB2/wiki/REST-API-Read-Permissions';

	/**
	 * Option key used to persist notice dismissal.
	 *
	 * @var   string
	 * @since 2.12.0
	 */
	const DISMISSED_OPTION = 'cmb2_rest_read_permissions_notice_dismissed';

	/**
	 * Nonce/action name used for the dismissal AJAX request.
	 *
	 * @var   string
	 * @since 2.12.0
	 */
	const AJAX_ACTION = 'cmb2_dismiss_rest_read_permissions_notice';

	/**
	 * Registers the notice and its dismissal handler on the admin side.
	 *
	 * @since 2.12.0
	 *
	 * @return void
	 */
	public static function hookup() {
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_dismiss' ) );
	}

	/**
	 * Returns the (filterable) explainer guide URL.
	 *
	 * @since 2.12.0
	 *
	 * @return string
	 */
	public static function guide_url() {
		/**
		 * Filters the URL of the REST read-permissions explainer guide.
		 *
		 * @since 2.12.0
		 *
		 * @param string $url The guide URL.
		 */
		return apply_filters( 'cmb2_rest_read_permissions_guide_url', self::GUIDE_URL );
	}

	/**
	 * Whether the notice has been dismissed.
	 *
	 * @since 2.12.0
	 *
	 * @return bool
	 */
	public static function is_dismissed() {
		return (bool) get_option( self::DISMISSED_OPTION, false );
	}

	/**
	 * Persists dismissal of the notice.
	 *
	 * @since 2.12.0
	 *
	 * @return void
	 */
	public static function dismiss() {
		update_option( self::DISMISSED_OPTION, 1, false );
	}

	/**
	 * Collects the registered REST-readable options-page boxes (the affected boxes).
	 *
	 * @since 2.12.0
	 *
	 * @return CMB2[] Array of affected CMB2 boxes.
	 */
	public static function get_affected_boxes() {
		$affected = array();

		if ( ! class_exists( 'CMB2_Boxes' ) || ! class_exists( 'CMB2_REST' ) ) {
			return $affected;
		}

		foreach ( CMB2_Boxes::get_all() as $cmb ) {
			if ( CMB2_REST::is_options_page_box( $cmb ) ) {
				$affected[] = $cmb;
			}
		}

		return $affected;
	}

	/**
	 * Determines whether the notice is eligible to be shown.
	 *
	 * Eligible only when the site has at least one affected box whose reads are
	 * still ungated (the alignment filter is returning its default false), and the
	 * notice has not already been dismissed.
	 *
	 * @since 2.12.0
	 *
	 * @return bool
	 */
	public static function should_show() {
		if ( self::is_dismissed() ) {
			return false;
		}

		foreach ( self::get_affected_boxes() as $cmb ) {
			// If any affected box is still ungated, the owner should be informed.
			if ( ! apply_filters( 'cmb2_rest_enforce_options_page_read_permissions', false, $cmb ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Renders the admin notice, when eligible and the user can manage options.
	 *
	 * @since 2.12.0
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) || ! self::should_show() ) {
			return;
		}

		$guide_url = self::guide_url();
		$nonce     = wp_create_nonce( self::AJAX_ACTION );
		?>
		<div class="notice notice-info is-dismissible cmb2-rest-read-permissions-notice" data-cmb2-notice-nonce="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<strong><?php esc_html_e( 'CMB2: Upcoming change to REST API read permissions for settings data', 'cmb2' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'This site uses CMB2 (a custom-fields library that is usually bundled inside a theme or plugin) to register one or more settings pages that are readable through the WordPress REST API. To align with WordPress core conventions, a future CMB2 release will limit REST API reads of settings-page data to users who can manage those settings (administrators, typically). Most sites need no action, and nothing changes yet. If your site has a custom integration that reads these settings through the REST API, please share the guide below with your developer.', 'cmb2' ); ?>
			</p>
			<p>
				<a class="button button-secondary" href="<?php echo esc_url( $guide_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the guide', 'cmb2' ); ?></a>
			</p>
		</div>
		<script>
		( function () {
			var notice = document.querySelector( '.cmb2-rest-read-permissions-notice' );
			if ( ! notice ) {
				return;
			}
			notice.addEventListener( 'click', function ( event ) {
				if ( ! event.target.classList.contains( 'notice-dismiss' ) ) {
					return;
				}
				var body = new URLSearchParams();
				body.append( 'action', '<?php echo esc_js( self::AJAX_ACTION ); ?>' );
				body.append( 'nonce', notice.getAttribute( 'data-cmb2-notice-nonce' ) );
				window.fetch( window.ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString()
				} );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * AJAX handler that persists dismissal of the notice.
	 *
	 * @since 2.12.0
	 *
	 * @return void
	 */
	public static function ajax_dismiss() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( '', 403 );
		}

		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		self::dismiss();

		wp_send_json_success();
	}

}
