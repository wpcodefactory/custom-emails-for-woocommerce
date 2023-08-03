<?php
/**
 * Custom Emails for WooCommerce - Emails Shortcodes Class
 *
 * @version 2.2.4
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Emails_Shortcodes' ) ) :

class Alg_WC_Custom_Emails_Shortcodes {

	/**
	 * order.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $order = false;

	/**
	 * user.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $user = false;

	/**
	 * email.
	 *
	 * @version 2.1.0
	 * @since   1.0.0
	 */
	public $email = false;

	/**
	 * Constructor.
	 *
	 * @version 2.2.4
	 * @since   1.0.0
	 *
	 * @todo    (dev) not order related (e.g., customer; product)
	 * @todo    (dev) `[order_total_in_words]`
	 * @todo    (dev) maybe use more general shortcodes (e.g., `[order]`) instead? or even more general (e.g., `[prop]`)?
	 */
	function __construct() {
		add_shortcode( 'if',                         array( $this, 'if' ) );
		add_shortcode( 'clear',                      array( $this, 'clear' ) );
		add_shortcode( 'site_title',                 array( $this, 'site_title' ) );
		add_shortcode( 'site_address',               array( $this, 'site_address' ) );
		add_shortcode( 'translate',                  array( $this, 'translate' ) );
		add_shortcode( 'order_meta',                 array( $this, 'order_meta' ) );
		add_shortcode( 'order_func',                 array( $this, 'order_func' ) );
		add_shortcode( 'order_number',               array( $this, 'order_number' ) );
		add_shortcode( 'order_total',                array( $this, 'order_total' ) );
		add_shortcode( 'order_total_tax',            array( $this, 'order_total_tax' ) );
		add_shortcode( 'order_total_excl_tax',       array( $this, 'order_total_excl_tax' ) );
		add_shortcode( 'order_shipping_total',       array( $this, 'order_shipping_total' ) );
		add_shortcode( 'order_shipping_method',      array( $this, 'order_shipping_method' ) );
		add_shortcode( 'order_payment_method_id',    array( $this, 'order_payment_method_id' ) );
		add_shortcode( 'order_payment_method_title', array( $this, 'order_payment_method_title' ) );
		add_shortcode( 'order_total_items_count',    array( $this, 'order_total_items_count' ) );
		add_shortcode( 'order_date',                 array( $this, 'order_date' ) );
		add_shortcode( 'order_details',              array( $this, 'order_details' ) );
		add_shortcode( 'order_downloads',            array( $this, 'order_downloads' ) );
		add_shortcode( 'order_billing_address',      array( $this, 'order_billing_address' ) );
		add_shortcode( 'order_shipping_address',     array( $this, 'order_shipping_address' ) );
		add_shortcode( 'order_item_meta',            array( $this, 'order_item_meta' ) );
		add_shortcode( 'order_item_names',           array( $this, 'order_item_names' ) );
		add_shortcode( 'order_item_product_ids',     array( $this, 'order_item_product_ids' ) );
		add_shortcode( 'order_user_id',              array( $this, 'order_user_id' ) );
		add_shortcode( 'order_user_data',            array( $this, 'order_user_data' ) );
		add_shortcode( 'generate_coupon_code',       array( $this, 'generate_coupon_code' ) );
	}

	/**
	 * translate.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 *
	 * @todo    (dev) try to get *order* language (see `get_order_wpml_language()`)
	 */
	function translate( $atts, $content = '' ) {
		// E.g.: `[translate lang="EN,DE" lang_text="Text for EN & DE" not_lang_text="Text for other languages"]`
		if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
			return ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
				$atts['not_lang_text'] : $atts['lang_text'];
		}
		// E.g.: `[translate lang="EN,DE"]Text for EN & DE[/translate][translate not_lang="EN,DE"]Text for other languages[/translate]`
		return (
			( ! empty( $atts['lang'] )     && ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
			( ! empty( $atts['not_lang'] ) &&     defined( 'ICL_LANGUAGE_CODE' ) &&   in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
		) ? '' : $content;
	}

	/**
	 * generate_coupon_code.
	 *
	 * @version 1.7.2
	 * @since   1.1.0
	 *
	 * @todo    (dev) generate coupon from *order*
	 * @todo    (dev) more `$atts`, e.g. `discount_type`
	 * @todo    (dev) optional `customer_email`
	 * @todo    (dev) optional `first_name` in coupon code
	 */
	function generate_coupon_code( $atts, $content = '' ) {
		if ( ! $this->user || ! isset( $atts['amount'] ) ) {
			return '';
		}
		// Values
		$coupon_code   = $this->user->first_name;
		$coupon_amount = $atts['amount'];
		// Generate valid code
		$i = 0;
		$coupon = new WC_Coupon( $coupon_code );
		while ( $coupon && $coupon->get_date_created() ) {
			$i++;
			$coupon_code = esc_html( sprintf( '%s-%d', $this->user->first_name, $i ) );
			$coupon      = new WC_Coupon( $coupon_code );
		}
		// Create new coupon
		$coupon = array(
			'post_title'   => $coupon_code,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'shop_coupon',
			'post_excerpt' => sprintf( esc_html__( 'Created by the "%s" plugin', 'custom-emails-for-woocommerce' ),
				__( 'Custom Emails for WooCommerce', 'custom-emails-for-woocommerce' ) ),
		);
		$coupon_id = wp_insert_post( $coupon );
		if ( $coupon_id && ! is_wp_error( $coupon_id ) ) {
			$data = array(
				'product_ids'            => '',
				'exclude_product_ids'    => '',
				'discount_type'          => 'percent', // `fixed_cart`, `percent`, `fixed_product`, `percent_product`?
				'free_shipping'          => 'no',
				'coupon_amount'          => $coupon_amount,
				'individual_use'         => 'no',
				'expiry_date'            => '',
				'usage_limit'            => 1,
				'usage_limit_per_user'   => 1,
				'customer_email'         => $this->user->user_email,
				'apply_before_tax'       => 'yes',
			);
			foreach ( $data as $key => $value ) {
				update_post_meta( $coupon_id, $key, $value );
			}
			return $coupon_code;
		} else {
			return '';
		}
	}

	/**
	 * order_user_id.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function order_user_id( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_user_id(), $atts );
	}

	/**
	 * order_user_data.
	 *
	 * e.g., `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function order_user_data( $atts, $content = '' ) {
		if ( ! $this->order || ! isset( $atts['key'] ) ) {
			return '';
		}
		$key = $atts['key'];
		$res = ( ( $user = $this->order->get_user() ) && isset( $user->data->{$key} ) ? $user->data->{$key} : '' );
		return $this->return_shortcode( $res, $atts );
	}

	/**
	 * order_item_names.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 *
	 * @todo    (feature) optionally `$product->get_formatted_name()`
	 * @todo    (feature) customizable sep?
	 * @todo    (feature) `[order_item_props]`
	 */
	function order_item_names( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		$order_item_names = array();
		foreach ( $this->order->get_items() as $item ) {
			$order_item_names[] = $item['name'];
		}
		$order_item_names = implode( ', ', $order_item_names );
		return $this->return_shortcode( $order_item_names, $atts );
	}

	/**
	 * order_item_meta.
	 *
	 * @version 2.2.4
	 * @since   2.2.4
	 *
	 * @todo    (feature) customizable sep?
	 */
	function order_item_meta( $atts, $content = '' ) {
		if ( ! $this->order || ( ! isset( $atts['key'] ) && empty( $atts['debug'] ) ) ) {
			return '';
		}
		$is_debug = ( ! isset( $atts['key'] ) );
		$meta     = array();
		foreach ( $this->order->get_items() as $item ) {
			$meta[] = ( ! $is_debug ?
				$item->get_meta( $atts['key'] ) :
				'<pre>' . print_r( $item->get_meta_data(), true ) . '</pre>'
			);
		}
		$sep  = ( ! $is_debug ? ', ' : '' );
		$meta = implode( $sep, $meta );
		return $this->return_shortcode( $meta, $atts );
	}

	/**
	 * order_item_product_ids.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 *
	 * @todo    (feature) customizable sep?
	 * @todo    (feature) optionally `product_id` only (i.e., ignore `variation_id`)?
	 */
	function order_item_product_ids( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		$order_item_product_ids = array();
		foreach ( $this->order->get_items() as $item ) {
			$order_item_product_ids[] = ( ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'] );
		}
		$order_item_product_ids = implode( ', ', $order_item_product_ids );
		return $this->return_shortcode( $order_item_product_ids, $atts );
	}

	/**
	 * order_total.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_total( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_total(), $atts );
	}

	/**
	 * order_total_tax.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_total_tax( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_total_tax(), $atts );
	}

	/**
	 * order_total_excl_tax.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_total_excl_tax( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( ( $this->order->get_total() - $this->order->get_total_tax() ), $atts );
	}

	/**
	 * order_shipping_total.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function order_shipping_total( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_shipping_total(), $atts );
	}

	/**
	 * order_shipping_method.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function order_shipping_method( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_shipping_method(), $atts );
	}

	/**
	 * order_payment_method_title.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function order_payment_method_title( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_payment_method_title(), $atts );
	}

	/**
	 * order_payment_method_id.
	 *
	 * @version 2.2.1
	 * @since   2.2.1
	 */
	function order_payment_method_id( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_payment_method(), $atts );
	}

	/**
	 * order_total_items_count.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_total_items_count( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( count( $this->order->get_items() ), $atts );
	}

	/**
	 * if.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function if( $atts, $content = '' ) {
		if ( ! isset( $atts['value1'], $atts['operator'], $atts['value2'] ) || '' === $content ) {
			return '';
		}
		$value1 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value1'] ) );
		$value2 = do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $atts['value2'] ) );
		return ( $this->eval_operator( $value1, $atts['operator'], $value2 ) ? do_shortcode( $content ) : '' );
	}

	/**
	 * eval_operator.
	 *
	 * @version 2.1.0
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
		}
		return false;
	}

	/**
	 * order_shipping_address.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_shipping_address( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_formatted_shipping_address(), $atts );
	}

	/**
	 * order_billing_address.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_billing_address( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_formatted_billing_address(), $atts );
	}

	/**
	 * order_details.
	 *
	 * @version 1.9.3
	 * @since   1.0.0
	 */
	function order_details( $atts, $content = '' ) {
		if ( ! $this->order || ! $this->email ) {
			return '';
		}
		$sent_to_admin = ( isset( $atts['sent_to_admin'] ) && filter_var( $atts['sent_to_admin'], FILTER_VALIDATE_BOOLEAN ) );
		$plain_text    = ( isset( $atts['plain_text'] )    && filter_var( $atts['plain_text'],    FILTER_VALIDATE_BOOLEAN ) );
		$wc_emails     = WC_Emails::instance();
		ob_start();
		$wc_emails->order_details( $this->order, $sent_to_admin, $plain_text, $this->email );
		return $this->return_shortcode( ob_get_clean(), $atts );
	}

	/**
	 * order_downloads.
	 *
	 * @version 2.1.0
	 * @since   2.1.0
	 */
	function order_downloads( $atts, $content = '' ) {
		if ( ! $this->order || ! $this->email ) {
			return '';
		}
		$sent_to_admin = ( isset( $atts['sent_to_admin'] ) && filter_var( $atts['sent_to_admin'], FILTER_VALIDATE_BOOLEAN ) );
		$plain_text    = ( isset( $atts['plain_text'] )    && filter_var( $atts['plain_text'],    FILTER_VALIDATE_BOOLEAN ) );
		$wc_emails     = WC_Emails::instance();
		ob_start();
		$wc_emails->order_downloads( $this->order, $sent_to_admin, $plain_text, $this->email );
		return $this->return_shortcode( ob_get_clean(), $atts );
	}

	/**
	 * order_date.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_date( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( wc_format_datetime( $this->order->get_date_created() ), $atts );
	}

	/**
	 * order_number.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function order_number( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_order_number(), $atts );
	}

	/**
	 * order_meta.
	 *
	 * @version 2.2.3
	 * @since   1.0.0
	 */
	function order_meta( $atts, $content = '' ) {
		if ( ! $this->order || ! isset( $atts['key'] ) ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_meta( $atts['key'] ), $atts );
	}

	/**
	 * order_func.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) add (optional) function args
	 */
	function order_func( $atts, $content = '' ) {
		if ( ! $this->order || ! isset( $atts['func'] ) || ! is_callable( array( $this->order, $atts['func'] ) ) ) {
			return '';
		}
		$func = $atts['func'];
		return $this->return_shortcode( $this->order->{$func}(), $atts );
	}

	/**
	 * site_title.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function site_title( $atts, $content = '' ) {
		return $this->return_shortcode( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $atts );
	}

	/**
	 * site_address.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function site_address( $atts, $content = '' ) {
		return $this->return_shortcode( wp_parse_url( home_url(), PHP_URL_HOST ), $atts );
	}

	/**
	 * clear.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function clear( $atts, $content = '' ) {
		return $this->return_shortcode( '<p></p>', $atts );
	}

	/**
	 * return_shortcode.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) more common atts, e.g. find/replace, strip_tags, any_func, etc.
	 */
	function return_shortcode( $value, $atts ) {
		if ( is_numeric( $value ) ) {
			if ( ! empty( $atts['add'] ) ) {
				$value += $atts['add'];
			}
			if ( ! empty( $atts['multiply'] ) ) {
				$value *= $atts['multiply'];
			}
		}
		if ( isset( $atts['format'] ) ) {
			switch ( $atts['format'] ) {
				case 'price':
					$value = wc_price( $value );
					break;
				default:
					$value = sprintf( $atts['format'], $value );
			}
		}
		return ( '' !== $value ? ( ( isset( $atts['before'] ) ? $atts['before'] : '' ) . $value . ( isset( $atts['after'] ) ? $atts['after'] : '' ) ) : '' );
	}

}

endif;

return new Alg_WC_Custom_Emails_Shortcodes();
