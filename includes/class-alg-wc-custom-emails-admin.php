<?php
/**
 * Custom Emails for WooCommerce - Admin Class
 *
 * @version 3.6.6
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
	 * @version 3.6.6
	 * @since   1.0.0
	 */
	function __construct() {

		// Edit order > Order actions (dropdown)
		add_filter(
			'woocommerce_order_actions',
			array( $this, 'add_order_actions' ),
			PHP_INT_MAX
		);
		add_action(
			'woocommerce_order_action_alg_wc_send_email_custom',
			array( $this, 'do_order_actions' ),
			PHP_INT_MAX
		);

		// Orders > Bulk actions (dropdown)
		add_filter(
			'bulk_actions-edit-shop_order',
			array( $this, 'add_order_actions_bulk' ),
			20
		);
		add_filter(
			'bulk_actions-woocommerce_page_wc-orders',
			array( $this, 'add_order_actions_bulk' ),
			20
		);
		add_filter(
			'handle_bulk_actions-edit-shop_order',
			array( $this, 'do_order_actions_bulk' ),
			10,
			3
		);
		add_filter(
			'handle_bulk_actions-woocommerce_page_wc-orders',
			array( $this, 'do_order_actions_bulk' ),
			10,
			3
		);
		add_action(
			'admin_notices',
			array( $this, 'bulk_action_admin_notice' )
		);

		// Subscriptions > Bulk actions (dropdown)
		if ( class_exists( 'WC_Subscriptions' ) ) {
			add_filter(
				'bulk_actions-edit-shop_subscription',
				array( $this, 'add_order_actions_bulk' ),
				20
			);
			add_filter(
				'bulk_actions-woocommerce_page_wc-orders--shop_subscription',
				array( $this, 'add_order_actions_bulk' ),
				20
			);
			add_filter(
				'handle_bulk_actions-edit-shop_subscription',
				array( $this, 'do_order_actions_bulk' ),
				10,
				3
			);
			add_filter(
				'handle_bulk_actions-woocommerce_page_wc-orders--shop_subscription',
				array( $this, 'do_order_actions_bulk' ),
				10,
				3
			);
		}

		// Orders > Preview
		add_filter(
			'woocommerce_admin_order_preview_actions',
			array( $this, 'add_order_actions_preview' ),
			10,
			2
		);
		add_filter(
			'admin_init',
			array( $this, 'do_order_actions_preview' )
		);

		// Orders > Actions (column)
		add_filter(
			'woocommerce_admin_order_actions',
			array( $this, 'add_order_actions_column' ),
			10,
			2
		);
		add_filter(
			'admin_init',
			array( $this, 'do_order_actions_column' )
		);
		add_action(
			'admin_footer',
			array( $this, 'order_actions_column_icon_style' )
		);

		// Content template script
		add_action(
			'admin_footer',
			array( $this, 'add_content_template_script' )
		);

		// Unschedule email
		add_action(
			'wp_loaded',
			array( $this, 'unschedule_email' )
		);
		add_action(
			'admin_footer',
			array( $this, 'unschedule_email_confirm_js' )
		);

		// Shortcode dropdown CSS
		add_action(
			'admin_footer',
			array( $this, 'shortcode_dropdown_style' )
		);

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
	 * @version 3.5.0
	 * @since   1.9.5
	 *
	 * @todo    (dev) add success/error notice
	 * @todo    (dev) code refactoring: `function unschedule_single() {}`?
	 */
	function unschedule_email() {
		if ( isset( $_REQUEST['alg_wc_ce_unscheduler'], $_REQUEST['_wpnonce'] ) ) {

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Invalid user role.', 'custom-emails-for-woocommerce' ) );
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) ) ) {
				wp_die( esc_html__( 'Invalid nonce.', 'custom-emails-for-woocommerce' ) );
			}

			$unscheduler = sanitize_text_field( wp_unslash( $_REQUEST['alg_wc_ce_unscheduler'] ) );

			if ( 'wp_cron' === $unscheduler ) {
				if ( isset(
					$_REQUEST['alg_wc_ce_unschedule_class'],
					$_REQUEST['alg_wc_ce_unschedule_object_id'],
					$_REQUEST['alg_wc_ce_unschedule_time']
				) ) {

					// WP Cron
					$class     = sanitize_text_field( wp_unslash( $_REQUEST['alg_wc_ce_unschedule_class'] ) );
					$object_id = intval( $_REQUEST['alg_wc_ce_unschedule_object_id'] );
					$time      = intval( $_REQUEST['alg_wc_ce_unschedule_time'] );
					wp_unschedule_event( $time, 'alg_wc_custom_emails_send_email', array( $class, $object_id ) );
					wp_safe_redirect( remove_query_arg( array( 'alg_wc_ce_unschedule_class', 'alg_wc_ce_unschedule_object_id', 'alg_wc_ce_unschedule_time', 'alg_wc_ce_unscheduler', '_wpnonce' ) ) );
					exit;

				}
			} elseif ( 'as' === $unscheduler ) {
				if ( isset( $_REQUEST['alg_wc_ce_unschedule_action_id'] ) ) {

					// Action Scheduler
					$action_id = intval( $_REQUEST['alg_wc_ce_unschedule_action_id'] );
					try {
						ActionScheduler::store()->cancel_action( $action_id );
					} catch ( Exception $exception ) {
						ActionScheduler::logger()->log(
							$action_id,
							sprintf(
								/* translators: %1$s is the name of the hook to be cancelled, %2$s is the exception message. */
								__( 'Caught exception while cancelling action "%1$s": %2$s', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
								'alg_wc_custom_emails_send_email',
								$exception->getMessage()
							)
						);
					}
					wp_safe_redirect( remove_query_arg( array( 'alg_wc_ce_unschedule_action_id', 'alg_wc_ce_unscheduler', '_wpnonce' ) ) );
					exit;

				}
			}

		}
	}

	/**
	 * add_content_template_script.
	 *
	 * @version 3.5.0
	 * @since   1.3.1
	 *
	 * @todo    (dev) more templates
	 * @todo    (dev) move this to a separate JS file?
	 */
	function add_content_template_script() {
		if (
			isset( $_GET['page'], $_GET['tab'], $_GET['section'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'wc-settings' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'email' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'alg_wc_custom_email' === substr( sanitize_text_field( wp_unslash( $_GET['section'] ) ), 0, 19 ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			?>
			<script>
				jQuery( document ).ready( function ( $ ) {

					// Add button to each element with the class 'alg-wc-shortcode-field'
					$( '.alg-wc-shortcode-field' ).each( function () {
						// Find the closest ancestor element and add a class to it
						const shortcode_closest_element = $( this ).parents().first().addClass( 'alg-wc-shortcode-wrap' );

						// Create the link element
						let link = $( '<a>', {
							href: '#',
							title: '<?php esc_html_e( 'Shortcodes', 'custom-emails-for-woocommerce' ); ?>',
							class: 'alg-wc-shortcode-button button button-secondary',
							text: '<?php esc_html_e( 'Shortcodes', 'custom-emails-for-woocommerce' ); ?>'
						} );

						// Create the span element for the icon
						let icon = $( '<span>', {
							class: 'dashicons dashicons-arrow-down-alt2',
						} );

						// Append the icon to the link
						link.append( icon );

						// Append the link to the selected element
						shortcode_closest_element.append( link );
					} );

					// Define the content to append
					let shortcodes_list = `<?php echo wp_kses(
						alg_wc_custom_emails()->core->generate_shortcode_list_html(),
						array_merge(
							wp_kses_allowed_html( 'post' ),
							array(
								'input' => array(
									'type'        => true,
									'class'       => true,
									'placeholder' => true,
								),
							)
						)
					); ?>`;

					const shortcode_list_class = '.alg-wc-shortcode-list';

					$( document ).on( 'click', '.alg-wc-shortcode-button', function ( e ) {
						e.preventDefault();

						let container = $( this ).closest( '.alg-wc-shortcode-wrap' );

						$( shortcode_list_class ).not( container.find( shortcode_list_class ) ).hide();

						if ( container.find( shortcode_list_class ).length ) {
							container.find( shortcode_list_class ).toggle();
						} else {
							container.append( `${shortcodes_list}` );
							container.find( shortcode_list_class ).toggle();
						}

						e.stopPropagation();
					} );

					// Click event for hiding shortcodes list when clicking outside
					$( document ).on( 'click', function ( e ) {
						const shortcode_lists = $( shortcode_list_class );

						if ( ! shortcode_lists.is( e.target ) && 0 === shortcode_lists.has( e.target ).length ) {
							shortcode_lists.hide();
						}
					} );

					// Click and append shortcodes to the field or TinyMCE editor
					$( document ).on( 'click', '.alg-wc-shortcode-list li', function () {
						const shortcode = $( this ).data( 'shortcode' );
						const field_container = $( this ).closest( '.alg-wc-shortcode-wrap' );
						const field_id = field_container.find( '.alg-wc-shortcode-field' ).attr( 'id' );
						const field = $( `#${field_id}` );

						if ( ! field.length ) {
							return;
						} // Ensure the field exists

						field.focus();

						// Get current cursor position
						const cursor_pos = field.prop( 'selectionStart' );

						// Use execCommand to insert text
						try {
							document.execCommand( 'insertText', false, shortcode );
						} catch ( error ) {
							// Fallback method if execCommand fails
							const field_value = field.val();
							field.val( field_value.substring( 0, cursor_pos ) + shortcode + field_value.substring( cursor_pos ) );
						}

						// Update cursor position after inserting the shortcode
						field.prop( 'selectionStart', cursor_pos + shortcode.length );
						field.prop( 'selectionEnd', cursor_pos + shortcode.length );

						// For TinyMCE editor (Visual Editor)
						if ( typeof tinyMCE !== "undefined" ) {
							const editor = tinyMCE.get( field_id );

							if ( editor ) {
								// Insert the shortcode into the TinyMCE editor
								editor.execCommand( 'mceInsertContent', false, shortcode );
							}
						}
					} );

					// Default template content
					let templates = [
						"[order_details]\n" +
						"<table>\n" +
						"    <tbody>\n" +
						"        <tr><th>Billing address</th><th>Shipping address</th></tr>\n" +
						"        <tr><td>[order_billing_address]</td><td>[order_shipping_address]</td></tr>\n" +
						"    </tbody>\n" +
						"</table>",
					];

					// Reset default template content
					$( '#alg_wc_custom_emails_content_template_0' ).on( 'click', function ( event ) {
						event.preventDefault();

						const editor_container = $( this ).closest( '.alg-wc-editor' );
						const editor_id = editor_container.find( '.wp-editor-area' ).attr( 'id' );

						if ( typeof tinyMCE !== "undefined" ) {
							const editor = tinyMCE.get( editor_id );

							if ( editor ) {
								editor.setContent( templates[0] );
								$( `#${editor_id}` ).trigger( 'input' );
							}
						}

						const text_area = $( `#${editor_id}` );
						text_area.val( templates[0] );

						return false;
					} );

					// Listen for changes in TinyMCE editor (Visual Editor)
					if ( typeof tinyMCE !== "undefined" ) {
						const editor_container = $( '.alg-wc-editor' );
						const editor_id = editor_container.find( '.wp-editor-area' ).attr( 'id' );
						const editor = tinyMCE.get( editor_id );

						if ( editor ) {
							editor.on( 'Change', function () {
								$( `#${editor_id}` ).trigger( 'input' );
							} );
						}
					}

					// Filter items in the dropdown shortcode list.
					$( document ).on( 'keyup', '.alg-wc-shortcode-search', function () {
						let filter = $( this ).val().toLowerCase();
						$( this ).closest( '.alg-wc-shortcode-list' ).find( 'li' ).filter( function () {
							$( this ).toggle( $( this ).text().toLowerCase().indexOf( filter ) > - 1 );
						} );
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
	 * @version 3.5.0
	 * @since   1.2.1
	 */
	function bulk_action_admin_notice() {
		if ( isset( $_REQUEST['alg_wc_send_email_custom_count'], $_REQUEST['alg_wc_send_email_custom_num'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$email = apply_filters( 'alg_wc_custom_emails_class', 'Alg_WC_Custom_Email', intval( $_REQUEST['alg_wc_send_email_custom_num'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_REQUEST['alg_wc_send_email_custom_count'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible"><p>' .
				sprintf(
					/* Translators: %1$s: Custom email title, %2$d: Number of orders. */
					esc_html__( 'Emails: "%1$s" sent for %2$d orders.', 'custom-emails-for-woocommerce' ),
					esc_html( WC()->mailer()->emails[ $email ]->get_title() ),
					$count // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				) .
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
			$actions['alg_wc_send_email_custom'] = sprintf(
				/* Translators: %s: Custom email title. */
				esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title()
			);
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
			$actions['alg_wc_send_email_custom'] = sprintf(
				/* Translators: %s: Custom email title. */
				esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
				alg_wc_custom_emails()->core->email_settings->get_title()
			);
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_bulk', $actions );
	}

	/**
	 * do_order_actions_column.
	 *
	 * @version 3.5.0
	 * @since   1.9.0
	 *
	 * @see     https://wordpress.stackexchange.com/questions/256513/should-nonce-be-sanitized
	 *
	 * @todo    (dev) merge with `do_order_actions_preview()`?
	 */
	function do_order_actions_column() {
		if (
			isset( $_GET['action'], $_GET['email_id'], $_GET['order_id'], $_GET['_wpnonce'] ) &&
			'alg_wc_send_email_custom' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) &&
			( $order = wc_get_order( intval( $_GET['order_id'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'alg_wc_send_email_custom' ) ) {
				wp_die( esc_html__( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
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
				'name'   => sprintf(
					/* Translators: %s: Custom email title. */
					esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					alg_wc_custom_emails()->core->email_settings->get_title()
				),
				'action' => 'alg_wc_send_email_custom',
			);
		}
		return apply_filters( 'alg_wc_custom_emails_admin_add_order_actions_column', $actions, $order );
	}


	/**
	 * do_order_actions_preview.
	 *
	 * @version 3.5.0
	 * @since   1.9.0
	 *
	 * @see     https://wordpress.stackexchange.com/questions/256513/should-nonce-be-sanitized
	 */
	function do_order_actions_preview() {
		if (
			isset( $_GET['action'], $_GET['email_id'], $_GET['order_id'], $_GET['_wpnonce'] ) &&
			'alg_wc_send_email_custom' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) &&
			( $order = wc_get_order( intval( $_GET['order_id'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'alg_wc_send_email_custom' ) ) {
				wp_die( esc_html__( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
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
				'title'  => sprintf(
					/* Translators: %s: Custom email title. */
					esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					alg_wc_custom_emails()->core->email_settings->get_title()
				),
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

	/**
	 * Shortcode dropdown style.
	 *
	 * @version 3.1.2
	 * @since   3.1.0
	 */
	function shortcode_dropdown_style() {
		?>
		<style>
			.woocommerce table.form-table .alg-wc-editor textarea {
				width: 100%;
			}

			.alg-wc-shortcode-wrap {
				position: relative;
			}

			.alg-wc-shortcode-button.button-secondary {
				position: absolute;
				top: 0;
				right: 0;
				vertical-align: middle;
				display: flex;
				align-items: center;
				min-height: 30px;
				flex-direction: row-reverse;
				padding: 0 10px 0 5px;
			}

			.alg-wc-shortcode-button.button-secondary > span {
				padding-right: 3px;
			}

			.alg-wc-shortcode-wrap.wp-editor-container .alg-wc-shortcode-button.button-secondary {
				top: 2px;
			}

			.alg-rich-text-editor .wp-editor-container {
				position: relative;
			}

			.alg-wc-shortcode-list {
				z-index: 999;
				display: none;
				max-width: 450px;
				border: 1px solid #b5bfc9;
				border-radius: 6px;
				background: #fff;
				position: absolute;
				top: 36px;
				right: 0;
				width: 80%;
			}

			.alg-wc-shortcode-search {
				width: calc(100% - 10px) !important;
				position: absolute;
				top: 5px;
				left: 5px;
				margin-top: 5px;
			}

			.alg-wc-shortcode-list ul {
				margin: 40px 0 0 0;
				height: 220px;
				overflow: auto;
			}

			.alg-wc-shortcode-list li {
				margin: 0;
				padding: 10px 10px;
				cursor: pointer;
				border-bottom: 1px solid #b5bfc9;
			}

			.alg-wc-shortcode-list li:last-child {
				border: none;
			}
		</style>
		<?php
	}

}

endif;

return new Alg_WC_Custom_Emails_Admin();
