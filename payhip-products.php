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
        'name' => 'Payhips Products',
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
    $output = '<script src="https://payhip.com/payhip.js" type="text/javascript"></script>';

    query_posts([
        'post_type' => 'payhip-products',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => -1
    ]);

    if(have_posts()) {

        $output .= <<<EOD
    <style>
        .payhip-products-container {
            overflow: hidden;
        }
        .payhip-products-wrapper {
            margin: -1em;
            display: flex;
            align-items: stretch;
            align-content: stretch;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .payhip-products-container .payhip-products-item {
            flex: 0 33.33333%;
            padding: 1em;
        }
        .payhip-products-container .payhip-products-inner {
            cursor: pointer;
        }
        .payhip-products-container .payhip-products-thumbnail {
            background-color: #edf2f7;
            background-size: cover;
            background-position: 50%;
            padding-bottom: 100%;
        }
        .payhip-products-container .payhip-products-content {

        }
        .payhip-products-container .payhip-products-content button {
            background-color: #5a67d8;
            color: #fff;
            padding-left: .75rem;
            padding-right: .75rem;
            padding-top: .25rem;
            padding-bottom: .25rem;
            font-size: .875rem;
            border-radius: .25rem;
        }
    </style>

    <div class="payhip-products-container">
        <div class="payhip-products-wrapper">
EOD;

        while(have_posts()) {
            the_post();

            $product_title = get_the_title();
            $product_image = get_the_post_thumbnail_url();
            $product_id = get_post_meta(get_the_ID(), 'payhip-products-product-id', true);

            $output .= <<<EOD
            <div class="payhip-products-item">
                <div class="payhip-products-inner" data-payhip-products-id="$product_id" data-payhip-products-message="">
                    <div class="payhip-products-thumbnail" style="background-image: url($product_image)"></div>
                    <header class="payhip-products-header">
                        <h3>
                            $product_title
                        </h3>
                    </header>
                    <div class="payhip-products-content">
                        <button type="button">Buy now</button>
                    </div>
                </div>
            </div>
EOD;

        }

            $output .= <<<EOD
        </div>
    </div>

    <script type="text/javascript">
        jQuery("[data-payhip-products-id]").click(function(e) {
            var target = e.target;
            var element;

            do {
                if(target.getAttribute('data-payhip-products-id')) {
                    element = target;
                    break;
                }

                target = target.parentElement;

            } while (target.parentElement)

            if(!element) {
                return;
            }

            var data = {};
            data.product = element.getAttribute('data-payhip-products-id');

            if(element.getAttribute('data-payhip-products-message')) {
                data.message = element.getAttribute('data-payhip-products-message');
            }
            
            Payhip.Checkout.open(data);
        });
    </script>
EOD;
    }

    wp_reset_query();

    return $output;
});