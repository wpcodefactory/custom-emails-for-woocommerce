<?php
/**
 * Custom Emails for WooCommerce - Email Settings Class
 *
 * @version 2.1.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Email_Settings' ) ) :

class Alg_WC_Custom_Email_Settings {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		return true;
	}

	/**
	 * get_title.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_title( $id = 1 ) {
		$titles = get_option( 'alg_wc_custom_emails_titles', array() );
		return ( isset( $titles[ $id ] ) ? $titles[ $id ] :
			( 1 == $id ? __( 'Custom email', 'custom-emails-for-woocommerce' ) : sprintf( __( 'Custom email #%d', 'custom-emails-for-woocommerce' ), $id ) ) );
	}

	/**
	 * get_description.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) customizable description (similar as it is now with title)
	 */
	function get_description( $id = 1 ) {
		return __( 'Custom emails are sent to the recipient list when selected triggers are called.', 'custom-emails-for-woocommerce' );
	}

	/**
	 * get_default_heading.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_default_heading() {
		return sprintf( __( 'Order %s', 'custom-emails-for-woocommerce' ), '{order_number}' );
	}

	/**
	 * get_default_subject.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_default_subject() {
		return sprintf( __( '[%s] Order (%s) - %s', 'custom-emails-for-woocommerce' ), '{site_title}', '{order_number}', '{order_date}' );
	}

	/**
	 * get_default_content.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) better default content (include more shortcodes, e.g. `[clear]`, `[if]` etc.)
	 */
	function get_default_content() {
		return '[order_details]' . PHP_EOL .
			'<table>' . PHP_EOL .
			'    <tbody>' . PHP_EOL .
			'        <tr><th>Billing address</th><th>Shipping address</th></tr>' . PHP_EOL .
			'        <tr><td>[order_billing_address]</td><td>[order_shipping_address]</td></tr>' . PHP_EOL .
			'    </tbody>' . PHP_EOL .
			'</table>';
	}

	/**
	 * get_triggers.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `renewal`: are we sure all of them exist?
	 * @todo    (dev) `renewal`: `woocommerce_new_order_renewal_notification_alg_wc_ce_any`?
	 * @todo    (dev) `renewal`: only add if `WC_Subscriptions` class exist?
	 */
	function get_triggers() {

		// Init
		$triggers = array(
			'new_order'                   => array(),
			'order_status'                => array(),
			'order_status_change'         => array(),
			'subscription_status'         => array(),
			'subscription_status_change'  => array(),
			'renewal_new_order'           => array(),
			'renewal_order_status'        => array(),
			'renewal_order_status_change' => array(),
			'extra'                       => array(),
		);

		// Order triggers
		$triggers['new_order']['woocommerce_new_order_notification_alg_wc_ce_any'] = __( 'New order (Any status)', 'custom-emails-for-woocommerce' );
		$order_statuses = wc_get_order_statuses();
		foreach ( $order_statuses as $id => $name ) {
			$slug = substr( $id, 3 );
			$triggers['new_order'][            "woocommerce_new_order_notification_{$slug}" ]            = sprintf( __( 'New order (%s)', 'custom-emails-for-woocommerce' ),                     $name );
			$triggers['order_status'][         "woocommerce_order_status_{$slug}_notification" ]         = sprintf( __( 'Order status updated to %s', 'custom-emails-for-woocommerce' ),         $name );
			$triggers['renewal_new_order'][    "woocommerce_new_order_renewal_notification_{$slug}" ]    = sprintf( __( 'Renewal new order (%s)', 'custom-emails-for-woocommerce' ),             $name );
			$triggers['renewal_order_status'][ "woocommerce_order_status_{$slug}_renewal_notification" ] = sprintf( __( 'Renewal order status updated to %s', 'custom-emails-for-woocommerce' ), $name );
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$triggers['order_status_change'][         "woocommerce_order_status_{$slug}_to_{$_slug}_notification" ]         = sprintf( __( 'Order status %s to %s', 'custom-emails-for-woocommerce' ),         $name, $_name );
					$triggers['renewal_order_status_change'][ "woocommerce_order_status_{$slug}_to_{$_slug}_renewal_notification" ] = sprintf( __( 'Renewal order status %s to %s', 'custom-emails-for-woocommerce' ), $name, $_name );
				}
			}
		}

		// Extra triggers
		$triggers['extra'] = array(
			'woocommerce_reset_password_notification'           => __( 'Reset password notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_order_fully_refunded_notification'     => __( 'Order fully refunded notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_order_partially_refunded_notification' => __( 'Order partially refunded notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_new_customer_note_notification'        => __( 'New customer note notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_low_stock_notification'                => __( 'Low stock notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_no_stock_notification'                 => __( 'No stock notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_product_on_backorder_notification'     => __( 'Product on backorder notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_created_customer_notification'         => __( 'Created customer notification', 'custom-emails-for-woocommerce' ),
		);

		// WooCommerce Subscriptions
		$order_statuses = ( function_exists( 'wcs_get_subscription_statuses' ) ? wcs_get_subscription_statuses() : array() );
		foreach ( $order_statuses as $id => $name ) {
			$slug = substr( $id, 3 );
			$triggers['subscription_status'][ "woocommerce_subscription_status_{$slug}_notification" ] = sprintf( __( 'Subscription status updated to %s', 'custom-emails-for-woocommerce' ), $name );
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$triggers['subscription_status_change'][ "woocommerce_subscription_status_{$slug}_to_{$_slug}_notification" ] = sprintf( __( 'Subscription status %s to %s', 'custom-emails-for-woocommerce' ), $name, $_name );
				}
			}
		}

		// Filter enabled trigger groups
		$enabled_triggers       = array();
		$all_trigger_groups     = alg_wc_custom_emails()->core->get_trigger_groups();
		$enabled_trigger_groups = get_option( 'alg_wc_custom_emails_enabled_trigger_groups', array( 'order_status', 'order_status_change', 'new_order', 'extra' ) );
		foreach ( $enabled_trigger_groups as $trigger_group ) {
			$enabled_triggers[ $all_trigger_groups[ $trigger_group ] ] = $triggers[ $trigger_group ];
		}

		// Custom triggers
		foreach ( alg_wc_custom_emails()->core->get_custom_triggers() as $custom_trigger_action => $custom_trigger_title ) {
			$triggers['custom_triggers'][ $custom_trigger_action . '_notification' ] = $custom_trigger_title;
		}
		if ( ! empty( $triggers['custom_triggers'] ) ) {
			$enabled_triggers[ __( 'Custom triggers', 'custom-emails-for-woocommerce' ) ] = $triggers['custom_triggers'];
		}

		// Result
		return $enabled_triggers;

	}

	/**
	 * get_placeholder_text.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	function get_placeholder_text() {
		$placeholders = array( '{site_title}', '{site_address}', '{order_number}', '{order_date}' );
		return sprintf( __( 'You can use <a href="%s" target="_blank">shortcodes</a> and/or <span title="%s" style="%s">standard placeholders</span> here.', 'custom-emails-for-woocommerce' ),
			'https://wpfactory.com/docs/custom-emails-for-woocommerce/shortcodes/', implode( ', ', $placeholders ), 'text-decoration:underline;' );
	}

	/**
	 * get_terms.
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 *
	 * @todo    (dev) WPML
	 * @todo    (dev) replace this with AJAX
	 * @todo    (dev) add term ID to the title in the output?
	 */
	function get_terms( $taxonomy ) {
		if ( ! isset( $this->terms[ $taxonomy ] ) ) {
			$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
			$terms = ( ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name', 'term_id' ) : array() );
			$this->terms[ $taxonomy ] = $terms;
		}
		return $this->terms[ $taxonomy ];
	}

	/**
	 * get_ajax_options.
	 *
	 * @version 1.8.0
	 * @since   1.7.1
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-ajax.php#L1569
	 * @see     https://github.com/woocommerce/woocommerce/blob/6.3.1/plugins/woocommerce/includes/class-wc-ajax.php#L1681
	 *
	 * @todo    (dev) `customer`: add `guest` (check the "Order Status Rules" plugin)
	 */
	function get_ajax_options( $type, $email, $option, $key = false ) {
		$options = array();

		// Make sure we are not calling the `wc_get_product()` function too early: https://github.com/woocommerce/woocommerce/blob/7.4.1/plugins/woocommerce/includes/wc-product-functions.php#L64
		if ( ! did_action( 'woocommerce_init' ) || ! did_action( 'woocommerce_after_register_taxonomy' ) || ! did_action( 'woocommerce_after_register_post_type' ) ) {
			return $options;
		}

		// Current value
		$current = $email->get_option( $option, array() );
		if ( false !== $key ) {
			$current = ( isset( $current[ $key ] ) ? $current[ $key ] : array() );
		}

		// Post data
		$post_data = $email->get_post_data();
		$field_key = $email->get_field_key( $option );
		if ( ! empty( $post_data[ $field_key ] ) && is_array( $post_data[ $field_key ] ) ) {
			$current = array_unique( array_merge( $current, $post_data[ $field_key ] ) );
		}

		// Loop (ids)
		foreach ( $current as $id ) {

			// Prepare data
			switch ( $type ) {
				case 'product':
					$obj      = wc_get_product( $id );
					$is_valid = ( $obj && is_object( $obj ) );
					break;
				case 'customer':
					$obj      = new WC_Customer( $id );
					$is_valid = ( $obj && is_object( $obj ) && 0 != $obj->get_id() );
					break;
			}

			// Get option
			if ( ! $is_valid ) {

				// Not valid
				switch ( $type ) {
					case 'product':
						$res = sprintf( esc_html__( 'Product #%d', 'custom-emails-for-woocommerce' ), $id );
						break;
					case 'customer':
						$res = sprintf( esc_html__( 'User #%d', 'custom-emails-for-woocommerce' ), $id );
						break;
				}

			} else {

				// Valid
				switch ( $type ) {
					case 'product':
						$res = esc_html( wp_strip_all_tags( $obj->get_formatted_name() ) );
						break;
					case 'customer':
						$res = sprintf(
							/* translators: $1: customer name, $2 customer id, $3: customer email */
							esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
							$obj->get_first_name() . ' ' . $obj->get_last_name(),
							$obj->get_id(),
							$obj->get_email()
						);
						break;
				}

			}

			// Add option
			$options[ esc_attr( $id ) ] = $res;

		}

		return $options;
	}

	/**
	 * get_copy_emails_option.
	 *
	 * @version 1.7.2
	 * @since   1.7.2
	 */
	function get_copy_emails_option( $email ) {
		$copy_emails = apply_filters( 'alg_wc_custom_email_settings_copy', array(
			0 => __( 'Select an email&hellip;', 'custom-emails-for-woocommerce' ),
			1 => alg_wc_custom_emails()->core->email_settings->get_title( 1 ),
		) );
		unset( $copy_emails[ $email->alg_wc_ce_id ] );
		return ( count( $copy_emails ) > 1 ? $copy_emails : false );
	}

	/**
	 * get_form_fields.
	 *
	 * @version 1.9.6
	 * @since   1.0.0
	 *
	 * @todo    (feature) "Custom triggers"
	 * @todo    (feature) `cc` and `bcc`
	 * @todo    (desc) `delay`: better desc
	 * @todo    (dev) add sections, e.g. "Conditions"
	 * @todo    (dev) replace `woocommerce` text domain with `custom-emails-for-woocommerce` everywhere
	 * @todo    (feature) separate option for plain content
	 */
	function get_form_fields( $email ) {
		$fields = array();

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
				'title'       => __( 'Triggers', 'custom-emails-for-woocommerce' ),
				'type'        => 'title',
			),
			'trigger' => array(
				'title'       => __( 'Triggers', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => array(),
				'options'     => $this->get_triggers(),
				'desc_tip'    => __( 'Please note, that all <em>new orders</em> in WooCommerce by default are created with "Pending payment" status.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'delay' => array(
				'title'       => __( 'Delay', 'custom-emails-for-woocommerce' ),
				'type'        => 'number',
				'placeholder' => '',
				'default'     => 0,
				'css'         => 'width:100%;',
				'custom_attributes' => array( 'min' => 0, 'step' => 0.01 ),
			),
			'delay_unit' => array(
				'description' => __( 'Delay unit', 'custom-emails-for-woocommerce' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => 1,
				'options'     => array(
					1                 => __( 'seconds', 'custom-emails-for-woocommerce' ),
					MINUTE_IN_SECONDS => __( 'minutes', 'custom-emails-for-woocommerce' ),
					HOUR_IN_SECONDS   => __( 'hours', 'custom-emails-for-woocommerce' ),
					DAY_IN_SECONDS    => __( 'days', 'custom-emails-for-woocommerce' ),
					WEEK_IN_SECONDS   => __( 'weeks', 'custom-emails-for-woocommerce' ),
				),
				'css'         => 'width:100%;',
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
				'title'       => __( 'WC email template', 'custom-emails-for-woocommerce' ),
				'label'       => __( 'Wrap', 'custom-emails-for-woocommerce' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
			),
			'heading' => array(
				'title'       => __( 'Email heading', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => __( 'Used only if "WC email template" option is enabled and only for "HTML" email type.', 'custom-emails-for-woocommerce' ),
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
			'attachments' => array(
				'title'       => __( 'Email attachments', 'custom-emails-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => sprintf( __( 'File paths in %s, e.g.: %s', 'custom-emails-for-woocommerce' ),
					'<code>' . ABSPATH . '</code>', '<code>' . 'wp-content/uploads/example.pdf' . '</code>' ),
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
				'title'       => __( 'Order Options', 'custom-emails-for-woocommerce' ),
				'type'        => 'title',
				'description' => __( 'Options are ignored for non-order emails.', 'custom-emails-for-woocommerce' ),
			),
			'required_order_product_ids' => array(
				'title'       => __( 'Require products', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'wc-product-search',
				'default'     => array(),
				'options'     => $this->get_ajax_options( 'product', $email, 'required_order_product_ids' ),
				'desc_tip'    => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
				'custom_attributes' => array(
					'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
					'data-action'      => 'woocommerce_json_search_products_and_variations',
					'data-allow_clear' => true,
				),
			),
			'excluded_order_product_ids' => array(
				'title'       => __( 'Exclude products', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'wc-product-search',
				'default'     => array(),
				'options'     => $this->get_ajax_options( 'product', $email, 'excluded_order_product_ids' ),
				'desc_tip'    => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
				'custom_attributes' => array(
					'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
					'data-action'      => 'woocommerce_json_search_products_and_variations',
					'data-allow_clear' => true,
				),
			),
			'required_order_product_cats_ids' => array(
				'title'       => __( 'Require product categories', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => array(),
				'options'     => $this->get_terms( 'product_cat' ),
				'desc_tip'    => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'excluded_order_product_cats_ids' => array(
				'title'       => __( 'Exclude product categories', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => array(),
				'options'     => $this->get_terms( 'product_cat' ),
				'desc_tip'    => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'required_order_product_tags_ids' => array(
				'title'       => __( 'Require product tags', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => array(),
				'options'     => $this->get_terms( 'product_tag' ),
				'desc_tip'    => __( 'Email will be sent only if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'excluded_order_product_tags_ids' => array(
				'title'       => __( 'Exclude product tags', 'custom-emails-for-woocommerce' ),
				'type'        => 'multiselect',
				'class'       => 'chosen_select',
				'placeholder' => '',
				'default'     => array(),
				'options'     => $this->get_terms( 'product_tag' ),
				'desc_tip'    => __( 'Email will NOT be sent if there is at least one of the selected products in the order.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'min_order_amount' => array(
				'title'       => __( 'Minimum amount', 'custom-emails-for-woocommerce' ),
				'type'        => 'text',
				'class'       => 'wc_input_price',
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => __( 'Minimum order amount (subtotal) for email to be sent.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'max_order_amount' => array(
				'title'       => __( 'Maximum amount', 'custom-emails-for-woocommerce' ),
				'type'        => 'text',
				'class'       => 'wc_input_price',
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => __( 'Maximum order amount (subtotal) for email to be sent.', 'custom-emails-for-woocommerce' ),
				'css'         => 'width:100%;',
			),
			'order_conditions_logical_operator' => array(
				'title'       => __( 'Logical operator', 'custom-emails-for-woocommerce' ),
				'desc_tip'    => sprintf( __( 'Logical operator for the "Order Options" section, for example: %s vs %s.', 'custom-emails-for-woocommerce' ),
					'<br><em>"' . __( 'Require products AND Minimum amount', 'custom-emails-for-woocommerce' ) . '"</em><br>',
					'<br><em>"' . __( 'Require products OR Minimum amount', 'custom-emails-for-woocommerce' )  . '"</em>' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'default'     => 'AND',
				'options'     => array(
					'AND' => 'AND',
					'OR'  => 'OR',
				),
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

		return $fields;
	}

}

endif;

return new Alg_WC_Custom_Email_Settings();
