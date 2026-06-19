<?php
/*
Plugin Name: Additional Custom Emails & Recipients for WooCommerce
Plugin URI: https://wpfactory.com/item/custom-emails-for-woocommerce/
Description: Add custom emails to WooCommerce.
Version: 3.7.3
Author: WPFactory
Author URI: https://wpfactory.com
Requires at least: 4.4
Text Domain: custom-emails-for-woocommerce
Domain Path: /langs
WC tested up to: 10.8
Requires Plugins: woocommerce
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

defined( 'ABSPATH' ) || exit;

if ( 'custom-emails-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 3.7.3
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
		defined( 'WPFACTORY_WC_CUSTOM_EMAILS_FILE_FREE' ) || define( 'WPFACTORY_WC_CUSTOM_EMAILS_FILE_FREE', __FILE__ );
		return;
	}
}

/**
 * WPFACTORY_WC_CUSTOM_EMAILS_VERSION.
 *
 * @version 3.7.3
 * @since   1.0.0
 */
defined( 'WPFACTORY_WC_CUSTOM_EMAILS_VERSION' ) || define( 'WPFACTORY_WC_CUSTOM_EMAILS_VERSION', '3.7.3' );

/**
 * WPFACTORY_WC_CUSTOM_EMAILS_FILE.
 *
 * @version 3.7.3
 * @since   1.0.0
 */
defined( 'WPFACTORY_WC_CUSTOM_EMAILS_FILE' ) || define( 'WPFACTORY_WC_CUSTOM_EMAILS_FILE', __FILE__ );

/**
 * Require main class.
 *
 * @version 3.7.3
 * @since   1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpfactory-wc-custom-emails.php';

if ( ! function_exists( 'wpfactory_wc_custom_emails' ) ) {
	/**
	 * Returns the main instance of WPFactory_WC_Custom_Emails to prevent the need to use globals.
	 *
	 * @version 3.7.3
	 * @since   1.0.0
	 */
	function wpfactory_wc_custom_emails() {
		return WPFactory_WC_Custom_Emails::instance();
	}
}

/**
 * Init.
 *
 * @version 3.7.3
 * @since   1.0.0
 */
add_action( 'plugins_loaded', 'wpfactory_wc_custom_emails' );
