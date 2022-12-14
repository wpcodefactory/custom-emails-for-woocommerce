<?php
/*
Plugin Name: Custom Emails for WooCommerce
Plugin URI: https://wpfactory.com/item/custom-emails-for-woocommerce/
Description: Add custom emails to WooCommerce.
Version: 1.7.0
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: custom-emails-for-woocommerce
Domain Path: /langs
WC tested up to: 7.2
*/

defined( 'ABSPATH' ) || exit;

if ( 'custom-emails-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	$plugin = 'custom-emails-for-woocommerce-pro/custom-emails-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

defined( 'ALG_WC_CUSTOM_EMAILS_VERSION' ) || define( 'ALG_WC_CUSTOM_EMAILS_VERSION', '1.7.0' );

defined( 'ALG_WC_CUSTOM_EMAILS_FILE' ) || define( 'ALG_WC_CUSTOM_EMAILS_FILE', __FILE__ );

require_once( 'includes/class-alg-wc-custom-emails.php' );

if ( ! function_exists( 'alg_wc_custom_emails' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Custom_Emails to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_custom_emails() {
		return Alg_WC_Custom_Emails::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_custom_emails' );
