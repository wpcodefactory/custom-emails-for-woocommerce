<?php
/*
Plugin Name: Additional Custom Emails & Recipients for WooCommerce
Plugin URI: https://wpfactory.com/item/custom-emails-for-woocommerce/
Description: Add custom emails to WooCommerce.
Version: 3.6.0
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 4.4
Text Domain: custom-emails-for-woocommerce
Domain Path: /langs
WC tested up to: 9.9
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'custom-emails-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 2.2.7
	 * @since   1.4.0
	 */
	$plugin = 'custom-emails-for-woocommerce-pro/custom-emails-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		(
			is_multisite() &&
			array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
		)
	) {
		defined( 'ALG_WC_CUSTOM_EMAILS_FILE_FREE' ) || define( 'ALG_WC_CUSTOM_EMAILS_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_CUSTOM_EMAILS_VERSION' ) || define( 'ALG_WC_CUSTOM_EMAILS_VERSION', '3.6.0' );

defined( 'ALG_WC_CUSTOM_EMAILS_FILE' ) || define( 'ALG_WC_CUSTOM_EMAILS_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-custom-emails.php';

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
