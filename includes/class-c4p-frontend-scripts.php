<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Frontend_Scripts {

    private static $scripts = array();

    public static function init(){
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 0 );
    }

    public static function load_scripts() {
        global $post;
        if ( ! did_action( 'before_woocommerce_init' ) ) {
            return;
        }

        self::register_scripts();

        $is_checkout = true;
        if ( $is_checkout ) {
            self::enqueue_script( 'c4p-checkout' );
        }

        $is_add_to_cart = true;
        if ( $is_add_to_cart ) {
            self::enqueue_script( 'c4p-add-to-cart' );
        }
    }

    public static function register_scripts() {
        // $suffix = true || defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $suffix = '';
        $register_scripts = array(
            'c4p-checkout' => array(
                'src'   => plugins_url( 'assets/js/checkout' . $suffix .'.js', C4P_PLUGIN_FILE ),
                'deps' => array( 'jquery' ),
                'version' => C4P_VERSION,
            ),
            'c4p-add-to-cart' => array(
                'src'   => plugins_url( 'assets/js/add-to-cart' . $suffix .'.js', C4P_PLUGIN_FILE ),
                'deps' => array( 'jquery' ),
                'version' => C4P_VERSION,
            ),
        );
        foreach( $register_scripts as $name => $props ) {
            self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
        }
    }

    private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = C4P_VERSION, $in_footer = true ) {
        self::$scripts[] = $handle;
        wp_register_script( $handle, $path, $deps, $version, $in_footer );
    }

    private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = C4P_VERSION, $in_footer = true ){
        if ( ! in_array( $handle, self::$scripts ) && $path ){
            self::register_script( $handle, $path, $deps, $version, $in_footer );
        }
        wp_enqueue_script( $handle );
    }
}

C4P_Frontend_Scripts::init();
