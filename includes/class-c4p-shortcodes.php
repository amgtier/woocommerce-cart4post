<?php
/**
 *
 * @class C4P_Shortcodes
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Shortcodes extends WC_Shortcodes{
    /**
     * C4P_Shortcodes version
     */
    public static function init() {
        add_shortcode("cart4post", __CLASS__ . '::c4p_init');
        add_shortcode("c4p_products", __CLASS__ . '::products');
        add_shortcode("c4p_checkout", __CLASS__ . '::checkout');
    }

    public static function c4p_init(){
        global $c4p_page_id;
        $c4p_page_id = get_the_ID();
        printf("<h1>Page ID:" . $c4p_page_id . "</h1>");
        // self::create_cart();
    }

    public static function products( $atts ){
        $shortcode = new C4P_Shortcode_Products( $atts );
        // $shortcode = new WC_Shortcode_Products( $atts );
        return $shortcode -> get_content();
    }

    public static function checkout( $atts ){
        printf("<span>unresolved issue when ECFit is activated.</span>");
        return self::shortcode_wrapper( array( 'C4P_Shortcode_Checkout', 'output' ), $atts );
    }
}

?>
