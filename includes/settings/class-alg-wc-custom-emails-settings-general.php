<?php
/**
 * Custom Emails for WooCommerce - General Section Settings
 *
 * @version 3.5.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

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
	 * @version 3.5.0
	 * @since   1.0.0
	 *
	 * @todo    (desc) `alg_wc_custom_emails_enabled_trigger_groups`
	 * @todo    (dev) `alg_wc_custom_emails_titles`: move to the email settings?
	 * @todo    (dev) `alg_wc_custom_emails_titles`: icon for settings (e.g., `<span class="dashicons dashicons-admin-settings"></span>`)?
	 * @todo    (desc) `alg_wc_custom_emails_total`: better desc?
	 */
	function get_settings() {

		$general_settings = apply_filters( 'alg_wc_custom_emails_admin_settings_general', array(
			array(
				'title'             => __( 'General Options', 'custom-emails-for-woocommerce' ),
				'desc'              => sprintf(
					/* Translators: %s: Emails link. */
					__( 'Settings for each custom email are located in %s.', 'custom-emails-for-woocommerce' ),
					sprintf(
						'<a href="%s">%s</a>',
						admin_url( 'admin.php?page=wc-settings&tab=email' ),
						__( 'WooCommerce > Settings > Emails', 'custom-emails-for-woocommerce' )
					)
				),
				'type'              => 'title',
				'id'                => 'alg_wc_custom_emails_general_options',
			),
			array(
				'title'             => __( 'Number of custom emails', 'custom-emails-for-woocommerce' ),
				'desc'              => apply_filters(
					'alg_wc_custom_emails_admin_settings',
					'You will need <a href="https://wpfactory.com/item/custom-emails-for-woocommerce/">Additional Custom Emails & Recipients for WooCommerce Pro</a> plugin to add more than one custom email.',
					'button-total'
				),
				'id'                => 'alg_wc_custom_emails_total',
				'default'           => 1,
				'type'              => 'number',
				'custom_attributes' => apply_filters(
					'alg_wc_custom_emails_admin_settings',
					array( 'readonly' => 'readonly' ),
					'array-total'
				),
			),
			array(
				'title'             => __( 'Admin title', 'custom-emails-for-woocommerce' ),
				'desc'              => sprintf(
					'[<a href="%s">%s</a>]',
					admin_url( 'admin.php?page=wc-settings&tab=email&section=alg_wc_custom_email' ),
					__( 'settings', 'custom-emails-for-woocommerce' )
				),
				'id'                => 'alg_wc_custom_emails_titles[1]',
				'default'           => __( 'Custom email', 'custom-emails-for-woocommerce' ),
				'type'              => 'text',
			),
			array(
				'type'              => 'sectionend',
				'id'                => 'alg_wc_custom_emails_general_options',
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
				'title'    => __( 'Custom triggers', 'custom-emails-for-woocommerce' ),
				'desc'     => __( 'One trigger per line.', 'custom-emails-for-woocommerce' ) . '<br>' .
					sprintf(
						/* Translators: %1$s: Format, %2$s: Format, %3$s: Example, %4$s: Example. */
						__( 'Format: %1$s or %2$s, e.g.: %3$s or %4$s.', 'custom-emails-for-woocommerce' ),
						'<code>action</code>',
						'<code>action|title</code>',
						'<code>woocommerce_checkout_order_processed</code>',
						'<code>woocommerce_checkout_order_processed|' . __( 'Checkout order processed', 'custom-emails-for-woocommerce' ) . '</code>'
					),
				'id'       => 'alg_wc_custom_emails_custom_triggers',
				'default'  => '',
				'type'     => 'textarea',
				'css'      => 'height:150px;font-family:monospace;',
			),
			array(
				'title'    => __( 'Base dir', 'custom-emails-for-woocommerce' ),
				'desc_tip' => __( 'Affects "Email Data > Email attachments" options.', 'custom-emails-for-woocommerce' ),
				'id'       => 'alg_wc_custom_emails_base_dir',
				'default'  => 'abspath',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'abspath'       => __( 'WP root directory', 'custom-emails-for-woocommerce' ),
					'wp_upload_dir' => __( 'WP upload directory', 'custom-emails-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Scheduler', 'custom-emails-for-woocommerce' ),
				'desc_tip' => __( 'Affects "Delay" options (scheduled emails).', 'custom-emails-for-woocommerce' ),
				'id'       => 'alg_wc_custom_emails_scheduler',
				'default'  => 'wp_cron',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'wp_cron' => __( 'WP Cron', 'custom-emails-for-woocommerce' ),
					'as'      => __( 'Action Scheduler', 'custom-emails-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Replace line breaks', 'custom-emails-for-woocommerce' ),
				'desc'     => __( 'Replace line breaks in HTML email content', 'custom-emails-for-woocommerce' ),
				'desc_tip' =>
					sprintf(
						/* Translators: %s: Tag name. */
						__( 'Replaces double line breaks with HTML paragraph tags, and all remaining line breaks with %s tag.', 'custom-emails-for-woocommerce' ),
						'<code>' . esc_html( '<br />' ) . '</code>'
					) . ' ' .
					sprintf(
						/* Translators: %s: Function link. */
						__( 'Uses WordPress %s function.', 'custom-emails-for-woocommerce' ),
						'<a href="https://developer.wordpress.org/reference/functions/wpautop/" target="_blank"><code>wpautop()</code></a>'
					),
				'id'       => 'alg_wc_custom_emails_wpautop',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Debug', 'custom-emails-for-woocommerce' ),
				'desc'     => __( 'Enable', 'custom-emails-for-woocommerce' ),
				'desc_tip' => sprintf(
					/* Translators: %s: Logs link. */
					__( 'Will add a log to %s.', 'custom-emails-for-woocommerce' ),
					'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' .
						__( 'WooCommerce > Status > Logs', 'custom-emails-for-woocommerce' ) .
					'</a>'
				),
				'id'       => 'alg_wc_custom_emails_debug_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_custom_emails_advanced_options',
			),
		);

		return array_merge( $general_settings, $advanced_settings );
	}

}

endif;

return new Alg_WC_Custom_Emails_Settings_General();
