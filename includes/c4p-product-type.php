<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'register_group_buy_product_type' );
if ( ! function_exists( 'register_group_buy_product_type' ) ) {
    function register_group_buy_product_type( ) {

        class C4P_Product_Group_Buy extends WC_Product {
            public function __construct( $product ){
                $this->product_type = 'group_buy';
                parent::__construct( $product );
            }
        }

    }
}

add_filter( 'product_type_selector', 'c4p_add_group_product_type', 10, 1 );
if ( ! function_exists( 'c4p_add_group_product_type' ) ) {
    function c4p_add_group_product_type( $types ) {
        $types[ 'group-buy' ] = __( 'Group-Buy', 'c4p' );
        return $types;
    }
}

add_action( 'woocommerce_process_product_meta', 'c4p_save_group_buy_product_settings' );
if ( ! function_exists( 'c4p_save_group_buy_product_settings' ) ) {
    function c4p_save_group_buy_product_settings( $post_id) {
        return $types;
    }
}

