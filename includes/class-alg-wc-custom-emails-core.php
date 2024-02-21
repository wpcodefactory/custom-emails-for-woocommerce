<?php
/**
 * Custom Emails for WooCommerce - Core Class
 *
 * @version 2.6.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Core' ) ) :

class Alg_WC_Custom_Emails_Core {

	/**
	 * do_debug.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $do_debug;

	/**
	 * email_settings.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $email_settings;

	/**
	 * shortcodes.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $shortcodes;

	/**
	 * Constructor.
	 *
	 * @version 2.6.0
	 * @since   1.0.0
	 *
	 * @todo    (feature) option to conditionally disable some standard WC emails (e.g., "order completed" email, etc.)?
	 */
	function __construct() {

		// Properties
		$this->do_debug       = ( 'yes' === get_option( 'alg_wc_custom_emails_debug_enabled', 'no' ) );
		$this->email_settings = require_once( 'settings/class-alg-wc-custom-email-settings.php' );
		$this->shortcodes     = require_once( 'class-alg-wc-custom-emails-shortcodes.php' );

		// Hooks
		add_filter( 'woocommerce_email_classes', array( $this, 'add_custom_emails' ) );
		add_filter( 'woocommerce_email_actions', array( $this, 'add_custom_email_trigger_actions' ) );

		// Delayed emails
		add_action( 'alg_wc_custom_emails_send_email', array( $this, 'send_delayed_email' ), 10, 2 );

		// Templates
		add_filter( 'woocommerce_locate_template', array( $this, 'locate_template' ), 10, 3 );

		// Product published
		add_action( 'transition_post_status', array( $this, 'alg_wc_ce_product_published' ), 10, 3 );

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
	 * alg_wc_ce_product_published.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 *
	 * @see     https://developer.wordpress.org/reference/hooks/transition_post_status/
	 *
	 * @todo    (dev) run this only if `alg_wc_ce_product_published` is in `$email->get_option( 'trigger' )` for at least one of the emails?
	 */
	function alg_wc_ce_product_published( $new_status, $old_status, $post ) {
		if ( 'product' === $post->post_type && 'publish' === $new_status && 'publish' !== $old_status ) {
			do_action( 'alg_wc_ce_product_published', $post->ID );
		}
	}

	/**
	 * locate_template.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function locate_template( $template, $template_name, $template_path ) {
		if (
			in_array( $template_name, array( 'emails/alg-wc-custom-email.php', 'emails/plain/alg-wc-custom-email.php' ) ) &&
			! file_exists( $template )
		) {
			return alg_wc_custom_emails()->plugin_path() . '/templates/' . $template_name;
		}
		return $template;
	}

	/**
	 * get_base_dir.
	 *
	 * E.g., for attachments.
	 *
	 * @version 2.2.7
	 * @since   2.2.7
	 */
	function get_base_dir() {
		$option = get_option( 'alg_wc_custom_emails_base_dir', 'abspath' );
		switch ( $option ) {
			case 'wp_upload_dir':
				$dir = wp_upload_dir();
				$res = $dir['basedir'];
				break;
			default: // 'abspath'
				$res = ABSPATH;
		}
		return apply_filters( 'alg_wc_custom_emails_base_dir', trailingslashit( $res ) );
	}

	/**
	 * get_base_dir_example.
	 *
	 * Used in the settings.
	 *
	 * @version 2.2.7
	 * @since   2.2.7
	 */
	function get_base_dir_example() {
		$option = get_option( 'alg_wc_custom_emails_base_dir', 'abspath' );
		switch ( $option ) {
			case 'wp_upload_dir':
				return 'example.pdf';
			default: // 'abspath'
				return 'wp-content/uploads/example.pdf';
		}
	}

	/**
	 * send_delayed_email.
	 *
	 * @version 1.4.1
	 * @since   1.3.0
	 *
	 * @todo    (dev) better debug info
	 */
	function send_delayed_email( $email, $object_id ) {
		$this->debug( sprintf( esc_html__( '%s: Sending delayed email.', 'custom-emails-for-woocommerce' ), $email ) );
		$this->send_email( $email, $object_id, __( 'delayed', 'custom-emails-for-woocommerce' ) );
	}

	/**
	 * send_email.
	 *
	 * @version 2.0.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) what's with `WC()->payment_gateways()` and `WC()->shipping()`?
	 */
	function send_email( $email, $object_id, $note = '' ) {
		WC()->payment_gateways();
		WC()->shipping();
		if ( ! empty( WC()->mailer()->emails[ $email ] ) ) {
			WC()->mailer()->emails[ $email ]->alg_wc_ce_send_email( $object_id, true, $note );
		}
	}

	/**
	 * add_custom_email_trigger_actions.
	 *
	 * @version 2.6.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) [!] maybe we need to add "Subscriptions: Renewals" here (`'woocommerce_order_status_' . $slug . '_renewal'`, `'woocommerce_order_status_' . $slug . '_to_' . $_slug . '_renewal'`)?
	 */
	function add_custom_email_trigger_actions( $email_actions ) {

		// Checkout order processed (new order)
		$email_actions[] = 'woocommerce_checkout_order_processed';

		// Order statuses
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

		// Products
		$email_actions[] = 'alg_wc_ce_product_published';
		$email_actions[] = 'woocommerce_update_product';

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

		// Custom triggers
		$custom_triggers = $this->get_custom_triggers();
		if ( ! empty( $custom_triggers ) ) {
			$email_actions = array_merge( $email_actions, array_keys( $custom_triggers ) );
		}

		// Final email actions
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
	 * @version 2.6.0
	 * @since   1.0.0
	 */
	function process_content( $content, $placeholders, $order, $user, $product, $email ) {

		// Placeholders
		$content = str_replace( array_keys( $placeholders ), $placeholders, $content );

		// Shortcodes
		$this->shortcodes->order   = false;
		$this->shortcodes->user    = false;
		$this->shortcodes->product = false;
		$this->shortcodes->email   = false;

		if ( is_a( $email, 'WC_Email' ) ) {
			$this->shortcodes->email = $email;
		}
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->shortcodes->order = $order;
		}
		if ( is_a( $user, 'WP_User' ) ) {
			$this->shortcodes->user = $user;
		}
		if ( is_a( $product, 'WC_Product' ) ) {
			$this->shortcodes->product = $product;
		}

		$content = do_shortcode( $content );

		$this->shortcodes->order   = false;
		$this->shortcodes->user    = false;
		$this->shortcodes->product = false;
		$this->shortcodes->email   = false;

		// Final content
		return $content;

	}

	/**
	 * get_trigger_groups.
	 *
	 * @version 1.5.3
	 * @since   1.5.0
	 *
	 * @todo    (dev) [!] add `subscription_status` and `subscription_status_change` to the default value || remove this option entirely?
	 * @todo    (feature) [!] `_switch_notification`
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

	/**
	 * get_custom_triggers.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function get_custom_triggers() {
		$res = array();
		$custom_triggers = array_map( 'trim', explode( PHP_EOL, get_option( 'alg_wc_custom_emails_custom_triggers', '' ) ) );
		foreach ( $custom_triggers as $custom_trigger ) {
			$custom_trigger = array_map( 'trim', explode( '|', $custom_trigger, 2 ) );
			$res[ $custom_trigger[0] ] = ( isset( $custom_trigger[1] ) ? $custom_trigger[1] : $custom_trigger[0] );
		}
		return $res;
	}

}

endif;

return new Alg_WC_Custom_Emails_Core();
