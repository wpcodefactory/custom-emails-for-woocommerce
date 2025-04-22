<?php
/**
 * Custom Emails for WooCommerce - Main Class
 *
 * @version 3.5.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails' ) ) :

final class Alg_WC_Custom_Emails {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = ALG_WC_CUSTOM_EMAILS_VERSION;

	/**
	 * core.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $core;

	/**
	 * admin_core.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $admin_core;

	/**
	 * @var   Alg_WC_Custom_Emails The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Custom_Emails Instance
	 *
	 * Ensures only one instance of Alg_WC_Custom_Emails is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Alg_WC_Custom_Emails - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Custom_Emails Constructor.
	 *
	 * @version 3.4.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( ALG_WC_CUSTOM_EMAILS_FILE ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'custom-emails-for-woocommerce-pro.php' === basename( ALG_WC_CUSTOM_EMAILS_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-custom-emails-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function localize() {
		load_plugin_textdomain(
			'custom-emails-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_CUSTOM_EMAILS_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 2.2.7
	 * @since   2.2.3
	 *
	 * @see     https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'ALG_WC_CUSTOM_EMAILS_FILE_FREE' ) ?
				array( ALG_WC_CUSTOM_EMAILS_FILE, ALG_WC_CUSTOM_EMAILS_FILE_FREE ) :
				array( ALG_WC_CUSTOM_EMAILS_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $file, true );
			}
		}
	}

	/**
	 * includes.
	 *
	 * @version 3.4.0
	 * @since   1.0.0
	 */
	function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'alg-wc-custom-emails-functions.php';
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-custom-emails-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 3.5.1
	 * @since   1.0.0
	 */
	function admin() {

		// Admin core
		$this->admin_core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-custom-emails-admin.php';

		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( ALG_WC_CUSTOM_EMAILS_FILE ), array( $this, 'action_links' ) );

		// "Recommendations" page
		$this->add_cross_selling_library();

		// WC Settings tab as WPFactory submenu item
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );

		// Version update
		if ( get_option( 'alg_wc_custom_emails_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}

	}

	/**
	 * action_links.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_emails' ) . '">' .
			__( 'Settings', 'custom-emails-for-woocommerce' ) .
		'</a>';

		if ( 'custom-emails-for-woocommerce.php' === basename( ALG_WC_CUSTOM_EMAILS_FILE ) ) {
			$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/custom-emails-for-woocommerce/">' .
				__( 'Go Pro', 'custom-emails-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 3.4.0
	 * @since   3.4.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_WC_CUSTOM_EMAILS_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 3.4.0
	 * @since   3.4.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'alg_wc_custom_emails',
			'menu_title'         => __( 'Custom Emails', 'custom-emails-for-woocommerce' ),
			'page_title'         => __( 'Custom Emails', 'custom-emails-for-woocommerce' ),
		) );

	}

	/**
	 * add_woocommerce_settings_tab.
	 *
	 * @version 3.4.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-custom-emails-settings.php';
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function version_updated() {
		update_option( 'alg_wc_custom_emails_version', $this->version );
	}

	/**
	 * plugin_url.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_CUSTOM_EMAILS_FILE ) );
	}

	/**
	 * plugin_path.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_CUSTOM_EMAILS_FILE ) );
	}

}

endif;
