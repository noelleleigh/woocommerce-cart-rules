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

// Main functionality of the plugin
function wcr_prevent_multiple_bulk_orders($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    global $woocommerce;

    // Get product
    $product = wc_get_product($product_id);
    // Get the categories this product belongs to
    $category_ids = $product->get_category_ids();
    // Check if it's a bulk product (ID 122) TODO: Make variable
    $is_bulk = in_array(122, $category_ids);
    // If it's not a bulk product, then we can add to cart as normal.
    if (!$is_bulk) {
        return true;
    }
    // Otherwise, we need to check to see if the cart already contains a different bulk product
    $cart = $woocommerce->cart;
    foreach ( $cart->get_cart_contents() as $cart_item_key => $values ) {
        $cart_item_is_bulk = in_array(122, $values['data']->get_category_ids());
        $cart_item_is_different = $values['data']->get_id() !== $product_id;
        if ($cart_item_is_bulk and $cart_item_is_different) {
            throw new Exception( 'You cannot have more than one kind of bulk product in your cart.');
        }
    }
    // No conflicts were found
    return true;
}

// Render the options page
function wcr_options_page_html()
{
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options-general.php?page=wcr" method="post">
            <?php
            // output security fields for the registered setting "wcr_options"
            settings_fields('wcr_options');
            // output setting sections and their fields
            // (sections are registered for "wporg", each field is registered to a specific section)
            do_settings_sections('wcr');
            // output save settings button
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Add the options submenu
function wcr_options_page() {
    // add_submenu_page(
    //     'edit.php?post_type=shop_order',
    //     'WooCommerce Cart Rules Options',
    //     'Cart Rules Options',
    //     'manage_options',
    //     'wcr',
    //     'wcr_options_page_html'
    // );
    add_options_page(
        'WooCommerce Cart Rules Options',
        'Cart Rules',
        'manage_options',
        'wcr',
        'wcr_options_page_html'
    );
}

// Deactivation
function deactivate_woocommerce_cart_rules() {
    remove_submenu_page('options-general.php', 'wcr');
}

register_deactivation_hook( __FILE__, 'deactivate_woocommerce_cart_rules' );


// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Run this function whenever an item is added to the cart.
    add_action('woocommerce_add_to_cart', 'wcr_prevent_multiple_bulk_orders', 10, 6);
    // Add the options page to the Dashboard
    add_action('admin_menu', 'wcr_options_page');
}