<?php
/**
 *
 * @class C4P_Pre_Order
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Pre_Order{
    /**
     * C4P_Pre_Order version 0.0.1
     */
    protected static $instance = null;
    public static $META_PRE_ORDER = "_c4p_pre_order";
    public function init() {
    }

    public function __construct() {
        $this->setup_product_posts_columns();
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setup_product_posts_columns() {
        // add_filter( 'manage_product_posts_columns', array( $this, 'add_product_list_table_columns' ) );
        // add_action( 'manage_product_posts_custom_column', array( $this, 'add_product_list_table_columns_content' ) );
        add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'product_options_inventory_submit_pre_order' ) );
        // add_action( 'woocommerce_product_bulk_and_quick_edit', array( $this, 'bulk_edit_variations' ), 10, 2 );
        add_action( 'woocommerce_product_is_in_stock', array( $this, 'product_is_in_stock' ), 10, 2 );
        add_filter( 'woocommerce_product_stock_status_options', array( $this, 'add_stock_statuses' ), 10, 1 );
        add_filter( 'woocommerce_admin_stock_html', array( $this, 'admin_stock_html' ), 10, 2 );
    }

    public function add_product_list_table_columns( $columns ) {
        $columns[ 'c4p-pre-order' ] = __( 'Pre-order', 'c4p' );
        return $columns;
    }

    public function add_product_list_table_columns_content( $column ) {
        global $post;

        if ( 'c4p-pre-order' !== $column ) {
            return;
        }
        
        $product = wc_get_product( $post );

        if ( $product ) {
            esc_html_e( 'Test', 'c4p' );
        }
    }

    public function product_options_inventory_submit_pre_order() {
        global $post;

        $product = wc_get_product( $post );
?>
        <div class="options_group show_if_simple show_if_variable">
<?php
            // woocommerce_wp_checkbox(
            //     array(
            //         'id'            => '_c4p_pre_order',
            //         'value'         => get_post_meta( $product->get_id(), self::$META_PRE_ORDER, true ),
            //         'wrapper_class' => 'show_if_simple show_if_variable',
            //         'label'         => __( 'Pre-order', 'c4p' ),
            //         'description'   => __( 'Enable to set this a pre-order product.', 'c4p' ),
            //     )
            // );
?>
            <button disabled type="button">Submit</button>
        </div>
<?php
    }

    public function bulk_edit_variations( $post_id, $post ) {
        $product = wc_get_product( $post );

        if ( isset( $_REQUEST[ '_c4p_pre_order' ] ) && ( $_REQUEST[ '_c4p_pre_order' ] == 'yes' || $_REQUEST[ '_c4p_pre_order' ] == 'no' ) ) {
            update_post_meta( $post_id, self::$META_PRE_ORDER, $_REQUEST[ '_c4p_pre_order' ] );
            $product->set_stock_status( 'c4p-pre-order' );
        } else {
            update_post_meta( $post_id, self::$META_PRE_ORDER, 'no' );
            $product->validate_props();
        }
    }

    public function product_is_in_stock( $stock_status, $product ) {
        return $stock_status;
    }

    public function add_stock_statuses( $statuses ) {
        $statuses[ 'c4p-pre-order' ] = __( 'Pre-order', 'c4p' );
        return $statuses;
    }

    public function admin_stock_html( $html, $product ){
        return $product->get_stock_status() == 'c4p-pre-order' ?
            '<mark class="onbackorder">' . __( 'Pre-order', 'c4p' ) . '</mark>' : $html;
    }
}

new C4P_Pre_order();
