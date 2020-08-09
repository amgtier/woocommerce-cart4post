<?php
/**
 *
 * @class C4P_Shortcodes
 * @version 0.0.1
 */

class C4P_Shortcodes {
    /**
     * C4P_Shortcodes version
     */
    public static function init() {
        add_shortcode("c4p_products", __CLASS__ . '::products');
    }

    public static function returnHello(){
        return "<br/> <p>Hello abc!!!!". class_exists('WC_Shortcode_Products') ."</p>";
    }

    public static function products( $atts ){
        $shortcode = new C4P_Shortcode_Products( $atts );
        // $shortcode = new WC_Shortcode_Products( $atts );
        return $shortcode -> get_content();
    }
}

?>
