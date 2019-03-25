<?php
/**
 * Plugin Name: WooCommerce Cart Rules
 * Description: Add rules for what products can be in a cart at the same time
 * Version:     0.1.0
 * Author:      Noelle Leigh
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * WC requires at least: 3.5.7
 */
// Mitigate direct file access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function wcr_test_plugin() {
    print('This is woocommerce-cart-rules');
}
add_action( 'admin_notices', 'wcr_test_plugin' );

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here
    function wcr_test_woo_is_active() {
        print('WooCommerce is active!');
    }
    add_action( 'admin_notices', 'wcr_test_woo_is_active' );
}