<?php
/**
 * Plugin Name:       Payhip Products
 * Plugin URI:        
 * Description:       An unofficial Payhip plugin to show an overview page with products
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Martijn van den Bosch
 * Author URI:        https://martijnvandenbosch.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Martijnvdb\WordpressPluginTools;

use Martijnvdb\WordpressPluginTools\{PostType, CustomField, MetaBox, SettingsPage};

require_once __DIR__ . '/vendor/autoload.php';

$payhip_product_product_id = CustomField::create('payhip-products-product-id')
    ->setLabel('Payhip Product ID')
    ->setType('text');

$payhip_products_metadata = MetaBox::create('payhip-products-metadata')
    ->setTitle('Payhip product data')
    ->addCustomFields([
        $payhip_product_product_id,
    ]);

$payhip_products = PostType::create('payhip-products')
    ->setIcon('dashicons-cart')
    //->setSlug('webshop')
    ->setLabels([
        'name' => 'Products',
        'singular_name' => 'Product',
        'add_new' => 'New product',
        'add_new_item' => 'New product',
        'edit_item' => 'Edit product',
        'new_item' => 'New product',
        'view_item' => 'View product',
        'view_items' => 'Views products',
        'search_items' => 'Search products',
        'not_found' => 'No products',
        'not_found_in_trash' => 'No products found in trash',
        'all_items' => 'All products',
        'archives' => 'Product archives',
        'attributes' => 'Product attributes',
        'insert_into_item' => 'Insert into product',
        'uploaded_to_this_item' => 'Uploaded to this product',
        'featured_image' => 'Product image',
        'set_featured_image' => 'Set product image',
        'remove_featured_image' => 'Remove product image',
        'use_featured_image' => 'Use as product image',
        'filter_items_list' => 'Filter products lists',
        'items_list_navigation' => 'Products list navigation',
        'items_list' => 'Products list',
        'item_published' => 'Product published.',
        'item_published_privately' => 'Product published privately.',
        'item_reverted_to_draft' => 'Product reverted to draft.',
        'item_scheduled' => 'Product scheduled.',
        'item_updated' => 'Product updated.',
    ])
    ->addSupport(['excerpt', 'thumbnail'])
    ->addMetaBox($payhip_products_metadata)
    ->setPublic()
    ->build();

$payhip_products_settings_script_url = CustomField::create('payhip-products-settings-script-url')
    ->setLabel('Payhip Script URL')
    ->setType('text');

$payhip_products_settings = SettingsPage::create('payhip-products-settings')
    ->setPageTitle('Payhip Products Settings')
    ->setMenuTitle('Payhip Products Settings')
    ->addCustomFields([
        $payhip_products_settings_script_url
    ])
    ->build();


add_shortcode('payhip-products', function($atts) {
    $paypal_script_url = get_option('payhip-products-settings-script-url');

    if(empty($paypal_script_url)) {
        return '';
    }

    $output = '<script src="' . $paypal_script_url . '" type="text/javascript"></script>';

    query_posts([
        'post_type' => 'payhip-products',
        'orderby' => 'date',
        'order' => 'DESC',
        'showposts' => 1
    ]);

    if(have_posts()) {
        while(have_posts()) {
            the_post();

            $product_title = get_the_title();
            $product_image = get_the_post_thumbnail();
            $product_id = get_post_meta(get_the_ID(), 'payhip-products-product-id', true);

            $output .= '<a href="http://payhip.com/b/' . $product_id . '" class="payhip-buy-button" data-theme="green" data-product="' . $product_id . '">Buy now</a>';
        }
    }

    wp_reset_query();

    return $output;
});