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

    // Restricted product category
    $restricted_cat = (int) get_option('wcr_restricted_category_id');
    // Get product
    $product = wc_get_product($product_id);
    // Get the categories this product belongs to
    $category_ids = $product->get_category_ids();
    // Check if it's a bulk product
    $is_bulk = in_array($restricted_cat, $category_ids);
    // If it's not a bulk product, then we can add to cart as normal.
    if (!$is_bulk) {
        return true;
    }
    // Otherwise, we need to check to see if the cart already contains a different bulk product
    $cart = $woocommerce->cart;
    foreach ( $cart->get_cart_contents() as $cart_item_key => $values ) {
        $cart_item_is_bulk = in_array($restricted_cat, $values['data']->get_category_ids());
        $cart_item_is_different = $values['data']->get_id() !== $product_id;
        if ($cart_item_is_bulk and $cart_item_is_different) {
            $message = get_option('wcr_restricted_error_message', '[Configure this message in WooCommerce -> Cart Rules Plugin]');
            throw new Exception($message);
        }
    }
    // No conflicts were found
    return true;
}
add_action('woocommerce_add_to_cart', 'wcr_prevent_multiple_bulk_orders', 10, 6);

// https://codex.wordpress.org/Adding_Administration_Menus
// https://stackoverflow.com/questions/16928929/add-custom-admin-menu-to-woocommerce
/** Step 3. */
function wcr_options_html() {
	if ( !current_user_can( 'manage_woocommerce' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    <div class="wrap">
        <h1>WooCommerce Cart Rules</h1>
        <form method="POST" action="options.php">
            <?php
            settings_fields( 'wcr' );	//pass slug name of page, also referred to in Settings API as option group name
            do_settings_sections( 'wcr' ); 	//pass slug name of page
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/** Step 1. */
function wcr_add_submenu() {
	add_submenu_page( 'woocommerce', 'WooCommerce Cart Rules', 'Cart Rules Plugin', 'manage_woocommerce', 'wcr', 'wcr_options_html' );
}

/** Step 2 */
// Needs to be priority 20 to make sure it runs after WooCommerce sets up its own menus
add_action( 'admin_menu', 'wcr_add_submenu', 20 );

// https://codex.wordpress.org/Settings_API
// ------------------------------------------------------------------
// Add all your sections, fields and settings during admin_init
// ------------------------------------------------------------------
//

function wcr_settings_section_cb() {
    ?>
    <p>Use this plugin to prevent a customer from adding multiple different products from a specific category to their cart.</p>
    <p>If they already have a product from this category in their cart, attempts to add a different product from the same category will be denied.</p>
    <?php
}

function wcr_restricted_category_id_cb() {
    // https://stackoverflow.com/a/21012252
    $taxonomy     = 'product_cat';
    $orderby      = 'name';
    $show_count   = 0;      // 1 for yes, 0 for no
    $pad_counts   = 0;      // 1 for yes, 0 for no
    $hierarchical = 1;      // 1 for yes, 0 for no
    $title        = '';
    $empty        = 0;

    $args = array(
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li'     => $title,
            'hide_empty'   => $empty
    );
    $all_categories = get_categories( $args );
    ?>
    <select name="wcr_restricted_category_id" id="wcr_restricted_category_id" required>
        <option value="">Choose a product category</option>
        <?php
        $current_cat_id = (int) get_option('wcr_restricted_category_id');
        foreach($all_categories as $key => $cat) {
            $is_current_cat_id = $cat->cat_ID === $current_cat_id;
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $cat->cat_ID,
                $is_current_cat_id ? 'selected' : '',
                $cat->cat_name
            );
        }
        ?>
    </select>
    <?php
}

function wcr_sanitize_category_id($input) {
    $int_input = (int) $input;
    if ($int_input < 0) {
        throw new Error('Category ID must be greater than 0.');
    }
    return $int_input;
}

function wcr_restricted_error_message_cb() {
    // get the value of the setting we've registered with register_setting()
    $setting = get_option('wcr_restricted_error_message');
    // output the field
    ?>
    <input
        type="text"
        size="40"
        required
        name="wcr_restricted_error_message"
        value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>"
    >
    <?php
}

function wcr_sanitize_error_message($input) {
    if (!is_string($input)) {
        throw new TypeError('Error message must be a string, not a '. gettype($input) .'.');
    }
    return sanitize_text_field($input);
}

function wcr_settings_api_init() {
    add_settings_section(
        'wcr_settings_section',
        'Settings',
        'wcr_settings_section_cb',
        'wcr'
    );

    add_settings_field(
        'wcr_restricted_category_id',
        'Restricted product category',
        'wcr_restricted_category_id_cb',
        'wcr',
        'wcr_settings_section'
    );

    add_settings_field(
        'wcr_restricted_error_message',
        'Error message to show to the customer',
        'wcr_restricted_error_message_cb',
        'wcr',
        'wcr_settings_section'
    );

    register_setting(
        'wcr',
        'wcr_restricted_category_id',
        array(
            'type' => 'integer',
            'description' => 'The WooCommerce category ID that limited to one type per cart.',
            'sanitize_callback' => 'wcr_sanitize_category_id'
        )
    );
    register_setting(
        'wcr',
        'wcr_restricted_error_message',
        array(
            'type' => 'string',
            'description' => 'The error message to be displayed when multiple restricted items are added to the cart.',
            'sanitize_callback' => 'wcr_sanitize_error_message'
        )
    );
} // wcr_settings_api_init()

add_action( 'admin_init', 'wcr_settings_api_init' );

// Remove options
function wcr_remove_options() {
    delete_option('wcr_restricted_category_id');
    delete_option('wcr_restricted_error_message');
}
function wcr_uninstall() {
    wcr_remove_options();
}
register_uninstall_hook(__FILE__, 'wcr_uninstall');