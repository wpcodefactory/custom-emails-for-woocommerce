<?php
/**
 * Custom Emails for WooCommerce - Order Validator
 *
 * @version 1.8.0
 * @since   1.8.0
 *
 * @author  Algoritmika Ltd
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Email_Order_Validator' ) ) :

class Alg_WC_Custom_Email_Order_Validator {

	/**
	 * Constructor
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function __construct( $email ) {
		$this->email = $email;
	}

	/**
	 * validate
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function validate( $order ) {

		$checks = array(
			'required_products',
			'excluded_products',
			'required_product_cats',
			'excluded_product_cats',
			'required_product_tags',
			'excluded_product_tags',
			'min_amount',
			'max_amount',
		);

		if ( 'AND' === $this->email->get_option( 'order_conditions_logical_operator', 'AND' ) ) {

			// AND
			foreach ( $checks as $check ) {
				if ( false === $this->{$check}( $order ) ) {
					return false;
				}
			}
			return true;

		} else {

			// OR
			$is_all_empty = true;
			foreach ( $checks as $check ) {
				if ( true === ( $res = $this->{$check}( $order ) ) ) {
					return true;
				} elseif ( false === $res ) {
					$is_all_empty = false;
				}
			}
			return $is_all_empty;

		}

	}

	/**
	 * required_products
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function required_products( $order ) {
		$required_order_product_ids = $this->email->get_option( 'required_order_product_ids', array() );
		if ( ! empty( $required_order_product_ids ) && ! $this->check_order_products( $order, $required_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Require order product(s)', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $required_order_product_ids ) ? true : null );
	}

	/**
	 * excluded_products
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function excluded_products( $order ) {
		$excluded_order_product_ids = $this->email->get_option( 'excluded_order_product_ids', array() );
		if ( ! empty( $excluded_order_product_ids ) && $this->check_order_products( $order, $excluded_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Exclude order product(s)', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $excluded_order_product_ids ) ? true : null );
	}

	/**
	 * required_product_cats
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function required_product_cats( $order ) {
		$required_order_product_cats_ids = $this->email->get_option( 'required_order_product_cats_ids', array() );
		if ( ! empty( $required_order_product_cats_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Require order product categories', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $required_order_product_cats_ids ) ? true : null );
	}

	/**
	 * excluded_product_cats
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function excluded_product_cats( $order ) {
		$excluded_order_product_cats_ids = $this->email->get_option( 'excluded_order_product_cats_ids', array() );
		if ( ! empty( $excluded_order_product_cats_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Exclude order product categories', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $excluded_order_product_cats_ids ) ? true : null );
	}

	/**
	 * required_product_tags
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function required_product_tags( $order ) {
		$required_order_product_tags_ids = $this->email->get_option( 'required_order_product_tags_ids', array() );
		if ( ! empty( $required_order_product_tags_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Require order product tags', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $required_order_product_tags_ids ) ? true : null );
	}

	/**
	 * excluded_product_tags
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function excluded_product_tags( $order ) {
		$excluded_order_product_tags_ids = $this->email->get_option( 'excluded_order_product_tags_ids', array() );
		if ( ! empty( $excluded_order_product_tags_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Exclude order product tags', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $excluded_order_product_tags_ids ) ? true : null );
	}

	/**
	 * min_amount
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function min_amount( $order ) {
		$min_order_amount = $this->email->get_option( 'min_order_amount', '' );
		if ( ! empty( $min_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $min_order_amount ) && $this->get_order_amount( $order ) < $min_order_amount ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Minimum order amount', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $min_order_amount ) ? true : null );
	}

	/**
	 * max_amount
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	function max_amount( $order ) {
		$max_order_amount = $this->email->get_option( 'max_order_amount', '' );
		if ( ! empty( $max_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $max_order_amount ) && $this->get_order_amount( $order ) > $max_order_amount ) {
			alg_wc_custom_emails()->core->debug( sprintf( __( '%s: Blocked by the "%s" option.', 'custom-emails-for-woocommerce' ),
				$this->email->title, __( 'Maximum order amount', 'custom-emails-for-woocommerce' ) ) );
			return false;
		}
		return ( ! empty( $max_order_amount ) ? true : null );
	}

	/**
	 * check_order_products.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (feature) "require all products" (i.e., vs "require at least one")?
	 */
	function check_order_products( $order, $product_ids ) {
		foreach ( $order->get_items() as $item ) {
			if ( in_array( $item['product_id'], $product_ids ) || in_array( $item['variation_id'], $product_ids ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * check_order_product_terms.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @todo    [next] (feature) custom taxonomies
	 * @todo    [next] (feature) "require all" (i.e., vs "require at least one")?
	 */
	function check_order_product_terms( $order, $term_ids, $taxonomy ) {
		foreach ( $order->get_items() as $item ) {
			$_term_ids = get_the_terms( $item['product_id'], $taxonomy );
			$_term_ids = ( ! is_wp_error( $_term_ids ) ? wp_list_pluck( $_term_ids, 'term_id' ) : array() );
			if ( ! empty( array_intersect( $term_ids, $_term_ids ) ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * get_order_amount.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @see     https://woocommerce.github.io/code-reference/classes/WC-Order.html
	 *
	 * @todo    [next] (feature) total (vs subtotal), discounts, fees, taxes, shipping
	 */
	function get_order_amount( $order ) {
		return $order->get_subtotal();
	}

	/**
	 * is_equal_float.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (dev) better epsilon value?
	 */
	function is_equal_float( $float1, $float2 ) {
		return ( abs( $float1 - $float2 ) < ( defined( 'PHP_FLOAT_EPSILON' ) ? PHP_FLOAT_EPSILON : 0.000001 ) );
	}

}

endif;
