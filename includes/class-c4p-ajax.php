<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_AJAX {
    public static function init(){
        add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'do_c4p_ajax' ), 0);
        self::add_ajax_events();
    }

    public static function define_ajax() {
        if ( ! empty( $_GET['c4p-ajax'] ) ) {
            if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
                @ini_set( 'display_errors', 0 );
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    private static function c4p_ajax_headers() {
        send_origin_headers();
        @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        @header( 'X-Robots-Tag: noindex' );
        send_nosniff_header();
        // wc_nocache_headers();
        status_header( 200 );
    }

    public static function do_c4p_ajax(){
        global $wp_query;
        if ( ! empty( $_GET['c4p-ajax'] ) ) {
            $wp_query->set( 'c4p-ajax', sanitize_text_field( $_GET['c4p-ajax'] ) );
        }
        if ( $action = $wp_query->get( 'c4p-ajax' ) ) {
            self::c4p_ajax_headers();
            do_action( 'c4p_ajax_' . sanitize_text_field( $action ) );
            wp_die();
        }
    }

    public static function add_ajax_events() {
        $ajax_events = array(
            'checkout' => true,
            'get_cart_totals' => true
        );
        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_cart4post_' . $ajax_event, array( __CLASS__, $ajax_event) );
            if ( $nopriv ){
                add_action( 'wp_ajax_nopriv_cart4post_' . $ajax_event, array( __CLASS__, $ajax_event ) );
                add_action( 'c4p_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }
    }

    public static function checkout() {
        C4P()->checkout()->process_checkout();
        wp_die( 0 );
    }

    public static function get_cart_totals() {
        C4P()->cart()->calculate_shipping();
        C4P()->cart()->get_cart_totals();
        wp_die( 0 );
    }
}

C4P_AJAX::init();
