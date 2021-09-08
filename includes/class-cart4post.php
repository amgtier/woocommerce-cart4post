<?php
/**
 *
 * @class Cart4Post
 * @version 1.0.0
 */

class Cart4Post {
    /**
     * Cart4Post version
     * @var string
     */
    public $version = '1.0.0';
    /**
     * The single instance of the class.
     *
     * @var Cart4Post
     */
    protected static $_instance = null;

    /**
     * The hash table to store all the carts for each posts.
     */
    public $carts = Array();

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Cart4Post Constructor
     */
    public function __construct() {
        // exec when woocomerce exists.
        include_once (ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( function_exists( 'WC' ) || class_exists( 'WooCommerce' ) ){
            // initiates
            $this -> define_constants();
            $this -> init_hooks();
        } else {
            $class = 'notice notice-error';
            $message = __( 'Install and activate Woocommerce first.', 'c4p' );
            printf( '<br /><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
    }

    private function define_constants() {
        $this -> define( 'C4P_ABSPATH', dirname( C4P_PLUGIN_FILE ) . '/' );
        $this -> define( 'C4P_VERSION', '1.0.0' );
    }

    private function define( $name, $value ){
        if ( ! defined( $name )) {
            define( $name, $value );
        }
    }

    public function includes() {
        // admin page not implemented.
        // include_once( C4P_ABSPATH . 'admin/class-c4p-admin.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-ajax.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-shortcodes.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-cart.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-frontend-scripts.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-checkout.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-shortcode-products.php');
        include_once( C4P_ABSPATH . 'includes/class-c4p-shortcode-checkout.php');
        include_once( C4P_ABSPATH . 'includes/c4p-template-hooks.php');
        include_once( C4P_ABSPATH . 'includes/c4p-filter-functions.php');
    }

    private function install() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $collage = '';
        if ( $wpdb -> has_cap( 'collation' ) ) {
            $collage = $wpdb -> get_charset_collate();
        }
    }

    private function init_hooks() {
        add_action( 'init', array( $this, 'includes' ), 8 );
        add_action( 'init', array( 'C4P_Shortcodes', 'init' ) );
    }

    public function cart() {
        return C4P_Cart::instance();
    }

    public function checkout() {
        return C4P_Checkout::instance();
    }
}

?>
