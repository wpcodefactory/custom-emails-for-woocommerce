<?php
/**
 * Custom Emails for WooCommerce - Admin Class
 *
 * @version 1.8.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Emails_Admin' ) ) :

class Alg_WC_Custom_Emails_Admin {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function __construct() {

		// Admin core
		if ( 'yes' === get_option( 'alg_wc_custom_emails_plugin_enabled', 'yes' ) ) {

			// Edit order > Order actions (dropdown)
			add_filter( 'woocommerce_order_actions',                         array( $this, 'add_order_actions' ), PHP_INT_MAX );
			add_action( 'woocommerce_order_action_alg_wc_send_email_custom', array( $this, 'do_order_actions' ), PHP_INT_MAX );

			// Orders > Bulk actions (dropdown)
			add_filter( 'bulk_actions-edit-shop_order',                      array( $this, 'add_order_actions_bulk' ), 20, 1 );
			add_filter( 'handle_bulk_actions-edit-shop_order',               array( $this, 'do_order_actions_bulk' ), 10, 3 );
			add_action( 'admin_notices',                                     array( $this, 'bulk_action_admin_notice' ) );

			// Content template script
			add_action( 'admin_footer',                                      array( $this, 'add_content_template_script' ) );

		}

		// Admin core loaded
		do_action( 'alg_wc_custom_emails_admin_core_loaded', $this );

	}

	/**
	 * add_content_template_script.
	 *
	 * @version 1.4.1
	 * @since   1.3.1
	 *
	 * @todo    [next] (dev) more templates
	 * @todo    [next] (dev) move this to a separate JS file?
	 */
	function add_content_template_script() {
		if (
			isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) &&
			'wc-settings' === wc_clean( $_GET['page'] ) && 'email' === wc_clean( $_GET['tab'] ) && 'alg_wc_custom_email' === substr( wc_clean( $_GET['section'] ), 0, 19 )
		) {
			$email_id = str_replace( 'alg_wc_custom_email', '', wc_clean( $_GET['section'] ) );
			?><script>
				jQuery( document ).ready( function() {
					var templates = [
						"[order_details]\n" +
						"<table>\n" +
						"    <tbody>\n" +
						"        <tr><th>Billing address</th><th>Shipping address</th></tr>\n" +
						"        <tr><td>[order_billing_address]</td><td>[order_shipping_address]</td></tr>\n" +
						"    </tbody>\n" +
						"</table>",
					];
					jQuery( '#alg_wc_custom_emails_content_template_0' ).click( function( event ) {
						jQuery( '#woocommerce_alg_wc_custom<?php echo esc_attr( $email_id ); ?>_content' ).val( templates[0] );
						return false;
					} );
				} );
			</script><?php
		}
	}

	/**
	 * do_order_actions_bulk.
	 *
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	function do_order_actions_bulk( $redirect_to, $action, $post_ids ) {
		if ( 'alg_wc_send_email_custom' === $action ) {
			foreach ( $post_ids as $post_id ) {
				$order = wc_get_order( $post_id );
				$this->send_order_email( 'Alg_WC_Custom_Email', $order, __( 'bulk actions', 'custom-emails-for-woocommerce' ) );
			}
			$redirect_to = add_query_arg( array( 'alg_wc_send_email_custom_count' => count( $post_ids ), 'alg_wc_send_email_custom_num' => '1' ), $redirect_to );
		}
		return $redirect_to;
	}

	/**
	 * bulk_action_admin_notice.
	 *
	 * @version 1.4.1
	 * @since   1.2.1
	 */
	function bulk_action_admin_notice() {
		if ( isset( $_REQUEST['alg_wc_send_email_custom_count'], $_REQUEST['alg_wc_send_email_custom_num'] ) ) {
			$email = apply_filters( 'alg_wc_custom_emails_class', 'Alg_WC_Custom_Email', intval( $_REQUEST['alg_wc_send_email_custom_num'] ) );
			$count = intval( $_REQUEST['alg_wc_send_email_custom_count'] );
			echo '<div class="notice notice-success is-dismissible"><p>' .
				sprintf( esc_html__( 'Emails: "%s" sent for %d orders.', 'custom-emails-for-woocommerce' ), WC()->mailer()->emails[ $email ]->get_title(), $count ) .
			'</p></div>';
		}
	}

	/**
	 * send_order_email.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) remove this?
	 */
	function send_order_email( $email, $order, $note ) {
		alg_wc_custom_emails()->core->send_email( $email, $order->get_id(), $note );
	}

	/**
	 * do_order_actions.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 */
	function do_order_actions( $order ) {
		$this->send_order_email( 'Alg_WC_Custom_Email', $order, __( 'manually', 'custom-emails-for-woocommerce' ) );
	}

	/**
	 * add_order_actions.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function add_order_actions( $actions ) {
		if (
			( $wc_emails = WC_Emails::instance() ) &&
			isset( $wc_emails->emails['Alg_WC_Custom_Email'] ) &&
			$wc_emails->emails['Alg_WC_Custom_Email'] instanceof WC_Email &&
			in_array( 'order_actions_single', $wc_emails->emails['Alg_WC_Custom_Email']->get_option( 'admin_actions', array( 'order_actions_single', 'order_actions_bulk' ) ) )
		) {
			$actions['alg_wc_send_email_custom'] = sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title() );
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions', $actions );
	}

	/**
	 * add_order_actions_bulk.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function add_order_actions_bulk( $actions ) {
		if (
			( $wc_emails = WC_Emails::instance() ) &&
			isset( $wc_emails->emails['Alg_WC_Custom_Email'] ) &&
			$wc_emails->emails['Alg_WC_Custom_Email'] instanceof WC_Email &&
			in_array( 'order_actions_bulk', $wc_emails->emails['Alg_WC_Custom_Email']->get_option( 'admin_actions', array( 'order_actions_single', 'order_actions_bulk' ) ) )
		) {
			$actions['alg_wc_send_email_custom'] = sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title() );
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_bulk', $actions );
	}

}

endif;

return new Alg_WC_Custom_Emails_Admin();
