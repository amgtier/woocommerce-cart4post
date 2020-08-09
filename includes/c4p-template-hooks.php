<?php
/**
 *
 * @class C4P_Shortcode_Products
 * @version 0.0.1
 */

// remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
add_action('c4p_after_shop_loop_item', 'c4p_template_loop_add_quantity');

if ( ! function_exists( 'c4p_template_loop_add_quantity' ) ) {
    function c4p_template_loop_add_quantity( $args = array() ) {
		global $product;

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
            echo apply_filters( 'c4p_loop_edit_quantity_link',
            sprintf('<input type="number" value="0" min="0" max="%s" style="width: 100%%; height: 100%%"/>',
            esc_attr( $product->get_stock_quantity() )
            ), $product);
		}
    }
}

?>
