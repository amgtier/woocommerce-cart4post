<?php
/**
 *
 * @class C4P_Shortcode_Products
 * @version 0.0.1
 */

class C4P_Shortcode_Products extends WC_Shortcode_Products {
    /**
     * C4P_Shortcode_Products version
     */
	/**
	 * Dummy explain wording
	 *
	 * @since  3.2.0
	 * @return string
	 */

    public function get_content() {
        if ( $this->attributes[ 'ids' ] == '' ){
            return __( "c4p product ids attr is not set.", "c4p" );
        }
        return WC_Shortcode_Products::get_content();
    }

	protected function product_loop() {
		global $woocommerce_loop;
        global $c4p_cart_id;
        global $cart_id;
        $c4p_cart_id = get_the_ID();
        $cart_id = get_the_ID();

		$columns                     = absint( $this->attributes['columns'] );
		$classes                     = $this->get_wrapper_classes( $columns );
		$woocommerce_loop['columns'] = $columns;
		$woocommerce_loop['name']    = $this->type;
		$products_ids                = $this->get_query_results()->ids;

		ob_start();
        printf( "<script> var post_id=%s; </script>", get_the_ID() ); // resolved by global var
		if ( $products_ids ) {
			// Prime meta cache to reduce future queries.
			// update_meta_cache( 'post', $products_ids );
			// update_object_term_cache( $products_ids, 'product' );

			$original_post = $GLOBALS['post'];

			do_action( "woocommerce_shortcode_before_{$this->type}_loop", $this->attributes );

			woocommerce_product_loop_start();

			foreach ( $products_ids as $product_id ) {
                // check group-buy post meta
                $group_buy = get_post_meta( $product_id, '_group_buy' );
                if ( count( $group_buy ) == 0 || $group_buy[0] != 'yes' ){
                    if($WP_DEBUG){
                        error_log( sprintf( 'product_id: %d is not group_buy</br>', $product_id ) );
                        printf( 'product_id: %d is not group_buy</br>', $product_id );
                    }
                    continue;
                }

				$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
				setup_postdata( $GLOBALS['post'] );

				// Set custom product visibility when quering hidden products.
				add_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );

				// Render product template.
				// wc_get_template_part( 'content', 'product' );
                require __DIR__ . "/../templates/content-product.php";

				// Restore product visibility.
				remove_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );
			}

			$GLOBALS['post'] = $original_post; // WPCS: override ok.
			woocommerce_product_loop_end();
			do_action( "woocommerce_shortcode_after_{$this->type}_loop", $this->attributes );

			wp_reset_postdata();
		} else {
			do_action( "woocommerce_shortcode_{$this->type}_loop_no_results", $this->attributes );
		}

		woocommerce_reset_loop();

		return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . ob_get_clean() . '</div>';
	}
}

?>
