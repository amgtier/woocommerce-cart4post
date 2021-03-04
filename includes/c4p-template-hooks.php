<?php
/**
 *
 * @class C4P_Shortcode_Products
 * @version 0.0.1
 */

// remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
add_action('c4p_after_shop_loop_item', 'c4p_template_loop_add_quantity');
// add_action('c4p_checkout_order_review', 'woocommerce_checkout_payment', 20);
add_action('c4p_checkout_order_review', 'c4p_checkout_payment', 20);

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

            // wc_get_template( 'loop/add-to-cart.php', $args );
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
        /*
        wc_get_template( 'checkout/payment.php', array(
            'checkout'          => WC()->checkout(),
            'available_gateways'=> $available_gateways,
            'order_button_text' => apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) ),
        ) ); 
         */
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

?>
