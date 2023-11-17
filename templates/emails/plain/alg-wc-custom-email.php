<?php
/**
 * Custom Emails for WooCommerce - Custom Email Template - Plain Text
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/alg-wc-custom-email.php.
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
	 */
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
	echo esc_html( wp_strip_all_tags( $email_heading ) );
	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}

/**
 * Content.
 */
echo wp_kses_post( $content );

if ( $email->alg_wc_ce_do_add_header_and_footer() ) {
	/**
	 * Footer.
	 */
	echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
}
