<?php
/**
 * Custom Emails for WooCommerce - Email Settings - Fields
 *
 * @version 3.0.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

// Enable/Disable
$fields = array_merge( $fields, array(
	'enabled' => array(
		'title'       => __( 'Enable/Disable', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable this email notification', 'woocommerce' ),
		'default'     => 'yes',
	),
) );

// Triggers
$fields = array_merge( $fields, array(
	'trigger_options' => array(
		'title'             => __( 'Triggers', 'custom-emails-for-woocommerce' ),
		'type'              => 'title',
	),
	'trigger' => array(
		'title'             => __( 'Triggers', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => array(),
		'options'           => $this->get_triggers(),
		'desc_tip'          => __( 'Please note, that all <em>new orders</em> in WooCommerce by default are created with "Pending payment" status.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'delay' => array(
		'title'             => __( 'Delay', 'custom-emails-for-woocommerce' ),
		'type'              => 'number',
		'placeholder'       => '',
		'default'           => 0,
		'css'               => 'width:100%;',
		'custom_attributes' => array( 'min' => 0, 'step' => 0.01 ),
	),
	'delay_unit' => array(
		'desc_tip'          => __( 'Delay unit.', 'custom-emails-for-woocommerce' ),
		'description'       => sprintf( __( 'Scheduled emails will be listed in <a href="%s">%s</a>.', 'custom-emails-for-woocommerce' ),
			admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_emails&section=scheduled' ),
			__( 'WooCommerce > Settings > Custom Emails > Scheduled', 'custom-emails-for-woocommerce' ) ),
		'type'              => 'select',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => 1,
		'options'           => array(
			1                 => __( 'seconds', 'custom-emails-for-woocommerce' ),
			MINUTE_IN_SECONDS => __( 'minutes', 'custom-emails-for-woocommerce' ),
			HOUR_IN_SECONDS   => __( 'hours', 'custom-emails-for-woocommerce' ),
			DAY_IN_SECONDS    => __( 'days', 'custom-emails-for-woocommerce' ),
			WEEK_IN_SECONDS   => __( 'weeks', 'custom-emails-for-woocommerce' ),
		),
		'css'               => 'width:100%;',
	),
) );

// Email Data
$wpml_active_languages = apply_filters( 'wpml_active_languages', null );
$fields = array_merge( $fields, array(
	'data_options' => array(
		'title'       => __( 'Email Data', 'custom-emails-for-woocommerce' ),
		'type'        => 'title',
	),
	'recipient'  => array(
		'title'       => __( 'Recipients', 'woocommerce' ),
		'type'        => 'text',
		'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce' ),
				'<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ) . ' ' .
			sprintf( __( 'Use %s for customer billing email.', 'custom-emails-for-woocommerce' ), '<code>%customer%</code>' ),
		'placeholder' => '',
		'default'     => '',
		'css'         => 'width:100%;',
	),
	'subject' => array(
		'title'       => __( 'Subject', 'woocommerce' ),
		'type'        => 'text',
		'description' => $this->get_placeholder_text(),
		'placeholder' => $this->get_default_subject(),
		'default'     => '',
		'css'         => 'width:100%;',
	),
	'email_type' => array(
		'title'       => __( 'Email type', 'woocommerce' ),
		'type'        => 'select',
		'desc_tip'    => __( 'Choose which format of email to send.', 'woocommerce' ),
		'default'     => 'html',
		'class'       => 'email_type wc-enhanced-select',
		'options'     => $email->get_email_type_options(),
		'css'         => 'width:100%;',
	),
	'wrap_in_wc_template' => array(
		'title'       => __( 'Header & footer', 'custom-emails-for-woocommerce' ),
		'label'       => __( 'Add', 'custom-emails-for-woocommerce' ),
		'desc_tip'    => __( 'Adds WooCommerce email header and footer to the content.', 'custom-emails-for-woocommerce' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
	),
	'heading' => array(
		'title'       => __( 'Email heading', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => __( 'Used only if "Header & footer" option is enabled.', 'custom-emails-for-woocommerce' ),
		'description' => $this->get_placeholder_text(),
		'placeholder' => $this->get_default_heading(),
		'default'     => '',
		'css'         => 'width:100%;',
	),
	'content' => array(
		'title'       => __( 'Email content', 'custom-emails-for-woocommerce' ),
		'type'        => 'textarea',
		'desc_tip'    => __( 'Please make sure content is not empty.', 'custom-emails-for-woocommerce' ),
		'description' => $this->get_placeholder_text() . '<br>' .
			sprintf( __( 'You can test this email by opening some order\'s admin edit page, and selecting "%s" in "Order actions".', 'custom-emails-for-woocommerce' ),
				sprintf( __( 'Send email: %s', 'custom-emails-for-woocommerce' ), $email->get_title() ) ) .
			'<p>' .
				'<a class="button" href="#" id="alg_wc_custom_emails_content_template_0">' . __( 'Default content', 'custom-emails-for-woocommerce' ) . '</a>' .
			'</p>',
		'placeholder' => '',
		'default'     => $this->get_default_content(),
		'css'         => 'width:100%;height:500px;',
	),
	'alg_wc_ce_style' => array(
		'title'       => __( 'Email style', 'custom-emails-for-woocommerce' ),
		'desc_tip'    => __( 'Optional additional email styling.', 'custom-emails-for-woocommerce' ),
		'description' => sprintf( __( 'Without the %s tag.', 'custom-emails-for-woocommerce' ),
			'<code>' . esc_html( '<style></style>' ) . '</code>' ),
		'type'        => 'textarea',
		'placeholder' => '',
		'default'     => '',
		'css'         => 'width:100%;height:200px;',
	),
	'attachments' => array(
		'title'       => __( 'Email attachments', 'custom-emails-for-woocommerce' ),
		'type'        => 'textarea',
		'description' => sprintf( __( 'File paths in %s, e.g.: %s', 'custom-emails-for-woocommerce' ),
			'<code>' . alg_wc_custom_emails()->core->get_base_dir() . '</code>',
			'<code>' . alg_wc_custom_emails()->core->get_base_dir_example() . '</code>' ),
		'desc_tip'    => __( 'One file path per line.', 'custom-emails-for-woocommerce' ),
		'default'     => '',
		'css'         => 'width:100%;height:100px;',
	),
) );
if ( $wpml_active_languages ) {
	$fields = array_merge( $fields, array(
		'required_wpml_languages' => array(
			'title'       => __( 'WPML/Polylang language', 'custom-emails-for-woocommerce' ),
			'type'        => 'multiselect',
			'class'       => 'chosen_select',
			'placeholder' => '',
			'default'     => array(),
			'options'     => wp_list_pluck( $wpml_active_languages, 'native_name' ),
			'desc_tip'    => __( 'Require WPML/Polylang language.', 'custom-emails-for-woocommerce' ) . ' ' .
				__( 'Email will be sent only for selected current user languages.', 'custom-emails-for-woocommerce' ),
			'css'         => 'width:100%;',
		),
	) );
}

// Order Options
$fields = array_merge( $fields, array(
	'order_options' => array(
		'title'             => __( 'Order Options', 'custom-emails-for-woocommerce' ),
		'type'              => 'title',
		'description'       => __( 'Options are ignored for non-order emails.', 'custom-emails-for-woocommerce' ),
	),
	'required_order_product_ids' => array(
		'title'             => __( 'Require products', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-product-search',
		'default'           => array(),
		'options'           => $this->get_ajax_options( 'product', $email, 'required_order_product_ids' ),
		'desc_tip'          => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
		'custom_attributes' => array(
			'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
			'data-action'      => 'woocommerce_json_search_products_and_variations',
			'data-allow_clear' => true,
		),
	),
	'excluded_order_product_ids' => array(
		'title'             => __( 'Exclude products', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-product-search',
		'default'           => array(),
		'options'           => $this->get_ajax_options( 'product', $email, 'excluded_order_product_ids' ),
		'desc_tip'          => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
		'custom_attributes' => array(
			'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
			'data-action'      => 'woocommerce_json_search_products_and_variations',
			'data-allow_clear' => true,
		),
	),
	'required_order_product_cats_ids' => array(
		'title'             => __( 'Require product categories', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => array(),
		'options'           => $this->get_terms( 'product_cat' ),
		'desc_tip'          => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'excluded_order_product_cats_ids' => array(
		'title'             => __( 'Exclude product categories', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => array(),
		'options'           => $this->get_terms( 'product_cat' ),
		'desc_tip'          => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'required_order_product_tags_ids' => array(
		'title'             => __( 'Require product tags', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => array(),
		'options'           => $this->get_terms( 'product_tag' ),
		'desc_tip'          => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'excluded_order_product_tags_ids' => array(
		'title'             => __( 'Exclude product tags', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'chosen_select',
		'placeholder'       => '',
		'default'           => array(),
		'options'           => $this->get_terms( 'product_tag' ),
		'desc_tip'          => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'min_order_amount' => array(
		'title'             => __( 'Minimum amount', 'custom-emails-for-woocommerce' ),
		'type'              => 'text',
		'class'             => 'wc_input_price',
		'placeholder'       => '',
		'default'           => '',
		'desc_tip'          => __( 'Minimum order amount (subtotal) for email to be sent.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'max_order_amount' => array(
		'title'             => __( 'Maximum amount', 'custom-emails-for-woocommerce' ),
		'type'              => 'text',
		'class'             => 'wc_input_price',
		'placeholder'       => '',
		'default'           => '',
		'desc_tip'          => __( 'Maximum order amount (subtotal) for email to be sent.', 'custom-emails-for-woocommerce' ),
		'css'               => 'width:100%;',
	),
	'required_order_payment_gateway_ids' => array(
		'title'             => __( 'Require payment gateways', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_gateways(),
		'css'               => 'width:100%;',
	),
	'excluded_order_payment_gateway_ids' => array(
		'title'             => __( 'Exclude payment gateways', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_gateways(),
		'css'               => 'width:100%;',
	),
	'required_order_shipping_instance_ids' => array(
		'title'             => __( 'Require shipping methods', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_shipping_methods_instances(),
		'css'               => 'width:100%;',
	),
	'excluded_order_shipping_instance_ids' => array(
		'title'             => __( 'Exclude shipping methods', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_shipping_methods_instances(),
		'css'               => 'width:100%;',
	),
	'required_order_user_ids' => array(
		'title'             => __( 'Require users', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-customer-search',
		'default'           => array(),
		'options'           => $this->get_ajax_options( 'customer', $email, 'required_order_user_ids' ),
		'css'               => 'width:100%;',
		'custom_attributes' => array(
			'data-placeholder' => __( 'Search for a user&hellip;', 'woocommerce' ),
			'data-allow_clear' => true,
		),
	),
	'excluded_order_user_ids' => array(
		'title'             => __( 'Exclude users', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-customer-search',
		'default'           => array(),
		'options'           => $this->get_ajax_options( 'customer', $email, 'excluded_order_user_ids' ),
		'css'               => 'width:100%;',
		'custom_attributes' => array(
			'data-placeholder' => __( 'Search for a user&hellip;', 'woocommerce' ),
			'data-allow_clear' => true,
		),
	),
	'required_order_user_roles' => array(
		'title'             => __( 'Require user roles', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_user_roles(),
		'css'               => 'width:100%;',
	),
	'excluded_order_user_roles' => array(
		'title'             => __( 'Exclude user roles', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_user_roles(),
		'css'               => 'width:100%;',
	),
	'required_order_statuses' => array(
		'title'             => __( 'Require order status', 'custom-emails-for-woocommerce' ),
		'desc_tip'          => __( 'Developed to use with the scheduled emails ("Delay" option) to ensure order status has not changed.', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_order_statuses(),
		'css'               => 'width:100%;',
	),
	'excluded_order_statuses' => array(
		'title'             => __( 'Exclude order status', 'custom-emails-for-woocommerce' ),
		'desc_tip'          => __( 'Developed to use with the scheduled emails ("Delay" option) to ensure order status has not changed.', 'custom-emails-for-woocommerce' ),
		'type'              => 'multiselect',
		'class'             => 'wc-enhanced-select',
		'default'           => array(),
		'options'           => $this->get_order_statuses(),
		'css'               => 'width:100%;',
	),
	'order_conditions_logical_operator' => array(
		'title'             => __( 'Logical operator', 'custom-emails-for-woocommerce' ),
		'desc_tip'          => sprintf( __( 'Logical operator for the "Order Options" section, for example: %s vs %s.', 'custom-emails-for-woocommerce' ),
			'<br><em>"' . __( 'Require products AND Minimum amount', 'custom-emails-for-woocommerce' ) . '"</em><br>',
			'<br><em>"' . __( 'Require products OR Minimum amount', 'custom-emails-for-woocommerce' )  . '"</em>' ),
		'type'              => 'select',
		'class'             => 'chosen_select',
		'default'           => 'AND',
		'options'           => array(
			'AND' => 'AND',
			'OR'  => 'OR',
		),
	),
) );

// Email Sender Options
$fields = array_merge( $fields, array(
	'email_sender_options' => array(
		'title'       => __( 'Email Sender Options', 'custom-emails-for-woocommerce' ),
		'type'        => 'title',
		'description' => sprintf( __( 'You can use shortcodes here, e.g., %s (Name fields) or %s (Address fields).', 'custom-emails-for-woocommerce' ),
			'<code>[order_billing_first_name] [order_billing_last_name]</code>',
			'<code>[order_billing_email]</code>' ),
	),
	'alg_wc_ce_from_name' => array(
		'title'       => __( '"From" name', 'custom-emails-for-woocommerce' ),
		'placeholder' => $email->get_from_name(),
		'type'        => 'text',
		'css'         => 'width:100%;',
		'default'     => '',
	),
	'alg_wc_ce_from_address' => array(
		'title'       => __( '"From" address', 'custom-emails-for-woocommerce' ),
		'placeholder' => $email->get_from_address(),
		'type'        => 'text',
		'css'         => 'width:100%;',
		'default'     => '',
	),
	'alg_wc_ce_reply_to_name' => array(
		'title'       => __( '"Reply-to" name', 'custom-emails-for-woocommerce' ),
		'placeholder' => $email->get_from_name(),
		'type'        => 'text',
		'css'         => 'width:100%;',
		'default'     => '',
	),
	'alg_wc_ce_reply_to_address' => array(
		'title'       => __( '"Reply-to" address', 'custom-emails-for-woocommerce' ),
		'placeholder' => $email->get_from_address(),
		'type'        => 'text',
		'css'         => 'width:100%;',
		'default'     => '',
	),
) );

// Admin Option
$fields = array_merge( $fields, array(
	'admin_options' => array(
		'title'       => __( 'Admin Options', 'custom-emails-for-woocommerce' ),
		'type'        => 'title',
	),
	'admin_actions' => array(
		'title'       => __( 'Admin actions', 'custom-emails-for-woocommerce' ),
		'desc_tip' => sprintf( __( 'This will add "%s" option to the selected positions.', 'custom-emails-for-woocommerce' ),
			sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ), $this->get_title() ) ),
		'type'        => 'multiselect',
		'class'       => 'chosen_select',
		'css'         => 'width:100%;',
		'default'     => array( 'order_actions_single', 'order_actions_bulk' ),
		'options'     => array(
			'order_actions_single'  => __( 'Edit order > Order actions', 'custom-emails-for-woocommerce' ),
			'order_actions_bulk'    => __( 'Orders > Bulk actions', 'custom-emails-for-woocommerce' ),
			'order_actions_preview' => __( 'Orders > Preview', 'custom-emails-for-woocommerce' ),
			'order_actions_column'  => __( 'Orders > Actions column', 'custom-emails-for-woocommerce' ),
		),
	),
) );

// Advanced Option
$fields = array_merge( $fields, array(
	'advanced_options' => array(
		'title'       => __( 'Advanced Options', 'custom-emails-for-woocommerce' ),
		'type'        => 'title',
	),
	'alg_wc_ce_stop_emails' => array(
		'title'       => __( 'Stop emails', 'custom-emails-for-woocommerce' ),
		'desc_tip'    => __( 'Select emails that should NOT be sent for an order in case the current email is sent.', 'custom-emails-for-woocommerce' ) . ' ' .
			__( 'For example, override the "Completed order" email for selected users (with the "Require users" option) and send the standard email to the remaining users.', 'custom-emails-for-woocommerce' ),
		'type'        => 'multiselect',
		'class'       => 'wc-enhanced-select',
		'default'     => array(),
		'options'     => $this->get_stop_emails(),
		'css'         => 'width:100%;',
	),
	'exclude_recipients' => array(
		'title'       => __( 'Exclude recipients', 'custom-emails-for-woocommerce' ),
		'desc_tip'    => sprintf( __( 'Excludes recipient email addresses. For example, if you are using the `%s` placeholder for the recipient, you may want to block some email addresses from getting the email.', 'custom-emails-for-woocommerce' ),
				'%customer%' ) . ' ' .
			__( 'Ignored if empty.', 'custom-emails-for-woocommerce' ),
		'description' => sprintf( __( 'Separate emails with a comma or with a new line. You can also use wildcard (%s) here, for example: %s', 'custom-emails-for-woocommerce' ),
			'<code>*</code>', '<code>*@example.com,email@example.net</code>' ),
		'type'        => 'textarea',
		'css'         => 'width:100%;height:100px;',
		'default'     => '',
	),
) );

// Settings Tools
$fields = array_merge( $fields, array(
	'settings_tools' => array(
		'title'       => __( 'Settings Tools', 'custom-emails-for-woocommerce' ),
		'type'        => 'title',
	),
) );
if ( ( $copy_emails = $this->get_copy_emails_option( $email ) ) ) {
	$fields = array_merge( $fields, array(
		'copy_settings' => array(
			'title'       => __( 'Copy settings', 'custom-emails-for-woocommerce' ),
			'type'        => 'select',
			'class'       => 'chosen_select',
			'default'     => 0,
			'options'     => $copy_emails,
			'description' => __( 'Select an email to copy settings from and save changes.', 'custom-emails-for-woocommerce' ),
			'desc_tip'    => __( 'Please note that there is no undo for this action. Your current email settings will be overwritten.', 'custom-emails-for-woocommerce' ),
		),
	) );
}
$fields = array_merge( $fields, array(
	'reset_settings' => array(
		'title'       => __( 'Reset settings', 'custom-emails-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => '<strong>' . __( 'Reset', 'custom-emails-for-woocommerce' ) . '</strong>',
		'description' => __( 'Check the box and save changes to reset.', 'custom-emails-for-woocommerce' ),
		'default'     => 'no',
	),
) );
