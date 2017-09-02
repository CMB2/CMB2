<?php

/**
 * Class CMB2_Page_Save
 * Saves options data via the settings API.
 *
 * Uses: None
 * Applies CMB2 Filters: None
 *
 * Public methods:
 *     save_options()             Loops through boxes on this page and saves values
 *
 * Public methods accessed via callback: None
 *
 * Protected methods:
 *     can_save()                 Checks if fields can be saved legally
 *     field_values_to_default()  If action was 'reset', determines new values of fields.
 *
 * Private methods: None
 * Magic methods: None
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Page_Save {
	
	/**
	 * @since 2.XXX
	 * @var \CMB2_Page
	 */
	protected $page;
	
	/**
	 * CMB2_Page_Save constructor.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Page $page
	 */
	public function __construct( CMB2_Page $page ) {
		
		$this->page = $page;
	}
	
	/**
	 * Save data from options page, then redirects back.
	 *
	 * @since  2.XXX Checks multiple boxes
	 * @return void
	 */
	public function save_options() {
		
		$url = wp_get_referer() ? wp_get_referer() : admin_url();
		
		$action  = isset( $_POST['submit-cmb'] ) ? 'save' : ( isset( $_POST['reset-cmb'] ) ? 'reset' : FALSE );
		$option  = isset( $_POST['action'] ) ? $_POST['action'] : FALSE;
		$updated = FALSE;
		
		if ( $action && $option && $this->page->option_key === $option ) {
			
			foreach ( $this->page->hookups as $hookup ) {
				
				if ( $this->can_save( $hookup ) ) {
					
					if ( $action == 'reset' ) {
						$this->field_values_to_default( $hookup );
						$updated = 'reset';
					}
					
					$up = $hookup->cmb
						->save_fields( $this->page->option_key, $hookup->cmb->object_type(), $_POST )
						->was_updated();
					
					$updated = $updated ? $updated : $up;
				}
			}
		}
		
		$url = add_query_arg( 'updated', var_export( $updated, TRUE ), $url );
		wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
		
		exit;
	}
	
	/**
	 * Adaptation of CMB_Hookup 'can_save' -- allows arbitrary box to be checked
	 *
	 * @param \CMB2_Options_Hookup $hookup
	 * @return mixed
	 */
	protected function can_save( CMB2_Options_Hookup $hookup ) {
		
		$can_save = (
			$hookup->cmb->prop( 'save_fields' )
			&& isset( $_POST[ $hookup->cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $hookup->cmb->nonce() ], $hookup->cmb->nonce() )
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			&& ( 'options-page' && in_array( 'options-page', $hookup->cmb->box_types() ) )
			&& ! ( is_multisite() && ms_is_switched() )
		);
		
		return apply_filters( 'cmb2_can_save', $can_save, $hookup->cmb );
	}
	
	/**
	 * Changes _POST values for box fields to default values.
	 *
	 * @since 2.XXX
	 * @param \CMB2_Options_Hookup $hookup
	 */
	protected function field_values_to_default( $hookup ) {
		
		$fields = $hookup->cmb->prop( 'fields' );
		
		foreach ( $fields as $fid => $field ) {
			$f             = $hookup->cmb->get_field( $field );
			$_POST[ $fid ] = $hookup->cmb->prop( 'reset_action' ) == 'remove' ? '' : $f->get_default();
		}
	}
}