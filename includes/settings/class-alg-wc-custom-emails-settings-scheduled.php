<?php
/**
 * Custom Emails for WooCommerce - Scheduled Section Settings
 *
 * @version 1.9.5
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Settings_Scheduled' ) ) :

class Alg_WC_Custom_Emails_Settings_Scheduled extends Alg_WC_Custom_Emails_Settings_Section {

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
	 * get_unschedule_button_html.
	 *
	 * @version 1.9.5
	 * @since   1.9.5
	 */
	function get_unschedule_button_html( $class, $object_id, $timestamp ) {
		return sprintf( '<a href="%s" title="%s" class="%s">%s</a>',
			wp_nonce_url( add_query_arg( array(
				'alg_wc_ce_unschedule'           => $class,
				'alg_wc_ce_unschedule_object_id' => $object_id,
				'alg_wc_ce_unschedule_time'      => $timestamp,
			) ) ),
			esc_html__( 'Delete', 'custom-emails-for-woocommerce' ),
			'alg-wc-custom-emails-unschedule',
			'<span class="dashicons dashicons-trash"></span>' );
	}

	/**
	 * get_delayed_emails_info.
	 *
	 * @version 1.9.5
	 * @since   1.3.0
	 *
	 * @todo    (dev) better solution instead of `_get_cron_array()`?
	 * @todo    (dev) code refactoring, e.g., `$title = ...`
	 * @todo    (desc) better desc: "No scheduled emails found ..."
	 * @todo    (dev) `human_time_diff()`
	 * @todo    (feature) add "send now" buttons?
	 */
	function get_delayed_emails_info() {

		$result = array();
		$crons  = _get_cron_array();

		if ( ! empty( $crons ) ) {
			$titles = get_option( 'alg_wc_custom_emails_titles', array() );

			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron['alg_wc_custom_emails_send_email'] ) ) {

					foreach ( $cron['alg_wc_custom_emails_send_email'] as $_cron ) {
						if ( 2 == count( $_cron['args'] ) ) {

							$id    = str_replace( array( 'Alg_WC_Custom_Email', '_' ), '', $_cron['args'][0] );
							$id    = ( ! empty( $id ) ? $id : 1 );
							$title = ( isset( $titles[ $id ] ) ? $titles[ $id ] :
								( 1 == $id ? __( 'Custom email', 'custom-emails-for-woocommerce' ) : sprintf( __( 'Custom email #%d', 'custom-emails-for-woocommerce' ), $id ) ) );
							$time  = $timestamp + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

							$result[] = sprintf( '<td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
								$title, date_i18n( 'Y-m-d H:i:s', $time ), $_cron['args'][1], $this->get_unschedule_button_html( $_cron['args'][0], $_cron['args'][1], $timestamp ) );

						}
					}

				}
			}

		}

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
