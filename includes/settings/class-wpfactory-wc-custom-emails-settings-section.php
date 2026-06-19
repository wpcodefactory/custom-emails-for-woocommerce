<?php
/**
 * Custom Emails for WooCommerce - Section Settings
 *
 * @version 3.7.3
 * @since   1.0.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_Custom_Emails_Settings_Section' ) ) :

class WPFactory_WC_Custom_Emails_Settings_Section {

	/**
	 * id.
	 *
	 * @version 2.5.0
	 * @since   1.0.0
	 */
	public $id;

	/**
	 * desc.
	 *
	 * @version 2.5.0
	 * @since   1.0.0
	 */
	public $desc;

	/**
	 * Constructor.
	 *
	 * @version 3.7.3
	 * @since   1.0.0
	 */
	function __construct() {
		add_filter(
			'woocommerce_get_sections_' . 'wpfactory_wc_custom_emails',
			array( $this, 'settings_section' )
		);
		add_filter(
			'woocommerce_get_settings_' . 'wpfactory_wc_custom_emails' . '_' . $this->id,
			array( $this, 'get_settings' ),
			PHP_INT_MAX
		);
	}

	/**
	 * settings_section.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

}

endif;
