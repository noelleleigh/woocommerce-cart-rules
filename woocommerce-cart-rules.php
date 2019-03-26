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

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    function prevent_multiple_bulk_orders() {
        global $woocommerce;
        global $product;
        // Get the categories this product belongs to
        $category_ids = $product->get_category_ids();
        // Check if it's a bulk product (ID 122) TODO: Make variable
        $is_bulk = in_array(122, $category_ids);
        // If it's not a bulk product, then we can add to cart as normal.
        if (!$is_bulk) {
            return true;
        }
        // Otherwise, we need to check to see if the cart already contains a different bulk product
        $cart = $woocommerce->$cart;
        foreach ( $cart->get_cart_contents() as $cart_item_key => $values ) {
            printr($cart_item_key);
            printr($values);
        }
    }
    // Run this function whenever an item is added to the cart.
    add_action('woocommerce_add_to_cart', 'prevent_multiple_bulk_orders');
}