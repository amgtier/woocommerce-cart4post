<?php
/**
 *
 * @class C4P_Cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Cart extends WC_Cart{
    /**
     * C4P_Cart version
     */
    protected static $instance = null;
    public function init() {
    }

    public function __construct() {
        $this -> fees_api = new WC_Cart_Fees( $this );
        $this->tax_display_cart = get_option( 'woocommerce_tax_display_cart' );
		add_action( 'c4p_add_to_cart', array( $this, 'calculate_totals' ), 20, 0 );
		add_action( 'c4p_applied_coupon', array( $this, 'calculate_totals' ), 20, 0 );
		add_action( 'c4p_cart_item_removed', array( $this, 'calculate_totals' ), 20, 0 );
		add_action( 'c4p_cart_item_restored', array( $this, 'calculate_totals' ), 20, 0 );
		add_action( 'c4p_check_cart_items', array( $this, 'check_cart_items' ), 1 );
		add_action( 'c4p_check_cart_items', array( $this, 'check_cart_coupons' ), 1 );
		add_action( 'c4p_after_checkout_validation', array( $this, 'check_customer_coupons' ), 1 );
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_cart_totals () {
        $GLOBALS[ 'post' ] = $_GET[ 'cart_id' ];
        WC()->cart->calculate_totals();

        ob_start();
        do_action( 'c4p_cart_collaterals' );
        // do_action( 'woocommerce_cart_collaterals' );
        $totals = ob_get_clean();
        wc_print_notices();
        $notices = ob_get_clean();
        wp_send_json( [ 'totals' => $totals, 'notices' => $notices ] );
    }

}

?>
