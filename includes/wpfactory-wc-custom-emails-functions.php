<?php
/**
 * Custom Emails for WooCommerce - Functions
 *
 * @version 3.7.3
 * @since   2.1.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Custom_Emails\Functions
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wpfactory_wc_ce_send_email' ) ) {
	/**
	 * Send email.
	 *
	 * @version 3.7.3
	 * @since   2.1.0
	 *
	 * @param int|string $email     Email ID or class name.
	 * @param int|object $object_id Object ID or object.
	 * @param string     $note      Optional note.
	 *
	 * @return void
	 */
	function wpfactory_wc_ce_send_email( $email, $object_id, $note = '' ) {
		if ( is_numeric( $email ) ) {
			// Converting num to class, e.g., `1` to `WPFactory_WC_Custom_Email`.
			$email = apply_filters(
				'wpfactory_wc_custom_emails_class',
				'WPFactory_WC_Custom_Email',
				$email
			);
		}
		wpfactory_wc_custom_emails()->core->send_email( $email, $object_id, $note );
	}
}
