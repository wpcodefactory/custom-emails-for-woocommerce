<?php
/**
 * Custom Emails for WooCommerce - Custom Email Class
 *
 * @version 2.7.1
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Email' ) ) :

class Alg_WC_Custom_Email extends WC_Email {

	/**
	 * alg_wc_ce_id.
	 *
	 * @version 2.7.1
	 */
	public $alg_wc_ce_id;

	/**
	 * alg_wc_ce_original_recipient.
	 *
	 * @version 2.7.1
	 */
	public $alg_wc_ce_original_recipient;

	/**
	 * alg_wc_ce_delay.
	 *
	 * @version 2.7.1
	 */
	public $alg_wc_ce_delay;

	/**
	 * alg_wc_ce_delay_unit.
	 *
	 * @version 2.7.1
	 */
	public $alg_wc_ce_delay_unit;

	/**
	 * alg_wc_ce_order_validator.
	 *
	 * @version 2.7.1
	 */
	public $alg_wc_ce_order_validator;

	/**
	 * Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @see     https://woocommerce.github.io/code-reference/classes/WC-Email.html
	 */
	function __construct( $id = 1 ) {

		// `WC_Email` properties
		$this->id                 = ( 1 == $id ? 'alg_wc_custom' : "alg_wc_custom_{$id}" );
		$this->title              = alg_wc_custom_emails()->core->email_settings->get_title( $id );
		$this->description        = alg_wc_custom_emails()->core->email_settings->get_description( $id );
		$this->heading            = alg_wc_custom_emails()->core->email_settings->get_default_heading();
		$this->subject            = alg_wc_custom_emails()->core->email_settings->get_default_subject();
		$this->customer_email     = ( '%customer%' === $this->get_option( 'recipient' ) );

		// `Alg_WC_Custom_Email` properties
		$this->alg_wc_ce_id                 = $id;
		$this->alg_wc_ce_original_recipient = $this->get_option( 'recipient' );
		$this->alg_wc_ce_delay              = $this->get_option( 'delay', 0 );
		$this->alg_wc_ce_delay_unit         = $this->get_option( 'delay_unit', 1 );
		$this->alg_wc_ce_order_validator    = new Alg_WC_Custom_Email_Order_Validator( $this );

		// Triggers for this email
		$this->alg_wc_ce_hook_triggers();

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
		add_action( 'woocommerce_update_options_email_' . $this->id, array( $this, 'alg_wc_ce_admin_settings_tools' ) );

	}

	/**
	 * Get email attachments.
	 *
	 * @version 2.2.7
	 * @since   1.9.2
	 *
	 * @return  array
	 */
	function get_attachments() {

		// Attachments
		$attachments     = array();
		$raw_attachments = $this->get_option( 'attachments', '' );
		$raw_attachments = array_filter( array_map( 'trim', explode( PHP_EOL, $raw_attachments ) ) );
		foreach ( $raw_attachments as $attachment ) {
			$attachments[] = alg_wc_custom_emails()->core->get_base_dir() . $attachment;
		}

		// Debug
		if ( alg_wc_custom_emails()->core->do_debug ) {
			foreach ( $attachments as $attachment ) {
				$this->alg_wc_ce_debug( sprintf( __( 'Email attachment: %s [%s].', 'custom-emails-for-woocommerce' ),
					$attachment,
					( file_exists( $attachment ) ?
						__( 'ok', 'custom-emails-for-woocommerce' ) :
						__( 'does not exist!', 'custom-emails-for-woocommerce' ) )
				) );
			}
		}

		// Apply filters
		return apply_filters( 'woocommerce_email_attachments', $attachments, $this->id, $this->object, $this );

	}

	/**
	 * alg_wc_ce_do_add_header_and_footer.
	 *
	 * @version 2.4.0
	 * @since   2.4.0
	 */
	function alg_wc_ce_do_add_header_and_footer() {
		return ( 'yes' === $this->get_option( 'wrap_in_wc_template', 'yes' ) );
	}

	/**
	 * get_content_html.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	function get_content_html() {
		return wc_get_template_html(
			'emails/alg-wc-custom-email.php',
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'content'       => $this->get_option( 'content' ),
				'plain_text'    => false,
				'email'         => $this,
			)
		);
	}

	/**
	 * get_content_plain.
	 *
	 * @version 2.4.0
	 * @since   1.0.0
	 */
	function get_content_plain() {
		return wc_get_template_html(
			'emails/plain/alg-wc-custom-email.php',
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'content'       => $this->get_option( 'content' ),
				'plain_text'    => true,
				'email'         => $this,
			)
		);
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

	/**
	 * alg_wc_ce_admin_settings_tools.
	 *
	 * @version 2.0.0
	 * @since   1.7.2
	 *
	 * @todo    (dev) move this to another class/file?
	 */
	function alg_wc_ce_admin_settings_tools() {

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
	 * alg_wc_ce_hook_triggers.
	 *
	 * @version 2.6.0
	 * @since   1.0.0
	 */
	function alg_wc_ce_hook_triggers() {
		$triggers = $this->get_option( 'trigger' );
		if ( ! empty( $triggers ) && is_array( $triggers ) ) {
			$is_new_order_hook_added = false;
			foreach ( $triggers as $trigger ) {
				if ( ! $is_new_order_hook_added && false !== strpos( $trigger, 'woocommerce_new_order_notification_' ) ) {
					$trigger                 = 'woocommerce_checkout_order_processed_notification';
					$is_new_order_hook_added = true;
				}
				add_action( $trigger, array( $this, 'alg_wc_ce_trigger' ), PHP_INT_MAX );
			}
		}
	}

	/**
	 * alg_wc_ce_trigger.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function alg_wc_ce_trigger( $object_id ) {
		$this->alg_wc_ce_send_email( $object_id, false );
	}

	/**
	 * schedule_single.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 *
	 * @see     https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
	 * @see     https://actionscheduler.org/api/
	 *
	 * @todo    (dev) `alg_wc_custom_emails_scheduler`: default to `as`
	 */
	function schedule_single( $timestamp, $hook, $args ) {
		$scheduler = get_option( 'alg_wc_custom_emails_scheduler', 'wp_cron' );
		if ( 'wp_cron' === $scheduler ) {
			wp_schedule_single_event( $timestamp, $hook, $args );
		} elseif ( 'as' === $scheduler ) {
			as_schedule_single_action( $timestamp, $hook, $args );
		}
	}

	/**
	 * alg_wc_ce_send_email.
	 *
	 * @version 2.7.1
	 * @since   1.3.0
	 *
	 * @todo    (dev) `wc_get_product( $object_id )`: better solution, e.g., use `current_filter()`?
	 * @todo    (dev) `$order`, `$user`, `$product`: use `$data = array( 'order' => x, 'user' => y, 'product' => z )` instead?
	 * @todo    (dev) [!] block (by products, amounts, etc.) only if it's not sent manually
	 * @todo    (dev) "Order note": add "email delayed until..." note
	 * @todo    (dev) "Order note": better description
	 * @todo    (dev) `delay`: better debug info
	 * @todo    (dev) `delay`: `wp_next_scheduled()`?
	 * @todo    (dev) `delay`: add `current_filter()` to the args?
	 * @todo    (dev) `$this->object = $user;`?
	 * @todo    (dev) check if it's already sent for the current `$object_id`?
	 * @todo    (dev) `debug`: add more info?
	 */
	function alg_wc_ce_send_email( $object_id, $do_force_send, $note = '' ) {

		// Debug
		$this->alg_wc_ce_debug( __( 'Triggered.', 'custom-emails-for-woocommerce' ) );

		// Check if it's enabled
		if ( ! $this->is_enabled() || ! apply_filters( 'alg_wc_custom_emails_is_enabled', true, $this, $object_id, $do_force_send ) ) {
			$this->alg_wc_ce_debug( __( 'Disabled.', 'custom-emails-for-woocommerce' ) );
			return;
		}

		// Delay
		if ( ! $do_force_send && ! empty( $this->alg_wc_ce_delay ) ) {
			$class = str_replace( 'alg_wc_custom', 'Alg_WC_Custom_Email', $this->id );
			$delay = intval( $this->alg_wc_ce_delay * $this->alg_wc_ce_delay_unit );
			$this->schedule_single( time() + $delay, 'alg_wc_custom_emails_send_email', array( $class, $object_id ) );
			$this->alg_wc_ce_debug( sprintf( __( 'Delayed (%s): In %d seconds.', 'custom-emails-for-woocommerce' ), $class, $delay ) );
			return;
		}

		// Email
		$order   = false;
		$user    = false;
		$product = false;
		if ( $object_id ) {

			if (
				'woocommerce_created_customer_notification' === current_filter() ||
				apply_filters( 'alg_wc_custom_emails_is_user_email', false, current_filter() )
			) {

				// User email
				$user            = get_user_by( 'ID', $object_id );
				$this->recipient = $user->user_email;

			} elseif ( ( $_product = wc_get_product( $object_id ) ) && is_a( $_product, 'WC_Product' ) ) {

				// Product email
				$product = $_product;

				// Debug
				$this->alg_wc_ce_debug( sprintf( __( 'Product #%s.', 'custom-emails-for-woocommerce' ), $product->get_id() ) );

			} elseif ( ( $_order = wc_get_order( $object_id ) ) && is_a( $_order, 'WC_Order' ) ) {

				// Order email
				$order = $_order;

				// Setting object (must be named `object` as it's named so in the parent class (`WC_Email`), e.g., for attachments)
				$this->object = $order;

				// Debug
				$this->alg_wc_ce_debug( sprintf( __( 'Order #%s.', 'custom-emails-for-woocommerce' ), $order->get_id() ) );

				// Filter
				if ( ! apply_filters( 'alg_wc_custom_emails_do_send_order_email', true, $this, $order ) ) {
					$this->alg_wc_ce_debug( sprintf( __( 'Blocked by the "%s" filter.', 'custom-emails-for-woocommerce' ),
						'alg_wc_custom_emails_do_send_order_email' ) );
					return;
				}

				// Validate order
				if ( ! $this->alg_wc_ce_order_validator->validate( $order ) ) {
					return;
				}

				// Placeholders
				$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
				$this->placeholders['{order_number}'] = $order->get_order_number();

				// Recipient
				if ( $this->customer_email ) {
					$this->recipient = $order->get_billing_email();
				} elseif ( false !== strpos( $this->alg_wc_ce_original_recipient, '%customer%' ) ) {
					$this->recipient = str_replace( '%customer%', $order->get_billing_email(), $this->alg_wc_ce_original_recipient );
				}

				// Order note
				$order_note = sprintf( esc_html__( 'Sending "%s" email.', 'custom-emails-for-woocommerce' ), $this->get_title() ) .
					( '' != $note ? ' ' . sprintf( esc_html__( 'Description: %s.', 'custom-emails-for-woocommerce' ), $note ) : '' );
				$order->add_order_note( $order_note );

			}

		}

		// Send
		if ( $this->alg_wc_ce_do_send() ) {

			$res = $this->send(
				$this->get_recipient(),
				$this->alg_wc_ce_get_processed_subject( $order, $user, $product ),
				$this->alg_wc_ce_get_style() . $this->alg_wc_ce_get_processed_content( $order, $user, $product ),
				$this->get_headers(),
				$this->get_attachments()
			);

			// Debug
			$this->alg_wc_ce_debug( sprintf( __( 'Sent: %s', 'custom-emails-for-woocommerce' ),
				( $res ? __( 'success', 'custom-emails-for-woocommerce' ) : __( 'failed', 'custom-emails-for-woocommerce' ) ) ) );

		}

	}

	/**
	 * alg_wc_ce_get_style.
	 *
	 * @version 2.7.1
	 * @since   2.7.1
	 */
	function alg_wc_ce_get_style() {
		return ( ( $style = $this->get_option( 'alg_wc_ce_style', '' ) ) ? "<style>$style</style>" : '' );
	}

	/**
	 * alg_wc_ce_do_send.
	 *
	 * @version 2.0.0
	 * @since   1.9.6
	 */
	function alg_wc_ce_do_send() {

		// Filter
		if ( ! apply_filters( 'alg_wc_custom_emails_do_send', true, $this ) ) {
			$this->alg_wc_ce_debug( sprintf( __( 'Blocked by the "%s" filter.', 'custom-emails-for-woocommerce' ),
				'alg_wc_custom_emails_do_send' ) );
			return false;
		}

		// Exclude recipients
		if ( '' !== ( $exclude_recipients = $this->get_option( 'exclude_recipients', '' ) ) ) {
			$exclude_recipients = array_filter( array_map( 'trim', explode( ',', str_replace( PHP_EOL, ',', $exclude_recipients ) ) ) );
			foreach ( $exclude_recipients as $exclude_recipient ) {
				if ( $exclude_recipient === $this->get_recipient() || $this->alg_wc_ce_wildcard_match( $exclude_recipient, $this->get_recipient() ) ) {
					$this->alg_wc_ce_debug( sprintf( __( 'Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
						__( 'Exclude recipients', 'custom-emails-for-woocommerce' ) ) );
					return false;
				}
			}
		}

		// All checks passed
		return true;

	}

	/**
	 * alg_wc_ce_wildcard_match.
	 *
	 * @version 1.9.6
	 * @since   1.9.6
	 */
	function alg_wc_ce_wildcard_match( $pattern, $subject ) {
		$pattern = strtr( $pattern, array(
			'*' => '.*?', // 0 or more (lazy) - asterisk (*)
			'?' => '.',   // 1 character - question mark (?)
		) );
		return preg_match( "/$pattern/", $subject );
	}

	/**
	 * alg_wc_ce_get_processed_subject.
	 *
	 * @version 2.6.0
	 * @since   1.0.0
	 */
	function alg_wc_ce_get_processed_subject( $order, $user, $product ) {
		$subject = alg_wc_custom_emails()->core->process_content( $this->get_subject(), $this->placeholders, $order, $user, $product, $this );
		return apply_filters( 'alg_wc_custom_emails_subject', $subject, $this, $order, $user, $product );
	}

	/**
	 * alg_wc_ce_get_processed_content.
	 *
	 * @version 2.6.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) optional `wpautop()`
	 */
	function alg_wc_ce_get_processed_content( $order, $user, $product ) {
		$content = alg_wc_custom_emails()->core->process_content( $this->get_content(), $this->placeholders, $order, $user, $product, $this );
		return apply_filters( 'alg_wc_custom_emails_content', $content, $this, $order, $user, $product );
	}

	/**
	 * alg_wc_ce_debug.
	 *
	 * @version 1.9.6
	 * @since   1.9.6
	 */
	function alg_wc_ce_debug( $message ) {
		alg_wc_custom_emails()->core->debug( sprintf( '%s: %s', $this->title, $message ) );
	}

}

endif;
