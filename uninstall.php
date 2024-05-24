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
