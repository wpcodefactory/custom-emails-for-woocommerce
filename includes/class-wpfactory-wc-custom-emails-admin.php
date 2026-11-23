<?php
/**
 * Custom Emails for WooCommerce - Admin Class
 *
 * @version 3.7.5
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Custom_Emails
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_Custom_Emails_Admin' ) ) :

	/**
	 * WPFactory_WC_Custom_Emails_Admin class.
	 *
	 * @version 3.7.5
	 * @since   1.0.0
	 */
	class WPFactory_WC_Custom_Emails_Admin {

		/**
		 * Constructor.
		 *
		 * @version 3.7.3
		 * @since   1.0.0
		 */
		public function __construct() {
			// Edit order > Order actions (dropdown).
			add_filter(
				'woocommerce_order_actions',
				array( $this, 'add_order_actions' ),
				PHP_INT_MAX
			);
			add_action(
				'woocommerce_order_action_wpfactory_wc_send_email_custom',
				array( $this, 'do_order_actions' ),
				PHP_INT_MAX
			);

			// Orders > Bulk actions (dropdown).
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

			// Subscriptions > Bulk actions (dropdown).
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

			// Orders > Preview.
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

			// Orders > Actions (column).
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
				'admin_enqueue_scripts',
				array( $this, 'order_actions_column_icon_style' )
			);

			// Content template script.
			add_action(
				'admin_footer',
				array( $this, 'add_content_template_script' )
			);

			// Unschedule email.
			add_action(
				'wp_loaded',
				array( $this, 'unschedule_email' )
			);
			add_action(
				'admin_enqueue_scripts',
				array( $this, 'unschedule_email_confirm_js' )
			);

			// Shortcode dropdown CSS.
			add_action(
				'admin_enqueue_scripts',
				array( $this, 'shortcode_dropdown_style' )
			);

			// Admin core loaded.
			do_action( 'wpfactory_wc_custom_emails_admin_core_loaded', $this );
		}

		/**
		 * Is current screen.
		 *
		 * @version 3.7.3
		 * @since   3.7.3
		 *
		 * @param string|array $screen_id Screen ID or array of screen IDs to check against.
		 *
		 * @return bool True if current screen matches, false otherwise.
		 */
		public function is_current_screen( $screen_id ) {
			return (
				function_exists( 'get_current_screen' ) &&
				( $current_screen = get_current_screen() ) && // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
				isset( $current_screen->id ) &&
				(
					(
						is_array( $screen_id ) &&
						in_array( $current_screen->id, $screen_id, true )
					) ||
					(
						! is_array( $screen_id ) &&
						$screen_id === $current_screen->id
					)
				)
			);
		}

		/**
		 * Unschedule email confirm JS.
		 *
		 * @version 3.7.3
		 * @since   1.9.5
		 */
		public function unschedule_email_confirm_js() {
			if (
				! $this->is_current_screen( 'woocommerce_page_wc-settings' ) ||
				! isset( $_GET['tab'], $_GET['section'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'wpfactory_wc_custom_emails' !== $_GET['tab'] || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'scheduled' !== $_GET['section'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'wpfactory-wc-custom-emails-scheduled',
				wpfactory_wc_custom_emails()->plugin_url() . '/assets/js/wpfactory-wc-custom-emails-scheduled' . $min . '.js',
				array( 'jquery' ),
				wpfactory_wc_custom_emails()->version,
				true
			);

			wp_localize_script(
				'wpfactory-wc-custom-emails-scheduled',
				'wpfactoryWCCustomEmailsScheduled',
				array(
					'unscheduleMessage' => __( 'Are you sure?', 'custom-emails-for-woocommerce' ),
				)
			);
		}

		/**
		 * Unschedule email.
		 *
		 * @version 3.7.3
		 * @since   1.9.5
		 *
		 * @todo (dev) Add success/error notice.
		 * @todo (dev) Code refactoring: `function unschedule_single() {}`?
		 */
		public function unschedule_email() {
			if ( isset( $_REQUEST['wpfactory_wc_ce_unscheduler'], $_REQUEST['_wpnonce'] ) ) {

				if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
					wp_die( esc_html__( 'Invalid user role.', 'custom-emails-for-woocommerce' ) );
				}

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) ) ) {
					wp_die( esc_html__( 'Invalid nonce.', 'custom-emails-for-woocommerce' ) );
				}

				$unscheduler = sanitize_text_field( wp_unslash( $_REQUEST['wpfactory_wc_ce_unscheduler'] ) );

				if ( 'wp_cron' === $unscheduler ) {
					if (
						isset(
							$_REQUEST['wpfactory_wc_ce_unschedule_class'],
							$_REQUEST['wpfactory_wc_ce_unschedule_object_id'],
							$_REQUEST['wpfactory_wc_ce_unschedule_time']
						)
					) {
						// WP Cron.
						$class     = sanitize_text_field( wp_unslash( $_REQUEST['wpfactory_wc_ce_unschedule_class'] ) );
						$object_id = intval( $_REQUEST['wpfactory_wc_ce_unschedule_object_id'] );
						$time      = intval( $_REQUEST['wpfactory_wc_ce_unschedule_time'] );

						$res = wp_unschedule_event(
							$time,
							'wpfactory_wc_custom_emails_send_email',
							array( $class, $object_id )
						);

						if (
							true !== $res &&
							wp_next_scheduled(
								'alg_wc_custom_emails_send_email',
								array( $class, $object_id )
							)
						) {
							/**
							 * Attempt to unschedule the deprecated action.
							 *
							 * @deprecated since 3.7.3, use `wpfactory_wc_custom_emails_send_email`
							 */
							wp_unschedule_event(
								$time,
								'alg_wc_custom_emails_send_email',
								array( $class, $object_id )
							);
						}

						wp_safe_redirect(
							remove_query_arg(
								array(
									'wpfactory_wc_ce_unschedule_class',
									'wpfactory_wc_ce_unschedule_object_id',
									'wpfactory_wc_ce_unschedule_time',
									'wpfactory_wc_ce_unscheduler',
									'_wpnonce',
								)
							)
						);
						exit;
					}
				} elseif ( 'as' === $unscheduler ) {
					if ( isset( $_REQUEST['wpfactory_wc_ce_unschedule_action_id'] ) ) {
						// Action Scheduler.
						$action_id = intval( $_REQUEST['wpfactory_wc_ce_unschedule_action_id'] );
						try {
							ActionScheduler::store()->cancel_action( $action_id );
						} catch ( Exception $exception ) {
							ActionScheduler::logger()->log(
								$action_id,
								sprintf(
									/* translators: %1$s is the name of the hook to be cancelled, %2$s is the exception message. */
									__( 'Caught exception while cancelling action "%1$s": %2$s', 'custom-emails-for-woocommerce' ),
									'wpfactory_wc_custom_emails_send_email',
									$exception->getMessage()
								)
							);
						}

						wp_safe_redirect(
							remove_query_arg(
								array(
									'wpfactory_wc_ce_unschedule_action_id',
									'wpfactory_wc_ce_unscheduler',
									'_wpnonce',
								)
							)
						);
						exit;
					}
				}
			}
		}

		/**
		 * Is custom email settings screen.
		 *
		 * @version 3.7.3
		 * @since   3.7.3
		 */
		public function is_custom_email_settings_screen() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			return (
				$this->is_current_screen( 'woocommerce_page_wc-settings' ) &&
				isset( $_GET['tab'], $_GET['section'] ) &&
				'email' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) &&
				0 === strpos( sanitize_text_field( wp_unslash( $_GET['section'] ) ), 'wpfactory_wc_custom_email' )
			);
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add content template script.
		 *
		 * @version 3.7.3
		 * @since   1.3.1
		 *
		 * @todo (v3.7.3) Rename the method?
		 * @todo (v3.7.3) Do we need `wp_kses()` in `wp_localize_script()`?
		 */
		public function add_content_template_script() {
			if ( ! $this->is_custom_email_settings_screen() ) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'wpfactory-wc-custom-emails-content-template',
				wpfactory_wc_custom_emails()->plugin_url() . '/assets/js/wpfactory-wc-custom-emails-content-template' . $min . '.js',
				array( 'jquery' ),
				wpfactory_wc_custom_emails()->version,
				true
			);

			wp_localize_script(
				'wpfactory-wc-custom-emails-content-template',
				'wpfactoryWCCustomEmailsContentTemplate',
				array(
					'linkTitle'     => __( 'Shortcodes', 'custom-emails-for-woocommerce' ),
					'linkText'      => __( 'Shortcodes', 'custom-emails-for-woocommerce' ),
					'shortcodeList' => wp_kses(
						wpfactory_wc_custom_emails()->core->generate_shortcode_list_html(),
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
					),
				)
			);
		}

		/**
		 * Do order actions bulk.
		 *
		 * @version 3.7.3
		 * @since   1.2.1
		 *
		 * @param string $redirect_to The redirect URL.
		 * @param string $action      The bulk action being performed.
		 * @param array  $post_ids    The IDs of the posts being acted upon.
		 *
		 * @return string The modified redirect URL.
		 */
		public function do_order_actions_bulk( $redirect_to, $action, $post_ids ) {
			if ( 'wpfactory_wc_send_email_custom' === $action ) {
				foreach ( $post_ids as $post_id ) {
					$order = wc_get_order( $post_id );
					$this->send_order_email(
						'WPFactory_WC_Custom_Email',
						$order,
						__( 'bulk actions', 'custom-emails-for-woocommerce' )
					);
				}
				$redirect_to = add_query_arg(
					array(
						'wpfactory_wc_send_email_custom_count' => count( $post_ids ),
						'wpfactory_wc_send_email_custom_num'   => '1',
					),
					$redirect_to
				);
			}
			return $redirect_to;
		}

		/**
		 * Bulk action admin notice.
		 *
		 * @version 3.7.3
		 * @since   1.2.1
		 */
		public function bulk_action_admin_notice() {
			if (
				isset(
					$_REQUEST['wpfactory_wc_send_email_custom_count'], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$_REQUEST['wpfactory_wc_send_email_custom_num'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				)
			) {
				$email = apply_filters(
					'wpfactory_wc_custom_emails_class',
					'WPFactory_WC_Custom_Email',
					intval( $_REQUEST['wpfactory_wc_send_email_custom_num'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
				$count = intval( $_REQUEST['wpfactory_wc_send_email_custom_count'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		 * Send order email.
		 *
		 * @version 3.7.3
		 * @since   1.0.0
		 *
		 * @param string   $email The email class name.
		 * @param WC_Order $order The order object.
		 * @param string   $note  The note for the email.
		 *
		 * @return void
		 *
		 * @todo (dev) Remove this?
		 */
		public function send_order_email( $email, $order, $note ) {
			wpfactory_wc_custom_emails()->core->send_email( $email, $order->get_id(), $note );
		}

		/**
		 * Do order actions.
		 *
		 * @version 3.7.3
		 * @since   1.0.0
		 *
		 * @param WC_Order $order The order object.
		 *
		 * @return void
		 */
		public function do_order_actions( $order ) {
			$this->send_order_email(
				'WPFactory_WC_Custom_Email',
				$order,
				__( 'manually', 'custom-emails-for-woocommerce' )
			);
		}

		/**
		 * Add order actions.
		 *
		 * @version 3.7.3
		 * @since   1.0.0
		 *
		 * @param array $actions The order actions.
		 *
		 * @return array The modified order actions.
		 */
		public function add_order_actions( $actions ) {
			if (
				$this->do_add_admin_action(
					'WPFactory_WC_Custom_Email',
					'order_actions_single'
				)
			) {
				$actions['wpfactory_wc_send_email_custom'] = sprintf(
					/* Translators: %s: Custom email title. */
					esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					wpfactory_wc_custom_emails()->core->email_settings->get_title()
				);
			}
			return apply_filters(
				'wpfactory_wc_custom_emails_admin_add_order_actions',
				$actions
			);
		}

		/**
		 * Add order actions bulk.
		 *
		 * @version 3.7.3
		 * @since   1.8.0
		 *
		 * @param array $actions The bulk order actions.
		 *
		 * @return array The modified bulk order actions.
		 */
		public function add_order_actions_bulk( $actions ) {
			if (
				$this->do_add_admin_action(
					'WPFactory_WC_Custom_Email',
					'order_actions_bulk'
				)
			) {
				$actions['wpfactory_wc_send_email_custom'] = sprintf(
					/* Translators: %s: Custom email title. */
					esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
					wpfactory_wc_custom_emails()->core->email_settings->get_title()
				);
			}
			return apply_filters(
				'wpfactory_wc_custom_emails_admin_add_order_actions_bulk',
				$actions
			);
		}

		/**
		 * Do order actions column.
		 *
		 * @version 3.7.3
		 * @since   1.9.0
		 *
		 * @see https://wordpress.stackexchange.com/questions/256513/should-nonce-be-sanitized
		 *
		 * @todo (dev) Merge with `do_order_actions_preview()`?
		 */
		public function do_order_actions_column() {
			if (
				! isset(
					$_GET['action'],
					$_GET['email_id'],
					$_GET['order_id'],
					$_GET['_wpnonce']
				)
			) {
				return;
			}

			if ( 'wpfactory_wc_send_email_custom' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'Wrong user.', 'custom-emails-for-woocommerce' ) );
			}

			if (
				! wp_verify_nonce(
					sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
					'wpfactory_wc_send_email_custom'
				)
			) {
				wp_die( esc_html__( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
			}

			$order = wc_get_order( intval( $_GET['order_id'] ) );
			if ( ! $order ) {
				return;
			}

			$email = apply_filters(
				'wpfactory_wc_custom_emails_class',
				'WPFactory_WC_Custom_Email',
				intval( $_GET['email_id'] )
			);

			$this->send_order_email(
				$email,
				$order,
				__( 'order actions column', 'custom-emails-for-woocommerce' )
			);

			wp_safe_redirect(
				remove_query_arg(
					array(
						'action',
						'email_id',
						'order_id',
						'_wpnonce',
					)
				)
			);
			exit;
		}

		/**
		 * Order actions column icon style.
		 *
		 * @version 3.7.3
		 * @since   1.9.0
		 *
		 * @see https://developer.wordpress.org/resource/dashicons/
		 */
		public function order_actions_column_icon_style() {
			if (
				! $this->is_current_screen(
					array(
						'edit-shop_order',
						'woocommerce_page_wc-orders',
					)
				)
			) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style(
				'wpfactory-wc-custom-emails-order-actions-column',
				wpfactory_wc_custom_emails()->plugin_url() . '/assets/css/wpfactory-wc-custom-emails-order-actions-column' . $min . '.css',
				array(),
				wpfactory_wc_custom_emails()->version
			);
		}

		/**
		 * Add order actions column.
		 *
		 * @version 3.7.3
		 * @since   1.9.0
		 *
		 * @param array    $actions The order actions.
		 * @param WC_Order $order   The order object.
		 *
		 * @return array The modified order actions.
		 */
		public function add_order_actions_column( $actions, $order ) {
			if (
				$this->do_add_admin_action(
					'WPFactory_WC_Custom_Email',
					'order_actions_column'
				)
			) {
				$actions['wpfactory_wc_send_email_custom'] = array(
					'url'    => wp_nonce_url(
						add_query_arg(
							array(
								'action'   => 'wpfactory_wc_send_email_custom',
								'email_id' => 1,
								'order_id' => $order->get_id(),
							)
						),
						'wpfactory_wc_send_email_custom'
					),
					'name'   => sprintf(
						/* Translators: %s: Custom email title. */
						esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
						wpfactory_wc_custom_emails()->core->email_settings->get_title()
					),
					'action' => 'wpfactory_wc_send_email_custom',
				);
			}
			return apply_filters(
				'wpfactory_wc_custom_emails_admin_add_order_actions_column',
				$actions,
				$order
			);
		}

		/**
		 * Do order actions preview.
		 *
		 * @version 3.7.3
		 * @since   1.9.0
		 */
		public function do_order_actions_preview() {
			if (
				! isset(
					$_GET['action'],
					$_GET['email_id'],
					$_GET['order_id'],
					$_GET['_wpnonce']
				)
			) {
				return;
			}

			if ( 'wpfactory_wc_send_email_custom' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				wp_die( esc_html__( 'Wrong user.', 'custom-emails-for-woocommerce' ) );
			}

			if (
				! wp_verify_nonce(
					sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ),
					'wpfactory_wc_send_email_custom'
				)
			) {
				wp_die( esc_html__( 'Link has expired.', 'custom-emails-for-woocommerce' ) );
			}

			$order = wc_get_order( intval( $_GET['order_id'] ) );
			if ( ! $order ) {
				return;
			}

			$email = apply_filters(
				'wpfactory_wc_custom_emails_class',
				'WPFactory_WC_Custom_Email',
				intval( $_GET['email_id'] )
			);

			$this->send_order_email(
				$email,
				$order,
				__( 'order preview', 'custom-emails-for-woocommerce' )
			);

			wp_safe_redirect(
				remove_query_arg(
					array(
						'action',
						'email_id',
						'order_id',
						'_wpnonce',
					)
				)
			);
			exit;
		}

		/**
		 * Add order actions preview.
		 *
		 * @version 3.7.3
		 * @since   1.9.0
		 *
		 * @param array    $actions The order actions.
		 * @param WC_Order $order   The order object.
		 *
		 * @return array The modified order actions.
		 *
		 * @todo (dev) Better `url`.
		 */
		public function add_order_actions_preview( $actions, $order ) {
			if (
				$this->do_add_admin_action(
					'WPFactory_WC_Custom_Email',
					'order_actions_preview'
				)
			) {
				$actions['wpfactory_wc_send_email_custom'] = array(
					'url'    => wp_nonce_url(
						add_query_arg(
							array(
								'action'   => 'wpfactory_wc_send_email_custom',
								'email_id' => 1,
								'order_id' => $order->get_id(),
							),
							admin_url( 'edit.php?post_type=shop_order' )
						),
						'wpfactory_wc_send_email_custom'
					),
					'name'   => wpfactory_wc_custom_emails()->core->email_settings->get_title(),
					'title'  => sprintf(
						/* Translators: %s: Custom email title. */
						esc_html__( 'Send email: %s', 'custom-emails-for-woocommerce' ),
						wpfactory_wc_custom_emails()->core->email_settings->get_title()
					),
					'action' => 'wpfactory_wc_send_email_custom',
				);
			}
			return apply_filters(
				'wpfactory_wc_custom_emails_admin_add_order_actions_preview',
				$actions,
				$order
			);
		}

		/**
		 * Do add admin action.
		 *
		 * @version 3.7.5
		 * @since   1.9.0
		 *
		 * @param string $email  The email class name.
		 * @param string $option The admin action option.
		 *
		 * @return bool True if the admin action should be added, false otherwise.
		 */
		public function do_add_admin_action( $email, $option ) {
			$wc_emails = WC_Emails::instance();
			return (
				$wc_emails &&
				isset( $wc_emails->emails[ $email ] ) &&
				$wc_emails->emails[ $email ] instanceof WC_Email &&
				in_array( $option, $wc_emails->emails[ $email ]->get_option( 'admin_actions', array() ), true )
			);
		}

		/**
		 * Shortcode dropdown style.
		 *
		 * @version 3.7.3
		 * @since   3.1.0
		 */
		public function shortcode_dropdown_style() {
			if ( ! $this->is_custom_email_settings_screen() ) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style(
				'wpfactory-wc-custom-emails-shortcode-dropdown',
				wpfactory_wc_custom_emails()->plugin_url() . '/assets/css/wpfactory-wc-custom-emails-shortcode-dropdown' . $min . '.css',
				array(),
				wpfactory_wc_custom_emails()->version
			);
		}
	}

endif;

return new WPFactory_WC_Custom_Emails_Admin();
