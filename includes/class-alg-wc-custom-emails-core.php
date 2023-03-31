<?php
/**
 * Custom Emails for WooCommerce - Core Class
 *
 * @version 1.9.3
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Emails_Core' ) ) :

class Alg_WC_Custom_Emails_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.3.1
	 * @since   1.0.0
	 *
	 * @todo    [later] (feature) option to conditionally disable some standard WC emails (e.g. "order completed" email, etc.)?
	 */
	function __construct() {
		// Core
		if ( 'yes' === get_option( 'alg_wc_custom_emails_plugin_enabled', 'yes' ) ) {
			// Properties
			$this->do_debug       = ( 'yes' === get_option( 'alg_wc_custom_emails_debug_enabled', 'no' ) );
			$this->email_settings = require_once( 'settings/class-alg-wc-custom-email-settings.php' );
			$this->shortcodes     = require_once( 'class-alg-wc-custom-emails-shortcodes.php' );
			// Hooks
			add_filter( 'woocommerce_email_classes', array( $this, 'add_custom_emails' ) );
			add_filter( 'woocommerce_email_actions', array( $this, 'add_custom_email_trigger_actions' ) );
			// Delayed emails
			add_action( 'alg_wc_custom_emails_send_email', array( $this, 'send_delayed_email' ), 10, 2 );
		}
		// Core loaded
		do_action( 'alg_wc_custom_emails_core_loaded', $this );
	}

	/**
	 * add_to_log.
	 *
	 * @version 1.4.1
	 * @since   1.2.0
	 */
	function add_to_log( $message ) {
		if ( function_exists( 'wc_get_logger' ) && ( $log = wc_get_logger() ) ) {
			$log->log( 'info', esc_html( $message ), array( 'source' => 'custom-emails-for-woocommerce' ) );
		}
	}

	/**
	 * debug.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function debug( $message ) {
		if ( $this->do_debug ) {
			$this->add_to_log( $message );
		}
	}

	/**
	 * send_delayed_email.
	 *
	 * @version 1.4.1
	 * @since   1.3.0
	 *
	 * @todo    [next] (dev) better debug info
	 */
	function send_delayed_email( $email, $object_id ) {
		$this->debug( sprintf( esc_html__( '%s: Sending delayed email.', 'custom-emails-for-woocommerce' ), $email ) );
		$this->send_email( $email, $object_id, __( 'delayed', 'custom-emails-for-woocommerce' ) );
	}

	/**
	 * send_email.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [next] (dev) what's with `WC()->payment_gateways()` and `WC()->shipping()`?
	 */
	function send_email( $email, $object_id, $note = '' ) {
		WC()->payment_gateways();
		WC()->shipping();
		if ( ! empty( WC()->mailer()->emails[ $email ] ) ) {
			WC()->mailer()->emails[ $email ]->send_email( $object_id, true, $note );
		}
	}

	/**
	 * add_custom_email_trigger_actions.
	 *
	 * @version 1.5.3
	 * @since   1.0.0
	 *
	 * @todo    [next] [!] (dev) maybe we need to add "Subscriptions: Renewals" here (`'woocommerce_order_status_' . $slug . '_renewal'`, `'woocommerce_order_status_' . $slug . '_to_' . $_slug . '_renewal'`)?
	 */
	function add_custom_email_trigger_actions( $email_actions ) {

		$email_actions[] = 'woocommerce_checkout_order_processed';

		$order_statuses = wc_get_order_statuses();
		foreach ( $order_statuses as $id => $name ) {
			$slug = substr( $id, 3 );
			$email_actions[] = 'woocommerce_order_status_' . $slug;
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$email_actions[] = 'woocommerce_order_status_' . $slug . '_to_' . $_slug;
				}
			}
		}

		// WooCommerce Subscriptions
		$order_statuses = ( function_exists( 'wcs_get_subscription_statuses' ) ? wcs_get_subscription_statuses() : array() );
		foreach ( $order_statuses as $id => $name ) {
			$slug = substr( $id, 3 );
			$email_actions[] = 'woocommerce_subscription_status_' . $slug;
			foreach ( $order_statuses as $_id => $_name ) {
				if ( $id != $_id ) {
					$_slug = substr( $_id, 3 );
					$email_actions[] = 'woocommerce_subscription_status_' . $slug . '_to_' . $_slug;
				}
			}
		}

		return $email_actions;
	}

	/**
	 * add_custom_emails.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function add_custom_emails( $emails ) {
		if ( ! class_exists( 'Alg_WC_Custom_Email_Order_Validator' ) ) {
			require_once( 'classes/class-alg-wc-custom-email-order-validator.php' );
		}
		if ( ! class_exists( 'Alg_WC_Custom_Email' ) ) {
			require_once( 'classes/class-alg-wc-custom-email.php' );
		}
		$emails['Alg_WC_Custom_Email'] = new Alg_WC_Custom_Email();
		return apply_filters( 'alg_wc_custom_emails_add', $emails );
	}

	/**
	 * process_content.
	 *
	 * @version 1.9.3
	 * @since   1.0.0
	 */
	function process_content( $content, $placeholders, $order, $user, $email ) {

		// Placeholders
		$content = str_replace( array_keys( $placeholders ), $placeholders, $content );

		// Shortcodes
		$this->shortcodes->order = false;
		$this->shortcodes->user  = false;
		$this->shortcodes->email = false;

		if ( is_a( $email, 'WC_Email' ) ) {
			$this->shortcodes->email = $email;
		}
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->shortcodes->order = $order;
		}
		if ( is_a( $user, 'WP_User' ) ) {
			$this->shortcodes->user = $user;
		}

		$content = do_shortcode( $content );

		$this->shortcodes->order = false;
		$this->shortcodes->user  = false;
		$this->shortcodes->email = false;

		// Final content
		return $content;

	}

	/**
	 * wrap_in_wc_email_template.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function wrap_in_wc_email_template( $content, $email_heading = '' ) {
		return $this->get_wc_email_template_part( 'header', $email_heading ) .
			$content .
		str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $this->get_wc_email_template_part( 'footer' ) );
	}

	/**
	 * get_wc_email_template_part.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_wc_email_template_part( $part, $email_heading = '' ) {
		ob_start();
		switch ( $part ) {
			case 'header':
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
				break;
			case 'footer':
				wc_get_template( 'emails/email-footer.php' );
				break;
		}
		return ob_get_clean();
	}

	/**
	 * get_trigger_groups.
	 *
	 * @version 1.5.3
	 * @since   1.5.0
	 *
	 * @todo    [next] [!] (dev) add `subscription_status` and `subscription_status_change` to the default value || remove this option entirely?
	 * @todo    [next] [!] (feature) `_switch_notification`
	 */
	function get_trigger_groups() {
		return array(

			'order_status'                => __( 'Order status updated to', 'custom-emails-for-woocommerce' ),
			'order_status_change'         => __( 'Order status updated from to', 'custom-emails-for-woocommerce' ),
			'new_order'                   => __( 'New order', 'custom-emails-for-woocommerce' ),

			'extra'                       => __( 'Extra', 'custom-emails-for-woocommerce' ),

			'subscription_status'         => __( 'Subscriptions', 'custom-emails-for-woocommerce' ) . ': ' . __( 'Subscription status updated to', 'custom-emails-for-woocommerce' ),
			'subscription_status_change'  => __( 'Subscriptions', 'custom-emails-for-woocommerce' ) . ': ' . __( 'Subscription status updated from to', 'custom-emails-for-woocommerce' ),

			'renewal_order_status'        => __( 'Subscriptions', 'custom-emails-for-woocommerce' ) . ': ' . __( 'Renewal order status updated to', 'custom-emails-for-woocommerce' ),
			'renewal_order_status_change' => __( 'Subscriptions', 'custom-emails-for-woocommerce' ) . ': ' . __( 'Renewal order status updated from to', 'custom-emails-for-woocommerce' ),
			'renewal_new_order'           => __( 'Subscriptions', 'custom-emails-for-woocommerce' ) . ': ' . __( 'Renewal new order', 'custom-emails-for-woocommerce' ),

		);
	}

}

endif;

return new Alg_WC_Custom_Emails_Core();
