<?php
/**
 * Custom Emails for WooCommerce - Scheduled Section Settings
 *
 * @version 3.7.3
 * @since   1.3.0
 *
 * @author  WPFactory
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPFactory_WC_Custom_Emails_Settings_Scheduled' ) ) :

class WPFactory_WC_Custom_Emails_Settings_Scheduled extends WPFactory_WC_Custom_Emails_Settings_Section {

	/**
	 * email_titles.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	public $email_titles;

	/**
	 * Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function __construct() {
		$this->id   = 'scheduled';
		$this->desc = __( 'Scheduled', 'custom-emails-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_unschedule_button_html
	 *
	 * @version 3.7.3
	 * @since   1.9.5
	 */
	function get_unschedule_button_html( $url ) {
		return sprintf(
			'<a href="%s" title="%s" class="%s">%s</a>',
			$url,
			esc_html__( 'Cancel', 'custom-emails-for-woocommerce' ),
			'wpfactory-wc-custom-emails-unschedule',
			'<span class="dashicons dashicons-trash"></span>'
		);
	}

	/**
	 * get_unschedule_button_html_wp_cron.
	 *
	 * @version 3.7.3
	 * @since   2.7.0
	 */
	function get_unschedule_button_html_wp_cron( $class, $object_id, $timestamp ) {
		$url = wp_nonce_url( add_query_arg( array(
			'wpfactory_wc_ce_unschedule_class'     => $class,
			'wpfactory_wc_ce_unschedule_object_id' => $object_id,
			'wpfactory_wc_ce_unschedule_time'      => $timestamp,
			'wpfactory_wc_ce_unscheduler'          => 'wp_cron',
		) ) );
		return $this->get_unschedule_button_html( $url );
	}

	/**
	 * get_unschedule_button_html_as.
	 *
	 * @version 3.7.3
	 * @since   2.7.0
	 */
	function get_unschedule_button_html_as( $action_id ) {
		$url = wp_nonce_url( add_query_arg( array(
			'wpfactory_wc_ce_unschedule_action_id' => $action_id,
			'wpfactory_wc_ce_unscheduler'          => 'as',
		) ) );
		return $this->get_unschedule_button_html( $url );
	}

	/**
	 * get_email_title_from_class.
	 *
	 * @version 3.7.3
	 * @since   2.7.0
	 */
	function get_email_title_from_class( $class ) {
		$id = $class;

		/**
		 * Remove legacy class name
		 *
		 * @deprecated since 3.7.3, use `WPFactory_WC_Custom_Email`
		 */
		$id = str_replace( 'Alg_WC_Custom_Email', '', $id );

		// Remove class name
		$id = str_replace( 'WPFactory_WC_Custom_Email', '', $id );

		// Remove underscore
		$id = str_replace( '_', '', $id );

		// Get ID
		$id = ( ! empty( $id ) ? $id : 1 );

		// All email titles
		if ( ! isset( $this->email_titles ) ) {
			$this->email_titles = get_option( 'alg_wc_custom_emails_titles', array() );
		}

		// Email title
		return (
			$this->email_titles[ $id ] ??
			(
				1 == $id ?
				__( 'Custom email', 'custom-emails-for-woocommerce' ) :
				sprintf(
					/* Translators: %d: Email ID. */
					__( 'Custom email #%d', 'custom-emails-for-woocommerce' ),
					$id
				)
			)
		);
	}

	/**
	 * get_formatted_local_time.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function get_formatted_local_time( $timestamp ) {
		$local_time = $timestamp + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		return date_i18n( 'Y-m-d H:i:s', $local_time );
	}

	/**
	 * get_delayed_emails_info.
	 *
	 * @version 3.7.3
	 * @since   1.3.0
	 *
	 * @todo    (dev) better solution instead of `_get_cron_array()`?
	 * @todo    (desc) better desc: "No scheduled emails found ..."
	 * @todo    (dev) `human_time_diff()`
	 * @todo    (feature) add "send now" buttons?
	 */
	function get_delayed_emails_info() {

		$result = array();

		$hooks = array(
			'alg_wc_custom_emails_send_email', // @deprecated since 3.7.3, use `wpfactory_wc_custom_emails_send_email`
			'wpfactory_wc_custom_emails_send_email',
		);

		// WP Cron
		$crons = _get_cron_array();
		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cron ) {
				foreach ( $hooks as $hook ) {
					if ( isset( $cron[ $hook ] ) ) {
						foreach ( $cron[ $hook ] as $_cron ) {
							if ( 2 == count( $_cron['args'] ) ) {
								$class     = $_cron['args'][0];
								$object_id = $_cron['args'][1];
								$result[] = sprintf(
									'<td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
									$this->get_email_title_from_class( $class ),
									$this->get_formatted_local_time( $timestamp ),
									( is_scalar( $object_id ) ? $object_id : '' ),
									(
										is_scalar( $object_id ) ?
										$this->get_unschedule_button_html_wp_cron(
											$class,
											$object_id,
											$timestamp
										) :
										''
									)
								);
							}
						}
					}
				}
			}
		}

		// Action Scheduler
		foreach ( $hooks as $hook ) {
			$scheduled_actions = as_get_scheduled_actions( array(
				'hook'     => $hook,
				'per_page' => -1,
				'status'   => ActionScheduler_Store::STATUS_PENDING,
			) );
			if ( ! empty( $scheduled_actions ) ) {
				foreach ( $scheduled_actions as $scheduled_action_id => $scheduled_action ) {
					$args = $scheduled_action->get_args();
					if ( 2 == count( $args ) ) {
						$class     = $args[0];
						$object_id = $args[1];
						$result[] = sprintf(
							'<td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
							$this->get_email_title_from_class( $class ),
							$this->get_formatted_local_time( $scheduled_action->get_schedule()->get_date()->getTimestamp() ),
							( is_scalar( $object_id ) ? $object_id : '' ),
							$this->get_unschedule_button_html_as( $scheduled_action_id )
						);
					}
				}
			}
		}

		// Results
		if ( empty( $result ) ) {

			return '<p><em>' . __( 'No scheduled emails found.', 'custom-emails-for-woocommerce' ) . '</em></p>';

		} else {

			return '<table class="widefat striped"><tbody>' .
				'<tr>' .
					'<th>' . __( 'Email', 'custom-emails-for-woocommerce' ) . '</th>' .
					'<th>' . __( 'Date', 'custom-emails-for-woocommerce' ) . '</th>' .
					'<th>' .
						__( 'Object ID', 'custom-emails-for-woocommerce' ) . ' ' .
						wc_help_tip( __( 'E.g., order ID.', 'custom-emails-for-woocommerce' ) ) .
					'</th>' .
					'<th></th>' .
				'</tr>' .
				'<tr>' . implode( '</tr><tr>', $result ) . '</tr>' .
			'</tbody></table>' .
			'<p><a href="">' . __( 'Refresh list', 'custom-emails-for-woocommerce' ) . '</a></p>' .
			'<p>' . sprintf(
				/* Translators: %s: Time. */
				__( 'Current time: %s', 'custom-emails-for-woocommerce' ),
				date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
			) . '</p>';

		}

	}

	/**
	 * get_settings.
	 *
	 * @version 3.7.3
	 * @since   1.3.0
	 */
	function get_settings() {
		return array(
			array(
				'title' => __( 'Scheduled Emails', 'custom-emails-for-woocommerce' ),
				'type'  => 'title',
				'id'    => 'wpfactory_wc_custom_emails_scheduled',
				'desc'  => $this->get_delayed_emails_info(),
			),
			array(
				'type'  => 'sectionend',
				'id'    => 'wpfactory_wc_custom_emails_scheduled',
			),
		);
	}

}

endif;

return new WPFactory_WC_Custom_Emails_Settings_Scheduled();
