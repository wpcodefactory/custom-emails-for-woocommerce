<?php
/**
 * Custom Emails for WooCommerce - Order Validator
 *
 * @version 3.6.0
 * @since   1.8.0
 *
 * @author  Algoritmika Ltd
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Custom_Email_Order_Validator' ) ) :

class Alg_WC_Custom_Email_Order_Validator {

	/**
	 * email.
	 *
	 * @version 3.1.2
	 * @since   3.1.2
	 */
	public $email;

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
	 * @version 3.6.0
	 * @since   1.8.0
	 */
	function validate( $order ) {

		// Check filter
		if (
			in_array(
				current_filter(),
				array(
					'woocommerce_checkout_order_processed_notification',
					'alg_wc_ce_store_api_checkout_update_order_notification',
				)
			) &&
			! $this->check_new_order_status( $order )
		) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %s: Email title. */
					__( '%s: New order: different status.', 'custom-emails-for-woocommerce' ),
					$this->email->title
				)
			);
			return false;
		}

		// WPML/Polylang language
		if ( apply_filters( 'wpml_active_languages', null ) ) {
			$required_wpml_languages = $this->email->get_option( 'required_wpml_languages', array() );
			if ( ! empty( $required_wpml_languages ) && ! in_array( $this->get_order_wpml_language( $order ), $required_wpml_languages ) ) {
				alg_wc_custom_emails()->core->debug(
					sprintf(
						/* Translators: %1$s: Email title, %2$s: Condition title. */
						__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
						$this->email->title,
						__( 'Require WPML language', 'custom-emails-for-woocommerce' )
					)
				);
				return false;
			}
		}

		// Order options
		$checks = array(
			'required_products',
			'excluded_products',
			'required_product_cats',
			'excluded_product_cats',
			'required_product_tags',
			'excluded_product_tags',
			'min_amount',
			'max_amount',
			'required_payment_gateways',
			'excluded_payment_gateways',
			'required_shipping_methods',
			'excluded_shipping_methods',
			'required_users',
			'excluded_users',
			'required_user_roles',
			'excluded_user_roles',
			'required_statuses',
			'excluded_statuses',
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
	 * remove_order_status_prefix.
	 *
	 * @version 2.8.0
	 * @since   2.8.0
	 */
	function remove_order_status_prefix( $status ) {
		return ( 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status );
	}

	/**
	 * required_statuses.
	 *
	 * @version 3.5.0
	 * @since   2.8.0
	 */
	function required_statuses( $order ) {
		$required_statuses = $this->email->get_option( 'required_order_statuses', array() );
		$required_statuses = array_map( array( $this, 'remove_order_status_prefix' ), $required_statuses );
		if ( ! empty( $required_statuses ) && ! $order->has_status( $required_statuses ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order status', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_statuses ) ? true : null );
	}

	/**
	 * excluded_statuses
	 *
	 * @version 3.5.0
	 * @since   2.8.0
	 */
	function excluded_statuses( $order ) {
		$excluded_statuses = $this->email->get_option( 'excluded_order_statuses', array() );
		$excluded_statuses = array_map( array( $this, 'remove_order_status_prefix' ), $excluded_statuses );
		if ( ! empty( $excluded_statuses ) && $order->has_status( $excluded_statuses ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order status', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_statuses ) ? true : null );
	}

	/**
	 * check_user_id.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function check_user_id( $order, $user_ids ) {
		$order_user_id = ( is_callable( array( $order, 'get_user_id' ) ) ? $order->get_user_id() : false );
		return in_array( $order_user_id, $user_ids );
	}

	/**
	 * check_user_role.
	 *
	 * @version 2.5.0
	 * @since   2.5.0
	 */
	function check_user_role( $order, $user_roles ) {
		$order_user_id    = ( is_callable( array( $order, 'get_user_id' ) ) ? $order->get_user_id() : false );
		$order_user       = ( $order_user_id ? get_user_by( 'id', $order_user_id ) : false );
		$order_user_roles = ( $order_user && ! empty( $order_user->roles ) ? (array) $order_user->roles : array( 'alg_wc_ce_guest' ) );
		$intersect        = array_intersect( $order_user_roles, $user_roles );
		return ( ! empty( $intersect ) );
	}

	/**
	 * required_users.
	 *
	 * @version 3.5.0
	 * @since   2.5.0
	 */
	function required_users( $order ) {
		$required_user_ids = $this->email->get_option( 'required_order_user_ids', array() );
		if ( ! empty( $required_user_ids ) && ! $this->check_user_id( $order, $required_user_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require users', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_user_ids ) ? true : null );
	}

	/**
	 * excluded_users
	 *
	 * @version 3.5.0
	 * @since   2.5.0
	 */
	function excluded_users( $order ) {
		$excluded_user_ids = $this->email->get_option( 'excluded_order_user_ids', array() );
		if ( ! empty( $excluded_user_ids ) && $this->check_user_id( $order, $excluded_user_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude users', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_user_ids ) ? true : null );
	}

	/**
	 * required_user_roles.
	 *
	 * @version 3.5.0
	 * @since   2.5.0
	 */
	function required_user_roles( $order ) {
		$required_user_roles = $this->email->get_option( 'required_order_user_roles', array() );
		if ( ! empty( $required_user_roles ) && ! $this->check_user_role( $order, $required_user_roles ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require user roles', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_user_roles ) ? true : null );
	}

	/**
	 * excluded_user_roles
	 *
	 * @version 3.5.0
	 * @since   2.5.0
	 */
	function excluded_user_roles( $order ) {
		$excluded_user_roles = $this->email->get_option( 'excluded_order_user_roles', array() );
		if ( ! empty( $excluded_user_roles ) && $this->check_user_role( $order, $excluded_user_roles ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude user roles', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_user_roles ) ? true : null );
	}

	/**
	 * get_order_wpml_language.
	 *
	 * @version 3.5.0
	 * @since   1.9.1
	 *
	 * @see     https://wpml.org/faq/how-to-get-current-language-with-wpml/
	 * @see     https://wpml.org/wpml-hook/wpml_active_languages/
	 * @see     https://polylang.pro/doc/function-reference/
	 *
	 * @todo    (v3.6.5) WPML: `wpml_language` or `wpml_languages`?
	 * @todo    (dev) `ICL_LANGUAGE_CODE`?
	 */
	function get_order_wpml_language( $order ) {

		// WPML order language (meta)
		if ( ( $lang = $order->get_meta( 'wpml_language' ) ) ) {
			return $lang;
		}

		// Polylang order language (term)
		if (
			( $terms = get_the_terms( $order->get_id(), 'language' ) ) &&
			! is_wp_error( $terms )
		) {
			foreach ( $terms as $term ) {
				if ( ! empty( $term->slug ) ) {
					return $term->slug;
				}
			}
		}

		// WPML current language
		if ( ( $lang = apply_filters( 'wpml_current_language', null ) ) ) {
			return $lang;
		}

		// Polylang current language
		if (
			function_exists( 'pll_current_language' ) &&
			( $lang = pll_current_language() )
		) {
			return $lang;
		}

		/* phpcs:disable WordPress.Security.NonceVerification.Recommended */

		// WPML language in `$_REQUEST`
		if (
			! empty( $_REQUEST['meta'] ) &&
			is_array( $_REQUEST['meta'] )
		) {
			$metas = array_map(
				'sanitize_text_field',
				wp_unslash( $_REQUEST['meta'] )
			);
			foreach ( $metas as $meta ) {
				if (
					isset( $meta['key'] ) &&
					'wpml_language' === $meta['key'] &&
					! empty( $meta['value'] )
				) {
					return wc_clean( $meta['value'] );
				}
			}
		}

		// Polylang language in `$_REQUEST`
		if ( ! empty( $_REQUEST['post_lang_choice'] ) ) {
			return sanitize_text_field( wp_unslash( $_REQUEST['post_lang_choice'] ) );
		}

		/* phpcs:enable WordPress.Security.NonceVerification.Recommended */

		// No results
		return false;

	}

	/**
	 * check_new_order_status.
	 *
	 * @version 1.9.1
	 * @since   1.0.0
	 */
	function check_new_order_status( $order ) {
		$triggers = $this->email->get_option( 'trigger' );
		if ( in_array( 'woocommerce_new_order_notification_alg_wc_ce_any', $triggers ) ) {
			return true;
		}
		foreach ( $triggers as $trigger ) {
			if ( false !== ( $pos = strpos( $trigger, 'woocommerce_new_order_notification_' ) ) ) {
				$status = 'wc-' . substr( $trigger, 35 );
				if ( $order->has_status( $status ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * check_payment_gateway.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 */
	function check_payment_gateway( $order, $payment_gateways ) {
		$order_payment_gateway = ( is_callable( array( $order, 'get_payment_method' ) ) ? $order->get_payment_method() : false );
		return in_array( $order_payment_gateway, $payment_gateways );
	}

	/**
	 * required_payment_gateways
	 *
	 * @version 3.5.0
	 * @since   2.2.0
	 */
	function required_payment_gateways( $order ) {
		$required_order_payment_gateway_ids = $this->email->get_option( 'required_order_payment_gateway_ids', array() );
		if ( ! empty( $required_order_payment_gateway_ids ) && ! $this->check_payment_gateway( $order, $required_order_payment_gateway_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order payment gateways', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_order_payment_gateway_ids ) ? true : null );
	}

	/**
	 * excluded_payment_gateways
	 *
	 * @version 3.5.0
	 * @since   2.2.0
	 */
	function excluded_payment_gateways( $order ) {
		$excluded_order_payment_gateway_ids = $this->email->get_option( 'excluded_order_payment_gateway_ids', array() );
		if ( ! empty( $excluded_order_payment_gateway_ids ) && $this->check_payment_gateway( $order, $excluded_order_payment_gateway_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order payment gateways', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_order_payment_gateway_ids ) ? true : null );
	}

	/**
	 * is_array_intersect.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 */
	function is_array_intersect( $array1, $array2 ) {
		$intersect = array_intersect( $array1, $array2 );
		return ( ! empty( $intersect ) );
	}

	/**
	 * get_shipping_method_instance_id.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 */
	function get_shipping_method_instance_id( $shipping_method ) {
		return $shipping_method->get_instance_id();
	}

	/**
	 * check_shipping_method_instances.
	 *
	 * @version 2.2.0
	 * @since   2.2.0
	 */
	function check_shipping_method_instances( $order, $shipping_instances ) {
		$order_shipping_methods   = ( is_callable( array( $order, 'get_shipping_methods' ) ) ? $order->get_shipping_methods() : array() );
		$order_shipping_instances = ( ! empty( $order_shipping_methods ) ? array_map( array( $this, 'get_shipping_method_instance_id' ), $order_shipping_methods ) : array() );
		return $this->is_array_intersect( $order_shipping_instances, $shipping_instances );
	}

	/**
	 * required_shipping_methods
	 *
	 * @version 3.5.0
	 * @since   2.2.0
	 */
	function required_shipping_methods( $order ) {
		$required_order_shipping_instance_ids = $this->email->get_option( 'required_order_shipping_instance_ids', array() );
		if ( ! empty( $required_order_shipping_instance_ids ) && ! $this->check_shipping_method_instances( $order, $required_order_shipping_instance_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order shipping methods', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_order_shipping_instance_ids ) ? true : null );
	}

	/**
	 * excluded_shipping_methods
	 *
	 * @version 3.5.0
	 * @since   2.2.0
	 */
	function excluded_shipping_methods( $order ) {
		$excluded_order_shipping_instance_ids = $this->email->get_option( 'excluded_order_shipping_instance_ids', array() );
		if ( ! empty( $excluded_order_shipping_instance_ids ) && $this->check_shipping_method_instances( $order, $excluded_order_shipping_instance_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order shipping methods', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_order_shipping_instance_ids ) ? true : null );
	}

	/**
	 * required_products
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function required_products( $order ) {
		$required_order_product_ids = $this->email->get_option( 'required_order_product_ids', array() );
		if ( ! empty( $required_order_product_ids ) && ! $this->check_order_products( $order, $required_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order products', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_order_product_ids ) ? true : null );
	}

	/**
	 * excluded_products
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function excluded_products( $order ) {
		$excluded_order_product_ids = $this->email->get_option( 'excluded_order_product_ids', array() );
		if ( ! empty( $excluded_order_product_ids ) && $this->check_order_products( $order, $excluded_order_product_ids ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order products', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_order_product_ids ) ? true : null );
	}

	/**
	 * required_product_cats
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function required_product_cats( $order ) {
		$required_order_product_cats_ids = $this->email->get_option( 'required_order_product_cats_ids', array() );
		if ( ! empty( $required_order_product_cats_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order product categories', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_order_product_cats_ids ) ? true : null );
	}

	/**
	 * excluded_product_cats
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function excluded_product_cats( $order ) {
		$excluded_order_product_cats_ids = $this->email->get_option( 'excluded_order_product_cats_ids', array() );
		if ( ! empty( $excluded_order_product_cats_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_cats_ids, 'product_cat' ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order product categories', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_order_product_cats_ids ) ? true : null );
	}

	/**
	 * required_product_tags
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function required_product_tags( $order ) {
		$required_order_product_tags_ids = $this->email->get_option( 'required_order_product_tags_ids', array() );
		if ( ! empty( $required_order_product_tags_ids ) && ! $this->check_order_product_terms( $order, $required_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Require order product tags', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $required_order_product_tags_ids ) ? true : null );
	}

	/**
	 * excluded_product_tags
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function excluded_product_tags( $order ) {
		$excluded_order_product_tags_ids = $this->email->get_option( 'excluded_order_product_tags_ids', array() );
		if ( ! empty( $excluded_order_product_tags_ids ) &&   $this->check_order_product_terms( $order, $excluded_order_product_tags_ids, 'product_tag' ) ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Exclude order product tags', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $excluded_order_product_tags_ids ) ? true : null );
	}

	/**
	 * min_amount
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function min_amount( $order ) {
		$min_order_amount = $this->email->get_option( 'min_order_amount', '' );
		if ( ! empty( $min_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $min_order_amount ) && $this->get_order_amount( $order ) < $min_order_amount ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Minimum order amount', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $min_order_amount ) ? true : null );
	}

	/**
	 * max_amount
	 *
	 * @version 3.5.0
	 * @since   1.8.0
	 */
	function max_amount( $order ) {
		$max_order_amount = $this->email->get_option( 'max_order_amount', '' );
		if ( ! empty( $max_order_amount ) && ! $this->is_equal_float( $this->get_order_amount( $order ), $max_order_amount ) && $this->get_order_amount( $order ) > $max_order_amount ) {
			alg_wc_custom_emails()->core->debug(
				sprintf(
					/* Translators: %1$s: Email title, %2$s: Condition title. */
					__( '%1$s: Blocked by the "%2$s" option.', 'custom-emails-for-woocommerce' ),
					$this->email->title,
					__( 'Maximum order amount', 'custom-emails-for-woocommerce' )
				)
			);
			return false;
		}
		return ( ! empty( $max_order_amount ) ? true : null );
	}

	/**
	 * check_order_products.
	 *
	 * @version 2.1.0
	 * @since   1.2.0
	 *
	 * @todo    (feature) "require all products" (i.e., vs "require at least one")?
	 */
	function check_order_products( $order, $product_ids ) {
		foreach ( $order->get_items() as $item ) {
			if ( in_array( $item['product_id'], $product_ids ) || in_array( $item['variation_id'], $product_ids ) ) {
				return apply_filters( 'alg_wc_custom_emails_check_order_products', true, $order, $product_ids );
			}
		}
		return apply_filters( 'alg_wc_custom_emails_check_order_products', false, $order, $product_ids );
	}

	/**
	 * check_order_product_terms.
	 *
	 * @version 2.1.0
	 * @since   1.6.0
	 *
	 * @todo    (feature) custom taxonomies
	 * @todo    (feature) "require all" (i.e., vs "require at least one")?
	 */
	function check_order_product_terms( $order, $term_ids, $taxonomy ) {
		foreach ( $order->get_items() as $item ) {
			$product_term_ids = get_the_terms( $item['product_id'], $taxonomy );
			$product_term_ids = ( ! is_wp_error( $product_term_ids ) ? wp_list_pluck( $product_term_ids, 'term_id' ) : array() );
			$product_term_ids = apply_filters( 'alg_wc_custom_emails_order_product_term_ids', $product_term_ids, $item, $order, $term_ids, $taxonomy );
			if ( ! empty( array_intersect( $term_ids, $product_term_ids ) ) ) {
				return apply_filters( 'alg_wc_custom_emails_check_order_product_terms', true, $order, $term_ids, $taxonomy );
			}
		}
		return apply_filters( 'alg_wc_custom_emails_check_order_product_terms', false, $order, $term_ids, $taxonomy );
	}

	/**
	 * get_order_amount.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @see     https://woocommerce.github.io/code-reference/classes/WC-Order.html
	 *
	 * @todo    (feature) total (vs subtotal), discounts, fees, taxes, shipping
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
	 * @todo    (dev) better epsilon value?
	 */
	function is_equal_float( $float1, $float2 ) {
		return ( abs( $float1 - $float2 ) < ( defined( 'PHP_FLOAT_EPSILON' ) ? PHP_FLOAT_EPSILON : 0.000001 ) );
	}

}

endif;
