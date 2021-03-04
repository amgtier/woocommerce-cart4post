<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// add_to_cart
add_filter( 'woocommerce_add_cart_item_data', 'prefix_add_cart_item_data' );
if ( ! function_exists( 'prefix_add_cart_item_data' ) ) {
    function prefix_add_cart_item_data( $cart_item_data=0, $product_id=0, $variation_id=0 ) {
        if( array_key_exists('cart_id', $_POST) ){
            $cart_id = $_POST['cart_id'];
            $cart_item_data[ 'c4p_cart' ] = true;
            $cart_item_data[ 'c4p_cart_id' ] = $cart_id;
        }
        // error_log( http_build_query( $cart_item_data ) );
        return $cart_item_data;
    }
}

// add_to_cart -> gen cart id
add_filter( 'woocommerce_cart_id', 'c4p_set_cart_id' , 10, 5);
if( !function_exists( 'c4p_set_cart_id' ) ) {
    function c4p_set_cart_id( $org_cart_id, $product_id, $variation_id, $variation, $cart_item_data ) {
    if ( key_exists( 'c4p_cart_id', $cart_item_data ) ) {
        // return $cart_item_data[ 'c4p_cart_id' ];
        return $org_cart_id;
    }
    return $org_cart_id;
    }
}

// show cart
add_filter( 'woocommerce_cart_item_visible', 'c4p_cart_item_visible', 10, 3 );
if( ! function_exists( 'c4p_cart_item_visible' ) ){
    function c4p_cart_item_visible( $res, $cart_item, $cart_item_key ){
        if ( array_key_exists('c4p_cart', $cart_item) && $cart_item[ 'c4p_cart' ] ){
            return false;
        }
        return $res;
    }
}

// requires return apply_filters( 'woocommerce_get_cart_contents', (array) $this->cart_contents ); at class-wc-cart.php:137
add_filter( 'woocommerce_get_cart_contents', 'c4p_get_cart_contents', 10, 1 );
if ( ! function_exists( 'c4p_get_cart_contents' ) ){
    function c4p_get_cart_contents( $cart_contents ){
        // error_log( '' );
        // error_log( sprintf( "cart: %s @:%s", count( $cart_contents ), get_the_ID() ) );
        // error_log( urldecode( http_build_query( $cart_contents, '', ',   ' ) ) );
        // error_log( $_SERVER[ 'HTTP_REFERER' ] );
        // error_log( wp_debug_backtrace_summary() );
        // error_log( count( $cart_contents ) );

        global $c4p_cart_id;
        if ( isset( $_GET[ 'c4p' ] ) ){
            $c4p_cart_id = $_GET[ 'c4p' ];
            $GLOBALS[ 'post' ] = $_GET[ 'c4p' ];
        }
        else if ( isset( $_POST[ 'cart_id' ] ) ){
            $c4p_cart_id = $_POST[ 'cart_id' ];
            $GLOBALS[ 'post' ] = $_POST[ 'cart_id' ];
        }
        else if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) && isset( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ] ) ) {
            $query = [];
            parse_str( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ], $query );
            if ( isset( $query[ 'c4p' ] ) ) {
                $GLOBALS[ 'post' ] = $query[ 'c4p' ];
            }    
        }


        $will_set_cart = strpos( wp_debug_backtrace_summary(), 'WC_Cart_Session->set_session' ) !== false;
        $c4p_get_content = strpos( wp_debug_backtrace_summary(), 'WC_Cart->is_empty' ) !== false;
        $c4p_get_content = false;
        $res = Array();
        foreach( $cart_contents as $item_key => $item_data ){
            if( $will_set_cart || !isset( $c4p_cart_id ) && ( $c4p_get_content || !array_key_exists( 'c4p_cart_id', $item_data ) ) ) {
                $res[ $item_key ] = $item_data;
            }
            else if ( isset( $item_data[ 'c4p_cart_id' ] ) && 
                $item_data[ 'c4p_cart_id' ] == get_the_ID() ) { // can't use c4p_cart_id for showing shipping
                $res[ $item_key ] = $item_data;
            }
        }
        // error_log( sprintf( "count: %s, post id: %s", count($res), get_the_ID() ) );
        return $res;
    }
}

add_filter( 'product_type_options', 'c4p_get_product_type_options', 10, 1 );
if ( ! function_exists( 'c4p_get_product_type_options' ) ){
    function c4p_get_product_type_options( $arr ){
        $arr[ 'group_buy' ] = array(
            'id'                => '_group_buy',
            'wrapper_class'     => 'show_if_simple',
            'label'             => __( 'Group Buy', 'c4p' ),
            'description'       => __( 'Group Buy', 'c4p' ),
            'default'           => 'no',
        );
        return $arr;
    }
}

add_action( 'woocommerce_admin_process_product_object', 'c4p_admin_process_product_object', 10, 1 );
if( ! function_exists( 'c4p_admin_process_product_object' ) ){
    function c4p_admin_process_product_object( $product ){
        update_post_meta( $product->get_id(), '_group_buy', isset( $_POST[ '_group_buy' ] ) ? 'yes' : 'no' );

        // $errors = $product -> set_props( [
        //     'group_buy'     => isset( $_POST['_group_buy'] )
        // ]);
        // if ( is_wp_error( $errors ) ) {
        //     WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
        // }
    }
}

add_filter( 'woocommerce_shortcode_products_query', 'c4p_shortcode_products_query', 10, 3);
if ( ! function_exists( 'c4p_shortcode_products_query' ) ){
    function c4p_shortcode_products_query( $query_args, $attributes, $type ){
        // remove group-buy products from showing
        if ( $attributes[ 'ids' ] == ''  ){
            $query_args[ 'meta_query' ] = [ 
                'relation' => 'OR',
                [
                'key' => '_group_buy', 
                'value' => '1',
                'compare' => 'NOT EXISTS'
                ],
                [
                'key' => '_group_buy', 
                'value' => 'no',
                'compare' => '=' 
                ]
            ];
        }
        return $query_args;
    }
}

/********************/
/*     Discarded    */
/********************/

// show product
// add_filter( 'woocommerce_loop_add_to_cart_link', 'make_add_to_cart_link', 10, 2 );
if( ! function_exists( 'make_add_to_cart_link' ) ) {
    function make_add_to_cart_link( $html, $product ){
        $post_id = get_the_ID();
        $class = implode( ' ', array_filter( array(
            'button',
            'product_type_' . $product->get_type(),
            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button': '',
            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
        )));
	    $btn1 = sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s 1 </a>',
	    	esc_url( $product->add_to_cart_url() ),
	    	esc_attr( isset( $quantity ) ? $quantity : 1 ),
	    	esc_attr( $product->get_id() ),
	    	esc_attr( $product->get_sku() ),
	    	esc_attr( isset( $class ) ? $class : 'button' ),
	    	esc_html( $product->add_to_cart_text() )
        );
	    $btn2 = sprintf( '<a rel="nofollow" href="%s" data-cart_id="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s 2 </a>',
	    	esc_url( $product->add_to_cart_url() ),
	    	$post_id,
	    	esc_attr( isset( $quantity ) ? $quantity : 1 ),
	    	esc_attr( $product->get_id() ),
	    	esc_attr( $product->get_sku() ),
	    	esc_attr( isset( $class ) ? $class : 'button' ),
	    	esc_html( $product->add_to_cart_text() )
        );
        return $btn1 . $btn2;
    }
}

// add_to_cart
// add_filter( 'woocommerce_get_item_data', 'c4p_cart_item_data', 10, 2 );
if( ! function_exists( 'c4p_cart_item_data' ) ){
    function c4p_cart_item_data( $item_data, $cart_item ){
        return $item_data;
    }
}

// add_filter( 'woocommerce_cart_item_product', 'c4p_cart_item_product', 10, 3 );
if( ! function_exists( 'c4p_cart_item_product' ) ){
    function c4p_cart_item_product( $cart_item_data, $cart_item, $cart_item_key ){
        return $cart_item_data;
    }
}

?>
