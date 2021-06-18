<?php
/**
 *
 * @class C4P_Shortcode_Checkout
 * @version 0.0.1
 */

class C4P_Shortcode_Checkout extends WC_Shortcode_Checkout {
    /**
     * C4P_Shortcode_Checkout version
     */
	/**
	 * Output the shortcode.
	 *
	 * @param array $atts
	 */
	public static function output( $atts ) {
		global $wp;

		// Check cart class is loaded or abort
		if ( is_null( WC()->cart ) ) {
			return;
		}

		// Handle checkout actions
		if ( ! empty( $wp->query_vars['order-pay'] ) ) {

			self::order_pay( $wp->query_vars['order-pay'] );

		} elseif ( isset( $wp->query_vars['order-received'] ) ) {

			self::order_received( $wp->query_vars['order-received'] );

		} else {

			self::checkout();

		}
	}

	/**
	 * Show the checkout.
	 */
	private static function checkout() {

		// Show non-cart errors
		wc_print_notices();

		// Check cart contents for errors
		// do_action( 'woocommerce_check_cart_items' );
        if ( WC()->cart ){
            WC()->cart->calculate_totals();
        }
        if ( isset( $_GET[ 'coupon_code' ] ) ) {
            WC()->cart->add_discount( sanitize_text_field( $_GET[ 'coupon_code' ] ) );
        }
        do_action( 'c4p_cart_collaterals', get_the_ID() );
        return; // Don't fill in checkout form here.

		// Calc totals
		WC()->cart->calculate_totals();

		// Get checkout object
		$checkout = WC()->checkout();

		if ( empty( $_POST ) && wc_notice_count( 'error' ) > 0 ) {

			wc_get_template( 'checkout/cart-errors.php', array( 'checkout' => $checkout ) );

		} else {

			$non_js_checkout = ! empty( $_POST['woocommerce_checkout_update_totals'] ) ? true : false;

			if ( wc_notice_count( 'error' ) == 0 && $non_js_checkout ) {
				wc_add_notice( __( 'The order totals have been updated. Please confirm your order by pressing the "Place order" button at the bottom of the page.', 'woocommerce' ) );
			}

			// wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $checkout ) );
			require __DIR__ . '/../templates/checkout/form-checkout.php';

		}
	}

}

?>
