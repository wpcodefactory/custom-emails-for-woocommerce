<?php
/**
 * Custom Emails for WooCommerce - Shortcodes Class
 *
 * @version 3.6.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Emails_Shortcodes' ) ) :

class Alg_WC_Custom_Emails_Shortcodes {

	/**
	 * shortcodes.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	public $shortcodes = array();

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
	 * product.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 */
	public $product = false;

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
	 * @version 3.6.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) not order related (e.g., customer; product)
	 * @todo    (dev) `[order_total_in_words]`
	 * @todo    (dev) maybe use more general shortcodes (e.g., `[order]`) instead? or even more general (e.g., `[prop]`)?
	 */
	function __construct() {

		$this->shortcodes = array(

			'order_billing_address',
			'order_billing_email',
			'order_billing_first_name',
			'order_billing_last_name',
			'order_billing_phone',
			'order_cancel_url',
			'order_checkout_payment_url',
			'order_customer_note',
			'order_customer_order_notes',
			'order_date',
			'order_details',
			'order_downloads',
			'order_edit_url',
			'order_func',
			'order_id',
			'order_item_meta',
			'order_item_names',
			'order_item_product_ids',
			'order_item_product_images',
			'order_meta',
			'order_number',
			'order_payment_method_id',
			'order_payment_method_title',
			'order_received_url',
			'order_shipping_address',
			'order_shipping_address_map_url',
			'order_shipping_method',
			'order_shipping_total',
			'order_total',
			'order_total_excl_tax',
			'order_total_items_count',
			'order_total_items_qty',
			'order_total_tax',
			'order_user_data',
			'order_user_id',
			'order_user_meta',
			'order_view_url',

			'generate_coupon_code',
			'user_prop',
			'product_func',

			'translate',

		);

		$prefix = apply_filters( 'alg_wc_custom_emails_shortcode_prefix', '' );

		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $prefix . $shortcode, array( $this, $shortcode ) );
		}

	}

	/**
	 * translate_get_current_language.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 *
	 * @todo    (dev) WPML, Polylang: order language (see `get_order_wpml_language()`)
	 * @todo    (v3.6.0) use `get_locale()`?
	 * @todo    (v3.6.0) use `$_POST['language']`?
	 *
	 * @see     https://translatepress.com/docs/developers/
	 */
	function translate_get_current_language() {

		// Order
		if ( $this->order ) {

			// TranslatePress
			if ( '' !== ( $meta_value = $this->order->get_meta( 'trp_language' ) ) ) {
				return strtolower( $meta_value );
			}

		}

		// WPML, Polylang
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			return strtolower( ICL_LANGUAGE_CODE );
		}

		// TranslatePress
		global $TRP_LANGUAGE;
		if ( $TRP_LANGUAGE ) {
			return strtolower( $TRP_LANGUAGE );
		}

		return false;
	}

	/**
	 * translate.
	 *
	 * @version 3.6.0
	 * @since   1.7.0
	 */
	function translate( $atts, $content = '' ) {

		$current_language = $this->translate_get_current_language();

		// E.g.: `[translate lang="EN,DE" lang_text="Text for EN & DE" not_lang_text="Text for other languages"]`
		if (
			isset( $atts['lang_text'] ) &&
			isset( $atts['not_lang_text'] ) &&
			! empty( $atts['lang'] )
		) {
			return (
				(
					false === $current_language ||
					! in_array(
						$current_language,
						array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) )
					)
				) ?
				wp_kses_post( $atts['not_lang_text'] ) :
				wp_kses_post( $atts['lang_text'] )
			);
		}

		// E.g.: `[translate lang="EN,DE"]Text for EN & DE[/translate][translate not_lang="EN,DE"]Text for other languages[/translate]`
		return (
			(
				(
					! empty( $atts['lang'] ) &&
					(
						false === $current_language ||
						! in_array(
							$current_language,
							array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) )
						)
					)
				) ||
				(
					! empty( $atts['not_lang'] ) &&
					(
						false !== $current_language &&
						in_array(
							$current_language,
							array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) )
						)
					)
				)
			) ?
			'' :
			wp_kses_post( $content )
		);

	}

	/**
	 * generate_coupon_code.
	 *
	 * @version 3.5.0
	 * @since   1.1.0
	 *
	 * @todo    (dev) generate coupon from *order*
	 * @todo    (dev) more `$atts`, e.g., `discount_type`
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
			'post_excerpt' => sprintf(
				/* Translators: %s: Plugin name. */
				esc_html__( 'Created by the "%s" plugin', 'custom-emails-for-woocommerce' ),
				__( 'Additional Custom Emails & Recipients for WooCommerce', 'custom-emails-for-woocommerce' )
			),
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
	 * product_func.
	 *
	 * @version 2.6.0
	 * @since   2.6.0
	 *
	 * @todo    (dev) add (optional) function args
	 */
	function product_func( $atts, $content = '' ) {
		if (
			! $this->product ||
			! isset( $atts['func'] ) ||
			! is_callable( array( $this->product, $atts['func'] ) )
		) {
			return '';
		}
		$func = $atts['func'];
		return $this->return_shortcode( $this->product->{$func}(), $atts );
	}

	/**
	 * user_prop.
	 *
	 * @version 2.2.5
	 * @since   2.2.5
	 */
	function user_prop( $atts, $content = '' ) {
		if ( ! $this->user || ! isset( $atts['key'] ) ) {
			return '';
		}
		return $this->return_shortcode( $this->user->get( $atts['key'] ), $atts );
	}

	/**
	 * order_customer_order_notes.
	 *
	 * @version 2.9.6
	 * @since   2.9.6
	 *
	 * @todo    (dev) `comment_date`?
	 * @todo    (dev) `comment_author`, `comment_author_email`?
	 * @todo    (dev) customizable glue (`<br>`)?
	 * @todo    (dev) customizable sorting?
	 */
	function order_customer_order_notes( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		$notes = $this->order->get_customer_order_notes();
		$notes = wp_list_pluck( $notes, 'comment_content' );
		$notes = implode( '<br>', $notes );
		return $this->return_shortcode( $notes, $atts );
	}

	/**
	 * order_checkout_payment_url.
	 *
	 * @version 2.9.0
	 * @since   2.9.0
	 *
	 * @todo    (dev) add `[order_checkout_payment_link]` shortcode (i.e., `<a href="...">Pay</a>`)?
	 */
	function order_checkout_payment_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_checkout_payment_url(), $atts );
	}

	/**
	 * order_cancel_url.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_cancel_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_cancel_order_url(), $atts );
	}

	/**
	 * order_received_url.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_received_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_checkout_order_received_url(), $atts );
	}

	/**
	 * order_edit_url.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_edit_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_edit_order_url(), $atts );
	}

	/**
	 * order_shipping_address_map_url.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_shipping_address_map_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_shipping_address_map_url(), $atts );
	}

	/**
	 * order_view_url.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_view_url( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_view_order_url(), $atts );
	}

	/**
	 * order_billing_email.
	 *
	 * @version 2.8.1
	 * @since   2.8.1
	 */
	function order_billing_email( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_billing_email(), $atts );
	}

	/**
	 * order_billing_phone.
	 *
	 * @version 2.8.1
	 * @since   2.8.1
	 */
	function order_billing_phone( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_billing_phone(), $atts );
	}

	/**
	 * order_billing_last_name.
	 *
	 * @version 2.7.2
	 * @since   2.7.2
	 */
	function order_billing_last_name( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_billing_last_name(), $atts );
	}

	/**
	 * order_billing_first_name.
	 *
	 * @version 2.7.2
	 * @since   2.7.2
	 */
	function order_billing_first_name( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_billing_first_name(), $atts );
	}

	/**
	 * order_customer_note.
	 *
	 * @version 2.7.2
	 * @since   2.7.2
	 */
	function order_customer_note( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_customer_note(), $atts );
	}

	/**
	 * order_user_meta.
	 *
	 * @version 3.0.4
	 * @since   3.0.4
	 */
	function order_user_meta( $atts, $content = '' ) {
		if (
			! $this->order ||
			! isset( $atts['key'] ) ||
			! ( $user_id = $this->order->get_user_id() )
		) {
			return '';
		}
		return $this->return_shortcode( get_user_meta( $user_id, $atts['key'], true ), $atts );
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
				'<pre>' . print_r( $item->get_meta_data(), true ) . '</pre>' // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
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
	 * order_item_product_images.
	 *
	 * @version 3.0.1
	 * @since   3.0.1
	 *
	 * @todo    (dev) customizable separator
	 */
	function order_item_product_images( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		$order_item_product_images = array();
		foreach ( $this->order->get_items() as $item ) {
			if (
				is_callable( array( $item, 'get_product' ) ) &&
				( $product = $item->get_product() ) &&
				( $image = $product->get_image() )
			) {
				$order_item_product_images[] = $image;
			}
		}
		$order_item_product_images = implode( '<br>', $order_item_product_images );
		return $this->return_shortcode( $order_item_product_images, $atts );
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
	 * order_total_items_qty.
	 *
	 * @version 2.9.5
	 * @since   2.9.5
	 */
	function order_total_items_qty( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		$qty = 0;
		foreach ( $this->order->get_items() as $item ) {
			$qty += $item->get_quantity();
		}
		return $this->return_shortcode( $qty, $atts );
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
	 * add_order_details_product_link.
	 *
	 * @version 2.9.7
	 * @since   2.9.7
	 *
	 * @todo    (dev) No changes: add `... || ! ( $name = $item->get_name() ) ...`?
	 */
	function add_order_details_product_link( $item_name, $item ) {

		// No changes
		if (
			! is_callable( array( $item, 'get_product' ) ) ||
			! ( $product = $item->get_product() ) ||
			! ( $product_permalink = $product->get_permalink() )
		) {
			return $item_name;
		}

		// Permalink
		return sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() );

	}

	/**
	 * add_order_details_product_image.
	 *
	 * @version 3.0.1
	 * @since   3.0.1
	 *
	 * @todo    (dev) `$args['image_size'] = array( 32, 32 );`
	 */
	function add_order_details_product_image( $args ) {
		$args['show_image'] = true;
		return $args;
	}

	/**
	 * add_order_details_product_short_desc.
	 *
	 * @version 3.0.4
	 * @since   3.0.4
	 */
	function add_order_details_product_short_desc( $item_id, $item, $order, $plain_text ) {
		$this->add_order_details_product_desc( $item_id, $item, $order, $plain_text, 'short' );
	}

	/**
	 * add_order_details_product_long_desc.
	 *
	 * @version 3.0.4
	 * @since   3.0.4
	 */
	function add_order_details_product_long_desc( $item_id, $item, $order, $plain_text ) {
		$this->add_order_details_product_desc( $item_id, $item, $order, $plain_text, 'long' );
	}

	/**
	 * add_order_details_product_desc.
	 *
	 * @version 3.5.0
	 * @since   3.0.4
	 */
	function add_order_details_product_desc( $item_id, $item, $order, $plain_text, $short_or_long ) {

		// Get product
		if (
			! is_callable( array( $item, 'get_product' ) ) ||
			! ( $product = $item->get_product() )
		) {
			return;
		}

		// Get product description
		$product_desc = (
			'short' === $short_or_long ?
			$product->get_short_description() :
			$product->get_description()
		);
		if ( '' === $product_desc ) {
			return;
		}

		// Plain text or HTML
		$product_desc = (
			$plain_text ?
			"\n" . strip_tags( $product_desc ) : // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
			'<br>' . $product_desc
		);

		// Output product description
		echo wp_kses_post( $product_desc );

	}

	/**
	 * order_details.
	 *
	 * @version 3.0.4
	 * @since   1.0.0
	 */
	function order_details( $atts, $content = '' ) {

		if ( ! $this->order || ! $this->email ) {
			return '';
		}

		// Atts
		$sent_to_admin             = ( isset( $atts['sent_to_admin'] )          && filter_var( $atts['sent_to_admin'],          FILTER_VALIDATE_BOOLEAN ) );
		$plain_text                = ( isset( $atts['plain_text'] )             && filter_var( $atts['plain_text'],             FILTER_VALIDATE_BOOLEAN ) );
		$do_add_product_links      = ( isset( $atts['add_product_links'] )      && filter_var( $atts['add_product_links'],      FILTER_VALIDATE_BOOLEAN ) );
		$do_add_product_images     = ( isset( $atts['add_product_images'] )     && filter_var( $atts['add_product_images'],     FILTER_VALIDATE_BOOLEAN ) );
		$do_add_product_desc       = ( isset( $atts['add_product_desc'] )       && filter_var( $atts['add_product_desc'],       FILTER_VALIDATE_BOOLEAN ) );
		$do_add_product_short_desc = ( isset( $atts['add_product_short_desc'] ) && filter_var( $atts['add_product_short_desc'], FILTER_VALIDATE_BOOLEAN ) );

		// WC Emails
		$wc_emails = WC_Emails::instance();

		// Turn on output buffering
		ob_start();

		// Product links
		if ( $do_add_product_links ) {
			add_filter( 'woocommerce_order_item_name', array( $this, 'add_order_details_product_link' ), PHP_INT_MAX, 2 );
		}

		// Product images
		if ( $do_add_product_images ) {
			add_filter( 'woocommerce_email_order_items_args', array( $this, 'add_order_details_product_image' ), PHP_INT_MAX );
		}

		// Product desc
		if ( $do_add_product_desc ) {
			add_action( 'woocommerce_order_item_meta_end', array( $this, 'add_order_details_product_long_desc' ), 10, 4 );
		}

		// Product short desc
		if ( $do_add_product_short_desc ) {
			add_action( 'woocommerce_order_item_meta_end', array( $this, 'add_order_details_product_short_desc' ), 10, 4 );
		}

		// Order details
		$wc_emails->order_details( $this->order, $sent_to_admin, $plain_text, $this->email );

		// Product links
		if ( $do_add_product_links ) {
			remove_filter( 'woocommerce_order_item_name', array( $this, 'add_order_details_product_link' ), PHP_INT_MAX );
		}

		// Product images
		if ( $do_add_product_images ) {
			remove_filter( 'woocommerce_email_order_items_args', array( $this, 'add_order_details_product_image' ), PHP_INT_MAX );
		}

		// Product desc
		if ( $do_add_product_desc ) {
			remove_action( 'woocommerce_order_item_meta_end', array( $this, 'add_order_details_product_long_desc' ), 10 );
		}

		// Product short desc
		if ( $do_add_product_short_desc ) {
			remove_action( 'woocommerce_order_item_meta_end', array( $this, 'add_order_details_product_short_desc' ), 10 );
		}

		// The end
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
	 * order_id.
	 *
	 * @version 2.9.4
	 * @since   2.9.4
	 */
	function order_id( $atts, $content = '' ) {
		if ( ! $this->order ) {
			return '';
		}
		return $this->return_shortcode( $this->order->get_id(), $atts );
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
	 * return_shortcode.
	 *
	 * @version 3.6.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) more common atts, e.g., on_empty, find/replace, strip_tags, any_func, etc.
	 */
	function return_shortcode( $value, $atts ) {

		// Add, multiply
		if ( is_numeric( $value ) ) {
			if ( ! empty( $atts['add'] ) ) {
				$value += $atts['add'];
			}
			if ( ! empty( $atts['multiply'] ) ) {
				$value *= $atts['multiply'];
			}
		}

		// Format
		if ( isset( $atts['format'] ) ) {
			switch ( $atts['format'] ) {
				case 'price':
					$value = wc_price( $value );
					break;
				default:
					$value = sprintf( $atts['format'], $value );
			}
		}

		// Before, after
		return (
			'' !== $value ?
			(
				( isset( $atts['before'] ) ? wp_kses_post( $atts['before'] ) : '' ) .
					$value .
				( isset( $atts['after'] )  ? wp_kses_post( $atts['after'] )  : '' )
			) :
			''
		);

	}

}

endif;

return new Alg_WC_Custom_Emails_Shortcodes();
