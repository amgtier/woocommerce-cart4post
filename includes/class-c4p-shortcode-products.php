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
	 * Loop over found products.
	 *
	 * @since  3.2.0
	 * @return string
	 */
	protected function product_loop() {
		global $woocommerce_loop;

		$columns                     = absint( $this->attributes['columns'] );
		$classes                     = $this->get_wrapper_classes( $columns );
		$woocommerce_loop['columns'] = $columns;
		$woocommerce_loop['name']    = $this->type;
		$products_ids                = $this->get_products_ids();

		ob_start();

		if ( $products_ids ) {
			// Prime meta cache to reduce future queries.
			update_meta_cache( 'post', $products_ids );
			update_object_term_cache( $products_ids, 'product' );

			$original_post = $GLOBALS['post'];

			do_action( "woocommerce_shortcode_before_{$this->type}_loop", $this->attributes );

			woocommerce_product_loop_start();

			foreach ( $products_ids as $product_id ) {
				$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
				setup_postdata( $GLOBALS['post'] );

				// Set custom product visibility when quering hidden products.
				add_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );

				// Render product template.
				// wc_get_template_part( 'content', 'product' );
                require "content-product.php";

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
