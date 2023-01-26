<?php
/**
 * Custom Emails for WooCommerce - Custom Email Class
 *
 * @version 1.7.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Email' ) ) :

class Alg_WC_Custom_Email extends WC_Email {

	/**
	 * Constructor
	 *
	 * @version 1.7.2
	 * @since   1.0.0
	 */
	function __construct( $id = 1 ) {

		// Properties
		$this->alg_wc_ce_id       = $id;
		$this->id                 = ( 1 == $id ? 'alg_wc_custom' : "alg_wc_custom_{$id}" );
		$this->title              = alg_wc_custom_emails()->core->email_settings->get_title( $id );
		$this->description        = alg_wc_custom_emails()->core->email_settings->get_description( $id );
		$this->heading            = alg_wc_custom_emails()->core->email_settings->get_default_heading();
		$this->subject            = alg_wc_custom_emails()->core->email_settings->get_default_subject();
		$this->customer_email     = ( '%customer%' === $this->get_option( 'recipient' ) );
		$this->original_recipient = $this->get_option( 'recipient' );
		$this->delay              = $this->get_option( 'delay', 0 );
		$this->delay_unit         = $this->get_option( 'delay_unit', 1 );

		// Triggers for this email
		$this->hook_triggers();

		// Call parent constructor
		parent::__construct();

		// Recipient
		if ( ! $this->customer_email ) {
			$this->recipient = $this->get_option( 'recipient' );
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		// Admin actions
		add_action( 'woocommerce_update_options_email_' . $this->id, array( $this, 'admin_actions' ) );

	}

	/**
	 * admin_actions.
	 *
	 * @version 1.7.2
	 * @since   1.7.2
	 *
	 * @todo    [maybe] (dev) move this to another class/file?
	 */
	function admin_actions() {

		// Copy settings
		if ( 0 != ( $email_id_from = $this->get_option( 'copy_settings', 0 ) ) ) {
			$this->update_option( 'copy_settings', 0 );
			if ( ( $email_from = new Alg_WC_Custom_Email( $email_id_from ) ) ) {
				foreach ( $email_from->form_fields as $field_id => $field_data ) {
					if ( isset( $field_data['default'] ) ) {
						$this->update_option( $field_id, $email_from->get_option( $field_id, $field_data['default'] ) );
					}
				}
				$this->init_form_fields();
				if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
					WC_Admin_Settings::add_message( __( 'Your settings have been copied.', 'custom-emails-for-woocommerce' ) );
				}
			}
		}

		// Reset settings
		if ( 'yes' === $this->get_option( 'reset_settings', 'no' ) ) {
			$this->update_option( 'reset_settings', 'no' );
			foreach ( $this->form_fields as $field_id => $field_data ) {
				if ( isset( $field_data['default'] ) ) {
					$this->update_option( $field_id, $field_data['default'] );
				}
			}
			if ( method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
				WC_Admin_Settings::add_message( __( 'Your settings have been reset.', 'custom-emails-for-woocommerce' ) );
			}
		}

	}

	/**
	 * hook_triggers.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function hook_triggers() {
		$triggers = $this->get_option( 'trigger' );
		if ( ! empty( $triggers ) && is_array( $triggers ) ) {
			$is_new_order_hook_added = false;
			foreach ( $triggers as $trigger ) {
				if ( ! $is_new_order_hook_added && false !== strpos( $trigger, 'woocommerce_new_order_notification_' ) ) {
					add_action( 'woocommerce_checkout_order_processed_notification', array( $this, 'trigger' ), PHP_INT_MAX );
					$is_new_order_hook_added = true;
				} else {
					add_action( $trigger, array( $this, 'trigger' ), PHP_INT_MAX );
				}
			}
		}
	}

	/**
	 * validate_order.
	 *
	 * @version 1.7.0
	 * @since   1.6.0
	 *
	 * @see     https://wpml.org/faq/how-to-get-current-language-with-wpml/
	 * @see     https://wpml.org/wpml-hook/wpml_active_languages/
	 */
	function validate_order( $order ) {

		// Check filter
		if ( 'woocommerce_checkout_order_processed_notification' === current_filter() && ! $this->check_new_order_status( $order ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: New order: different status.', 'custom-emails-for-woocommerce' ),
				$this->title ) );
			return false;
		}

		// Language
		if ( apply_filters( 'wpml_active_languages', null ) ) {
			$required_wpml_languages = $this->get_option( 'required_wpml_languages', array() );
			if ( ! empty( $required_wpml_languages ) && ! in_array( apply_filters( 'wpml_current_language', null ), $required_wpml_languages ) ) {
				alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
					$this->title, __( 'Require WPML language', 'custom-emails-for-woocommerce' ) ) );
				return false;
			}
		}

		// Order products
		$required_order_product_ids = $this->get_option( 'required_order_product_ids', array() );
		if ( ! empty( $required_order_product_ids ) && ! $this->check_order_products( $order, $required_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Require order product(s)', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		$excluded_order_product_ids = $this->get_option( 'excluded_order_product_ids', array() );
		if ( ! empty( $excluded_order_product_ids ) &&   $this->check_order_products( $order, $excluded_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Exclude order product(s)', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}

		// Order product cats
		$required_order_product_cats_ids = $this->get_option( 'required_order_product_cats_ids', array() );
		if ( ! empty( $required_order_product_cats_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Require order product categories', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		$excluded_order_product_cats_ids = $this->get_option( 'excluded_order_product_cats_ids', array() );
		if ( ! empty( $excluded_order_product_cats_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Exclude order product categories', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}

		// Order product tags
		$required_order_product_tags_ids = $this->get_option( 'required_order_product_tags_ids', array() );
		if ( ! empty( $required_order_product_tags_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Require order product tags', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		$excluded_order_product_tags_ids = $this->get_option( 'excluded_order_product_tags_ids', array() );
		if ( ! empty( $excluded_order_product_tags_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Exclude order product tags', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}

		// Order amounts
		$min_order_amount = $this->get_option( 'min_order_amount', '' );
		if ( ! empty( $min_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $min_order_amount ) && $this->get_order_amount( $order ) < $min_order_amount ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Minimum order amount', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		$max_order_amount = $this->get_option( 'max_order_amount', '' );
		if ( ! empty( $max_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $max_order_amount ) && $this->get_order_amount( $order ) > $max_order_amount ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->title, __( 'Maximum order amount', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}

		// All passed
		return true;

	}

	/**
	 * check_new_order_status.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function check_new_order_status( $order ) {
		$triggers = $this->get_option( 'trigger' );
		if ( in_array( 'woocommerce_new_order_notification_alg_wc_ce_any', $triggers ) ) {
			return true;
		}
		foreach ( $triggers as $trigger ) {
			if ( false !== ( $pos = strpos( $trigger, 'woocommerce_new_order_notification_' ) ) ) {
				$status = 'wc-' . substr( $trigger, 35 );
				if ( $order->has_status( $status ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * check_order_products.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (feature) "require all products" (i.e., vs "require at least one")?
	 */
	function check_order_products( $order, $product_ids ) {
		foreach ( $order->get_items() as $item ) {
			if ( in_array( $item['product_id'], $product_ids ) || in_array( $item['variation_id'], $product_ids ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * check_order_product_terms.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @todo    [next] (feature) custom taxonomies
	 * @todo    [next] (feature) "require all" (i.e., vs "require at least one")?
	 */
	function check_order_product_terms( $order, $term_ids, $taxonomy ) {
		foreach ( $order->get_items() as $item ) {
			$_term_ids = get_the_terms( $item['product_id'], $taxonomy );
			$_term_ids = ( ! is_wp_error( $_term_ids ) ? wp_list_pluck( $_term_ids, 'term_id' ) : array() );
			if ( ! empty( array_intersect( $term_ids, $_term_ids ) ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * get_order_amount.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @see     https://woocommerce.github.io/code-reference/classes/WC-Order.html
	 *
	 * @todo    [next] (feature) total (vs subtotal), discounts, fees, taxes, shipping
	 */
	function get_order_amount( $order ) {
		return $order->get_subtotal();
	}

	/**
	 * is_equal_float.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (dev) better epsilon value?
	 */
	function is_equal_float( $float1, $float2 ) {
		return ( abs( $float1 - $float2 ) < ( defined( 'PHP_FLOAT_EPSILON' ) ? PHP_FLOAT_EPSILON : 0.000001 ) );
	}

	/**
	 * trigger.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function trigger( $object_id ) {
		$this->send_email( $object_id, false );
	}

	/**
	 * send_email.
	 *
	 * @version 1.6.0
	 * @since   1.3.0
	 *
	 * @todo    [next] [!] (dev) block (by products, amounts, etc.) only if it's not sent manually
	 * @todo    [next] (dev) "Order note": add "email delayed until..." note
	 * @todo    [next] (dev) "Order note": better description
	 * @todo    [next] (dev) `delay`: better debug info
	 * @todo    [next] (dev) `delay`: `wp_next_scheduled()`?
	 * @todo    [next] (dev) `delay`: add `current_filter()` to the args?
	 * @todo    [next] (dev) `$this->object = $user;`?
	 * @todo    [next] (dev) check if it's already sent for the current `$object_id`?
	 * @todo    [next] (dev) `debug`: add more info?
	 */
	function send_email( $object_id, $do_force_send, $note = '' ) {

		// Debug
		alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Triggered.', 'custom-emails-for-woocommerce' ), $this->title ) );

		// Check if it's enabled
		if ( ! $this->is_enabled() || ! apply_filters( 'alg_wc_custom_emails_is_enabled', true, $this, $object_id ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Disabled.', 'custom-emails-for-woocommerce' ), $this->title ) );
			return;
		}

		// Delay
		if ( ! $do_force_send && ! empty( $this->delay ) ) {
			$class = str_replace( 'alg_wc_custom', 'Alg_WC_Custom_Email', $this->id );
			$delay = intval( $this->delay * $this->delay_unit );
			wp_schedule_single_event( time() + $delay, 'alg_wc_custom_emails_send_email', array( $class, $object_id ) );
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Delayed (%s): In %d seconds.', 'custom-emails-for-woocommerce' ), $this->title, $class, $delay ) );
			return;
		}

		// Send email
		$order = false;
		$user  = false;
		if ( $object_id ) {

			if ( 'woocommerce_created_customer_notification' === current_filter() || apply_filters( 'alg_wc_custom_emails_is_user_email', false ) ) {

				// User email
				$user            = get_user_by( 'ID', $object_id );
				$this->recipient = $user->user_email;

			} else {

				// Order email
				$order = wc_get_order( $object_id );
				if ( is_a( $order, 'WC_Order' ) ) {

					// Setting object (must be named `object` as it's named so in the parent class (`WC_Email`), e.g., for attachments)
					$this->object = $order;

					// Debug
					alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Order #%s.', 'custom-emails-for-woocommerce' ), $this->title, $order->get_id() ) );

					// Validate order
					if ( ! $this->validate_order( $order ) ) {
						return;
					}

					// Placeholders
					$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
					$this->placeholders['{order_number}'] = $order->get_order_number();

					// Recipient
					if ( $this->customer_email ) {
						$this->recipient = $order->get_billing_email();
					} elseif ( false !== strpos( $this->original_recipient, '%customer%' ) ) {
						$this->recipient = str_replace( '%customer%', $order->get_billing_email(), $this->original_recipient );
					}

					// Order note
					$order_note = sprintf( esc_html__( 'Sending "%s" email.', 'custom-emails-for-woocommerce' ), $this->get_title() ) .
						( '' != $note ? ' ' . sprintf( esc_html__( 'Description: %s.', 'custom-emails-for-woocommerce' ), $note ) : '' );
					$order->add_order_note( $order_note );

				}

			}
		}

		// Send
		$res = $this->send(
			$this->get_recipient(),
			$this->get_processed_subject( $order, $user ),
			$this->get_processed_content( $order, $user ),
			$this->get_headers(),
			$this->get_attachments()
		);

		// Debug
		alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Sent: %s', 'custom-emails-for-woocommerce' ),
			$this->title, ( $res ? __( 'success', 'custom-emails-for-woocommerce' ) : __( 'failed', 'custom-emails-for-woocommerce' ) ) ) );

	}

	/**
	 * get_processed_subject.
	 *
	 * @version 1.3.1
	 * @since   1.0.0
	 */
	function get_processed_subject( $order, $user ) {
		return alg_wc_custom_emails()->core->process_content( $this->get_subject(), $this->placeholders, $order, $user );
	}

	/**
	 * get_processed_content.
	 *
	 * @version 1.3.1
	 * @since   1.0.0
	 *
	 * @todo    [later] optional `wpautop()`
	 */
	function get_processed_content( $order, $user ) {
		return alg_wc_custom_emails()->core->process_content( $this->get_content(), $this->placeholders, $order, $user );
	}

	/**
	 * get_content_html.
	 *
	 * @version 1.3.1
	 * @since   1.0.0
	 */
	function get_content_html() {
		$content = $this->get_option( 'content' );
		if ( 'yes' === $this->get_option( 'wrap_in_wc_template', 'yes' ) ) {
			$content = alg_wc_custom_emails()->core->wrap_in_wc_email_template( $content, $this->get_heading() );
		}
		return $content;
	}

	/**
	 * get_content_plain.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_content_plain() {
		return $this->get_option( 'content' );
	}

	/**
	 * Initialise settings form fields.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function init_form_fields() {
		$this->form_fields = alg_wc_custom_emails()->core->email_settings->get_form_fields( $this );
	}

}

endif;
