=== WooCommerce Cart Rules ===
Contributors: Noelle Leigh
Tags: woocommerce, ecommerce, cart
Requires at least: 5.1.1
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add rules for what products can be in a cart at the same time.

== Description ==

This plugin integrates with WooCommerce (tested on 3.5.7) to allow you to select a product category that can only have one unique product of that category in the cart at any one time. Here's an example:

Here are your products and the categories they belong to:

| Category | Product               |
|----------|-----------------------|
| Books    | To Kill a Mockingbird |
| Books    | The Great Gatsby      |
| Toys     | Rocking Horse         |

If you choose **Books** as the restricted category, a customer could add as many copies of *To Kill a Mockingbird* as they wanted to their cart, but they would be unable to add *The Great Gatsby* to their cart because there is already a different item in the cart from the same category.

== Installation ==

1. Make sure you're running a supported version of WordPress and WooCommerce.
1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the **Dashboard** > **WooCommerce** > **Cart Rules Plugin** screen to configure the restricted product category and the message to display to the user if they attempt to add different products of that category to the cart.

== Development ==

Run the `New-Plugin-Bundle.ps1` PowerShell script to build a new plugin ZIP file for installation through the WordPress plugins screen.