<?php
/**
 *
 * @class Cart4Post
 * @version 0.0.1
 */

class Cart4Post {
    /**
     * Cart4Post version
     * @var string
     */
    public $version = '0.0.1';
    /**
     * The single instance of the class.
     *
     * @var Cart4Post
     */
    protected static $_instance = null;

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
        if ( is_plugin_active( 'woocommerce-3.2.6/woocommerce.php' ) ){
            // initiates
            $this -> define_constants();
            // $this -> install();
            $this -> init_hooks();
            // $this -> init();
        } else {
            $class = 'notice notice-error';
            $message = __( 'Install and activate Woocommerce first.' );
            printf( '<br /><div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        }
    }

    private function define_constants() {
        $this -> define( 'CART4POSTPATH', ABSPATH . 'wp-content/plugins/woocommerce-cart4post/' );
    }

    private function define( $name, $value ){
        if ( ! defined( $name )) {
            define( $name, $value );
        }
    }

    public function includes() {
        include_once( CART4POSTPATH . 'admin/class-c4p-admin.php');
        include_once( CART4POSTPATH . 'includes/class-c4p-shortcodes.php');
        /* shortcodes */
        include_once( CART4POSTPATH . 'includes/class-c4p-shortcode-products.php');
        include_once( CART4POSTPATH . 'includes/c4p-template-hooks.php');
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
        // add_action( 'init', array( 'Cart4Post', 'init' ) );
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'init', array( 'C4P_Shortcodes', 'init' ) );
    }

}

?>
