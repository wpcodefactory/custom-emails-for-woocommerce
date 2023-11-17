<?php
/**
 * Custom Emails for WooCommerce - Custom Email Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/alg-wc-custom-email.php.
 *
 * @version 2.4.0
 * @since   2.4.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( $email->alg_wc_ce_do_add_header_and_footer() ) {
	/**
	 * Header.
	 *
	 * @hooked WC_Emails::email_header() Output the email header
	 */
	do_action( 'woocommerce_email_header', $email_heading, $email );
}

/**
 * Content.
 */
echo wp_kses_post( $content );

if ( $email->alg_wc_ce_do_add_header_and_footer() ) {
	/**
	 * Footer.
	 *
	 * @hooked WC_Emails::email_footer() Output the email footer
	 */
	do_action( 'woocommerce_email_footer', $email );
}
