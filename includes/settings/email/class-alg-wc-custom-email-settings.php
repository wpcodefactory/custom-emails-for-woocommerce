<?php
/**
 * Custom Emails for WooCommerce - Email Settings Class
 *
 * @version 3.6.3
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Email_Settings' ) ) :

class Alg_WC_Custom_Email_Settings {

	/**
	 * terms.
	 *
	 * @version 3.1.2
	 * @since   3.1.2
	 */
	public $terms;

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
	 * @version 3.5.0
	 * @since   1.0.0
	 */
	function get_title( $id = 1 ) {
		$titles = get_option( 'alg_wc_custom_emails_titles', array() );
		return (
			$titles[ $id ] ??
			(
				1 == $id ?
				__( 'Custom email', 'custom-emails-for-woocommerce' ) :
				sprintf(
					/* Translators: %d: Custom email ID. */
					__( 'Custom email #%d', 'custom-emails-for-woocommerce' ),
					$id
				)
			)
		);
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
		return sprintf(
			/* Translators: %s: Order number placeholder. */
			__( 'Order %s', 'custom-emails-for-woocommerce' ),
			'{order_number}'
		);
	}

	/**
	 * get_default_subject.
	 *
	 * @version 3.5.0
	 * @since   1.0.0
	 */
	function get_default_subject() {
		return sprintf(
			/* Translators: %1$s: Site title placeholder, %2$s: Order number placeholder, %3$s: Order date placeholder. */
			__( '[%1$s] Order (%2$s) - %3$s', 'custom-emails-for-woocommerce' ),
			'{site_title}',
			'{order_number}',
			'{order_date}'
		);
	}

	/**
	 * get_default_content.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) better default content (include more shortcodes, e.g., `[clear]`, `[if]` etc.)
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
	 * @version 3.5.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `alg_wc_ce_product_published_notification`: `woocommerce_new_product`?
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
			$triggers['new_order'][ "woocommerce_new_order_notification_{$slug}" ] = sprintf(
				/* Translators: %s: Status name. */
				__( 'New order (%s)', 'custom-emails-for-woocommerce' ),
				$name
			);
			$triggers['order_status'][ "woocommerce_order_status_{$slug}_notification" ] = sprintf(
				/* Translators: %s: Status name. */
				__( 'Order status updated to %s', 'custom-emails-for-woocommerce' ),
				$name
			);
			$triggers['renewal_new_order'][ "woocommerce_new_order_renewal_notification_{$slug}" ] = sprintf(
				/* Translators: %s: Status name. */
				__( 'Renewal new order (%s)', 'custom-emails-for-woocommerce' ),
				$name
			);
			$triggers['renewal_order_status'][ "woocommerce_order_status_{$slug}_renewal_notification" ] = sprintf(
				/* Translators: %s: Status name. */
				__( 'Renewal order status updated to %s', 'custom-emails-for-woocommerce' ),
				$name
			);
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$triggers['order_status_change'][ "woocommerce_order_status_{$slug}_to_{$_slug}_notification" ] = sprintf(
						/* Translators: %1$s: Status name, %2$s: Status name. */
						__( 'Order status %1$s to %2$s', 'custom-emails-for-woocommerce' ),
						$name,
						$_name
					);
					$triggers['renewal_order_status_change'][ "woocommerce_order_status_{$slug}_to_{$_slug}_renewal_notification" ] = sprintf(
						/* Translators: %1$s: Status name, %2$s: Status name. */
						__( 'Renewal order status %1$s to %2$s', 'custom-emails-for-woocommerce' ),
						$name,
						$_name
					);
				}
			}
		}

		// Extra triggers
		$triggers['extra'] = array(
			'woocommerce_reset_password_notification'                => __( 'Reset password notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_order_fully_refunded_notification'          => __( 'Order fully refunded notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_order_partially_refunded_notification'      => __( 'Order partially refunded notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_new_customer_note_notification'             => __( 'New customer note notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_low_stock_notification'                     => __( 'Low stock notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_no_stock_notification'                      => __( 'No stock notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_product_on_backorder_notification'          => __( 'Product on backorder notification', 'custom-emails-for-woocommerce' ),
			'alg_wc_ce_product_published_notification'               => __( 'Product published', 'custom-emails-for-woocommerce' ),
			'woocommerce_update_product_notification'                => __( 'Product updated', 'custom-emails-for-woocommerce' ),
			'woocommerce_created_customer_notification'              => __( 'Created customer notification', 'custom-emails-for-woocommerce' ),
			'woocommerce_after_save_address_validation_notification' => __( 'Customer address saved', 'custom-emails-for-woocommerce' ),
			'alg_wc_ce_user_address_changed_notification'            => __( 'Customer address changed', 'custom-emails-for-woocommerce' ),
		);

		// WooCommerce Subscriptions
		$order_statuses = ( function_exists( 'wcs_get_subscription_statuses' ) ? wcs_get_subscription_statuses() : array() );
		foreach ( $order_statuses as $id => $name ) {
			$slug = substr( $id, 3 );
			$triggers['subscription_status'][ "woocommerce_subscription_status_{$slug}_notification" ] = sprintf(
				/* Translators: %s: Status name. */
				__( 'Subscription status updated to %s', 'custom-emails-for-woocommerce' ),
				$name
			);
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$triggers['subscription_status_change'][ "woocommerce_subscription_status_{$slug}_to_{$_slug}_notification" ] = sprintf(
						/* Translators: %1$s: Status name, %2$s: Status name. */
						__( 'Subscription status %1$s to %2$s', 'custom-emails-for-woocommerce' ),
						$name,
						$_name
					);
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
	 * @version 3.5.0
	 * @since   1.0.0
	 */
	function get_placeholder_text() {
		$placeholders = array( '{site_title}', '{site_address}', '{order_number}', '{order_date}' );
		return sprintf(
			/* Translators: %1$s: Plugin URL, %2$s: Placeholder list, %3$s: Style. */
			__( 'You can use <a href="%1$s" target="_blank">shortcodes</a> or <span title="%2$s" style="%3$s">standard placeholders</span> here.', 'custom-emails-for-woocommerce' ),
			'https://wpfactory.com/docs/custom-emails-for-woocommerce/',
			implode( ', ', $placeholders ),
			'text-decoration:underline;'
		);
	}

	/**
	 * get_gateways.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 *
	 * @todo    (dev) add "Other" and/or "N/A" options?
	 */
	function get_gateways() {
		return ( ( $gateways = WC()->payment_gateways()->payment_gateways ) ? wp_list_pluck( $gateways, 'method_title', 'id' ) : array() );
	}

	/**
	 * get_shipping_zones.
	 *
	 * @version 3.6.3
	 * @since   2.2.0
	 */
	function get_shipping_zones( $include_empty_zone = true ) {
		if ( empty( WC()->countries ) ) {
			return array();
		}
		$zones = WC_Shipping_Zones::get_zones();
		if ( $include_empty_zone ) {
			$zone                                                = new WC_Shipping_Zone( 0 );
			$zones[ $zone->get_id() ]                            = $zone->get_data();
			$zones[ $zone->get_id() ]['zone_id']                 = $zone->get_id();
			$zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
			$zones[ $zone->get_id() ]['shipping_methods']        = $zone->get_shipping_methods();
		}
		return $zones;
	}

	/**
	 * get_shipping_methods_instances.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 */
	function get_shipping_methods_instances() {
		$shipping_methods = array();
		foreach ( $this->get_shipping_zones() as $zone_id => $zone_data ) {
			foreach ( $zone_data['shipping_methods'] as $shipping_method ) {
				$shipping_methods[ $shipping_method->instance_id ] = $zone_data['zone_name'] . ': ' . $shipping_method->title;
			}
		}
		return $shipping_methods;
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
	 * @version 2.9.8
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

		// Make sure it's backend
		if ( ! is_admin() ) {
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
						/* Translators: %d: Product ID. */
						$res = sprintf( esc_html__( 'Product #%d', 'custom-emails-for-woocommerce' ), $id );
						break;
					case 'customer':
						/* Translators: %d: User ID. */
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
							esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
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
	 * get_user_roles.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function get_user_roles() {
		global $wp_roles;
		return array_merge(
			array( 'alg_wc_ce_guest' => __( 'No role (guest)', 'custom-emails-for-woocommerce' ) ),
			( ! empty( $wp_roles->roles ) ? wp_list_pluck( apply_filters( 'editable_roles', $wp_roles->roles ), 'name' ) : array() )
		);
	}

	/**
	 * get_stop_emails.
	 *
	 * @version 2.7.3
	 * @since   2.7.3
	 *
	 * @todo    (dev) add all emails, e.g., subscriptions (`wp_list_pluck( WC()->mailer()->emails, 'title', 'id' )`)
	 */
	function get_stop_emails() {
		return apply_filters( 'alg_wc_custom_emails_stop_emails_list', array(
			'new_order'                 => __( 'New order', 'custom-emails-for-woocommerce' ),
			'cancelled_order'           => __( 'Cancelled order', 'custom-emails-for-woocommerce' ),
			'failed_order'              => __( 'Failed order', 'custom-emails-for-woocommerce' ),
			'customer_on_hold_order'    => __( 'Order on-hold', 'custom-emails-for-woocommerce' ),
			'customer_processing_order' => __( 'Processing order', 'custom-emails-for-woocommerce' ),
			'customer_completed_order'  => __( 'Completed order', 'custom-emails-for-woocommerce' ),
			'customer_refunded_order'   => __( 'Refunded order', 'custom-emails-for-woocommerce' ),
		) );
	}

	/**
	 * get_order_statuses.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 */
	function get_order_statuses() {
		$statuses = wc_get_order_statuses();
		if ( function_exists( 'wcs_get_subscription_statuses' ) ) {
			$statuses = array_merge( $statuses, wcs_get_subscription_statuses() );
		}
		return $statuses;
	}

	/**
	 * get_form_fields.
	 *
	 * @version 2.9.9
	 * @since   1.0.0
	 *
	 * @todo    (dev) load this in admin only (see `get_ajax_options()`)?
	 * @todo    (feature) "Custom triggers"
	 * @todo    (feature) `cc` and `bcc`
	 * @todo    (desc) `delay`: better desc
	 * @todo    (dev) add sections, e.g., "Conditions"
	 * @todo    (dev) replace `woocommerce` text domain with `custom-emails-for-woocommerce` everywhere
	 * @todo    (feature) separate option for plain content
	 */
	function get_form_fields( $email ) {
		$fields = array();
		require( 'class-alg-wc-custom-email-settings-fields.php' );
		return $fields;
	}

}

endif;

return new Alg_WC_Custom_Email_Settings();
