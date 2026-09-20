<?php
/**
 * Admin notice announcing the upcoming alignment of CMB2's REST API read
 * permissions for settings data with WordPress core conventions.
 *
 * Shown only when the site actually registers a REST-readable options/settings
 * box and the alignment has not already been enabled, so sites that need no
 * action (or that already opted in) are not nagged.
 *
 * @since 2.13.0
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
	 * @since 2.13.0
	 */
	const GUIDE_URL = 'https://cmb2.io/docs/REST-API-Read-Permissions';

	/**
	 * Option key used to persist notice dismissal.
	 *
	 * @var   string
	 * @since 2.13.0
	 */
	const DISMISSED_OPTION = 'cmb2_rest_read_permissions_notice_dismissed';

	/**
	 * Nonce/action name used for the dismissal AJAX request.
	 *
	 * @var   string
	 * @since 2.13.0
	 */
	const AJAX_ACTION = 'cmb2_dismiss_rest_read_permissions_notice';

	/**
	 * Affected (REST-readable options-page) boxes fed in via the per-box hookup,
	 * keyed by cmb_id.
	 *
	 * @var   CMB2[]
	 * @since 2.13.0
	 */
	protected static $tracked_boxes = array();

	/**
	 * Per-box hookup, matching CMB2's `cmb2_init_hookup_{$cmb_id}` convention
	 * (see CMB2_Hookup::maybe_init_and_hookup / CMB2_REST::maybe_init_and_hookup).
	 *
	 * Tracks each affected box so the notice can consult only the boxes actually
	 * registered on this request, and registers the admin render + dismissal
	 * handlers once, when the first affected box is seen.
	 *
	 * A box which declares a `rest_read_capability` has stated its intent and will
	 * not be affected by a future default change, so it is not an affected box at
	 * all — filtering it out here (rather than in should_show()) keeps "affected"
	 * defined in one place and leaves should_show() to the single remaining
	 * question: are any affected boxes still ungated?
	 *
	 * @since 2.13.0
	 *
	 * @param CMB2 $cmb The CMB2 object being hooked up.
	 *
	 * @return void
	 */
	public static function maybe_init_and_hookup( CMB2 $cmb ) {
		if ( ! is_admin() || ! CMB2_REST::is_options_page_box( $cmb ) ) {
			return;
		}

		if ( CMB2_REST::has_explicit_rest_read_capability( $cmb ) ) {
			return;
		}

		$first_box = empty( self::$tracked_boxes );

		self::$tracked_boxes[ $cmb->cmb_id ] = $cmb;

		if ( $first_box ) {
			add_action( 'admin_notices', array( __CLASS__, 'render' ) );
			add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_dismiss' ) );
		}
	}

	/**
	 * Resets tracked-box state.
	 *
	 * @internal Intended for test isolation.
	 *
	 * @since 2.13.0
	 *
	 * @return void
	 */
	public static function reset() {
		self::$tracked_boxes = array();
	}

	/**
	 * Whether the notice has been dismissed.
	 *
	 * @since 2.13.0
	 *
	 * @return bool
	 */
	public static function is_dismissed() {
		return (bool) get_option( self::DISMISSED_OPTION, false );
	}

	/**
	 * Persists dismissal of the notice.
	 *
	 * @since 2.13.0
	 *
	 * @return void
	 */
	public static function dismiss() {
		update_option( self::DISMISSED_OPTION, 1, false );
	}

	/**
	 * Determines whether the notice is eligible to be shown.
	 *
	 * Eligible only when at least one tracked affected box (fed in via
	 * maybe_init_and_hookup) still has ungated reads (the alignment filter is
	 * returning its default false), and the notice has not already been dismissed.
	 *
	 * @since 2.13.0
	 *
	 * @return bool
	 */
	public static function should_show() {
		if ( self::is_dismissed() ) {
			return false;
		}

		foreach ( self::$tracked_boxes as $cmb ) {
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
	 * @since 2.13.0
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) || ! self::should_show() ) {
			return;
		}

		$guide_url = self::GUIDE_URL;
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
				// keepalive lets the request finish if the user navigates right away;
				// a dismissal that fails to persist would otherwise be silent.
				window.fetch( window.ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					keepalive: true,
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString()
				} ).then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( response.status );
					}
					return response.text();
				} ).catch( function ( err ) {
					if ( window.console ) {
						console.warn( 'CMB2: notice dismissal may not have persisted.', err );
					}
				} );
			} );
		} )();
		</script>
		<?php
	}

	/**
	 * AJAX handler that persists dismissal of the notice.
	 *
	 * @since 2.13.0
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
