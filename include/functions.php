<?php
// Wrap your code in a function
function check_woocomerce_plugin_is_active() {
    // Check if WooCommerce is active
    if (class_exists('WooCommerce')) {
        // Enqueue styles
        function enqueue_custom_styles() {
            // Get the plugin directory URL
            $plugin_dir_url = plugin_dir_url(__FILE__);

            // Enqueue the stylesheet from the /template/template-parts folder
            wp_enqueue_style('customss-style', $plugin_dir_url . '../assets/css/main.css', array(), '1.0.0', 'all');

        }
        add_action('wp_enqueue_scripts', 'enqueue_custom_styles');

    //  Here is the code for display bundle product on frontend.
        function bundle_product_search_bars() {
            global $post;
            // Display selected products on the product page
            $selected_products = get_post_meta(get_the_ID(), '_selected_bundle_products', true);
            if (!empty($selected_products)) {
                echo '<h3 class="bundle_product">Bundle products</h3>';
                echo'<div class="bundle_data">';
                foreach ($selected_products as $product) {
                    echo '<div class="bundle-product-data">';
                    echo '<span class="bundle_product_name">' . esc_html($product['label']) . '</span>';
                    echo '<span class="bundle_product_image"><img src="' . esc_url($product['image']) . '" alt="' . esc_html($product['label']) . '"></span>';
                    echo '</div>';
                }
                echo'</div>';
            }
        }

        add_action('woocommerce_single_product_summary', 'bundle_product_search_bars');

    //  here is the code for display bundle product on cart page.
        function display_selected_products_in_cart($product_name, $cart_item) {
            // Get selected product IDs for this cart item
            $selected_products = get_post_meta($cart_item['product_id'], '_selected_bundle_products', true);
            
            if (!empty($selected_products)) {
                foreach ($selected_products as $product_data) {
                    $product_name .= '<div class="cart_product_data">';
                    $product_name .= '<span class="cart_product_name">' . esc_html($product_data['label']) . '</span>';
                    $product_name .= '<span class="cart_product_image"><img width ="25%" src="' . esc_url($product_data['image']) . '" alt="' . esc_html($product_data['label']) . '"></span>';
                    $product_name .= '</div>';
                }
            }

            return $product_name;
        }

        add_filter('woocommerce_cart_item_name', 'display_selected_products_in_cart', 10, 3);
        

} else {

    add_action('admin_notices', 'custom_bundle_product_missing_wc_notice');
}
}

// Add an action hook to run your plugin initialization function when WooCommerce is loaded
add_action('woocommerce_loaded', 'check_woocomerce_plugin_is_active');

// Function to display an admin notice if WooCommerce is not active
function display_error() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Custom Bundle Product plugin requires WooCommerce to be installed and activated.', 'custom-bundle-product'); ?></p>
    </div>
    <?php
}

