<?php
/**
 * Custom Emails for WooCommerce - Functions
 *
 * @version 3.7.3
 * @since   2.1.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpfactory_wc_ce_send_email' ) ) {
	/**
	 * wpfactory_wc_ce_send_email.
	 *
	 * @version 3.7.3
	 * @since   2.1.0
	 */
	function wpfactory_wc_ce_send_email( $email, $object_id, $note = '' ) {
		if ( is_numeric( $email ) ) {
			// Converting num to class, e.g., `1` to `WPFactory_WC_Custom_Email`
			$email = apply_filters(
				'wpfactory_wc_custom_emails_class',
				'WPFactory_WC_Custom_Email',
				$email
			);
		}
		wpfactory_wc_custom_emails()->core->send_email( $email, $object_id, $note );
	}
}
