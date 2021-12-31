<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'esunacq_order_failed_redirect', 'order_to_c4p_url', 10, 2 );
if ( ! function_exists( 'order_to_c4p_url' ) ) {
    function order_to_c4p_url( $order, $redirect ) {
        $order_id = $order->get_id();
        if ( get_post_meta( $order_id, '_c4p' ) ) {
            return get_permalink( (int) get_post_meta( $order_id, '_c4p' )[0] );
        } else {
            return $redirect;
        }
    }
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
        return $cart_item_data;
    }
}

// add_to_cart -> gen cart id
add_filter( 'woocommerce_cart_id', 'c4p_set_cart_id' , 10, 5);
if( !function_exists( 'c4p_set_cart_id' ) ) {
    function c4p_set_cart_id( $org_cart_id, $product_id, $variation_id, $variation, $cart_item_data ) {
    if ( key_exists( 'c4p_cart_id', $cart_item_data ) ) {
        return $org_cart_id;
    }
    return $org_cart_id;
    }
}


// add_to_cart -> returning freagments at adding
add_filter( 'woocommerce_add_to_cart_fragments', 'c4p_add_to_cart_fragments', 10, 1);
if( !function_exists( 'c4p_add_to_cart_fragments' ) ) {
    function c4p_add_to_cart_fragments( $arr ){
        foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ){
            $arr[ "c4p_cart_item_keys" ][ $cart_item[ "product_id" ] ] = $cart_item[ "key" ];
        }
        // $arr[ "c4p_cart_item_keys" ] = WC()->cart->get_cart();
        return $arr;
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
        global $c4p_cart_id;
        if ( isset( $_GET[ 'c4p' ] ) ){
            $c4p_cart_id = $_GET[ 'c4p' ];
            $GLOBALS[ 'post' ] = $_GET[ 'c4p' ];
        }
        else if ( isset( $_GET[ 'cart_id' ] ) ){
            $c4p_cart_id = $_GET[ 'cart_id' ];
            $GLOBALS[ 'post' ] = $_GET[ 'cart_id' ];
        }
        else if ( isset( $_POST[ 'cart_id' ] ) ){
            $c4p_cart_id = $_POST[ 'cart_id' ];
            $GLOBALS[ 'post' ] = $_POST[ 'cart_id' ];
        }
        else if ( ( isset( $_POST[ 'cart_id' ]  ) || ( isset( $_GET[ 'wc-ajax' ] ) && 
                ( $_GET[ 'wc-ajax' ] == 'update_order_review' )
                // ( $_GET[ 'wc-ajax' ] == 'checkout' || 
                // $_GET[ 'wc-ajax' ] == 'get_refreshed_fragments' ) 
            ) ) && 
            isset( $_SERVER[ 'HTTP_REFERER' ] ) && isset( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ] ) ) {
            // checkout to order using: REQUEST_URI w/ ?wc-ajax=checkout; HTTP_REFERER w/ ?c4p=<post_id>
            $query = [];
            parse_str( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ], $query );
            if ( isset( $query[ 'c4p' ] ) ) {
                $GLOBALS[ 'post' ] = $query[ 'c4p' ];
            }    
            if ( isset( $_POST[ 'cart_id' ] ) ) {
                $c4p_cart_id = $_POST[ 'cart_id' ];
            } else {
                $c4p_cart_id = $GLOBALS[ 'post' ];
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

add_filter( 'woocommerce_register_shop_order_post_statuses', 'c4p_register_post_status', 10, 1);
if ( ! function_exists( 'c4p_register_post_status' ) ){
    function c4p_register_post_status( $arr ){
        $arr[ 'wc-c4p-processing' ] = array(
            'label'                     => _x( 'C4P Processing', 'Order status', 'c4p' ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'C4P Processing <span class="count">(%s)</span>', 'C4P Processing <span class="count">(%s)</span>', 'c4p' ),
        );
        $arr[ 'wc-c4p-on-hold' ] = array(
            'label'                     => _x( 'C4P On hold', 'Order status', 'c4p' ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'C4P On hold <span class="count">(%s)</span>', 'C4P On hold <span class="count">(%s)</span>', 'c4p' ),
        );
        $arr[ 'wc-c4p-completed' ] = array(
            'label'                     => _x( 'C4P Completed', 'Order status', 'c4p' ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'C4P Completed <span class="count">(%s)</span>', 'C4P Completed <span class="count">(%s)</span>', 'c4p' ),
        );
        return $arr;
    }
}

add_filter( 'wc_order_statuses', 'c4p_order_statuses', 10, 1);
if ( ! function_exists( 'c4p_order_statuses' ) ){
    function c4p_order_statuses( $arr ){
        $arr[ 'wc-c4p-processing' ] = _x( 'C4P Processing', 'Order status', 'c4p' );
        $arr[ 'wc-c4p-on-hold' ] = _x( 'C4P On hold', 'Order status', 'c4p' );
        $arr[ 'wc-c4p-completed' ] = _x( 'C4P Completed', 'Order status', 'c4p' );
        return $arr;
    }
}

// need to query order_item_type=coupon, order_item_name=code_name
add_filter( 'posts_where', 'c4p_query_posts_where', 10, 2);
if ( ! function_exists( 'c4p_query_posts_where' ) ){
    function c4p_query_posts_where( $where, $wp_query ){
        if ( array_key_exists( "woocommerce_order_item_query", $wp_query->query_vars ) ) {
            foreach( $wp_query->query_vars[ "woocommerce_order_item_query" ] as $order_item ){
                if ( is_array( $order_item ) && array_key_exists( "order_item_type", $order_item ) ) {
                    $where .= " AND ( ( wp_woocommerce_order_items.order_item_type = '{$order_item[ "order_item_type" ]}' AND wp_woocommerce_order_items.order_item_name = '{$order_item[ "value" ]}' ) ) ";
                }
            }
        }
        return $where;
    }
}

// need to query order_item_type=coupon, order_item_name=code_name
add_filter( 'posts_join', 'c4p_query_posts_join', 10, 2);
if ( ! function_exists( 'c4p_query_posts_join' ) ){
    function c4p_query_posts_join( $join, $wp_query ){
        if ( array_key_exists( "woocommerce_order_item_query", $wp_query->query_vars ) ) {
            $join .= " INNER JOIN wp_woocommerce_order_items ON ( wp_posts.ID = wp_woocommerce_order_items.order_id )";
        }
        return $join;
    }
}

// used for adminp age
add_filter( 'request', 'c4p_request_query', 10, 1);
if ( ! function_exists( 'c4p_request_query' ) ){
    function c4p_request_query( $vars ){
        if ( isset( $_GET[ '_c4p' ] ) && !empty( $_GET[ '_c4p' ] ) ) {
            $vars[ 'meta_query' ] = array_merge( $vars, array( 
                array(
                    'key'       => '_c4p',
                    'value'     => (int) wc_clean( $_GET[ '_c4p' ] ),
                    'compoare'  => '=',
            ) ) );
        }
        if ( isset( $_GET[ 'coupon_code' ] ) && !empty( $_GET[ 'coupon_code' ] ) ) {
            $vars[ 'woocommerce_order_item_query' ] = array_merge( $vars, array( 
                array(
                    'order_item_type'       => 'coupon',
                    'value'     => wc_clean( $_GET[ 'coupon_code' ] ),
                    'compoare'  => '=',
            ) ) );
        }
        return $vars;
    }
}

add_filter( 'woocommerce_thankyou_order_id', 'c4p_set_order_status', 10, 1 );
if ( ! function_exists( 'c4p_set_order_status' ) ) {
    function c4p_set_order_status( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $referer = parse_url( wp_get_referer() );
            parse_str( $referer[ "query" ], $r_GET );
            if ( isset( $r_GET[ "c4p" ] ) ) {
                update_post_meta( $order_id, "_c4p", $r_GET[ "c4p" ] );
                if ( $order->get_status() == "on-hold" ){
                    $order->update_status( "c4p-on-hold" );
                }
                else if ( $order->get_status() == "processing" ){
                    $order->update_status( "c4p-processing" );
                }
            }
        }
        return $order_id;
    }
}

add_action( 'woocommerce_checkout_order_processed', 'c4p_add_order_metadata', 10, 3 );
if ( ! function_exists( 'c4p_add_order_metadata' ) ) {
    function c4p_add_order_metadata( $order_id, $postdata, $order ) {
        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) && isset( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ] ) ) {
        // if ( isset( parse_url( wp_get_referer() )[ 'query' ] ) ) {
            $query = [];
            parse_str( parse_url( $_SERVER[ 'HTTP_REFERER' ] )[ 'query' ], $query );
            if ( isset( $query[ 'c4p' ] ) ) {
                update_post_meta( $order_id, "_c4p", $query[ 'c4p' ] );
            }
        }
    }
}

add_filter( 'woocommerce_return_to_shop_redirect', 'c4p_return_to_shop_redirect', 1 );
if ( ! function_exists( 'c4p_return_to_shop_redirect' ) ) {
    function c4p_return_to_shop_redirect( $link ) {
        if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) && $_SERVER[ 'HTTP_REFERER' ] != ( get_site_url() . '/' ) ) {
            return $_SERVER[ 'HTTP_REFERER' ];
        }
        return $link;
    }
}

// custome filter added at woocommerce/templates/checkout/cart-errors.php:31
// href="<?php echo esc_url( apply_filters( 'c4p_cart_error_return_link', wc_get_page_permalink( 'cart' ) ) );
add_filter( 'c4p_cart_error_return_link', 'c4p_cart_error_return_link', 10, 1 );
if ( ! function_exists( 'c4p_cart_error_return_link' ) ) {
    function c4p_cart_error_return_link( $link ) {
        if ( isset( $_GET[ 'c4p' ] ) ) {
            return get_permalink( $_GET[ 'c4p' ] );
        }
        return $link;
    }
}
