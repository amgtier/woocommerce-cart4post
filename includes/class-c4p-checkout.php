<?php
/**
 *
 * @class C4P_Checkout
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Checkout extends WC_Checkout{
    /**
     * C4P_Checkout version
     */
    protected static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function process_checkout() {
        error_log( 'c4p process_checkout' );
        error_log(http_build_query($_POST, '', ', '));
    }

}

?>
