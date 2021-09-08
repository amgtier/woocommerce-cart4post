<?php
/**
 * Plugin Name: Woocommerce Cart for posts
 * Plugin URI: https://github.com/amgtier/woocommerce-cart4post
 * Author: Tzu-Hsiang Chao
 * Author URI: https://github.com/amgtier/
 * Description: Extends the functionality of the [product] shortcode for woocommerce.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists ('Cart4Post' ) ){
    include_once dirname( __FILE__ ) . '/includes/class-cart4post.php';
}

if ( ! defined( 'C4P_PLUGIN_FILE' ) ) {
    define( 'C4P_PLUGIN_FILE', __FILE__ );
}

function c4p(){
    return Cart4Post::instance();
}

function c4p_load_textdomain() {
    load_plugin_textdomain( 'c4p', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    error_log( dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action('plugins_loaded', 'cart4post_init', 0);
function cart4post_init() {
    add_action( 'plugins_loaded', 'c4p_load_textdomain' );
    $GLOBALS['cart4post'] = C4P();
}
?>
