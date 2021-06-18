<?php
/**
 *
 * @class C4P_Shortcode_Products
 * @version 0.0.1
 */

add_action('c4p_after_shop_loop_item', 'c4p_template_loop_add_quantity');
add_action('c4p_checkout_order_review', 'c4p_checkout_payment', 20);
add_action('c4p_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);

if ( ! function_exists( 'c4p_template_loop_add_quantity' ) ) {
    function c4p_template_loop_add_quantity( $args = array() ) {
		global $product;
        global $cart_id;

        $post_cart_id = $cart_id;

        if ( !WC()->cart ) {
            return ;
        }
        else{
            $original_post_id = $post_cart_id;
            $GLOBALS[ 'post' ] = $post_cart_id;
            $cart = WC()->cart->get_cart();
            $GLOBALS[ 'post' ] = $original_post_id;
        }
        $product_key = "";
        $product_qty = 0;
        foreach( $cart as $key => $val ){
            if ( $val[ 'product_id' ] == $product->get_id() ) {
                $product_key = $key;
                $product_qty = $val[ 'quantity' ];
            }
        }

		if ( $product ) {
			$defaults = array(
				'quantity' => 1,
				'class'    => implode( ' ', array_filter( array(
						'button',
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
				) ) ),
			);

			$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

            wp_nonce_field( 'woocommerce-cart' ); 

            $attr_max = '';
            $attr_disabled = '';
            if ( $product -> is_purchasable() && $product -> get_stock_quantity() ){
                echo apply_filters( 'c4p_loop_edit_quantity_link',
                sprintf('<form class="c4p-add-to-cart-form" data-product_id="%s" action="%s"><input type="number" name="%s" class="c4p-add-to-cart" value="%d" min="0" max="%s" data-product_id="%s" data-product_sku="%s" data-cart_id="%s" style="width: 100%%; height: 100%%"/></form>',
                $product->get_id(), wc_get_cart_url(), $product_key !== "" ? sprintf( "cart[%s][qty]", $product_key ) : "", $product_qty
                ,esc_attr( $product->get_stock_quantity() ), $product->get_id(), $product->get_sku(), $post_cart_id,
                ), $product);
                $attr_max = "'max=" . $product -> get_stock_quantity() . "'";
            }
            else if ( $product -> is_purchasable() && $product -> is_in_stock() ){
                echo apply_filters( 'c4p_loop_edit_quantity_link',
                sprintf('<form class="c4p-add-to-cart-form" data-product_id="%s" action="%s"><input type="number" name="%s" class="c4p-add-to-cart" value="%d" min="0" data-product_id="%s" data-product_sku="%s" data-cart_id="%s" style="width: 100%%; height: 100%%"/></form>',
                $product->get_id(), wc_get_cart_url(), $product_key !== "" ? sprintf( "cart[%s][qty]", $product_key ) : "", $product_qty
                , $product->get_id() , $product->get_sku(), $post_cart_id,
                ), $product);
            }
            else {
                echo apply_filters( 'c4p_loop_edit_quantity_link',
                sprintf('<form class="c4p-add-to-cart-form" data-product_id="%s" action="%s"><input type="number" name="" class="c4p-add-to-cart" disabled value="0" data-product_id="%s" data-product_sku="%s" data-cart_id="%s" style="width: 100%%; height: 100%%"/></form>',
                $product->get_id(), $product->get_id(), wc_get_cart_url(), $product->get_sku(), $post_cart_id,
                ), $product);
                $attr_disabled = "disabled";
            }
            sprintf( '</form>' );
		}
    }
}

if ( ! function_exists( 'c4p_checkout_payment' ) ) {
    function c4p_checkout_payment() {
        $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
        WC()->payment_gateways()->set_current_gateway( $available_gateways );
        wc_get_template( 'checkout/payment.php', array(
            'checkout'          => WC()->checkout(),
            'available_gateways'=> $available_gateways,
            'order_button_text' => apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) ),
        ),
        '',
        __DIR__ . "/../templates/" ); 
    }
}

add_action( 'c4p_cart_collaterals', 'c4p_cart_totals' );
if ( ! function_exists( 'c4p_cart_totals' ) ) {
    function c4p_cart_totals( $cart_id ) {
        if ( is_checkout() ) {
            return;
        }
        if ( WC()->customer ){
            include __DIR__ . '/../templates/cart/cart-totals.php';
        } }
}

add_action( 'restrict_manage_posts', 'c4p_filter_coupon', 11 );
if ( ! function_exists( 'c4p_filter_coupon' ) ) {
    function c4p_filter_coupon( ) {
        global $wpdb;
        if ( isset( $_GET[ 'post_status' ] ) && 
            ( wc_clean( $_GET[ 'post_status' ] ) == "wc-c4p-processing" ||
             wc_clean( $_GET[ 'post_status' ] ) == "wc-c4p-on-hold" )
            ) {
            $group_buys =  $wpdb->get_col( $wpdb->prepare( "
                SELECT DISTINCT meta_value
                FROM $wpdb->postmeta
                WHERE meta_key = '_c4p';
            ", 1 ) );
            $coupons =  $wpdb->get_col( $wpdb->prepare( "
                SELECT post_title
                FROM $wpdb->posts
                WHERE 1
                AND post_type = 'shop_coupon'
                AND post_status = 'publish';
            ", 1 ) );
        ?>
            <select name="c4p" data-placeholder="<?php esc_attr_e( 'Search for a group buy', 'c4p' ); ?>" data-allow_clear="true">
                <option value="">All group buys</option>
        <?php
                    foreach ( $group_buys as $idx => $group_buy ) {
                        $selected = isset( $_GET[ 'c4p' ] ) && $_GET[ 'c4p' ] == $group_buy ? "selected" : "";
                        echo "<option value='" . $group_buy . "' " . $selected . ">" . get_the_title( $group_buy ) . "</option>";
                    }
        ?>

            </select>

            <select name="coupon_code" data-placeholder="<?php esc_attr_e( 'Search for a coupon code', 'c4p' ); ?>" data-allow_clear="true">
                <option value="">All coupons</option>
        <?php
                foreach ( $coupons as $idx => $coupon ) {
                    $selected = isset( $_GET[ 'coupon_code' ] ) && $_GET[ 'coupon_code' ] == $coupon ? "selected" : "";
                       echo "<option " . $selected . " >" . $coupon . "</option>";
                }
        ?>
            </select>
        <?php
            if ( isset( $_GET[ 'c4p' ] ) ) {
                echo "<input type='button' class='button' onClick='send_order()' value='" . __( 'Send to ECFit', 'c4p' ) . "' id='c4p-send-order' />
                       <script>

                        function send_order() {
                            document.getElementById( 'c4p-send-order' ).disabled = true;

                            var xmlhttp = new XMLHttpRequest();

                            xmlhttp.onreadystatechange = function() {
                                if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
                                    if (xmlhttp.status == 200) {
                                        alert('執行成功');
                                    } else if (xmlhttp.status == 400) {
                                        alert('執行失敗');
                                    } else {
                                        alert('執行失敗');
                                    }
                                    document.getElementById( 'send_button' ).disabled = false;
                                }
                            };

                            alert( '" . get_admin_url() . "admin.php?page=ECFIT&send=1&c4p=" . $_GET[ 'c4p' ] . "');
                            // xmlhttp.open( 'GET', " . get_admin_url() . " + '/admin.php?page=ECFIT&send=1&c4p=" . $_GET[ 'c4p' ] . "', true);
                            // xmlhttp.send();
                        }

                        </script>";
            }
        }
    }
}

add_action( 'manage_shop_order_posts_columns', 'c4p_shop_order_posts_columns', 9 );
if ( ! function_exists( 'c4p_shop_order_posts_columns' ) ) {
    function c4p_shop_order_posts_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
             $existing_columns = array();
        }

        $columns = array();
        if ( isset( $_GET[ 'post_status' ] ) && (
            $_GET[ 'post_status' ] == 'wc-c4p-on-hold' || 
            $_GET[ 'post_status' ] == 'wc-c4p-processing'
        ) ) {
            $columns[ 'c4p' ] = __( 'C4P', 'c4p' );
            // $columns[ 'coupon' ] = __( 'coupon', 'c4p' );
        }
        return array_merge( $columns, $existing_columns );
    }
}

add_action( 'manage_shop_order_posts_custom_column', 'c4p_render_shop_order_columns', 1 );
if ( ! function_exists( 'c4p_render_shop_order_columns' ) ) {
    function c4p_render_shop_order_columns( $column ) {
        global $post, $the_order;

        if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
            $the_order = wc_get_order( $post->ID );
        }

        switch ( $column ) {
            case 'c4p':
                if ( get_post_meta( $post->ID, '_c4p' ) ) {
                    printf( get_the_title( get_post_meta( $post->ID, '_c4p' )[0] ) );
                }
        }
    }
}

?>
