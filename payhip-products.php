<?php
/**
 * Plugin Name:       Payhip Products
 * Plugin URI:        https://github.com/martijnvdb87/wordpress-plugin-payhip-products
 * Description:       An unofficial Payhip plugin to show an overview page with products
 * Version:           1.0.0
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
    ->setLabel('Product ID')
    ->setType('text');

$payhip_product_product_currency = CustomField::create('payhip-products-product-currency')
    ->setLabel('Price Currency')
    ->setType('text');

$payhip_product_product_price = CustomField::create('payhip-products-product-price')
    ->setLabel('Product Price')
    ->setType('text');

$payhip_product_product_buynow = CustomField::create('payhip-products-product-buynow')
    ->setLabel('Button \'Buy Now\' Label')
    ->setType('text');

$payhip_products_metadata = MetaBox::create('payhip-products-metadata')
    ->setTitle('Payhip product data')
    ->addCustomFields([
        $payhip_product_product_id,
        $payhip_product_product_currency,
        $payhip_product_product_price,
        $payhip_product_product_buynow,
    ]);

$payhip_products = PostType::create('payhip-products')
    ->setIcon('dashicons-cart')
    ->setLabels([
        'name' => 'Payhip Products',
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
    ->addSupport(['thumbnail'])
    ->removeSupport(['editor'])
    ->addMetaBox($payhip_products_metadata)
    ->setPublic()
    ->addOption('publicly_queryable', false)
    ->build();

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script( 'payhip-main', 'https://payhip.com/payhip.js', false );
    wp_enqueue_script('payhip-products-script', plugins_url( 'payhip-products/resources/js/main.js', __DIR__ ), ['jquery', 'payhip-main'], false, true);
    wp_enqueue_style('payhip-products-style', plugins_url( 'payhip-products/resources/css/main.css', __DIR__ ));
});

add_shortcode('payhip-products', function($atts) {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/resources/views');
    $twig = new \Twig\Environment($loader);

    query_posts([
        'post_type' => 'payhip-products',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => -1
    ]);

    $products = [];

    if(have_posts()) {
        while(have_posts()) {
            the_post();

            $products[] = [
                'id' => get_post_meta(get_the_ID(), 'payhip-products-product-id', true),
                'title' => get_the_title(),
                'image' => get_the_post_thumbnail_url(),
                'currency' => get_post_meta(get_the_ID(), 'payhip-products-product-currency', true),
                'price' => get_post_meta(get_the_ID(), 'payhip-products-product-price', true),
                'buynow' => get_post_meta(get_the_ID(), 'payhip-products-product-buynow', true)
            ];
            $product_title = get_the_title();
            $product_image = get_the_post_thumbnail_url();
            $product_id = get_post_meta(get_the_ID(), 'payhip-products-product-id', true);
            $product_currency = get_post_meta(get_the_ID(), 'payhip-products-product-currency', true);
            $product_price = get_post_meta(get_the_ID(), 'payhip-products-product-price', true);
            $product_buynow = get_post_meta(get_the_ID(), 'payhip-products-product-buynow', true);
        }
    }

    wp_reset_query();
    
    return $twig->render('shortcode-payhip-products.html', [
        'products' => $products
    ]);
});