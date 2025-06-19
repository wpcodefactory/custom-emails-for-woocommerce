<?php
/**
 * Custom Emails for WooCommerce - General Shortcodes Class
 *
 * @version 3.6.0
 * @since   3.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Shortcodes_General' ) ) :

class Alg_WC_Custom_Emails_Shortcodes_General {

	/**
	 * Constructor.
	 *
	 * @version 3.6.0
	 * @since   3.0.0
	 */
	function __construct() {

		$shortcodes = array(
			'if',
			'clear',
			'site_title',
			'site_address',
		);

		$prefix = apply_filters( 'alg_wc_custom_emails_shortcode_prefix', '' );

		foreach ( $shortcodes as $shortcode ) {
			add_shortcode( $prefix . $shortcode, array( $this, $shortcode ) );
		}

	}

	/**
	 * site_title.
	 *
	 * @version 3.0.0
	 * @since   1.0.0
	 */
	function site_title( $atts, $content = '' ) {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * site_address.
	 *
	 * @version 3.0.0
	 * @since   1.0.0
	 */
	function site_address( $atts, $content = '' ) {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * clear.
	 *
	 * @version 3.0.0
	 * @since   1.0.0
	 */
	function clear( $atts, $content = '' ) {
		return '<p></p>';
	}

	/**
	 * if.
	 *
	 * @version 2.6.1
	 * @since   1.0.0
	 *
	 * @todo    (dev) rename the function to `shortcode_if`?
	 */
	function if( $atts, $content = '' ) {

		if ( ! isset( $atts['value1'], $atts['operator'], $atts['value2'] ) || '' === $content ) {
			return '';
		}

		$value1 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value1'] ) );
		$value2 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value2'] ) );

		if ( isset( $atts['case_insensitive'] ) && filter_var( $atts['case_insensitive'], FILTER_VALIDATE_BOOLEAN ) ) {
			$value1 = strtolower( $value1 );
			$value2 = strtolower( $value2 );
		}

		return ( $this->eval_operator( $value1, $atts['operator'], $value2 ) ? do_shortcode( $content ) : '' );

	}

	/**
	 * eval_operator.
	 *
	 * @version 2.6.1
	 * @since   1.8.0
	 */
	function eval_operator( $value1, $operator, $value2 ) {
		switch ( $operator ) {
			case 'equal':
				return ( $value1 == $value2 );
			case 'not_equal':
				return ( $value1 != $value2 );
			case 'less':
				return ( $value1 <  $value2 );
			case 'less_or_equal':
				return ( $value1 <= $value2 );
			case 'greater':
				return ( $value1 >  $value2 );
			case 'greater_or_equal':
				return ( $value1 >= $value2 );
			case 'in':
				return (   in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
			case 'not_in':
				return ( ! in_array( $value1, array_map( 'trim', explode( ',', $value2 ) ) ) );
			case 'find':
				return ( false !== strpos( $value2, $value1 ) );
			case 'not_find':
				return ( false === strpos( $value2, $value1 ) );
		}
		return false;
	}

}

endif;

return new Alg_WC_Custom_Emails_Shortcodes_General();
