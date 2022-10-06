<?php
/**
 * Custom Emails for WooCommerce - Scheduled Section Settings
 *
 * @version 1.3.0
 * @since   1.3.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
	 * get_delayed_emails_info.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 *
	 * @todo    [next] (desc) better desc: "No scheduled emails found ..."
	 * @todo    [next] (dev) better solution? i.e. instead of `_get_cron_array()`
	 * @todo    [next] (dev) code refactoring, e.g. `$title = ...`
	 * @todo    [later] (dev) `human_time_diff()`
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
							$result[] = sprintf( '<td>%s</td><td>%s</td><td>%s</td>',
								$title, date_i18n( 'Y-m-d H:i:s', $time ), $_cron['args'][1] );
						}
					}
				}
			}
		}
		return ( empty( $result ) ? '<p><em>' . __( 'No scheduled emails found.', 'custom-emails-for-woocommerce' ) . '</em></p>' :
			'<table class="widefat striped"><tbody>' .
				'<tr>' .
					'<th>' . __( 'Email', 'custom-emails-for-woocommerce' ) . '</th>' .
					'<th>' . __( 'Date', 'custom-emails-for-woocommerce' ) . '</th>' .
					'<th>' . __( 'Object ID', 'custom-emails-for-woocommerce' ) . ' ' . wc_help_tip( __( 'E.g. order ID.', 'custom-emails-for-woocommerce' ) ) . '</th>' .
				'</tr>' .
				'<tr>' . implode( '</tr><tr>', $result ) . '</tr>' .
			'</tbody></table>' ) .
			'<p><a href="">' . __( 'Refresh list', 'custom-emails-for-woocommerce' ) . '</a></p>' .
			'<p>' . sprintf( __( 'Current time: %s', 'custom-emails-for-woocommerce' ), date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ) . '</p>';
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
				'title'    => __( 'Scheduled Emails', 'custom-emails-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_custom_emails_scheduled',
				'desc'     => $this->get_delayed_emails_info(),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_custom_emails_scheduled',
			),
		);
	}

}

endif;

return new Alg_WC_Custom_Emails_Settings_Scheduled();
