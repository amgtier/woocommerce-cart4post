<?php
/**
 * Cart4Post Admin
 *
 * @class C4P_Admin
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class C4P_Admin {
    public function __construct() {
        $this -> init();
    }

    private function init() {
        $this -> add_admin_menu();
    }

    private function add_admin_menu(){
        add_menu_page(
            __('Cart4Post', 'cart4post'),
            __('Cart4Post', 'cart4post'),
            'manage_options',
            'cart4post',
            array( $this, 'testFunc'),
            null,
            100
        );
        add_submenu_page(
            'cart4post',
            __('Cart4Post - Posts', 'cart4post'),
            __('Posts', 'cart4post'),
            'manage_options',
            'c4p-posts',
            array( $this, 'testFunc')
        );

    }

    public function testFunc(){
        printf("<h1>Hello world;</h1>");
    }

};

// return new C4P_Admin();
