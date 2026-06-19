<?php
/**
 * Custom Emails for WooCommerce - Custom Email Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/wpfactory-wc-custom-email.php.
 *
 * @version 3.7.3
 * @since   2.4.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( $email->wpfactory_wc_ce_do_add_header_and_footer() ) {
	/**
	 * Header.
	 *
	 * @hooked WC_Emails::email_header() Output the email header
	 */
	do_action( 'woocommerce_email_header', $email_heading, $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}

/**
 * Content.
 */
echo wp_kses_post( $content );

if ( $email->wpfactory_wc_ce_do_add_header_and_footer() ) {
	/**
	 * Footer.
	 *
	 * @hooked WC_Emails::email_footer() Output the email footer
	 */
	do_action( 'woocommerce_email_footer', $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}
