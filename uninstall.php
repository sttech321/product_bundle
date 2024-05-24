<<<<<<< HEAD
<?php
/**
 * Product Bundle Uninstall file
 *
 * Uninstalling Product Bundle deletes user roles, pages, tables, and options related to the plugin.
 */

// Exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

// Get all posts with the '_selected_bundle_products' meta key
$args = array(
    'post_type'      => 'any', // Or specify the post type if known
    'meta_key'       => '_selected_bundle_products',
    'posts_per_page' => -1,
    'fields'         => 'ids',
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    foreach ($query->posts as $post_id) {
        // Delete the '_selected_bundle_products' meta key for this post
        delete_post_meta($post_id, '_selected_bundle_products');
    }
}

// Flush WordPress cache
wp_cache_flush();
?>
=======
<?php
/**
 * Product Bundle Uninstall
 *
 * Uninstalling Product Bundle deletes user roles, pages, tables, and options related to the plugin.
 */

// Exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

// Get all posts with the '_selected_bundle_products' meta key
$args = array(
    'post_type'      => 'any', // Or specify the post type if known
    'meta_key'       => '_selected_bundle_products',
    'meta_value'     => '',
    'meta_compare'   => '!=',
    'posts_per_page' => -1,
    'fields'         => 'ids',
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    foreach ($query->posts as $post_id) {
        // Get the selected products for this post
        $selected_products = get_post_meta($post_id, '_selected_bundle_products', true);

        if (!empty($selected_products) && is_array($selected_products)) {
            foreach ($selected_products as $product_id) {
                // Delete each selected product post
                wp_delete_post($product_id, true);
            }
        }

        // Optionally, delete the post itself
        // wp_delete_post($post_id, true);
    }
}

// Flush WordPress cache
wp_cache_flush();
?>
>>>>>>> a12ae334955435e5efcdcc69a687fd42bbe0febf
