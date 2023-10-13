<?php
function add_bundle_product_type_option($product_type_options) {
  $product_type_options['product_bundle'] = __('Product Bundle');
  return $product_type_options;
}
add_filter('product_type_selector', 'add_bundle_product_type_option');

function add_bundle_product_custom_tab($tabs) {
  $tabs['bundle_product_tab'] = array(
      'label'    => __('Bundle Product', 'product-bundle'),
      'target'   => 'bundle_product_options',
      'class'    => array('show_if_bundle_product'),
      'priority' => 80, 
  );
  return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'add_bundle_product_custom_tab');

function hide_product_data_tabs() {
  if ( 'product' !== get_post_type() ) {
    return;
  }
  ?>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      $('.show_if_bundle_product').hide();
      $('select#product-type').change(function() {
        if ($(this).val() === 'product_bundle') {
          $('.show_if_bundle_product').show();
        } else {
          $('.show_if_bundle_product').hide();
        }
      });
    });
  </script>
  <?php
}
add_action('admin_footer', 'hide_product_data_tabs');

function bundle_product_search_bar() {
  global $post;
  echo '<div id="bundle_product_options" class="bundle_product_search panel woocommerce_options_panel">';
  echo '<form action="" method="POST">';
  echo '<input type="text" id="bundle_product_search_input" name="bundle_product_search_input" placeholder="Search for products..." autocomplete="off">';
  echo '<input type="button" id="bundle_product_search_btn" value="Search">';
  echo '<div id="bundle_product_search_results"></div>';
  if (!empty($post->ID)) {
    $selected_products = get_post_meta($post->ID, 'select_product', true);

    if (!empty($selected_products)) {
        echo '<h4>Selected Products:</h4>';
        echo '<ul class="selected-products-list">';
        foreach ($selected_products as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                echo '<li class="selected-product" data-product-id="' . $product->get_id() . '">';
                echo '<h5>' . $product->get_name() . '</h5>';
                echo '<span>' . $product->get_image() . '</span>';
                echo '<a href="#" class="remove-selected-product" data-product-id="' . $product->get_id() . '">Remove</a>';
                echo '</li>';
            }
        }
        echo '</ul>';
    }
  }
  echo '</form>';
  echo '</div>';
}

add_action('woocommerce_product_data_panels', 'bundle_product_search_bar');

// AJAX callback to fetch search results
function bundle_product_search_results_callback() {
    $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';
    
    // Create a WP_Query to fetch the related product posts
    $args = array(
      'post_type' => 'product',
      'post_status' => 'publish',
      's' => $search_query,
      'posts_per_page' => -1
    );
  
    $query = new WP_Query($args);
  
    // Display the search results
    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        echo '<span><h2>' . get_the_title() . '</h2></span>';
        echo '<span><img src="' . get_the_post_thumbnail_url() . '" style="width:80px;"></span>';
        $selected_products = isset($_POST['selected_products']) ? $_POST['selected_products'] : array();
        $checked = in_array(get_the_id(), $selected_products) ? 'checked="checked"' : '';
        echo '<input type="checkbox" name="select_product[]" id="' . get_the_id() . '" class="" value="' . get_the_id() . '" ' . $checked . '">';
      }
    } else {
      echo 'No products found.';
    }
  
    wp_reset_postdata();
    wp_die();
  }
  

add_action('wp_ajax_bundle_product_search_results', 'bundle_product_search_results_callback');
add_action('wp_ajax_nopriv_bundle_product_search_results', 'bundle_product_search_results_callback');


function bundle_product_search_script() {
  ?>
  <script>
    jQuery(document).ready(function($) {
      $('#bundle_product_search_btn').click(function(e) {
        var searchQuery = $('#bundle_product_search_input').val();
        if (searchQuery !== '') {
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            data: {
              action: 'bundle_product_search_results',
              search_query: searchQuery
            },
            success: function(response) {
              $('#bundle_product_search_results').html(response);
            },
              error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });

      jQuery(document).ready(function($) {
      $('.remove-selected-product').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var selectedProduct = $('.selected-product[data-product-id="' + productId + '"]');
        selectedProduct.remove();
      });
    });
  });
  </script>
  <?php
}

add_action('admin_footer', 'bundle_product_search_script');

function wp_save_custom_tab_data($post_id) {
  $product_select = isset($_POST['select_product']) ? array_map('intval', $_POST['select_product']) : array();
  $existing_selected_products = get_post_meta($post_id, 'select_product', true);

  if (!is_array($existing_selected_products)) {
    $existing_selected_products = array();
  }

  $removed_products = array_diff($existing_selected_products, $product_select);

  update_post_meta($post_id, 'select_product', $product_select);

  foreach ($removed_products as $removed_product_id) {
    wp_delete_post($removed_product_id, true);
  }
}

add_action('woocommerce_process_product_meta', 'wp_save_custom_tab_data');

function wp_display_single_product_page() {
  global $product;
  $bundle_products = get_post_meta($product->get_id(), 'select_product', true);

  if (!empty($bundle_products)) {
    echo '<table>';
    foreach ($bundle_products as $bundle_product_id) {
      // Get the product object
      $bundle_product = wc_get_product($bundle_product_id);   
      // Display the product image and title
      echo '<tr style="background-color:white; border:1px solid black;">';
      echo '<td class="" style="width:50px;">' . $bundle_product->get_image(). ' </td>';
      echo '<td>' . $bundle_product->get_name() . '</td>';
      echo '<td>' . $bundle_product->get_price() . '</td>';
      echo '</tr>';
    }
    echo '</table>';
  }
}

add_action('woocommerce_single_product_summary', 'wp_display_single_product_page', 25);

function display_selected_products_in_cart($product_name, $cart_item, $cart_item_key) {
  $selected_products = get_post_meta($cart_item['product_id'], 'select_product', true);
  if (!empty($selected_products)) {
      $selected_product_names = array();
      // Retrieve the names of the selected products
      foreach ($selected_products as $product_id) {
          $product = wc_get_product($product_id);
          if ($product) {
              $selected_product_names[] = '<br>'.$product->get_image().'<br>';
              $selected_product_names[] = $product->get_name();
          }
      }
      // Add selected product names to the cart item name and a remove icon
      $product_name .= '<p class="cart" style="background-color:white; border:1px solid black;">'. implode($selected_product_names) . '</p>';
      $product_name .= '<a class="remove" href="' . esc_url(wc_get_cart_remove_url($cart_item_key)) . '" aria-label="Remove this item" data-product_id="' . $cart_item['product_id'] . '" data-cart_item_key="' . $cart_item_key . '"></a>';
  }
  return $product_name;
}

add_filter('woocommerce_cart_item_name', 'display_selected_products_in_cart', 10, 3);
