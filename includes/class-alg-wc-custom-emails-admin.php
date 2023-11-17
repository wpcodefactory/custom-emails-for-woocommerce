<?php
/**
 * Custom Emails for WooCommerce - Admin Class
 *
 * @version 2.3.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Admin' ) ) :

class Alg_WC_Custom_Emails_Admin {

	/**
	 * Constructor.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	function __construct() {

		// Edit order > Order actions (dropdown)
		add_filter( 'woocommerce_order_actions',                         array( $this, 'add_order_actions' ), PHP_INT_MAX );
		add_action( 'woocommerce_order_action_alg_wc_send_email_custom', array( $this, 'do_order_actions' ), PHP_INT_MAX );

		// Orders > Bulk actions (dropdown)
		add_filter( 'bulk_actions-edit-shop_order',                   array( $this, 'add_order_actions_bulk' ), 20 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders',        array( $this, 'add_order_actions_bulk' ), 20 );
		add_filter( 'handle_bulk_actions-edit-shop_order',            array( $this, 'do_order_actions_bulk' ), 10, 3 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'do_order_actions_bulk' ), 10, 3 );
		add_action( 'admin_notices',                                  array( $this, 'bulk_action_admin_notice' ) );

		// Orders > Preview
		add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'add_order_actions_preview' ), 10, 2 );
		add_filter( 'admin_init',                              array( $this, 'do_order_actions_preview' ) );

		// Orders > Actions (column)
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_order_actions_column' ), 10, 2 );
		add_filter( 'admin_init',                      array( $this, 'do_order_actions_column' ) );
		add_action( 'admin_footer',                    array( $this, 'order_actions_column_icon_style' ) );

		// Content template script
		add_action( 'admin_footer', array( $this, 'add_content_template_script' ) );

		// Unschedule email
		add_action( 'wp_loaded',    array( $this, 'unschedule_email' ) );
		add_action( 'admin_footer', array( $this, 'unschedule_email_confirm_js' ) );

		// Admin core loaded
		do_action( 'alg_wc_custom_emails_admin_core_loaded', $this );

	}

	/**
	 * unschedule_email_confirm_js.
	 *
	 * @version 1.9.5
	 * @since   1.9.5
	 *
	 * @todo    (dev) load only on `page=wc-settings&tab=alg_wc_custom_emails&section=scheduled`
	 * @todo    (dev) `#content`?
	 */
	function unschedule_email_confirm_js() {
		?><script>
			jQuery( document ).ready( function () {
				jQuery( '.alg-wc-custom-emails-unschedule' ).on( 'click', function ( e ) {
					if ( confirm( <?php echo "'" . esc_html__( 'Are you sure?', 'custom-emails-for-woocommerce' ) . "'"; ?> ) ) {
						var url = jQuery( this ).attr( 'href' );
						jQuery( '#content' ).load( url );
					} else {
						e.preventDefault();
					}
				} );
			} );
		</script><?php
	}

	/**
	 * unschedule_email.
	 *
	 * @version 1.9.5
	 * @since   1.9.5
	 *
	 * @todo    (dev) add success/error notice
	 */
	function unschedule_email() {
		if ( isset( $_REQUEST['alg_wc_ce_unschedule'], $_REQUEST['alg_wc_ce_unschedule_object_id'], $_REQUEST['alg_wc_ce_unschedule_time'], $_REQUEST['_wpnonce'] ) ) {

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Invalid user role.', 'custom-emails-for-woocommerce' ) );
			}

			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'] ) ) {
				wp_die( esc_html__( 'Invalid nonce.', 'custom-emails-for-woocommerce' ) );
			}

			$class     = wc_clean( $_REQUEST['alg_wc_ce_unschedule'] );
			$object_id = intval( $_REQUEST['alg_wc_ce_unschedule_object_id'] );
			$time      = intval( $_REQUEST['alg_wc_ce_unschedule_time'] );

			wp_unschedule_event( $time, 'alg_wc_custom_emails_send_email', array( $class, $object_id ) );

			wp_safe_redirect( remove_query_arg( array( 'alg_wc_ce_unschedule', 'alg_wc_ce_unschedule_object_id', 'alg_wc_ce_unschedule_time', '_wpnonce' ) ) );
			exit;

		}
	}

	/**
	 * add_content_template_script.
	 *
	 * @version 1.4.1
	 * @since   1.3.1
	 *
	 * @todo    (dev) more templates
	 * @todo    (dev) move this to a separate JS file?
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
	 * @todo    (dev) remove this?
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
	 * @version 1.9.0
	 * @since   1.0.0
	 */
	function add_order_actions( $actions ) {
		if ( $this->do_add_admin_action( 'Alg_WC_Custom_Email', 'order_actions_single' ) ) {
			$actions['alg_wc_send_email_custom'] = sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title() );
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions', $actions );
	}

	/**
	 * add_order_actions_bulk.
	 *
	 * @version 1.9.0
	 * @since   1.8.0
	 */
	function add_order_actions_bulk( $actions ) {
		if ( $this->do_add_admin_action( 'Alg_WC_Custom_Email', 'order_actions_bulk' ) ) {
			$actions['alg_wc_send_email_custom'] = sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title() );
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_bulk', $actions );
	}

	/**
	 * do_order_actions_column.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @see     https://wordpress.stackexchange.com/questions/256513/should-nonce-be-sanitized
	 *
	 * @todo    (dev) merge with `do_order_actions_preview()`?
	 */
	function do_order_actions_column() {
		if (
			isset( $_GET['action'], $_GET['email_id'], $_GET['order_id'], $_GET['_wpnonce'] ) &&
			'alg_wc_send_email_custom' === wc_clean( $_GET['action'] ) &&
			( $order = wc_get_order( intval( $_GET['order_id'] ) ) )
		) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'alg_wc_send_email_custom' ) ) {
				wp_die( __( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
			}
			$email = apply_filters( 'alg_wc_custom_emails_class', 'Alg_WC_Custom_Email', intval( $_GET['email_id'] ) );
			$this->send_order_email( $email, $order, __( 'order actions column', 'custom-emails-for-woocommerce' ) );
			wp_safe_redirect( remove_query_arg( array( 'action', 'email_id', 'order_id', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * order_actions_column_icon_style.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @see     https://developer.wordpress.org/resource/dashicons/
	 */
	function order_actions_column_icon_style() {
		?><style>
		.widefat .column-wc_actions a.alg_wc_send_email_custom::after {
			content: "\f466";
		}
		</style><?php
	}

	/**
	 * add_order_actions_column.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 */
	function add_order_actions_column( $actions, $order ) {
		if ( $this->do_add_admin_action( 'Alg_WC_Custom_Email', 'order_actions_column' ) ) {
			$actions['alg_wc_send_email_custom'] = array(
				'url'    => wp_nonce_url( add_query_arg( array(
					'action'   => 'alg_wc_send_email_custom',
					'email_id' => 1,
					'order_id' => $order->get_id(),
				) ), 'alg_wc_send_email_custom' ),
				'name'   => sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					alg_wc_custom_emails()->core->email_settings->get_title() ),
				'action' => 'alg_wc_send_email_custom',
			);
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_column', $actions, $order );
	}


	/**
	 * do_order_actions_preview.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @see     https://wordpress.stackexchange.com/questions/256513/should-nonce-be-sanitized
	 */
	function do_order_actions_preview() {
		if (
			isset( $_GET['action'], $_GET['email_id'], $_GET['order_id'], $_GET['_wpnonce'] ) &&
			'alg_wc_send_email_custom' === wc_clean( $_GET['action'] ) &&
			( $order = wc_get_order( intval( $_GET['order_id'] ) ) )
		) {
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'alg_wc_send_email_custom' ) ) {
				wp_die( __( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
			}
			$email = apply_filters( 'alg_wc_custom_emails_class', 'Alg_WC_Custom_Email', intval( $_GET['email_id'] ) );
			$this->send_order_email( $email, $order, __( 'order preview', 'custom-emails-for-woocommerce' ) );
			wp_safe_redirect( remove_query_arg( array( 'action', 'email_id', 'order_id', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * add_order_actions_preview.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 *
	 * @todo    (dev) better `url`
	 */
	function add_order_actions_preview( $actions, $order ) {
		if ( $this->do_add_admin_action( 'Alg_WC_Custom_Email', 'order_actions_preview' ) ) {
			$actions['alg_wc_send_email_custom'] = array(
				'url'    => wp_nonce_url( add_query_arg( array(
					'action'   => 'alg_wc_send_email_custom',
					'email_id' => 1,
					'order_id' => $order->get_id(),
				), admin_url( 'edit.php?post_type=shop_order' ) ), 'alg_wc_send_email_custom' ),
				'name'   => alg_wc_custom_emails()->core->email_settings->get_title(),
				'title'  => sprintf( esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					alg_wc_custom_emails()->core->email_settings->get_title() ),
				'action' => 'alg_wc_send_email_custom',
			);
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_preview', $actions, $order );
	}

	/**
	 * do_add_admin_action.
	 *
	 * @version 1.9.4
	 * @since   1.9.0
	 */
	function do_add_admin_action( $email, $option ) {
		return (
			( $wc_emails = WC_Emails::instance() ) &&
			isset( $wc_emails->emails[ $email ] ) &&
			$wc_emails->emails[ $email ] instanceof WC_Email &&
			in_array( $option, $wc_emails->emails[ $email ]->get_option( 'admin_actions', array() ) )
		);
	}

}

endif;

return new Alg_WC_Custom_Emails_Admin();
