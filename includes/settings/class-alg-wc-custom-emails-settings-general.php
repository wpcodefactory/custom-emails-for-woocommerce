<?php
/**
 * Custom Emails for WooCommerce - General Section Settings
 *
 * @version 1.5.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Emails_Settings_General' ) ) :

class Alg_WC_Custom_Emails_Settings_General extends Alg_WC_Custom_Emails_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'custom-emails-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.5.1
	 * @since   1.0.0
	 *
	 * @todo    [later] (desc) `alg_wc_custom_emails_enabled_trigger_groups`
	 * @todo    [maybe] (dev) `alg_wc_custom_emails_titles`: move to the email settings?
	 * @todo    [maybe] (dev) `alg_wc_custom_emails_titles`: icon for settings (e.g. `<span class="dashicons dashicons-admin-settings"></span>`)?
	 * @todo    [maybe] (desc) `alg_wc_custom_emails_total`: better desc?
	 */
	function get_settings() {

		$plugin_settings = array(
			array(
				'title'    => __( 'Custom Emails Options', 'custom-emails-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_custom_emails_plugin_options',
			),
			array(
				'title'    => __( 'Custom Emails', 'custom-emails-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'custom-emails-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_custom_emails_plugin_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_custom_emails_plugin_options',
			),
		);

		$general_settings = apply_filters( 'alg_wc_custom_emails_admin_settings_general', array(
			array(
				'title'    => __( 'General Options', 'custom-emails-for-woocommerce' ),
				'desc'     => sprintf( __( 'Settings for each custom email are located in %s.', 'custom-emails-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email' ) . '">' . __( 'WooCommerce > Settings > Emails', 'custom-emails-for-woocommerce' ) . '</a>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_custom_emails_general_options',
			),
			array(
				'title'    => __( 'Number of custom emails', 'custom-emails-for-woocommerce' ),
				'desc'     => apply_filters( 'alg_wc_custom_emails_admin_settings',
					'You will need <a href="https://wpfactory.com/item/custom-emails-for-woocommerce/">Custom Emails for WooCommerce Pro</a> plugin to add more than one custom email.', 'button-total' ),
				'id'       => 'alg_wc_custom_emails_total',
				'default'  => 1,
				'type'     => 'number',
				'custom_attributes' => apply_filters( 'alg_wc_custom_emails_admin_settings', array( 'readonly' => 'readonly' ), 'array-total' ),
			),
			array(
				'title'    => __( 'Admin title', 'custom-emails-for-woocommerce' ),
				'desc'     => '[<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email&section=alg_wc_custom_email' ) . '">' .
					__( 'settings', 'custom-emails-for-woocommerce' ) . '</a>]',
				'id'       => 'alg_wc_custom_emails_titles[1]',
				'default'  => __( 'Custom email', 'custom-emails-for-woocommerce' ),
				'type'     => 'text',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_custom_emails_general_options',
			),
		) );

		$advanced_settings = array(
			array(
				'title'    => __( 'Advanced Options', 'custom-emails-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_custom_emails_advanced_options',
			),
			array(
				'title'    => __( 'Enabled triggers groups', 'custom-emails-for-woocommerce' ),
				'desc_tip' => __( 'This will set which triggers you will see in each custom email settings.', 'custom-emails-for-woocommerce' ) . ' ' .
					__( 'Disabling some triggers groups can help you to make triggers list more manageable.', 'custom-emails-for-woocommerce' ),
				'id'       => 'alg_wc_custom_emails_enabled_trigger_groups',
				'default'  => array( 'order_status', 'order_status_change', 'new_order', 'extra' ),
				'type'     => 'multiselect',
				'class'    => 'chosen_select',
				'options'  => alg_wc_custom_emails()->core->get_trigger_groups(),
			),
			array(
				'title'    => __( 'Debug', 'custom-emails-for-woocommerce' ),
				'desc'     => __( 'Enable', 'custom-emails-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Will add a log to %s.', 'custom-emails-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' . __( 'WooCommerce > Status > Logs', 'custom-emails-for-woocommerce' ) . '</a>' ),
				'id'       => 'alg_wc_custom_emails_debug_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_custom_emails_advanced_options',
			),
		);

		return array_merge( $plugin_settings, $general_settings, $advanced_settings );
	}

}

endif;

return new Alg_WC_Custom_Emails_Settings_General();
