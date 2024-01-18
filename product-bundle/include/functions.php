<?php

// $plugin_dir_path = plugin_dir_path(__FILE__);
// include($plugin_dir_path . '../admin/admin.php');

// Enqueue styles
function enqueue_custom_styles() {
    // Get the plugin directory URL
    $plugin_dir_url = plugin_dir_url(__FILE__);

    // Enqueue the stylesheet from the /template/template-parts folder
    wp_enqueue_style('custom-style', $plugin_dir_url . '../assets/css/style.css', array(), '1.0.0', 'all');

}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');


function bundle_product_search_bars() {
    global $post;
    // Display selected products on the product page
    $selected_products = get_post_meta(get_the_ID(), '_selected_bundle_products', true);
    if (!empty($selected_products)) {
        echo '<h3 class="bundle_product">Bundle products</h3>';
        echo'<div class="bundle_data">';
        foreach ($selected_products as $product) {
            echo '<div class="product_data">';
            echo '<span class="product_name">' . esc_html($product['label']) . '</span>';
            echo '<span class="product_image"><img width ="25%" src="' . esc_url($product['image']) . '" alt="' . esc_html($product['label']) . '"></span>';
            echo '</div>';
        }
        echo'</div>';
    }
}

add_action('woocommerce_single_product_summary', 'bundle_product_search_bars');


function display_selected_products_in_cart($product_name, $cart_item, $cart_item_key) {
    // Get selected product IDs for this cart item
    $selected_products = get_post_meta($cart_item['product_id'], '_selected_bundle_products', true);
    
    if (!empty($selected_products)) {
        foreach ($selected_products as $product_data) {
            $product_name .= '<div class="product-data">';
            $product_name .= '<span class="product-name">' . esc_html($product_data['label']) . '</span>';
            $product_name .= '<span class="product-image"><img width ="25%" src="' . esc_url($product_data['image']) . '" alt="' . esc_html($product_data['label']) . '"></span>';
            $product_name .= '</div>';
        }
    }

    return $product_name;
}

add_filter('woocommerce_cart_item_name', 'display_selected_products_in_cart', 10, 3);