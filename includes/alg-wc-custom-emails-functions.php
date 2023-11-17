<?php
/**
 * Custom Emails for WooCommerce - Functions
 *
 * @version 2.1.0
 * @since   2.1.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_ce_send_email' ) ) {
	/**
	 * alg_wc_ce_send_email.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function alg_wc_ce_send_email( $email, $object_id, $note = '' ) {
		if ( is_numeric( $email ) ) {
			// Converting num to class, e.g., `1` to `Alg_WC_Custom_Email`
			$email = apply_filters( 'alg_wc_custom_emails_class', 'Alg_WC_Custom_Email', $email );
		}
		alg_wc_custom_emails()->core->send_email( $email, $object_id, $note );
	}
}
