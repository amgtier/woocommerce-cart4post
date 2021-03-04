<?php
/**
 * Plugin Name: Woocommerce Cart for posts
 * Plugin URI: https://github.com/amgtier/woocommerce-cart4post
 * Author: Tzu-Hsiang Chao
 * Author URI: https://github.com/amgtier/
 * Description: Extends the functionality of the [product] shortcode for woocommerce.
 * Version: 0.0.1
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

$GLOBALS['cart4post'] = C4P();
?>
