<?php
/*
Plugin Name: Product Bundle
Version: 0.1
Description: Offer personalized product bundles, bulk discount packages, and assembled products.
Author: Supremetechnologies
Author URI: https://supremetechnologiesindia.com/
Text-domain: product-bundle
Woocommerce Version: work with 8.8.3 
*/



// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


include(plugin_dir_path(__FILE__) . "/include/functions.php");
include(plugin_dir_path(__FILE__) . '/admin/admin.php');

?>