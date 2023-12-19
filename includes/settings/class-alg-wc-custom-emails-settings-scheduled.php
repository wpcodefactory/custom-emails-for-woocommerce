<?php
/**
 * Custom Emails for WooCommerce - Scheduled Section Settings
 *
 * @version 2.7.0
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Settings_Scheduled' ) ) :

class Alg_WC_Custom_Emails_Settings_Scheduled extends Alg_WC_Custom_Emails_Settings_Section {

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
	 * @version 2.7.0
	 * @since   1.9.5
	 */
	function get_unschedule_button_html( $url ) {
		return sprintf( '<a href="%s" title="%s" class="%s">%s</a>',
			$url,
			esc_html__( 'Cancel', 'custom-emails-for-woocommerce' ),
			'alg-wc-custom-emails-unschedule',
			'<span class="dashicons dashicons-trash"></span>'
		);
	}

	/**
	 * get_unschedule_button_html_wp_cron.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function get_unschedule_button_html_wp_cron( $class, $object_id, $timestamp ) {
		$url = wp_nonce_url( add_query_arg( array(
			'alg_wc_ce_unschedule_class'     => $class,
			'alg_wc_ce_unschedule_object_id' => $object_id,
			'alg_wc_ce_unschedule_time'      => $timestamp,
			'alg_wc_ce_unscheduler'          => 'wp_cron',
		) ) );
		return $this->get_unschedule_button_html( $url );
	}

	/**
	 * get_unschedule_button_html_as.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function get_unschedule_button_html_as( $action_id ) {
		$url = wp_nonce_url( add_query_arg( array(
			'alg_wc_ce_unschedule_action_id' => $action_id,
			'alg_wc_ce_unscheduler'          => 'as',
		) ) );
		return $this->get_unschedule_button_html( $url );
	}

	/**
	 * get_email_title_from_class.
	 *
	 * @version 2.7.0
	 * @since   2.7.0
	 */
	function get_email_title_from_class( $class ) {
		if ( ! isset( $this->email_titles ) ) {
			$this->email_titles = get_option( 'alg_wc_custom_emails_titles', array() );
		}
		$id = str_replace( array( 'Alg_WC_Custom_Email', '_' ), '', $class );
		$id = ( ! empty( $id ) ? $id : 1 );
		return ( $this->email_titles[ $id ] ??
			( 1 == $id ? __( 'Custom email', 'custom-emails-for-woocommerce' ) : sprintf( __( 'Custom email #%d', 'custom-emails-for-woocommerce' ), $id ) ) );
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
	 * @version 2.7.0
	 * @since   1.3.0
	 *
	 * @todo    (dev) better solution instead of `_get_cron_array()`?
	 * @todo    (desc) better desc: "No scheduled emails found ..."
	 * @todo    (dev) `human_time_diff()`
	 * @todo    (feature) add "send now" buttons?
	 */
	function get_delayed_emails_info() {

		$result = array();

		// WP Cron
		$crons = _get_cron_array();
		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron['alg_wc_custom_emails_send_email'] ) ) {
					foreach ( $cron['alg_wc_custom_emails_send_email'] as $_cron ) {
						if ( 2 == count( $_cron['args'] ) ) {
							$class     = $_cron['args'][0];
							$object_id = $_cron['args'][1];
							$result[] = sprintf( '<td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
								$this->get_email_title_from_class( $class ),
								$this->get_formatted_local_time( $timestamp ),
								$object_id,
								$this->get_unschedule_button_html_wp_cron( $class, $object_id, $timestamp )
							);
						}
					}
				}
			}
		}

		// Action Scheduler
		$scheduled_actions = as_get_scheduled_actions( array(
			'hook'     => 'alg_wc_custom_emails_send_email',
			'per_page' => -1,
			'status'   => ActionScheduler_Store::STATUS_PENDING,
		) );
		if ( ! empty( $scheduled_actions ) ) {
			foreach ( $scheduled_actions as $scheduled_action_id => $scheduled_action ) {
				$args = $scheduled_action->get_args();
				if ( 2 == count( $args ) ) {
					$class     = $args[0];
					$object_id = $args[1];
					$result[] = sprintf( '<td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
						$this->get_email_title_from_class( $class ),
						$this->get_formatted_local_time( $scheduled_action->get_schedule()->get_date()->getTimestamp() ),
						$object_id,
						$this->get_unschedule_button_html_as( $scheduled_action_id )
					);
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
					'<th>' . __( 'Object ID', 'custom-emails-for-woocommerce' ) . ' ' . wc_help_tip( __( 'E.g., order ID.', 'custom-emails-for-woocommerce' ) ) . '</th>' .
					'<th></th>' .
				'</tr>' .
				'<tr>' . implode( '</tr><tr>', $result ) . '</tr>' .
			'</tbody></table>' .
			'<p><a href="">' . __( 'Refresh list', 'custom-emails-for-woocommerce' ) . '</a></p>' .
			'<p>' . sprintf( __( 'Current time: %s', 'custom-emails-for-woocommerce' ), date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ) . '</p>';

		}

	}

	/**
	 * get_settings.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function get_settings() {
		return array(
			array(
				'title' => __( 'Scheduled Emails', 'custom-emails-for-woocommerce' ),
				'type'  => 'title',
				'id'    => 'alg_wc_custom_emails_scheduled',
				'desc'  => $this->get_delayed_emails_info(),
			),
			array(
				'type'  => 'sectionend',
				'id'    => 'alg_wc_custom_emails_scheduled',
			),
		);
	}

}

endif;

return new Alg_WC_Custom_Emails_Settings_Scheduled();
