<?php
function add_bundle_product_type_option($product_type_options) {
  $product_type_options['product_bundle'] = __('Product Bundle');
  return $product_type_options;
}
add_filter('product_type_selector', 'add_bundle_product_type_option');
 
 
function set_default_product_type($product_type) {
  if (empty($_REQUEST['product-type']) && isset($_GET['post'])) {
      $post_id = $_GET['post'];
      $product = wc_get_product($post_id);
 
      if ($product && 'product_bundle' === $product->get_type()) {
          $product_type = 'product_bundle';
      }
  }
  return $product_type;
}
add_filter('default_product_type', 'set_default_product_type');
 
 
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
 
 
function force_select_product_bundle() {
  echo '<script type="text/javascript">
      jQuery(document).ready(function($) {
          // Select "Product Bundle" as the product type
          $("select#product-type").val("product_bundle").change();
 
          // Show the "Bundle Product" tab by triggering a click event on it
          $(".show_if_bundle_product").find("a").click();
 
          // Ensure that the tab content is visible
          $(".show_if_bundle_product").show();
      });
  </script>';
}
 
add_action('admin_footer', 'force_select_product_bundle');
 
function hide_product_data_tabs() {
  if ('product' !== get_post_type()) {
      return;
  }
  ?>
  <script type="text/javascript">
      jQuery(document).ready(function ($) {
          function showBundleTab() {
              if ($('select#product-type').val() === 'product_bundle') {
                  $('.show_if_bundle_product').show();
              } else {
                  $('.show_if_bundle_product').hide();
              }
          }
          showBundleTab();
 
          $('select#product-type').change(function () {
              showBundleTab();
          });
      });
  </script>
  <?php
}
 
add_action('admin_footer', 'hide_product_data_tabs');
 

// Bundle Product tab
function bundle_product_search_bar() {
    global $post;
    $html = '';
    $html .= '<div id="bundle_product_options" class="bundle_product_search panel woocommerce_options_panel">';
    $html .= '<form action="" method="POST">';
    $html .= '<input type="text" id="bundle_product_search_input" name="bundle_product_search_input" placeholder="Search for products..." autocomplete="off">';
    $html .= '<div id="bundle_product_search_results"></div>';
    $html .= '<div id="selected_products"></div>';
    $html .= '<div id="loader" style="display:none; position:absolute; bottom:100px; left:600px;">';
    $html .= '<img src="https://i.gifer.com/ZKZg.gif" alt="Loading..." style="width:100px; height:100px; ">';
    $html .= '</div>';
    $html .= '</form>';

    $selected_products = get_post_meta($post->ID, '_selected_bundle_products', true);

    if (!empty($selected_products)) {
        $html .= '<br><br>';
        $html .= '<h2>Selected Products:</h2>';
        $html .= '<ul id="selected-products-list">'; 
        foreach ($selected_products as $product) {
            // $html .= '<li data-product-index="' . $index . '">';
            $html .= '<li style="margin-right:20px; margin-left:20px;">' . esc_html($product['label']) . '</li>';
            $html .= '<li>' . esc_html($product['image']) . '</li>';
            $html .=  '<input type="submit" value="remove" class="remove" id="remove">';
            // $html .= '</li>';
        }
        $html .= '</ul>';
    }

    $html .= '</div>';
    echo $html;
}

add_action('woocommerce_product_data_panels', 'bundle_product_search_bar');

function bundle_product_search_scripts() {
    ?>
    <script>
        jQuery(document).ready(function($) {
    // Remove product when the remove button is clicked
    $(document).on('click', '.remove', function(e) {
        e.preventDefault();

        // Get the parent <ul> element
        var $parentUl = $(this).closest('ul');

        // Get the product label
        var productLabel = $parentUl.find('li.product-label').text();

        // Confirm removal
        var confirmRemove = confirm('Are you sure you want to remove the product: ' + productLabel + '?');

        if (confirmRemove) {
            // Remove the product <ul> from the DOM
            $parentUl.remove();

            // You may also want to send an AJAX request to remove the data from the server
            var productId = $parentUl.data('product-id');
            
            // Add your AJAX request here to delete the product data based on productId
            // Example:
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'remove_selected_product',
                    product_id: productId,
                    post_id: <?php echo get_the_ID(); ?>,
                    security: '<?php echo wp_create_nonce("your_ajax_nonce"); ?>'
                },
                success: function(response) {
                    // Handle the server response
                    console.log(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    });
});

    </script>
    <?php
}
add_action('admin_footer', 'bundle_product_search_scripts'); 

// Add this to your functions.php file or a custom plugin
add_action('wp_ajax_remove_selected_product', 'remove_selected_product');
add_action('wp_ajax_nopriv_remove_selected_product', 'remove_selected_product');

function remove_selected_product() {
    check_ajax_referer('your_ajax_nonce', 'security');
    $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
    $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : '';

    $metakey = '_selected_bundle_products';
    // Get the existing selected products
    $selected_products = get_post_meta($post_id, $metakey, true);

    // Remove the product based on product ID
    foreach ($selected_products as $index => $product) {
        if ($product['id'] == $product_id) {
            unset($selected_products[$index]);
            break;
        }
    }

    // Update post meta
    update_post_meta($post_id, $metakey, $selected_products);

    // Send a response back to the client
    echo json_encode(array('success' => true));

    wp_die(); // This is required to terminate immediately and return a proper response
}



function save_selected_products_callback() {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $metakey = '_selected_bundle_products';

    $selected_products = isset($_POST['selected_products']) ? $_POST['selected_products'] : array();
    $existing_selected_products = get_post_meta($post_id, $metakey, true);

    // If existing selected products exist, merge them with the new ones and remove duplicates
    if (is_array($existing_selected_products)) {
        $merged_selected_products = array_merge($existing_selected_products, $selected_products);

        // Filter out duplicates manually
        $unique_selected_products = array();
        foreach ($merged_selected_products as $product) {
            if (!in_array($product, $unique_selected_products)) {
                $unique_selected_products[] = $product;
            }
        }
    } else {
        // If no existing selected products, use the new ones
        $unique_selected_products = $selected_products;
    }

    // Update post meta with the unique selected products
    update_post_meta($post_id, $metakey, $unique_selected_products);

    // Output success message or any response needed
    echo json_encode(['status' => 'success', 'message' => 'Selected products saved successfully.']);

    wp_die(); // Always use wp_die() to end AJAX requests
}




add_action('wp_ajax_save_selected_products', 'save_selected_products_callback');
add_action('wp_ajax_nopriv_save_selected_products', 'save_selected_products_callback');

 

function bundle_product_search_script() {
  ?>
  <script>

      jQuery(document).ready(function($) {
          var selectedProducts = [];
          var searchField = $('#bundle_product_search_input');
          var selectedProductsContainer = $('#selected_products');
          var post_id = <?php echo get_the_ID(); ?>;

          searchField.autocomplete({
              source: function(request, response) {
                  if (request.term.length < 5) {
                      $('#bundle_product_search_results').css({
                          "display": "flex",
                          "text-align": "left",
                          "width": "100%",
                          "margin": "9px"
                      }).text('Please enter at least 5 letters');
                      response([]);
                  } else {
                      $('#bundle_product_search_results').text('');
                      $.ajax({
                          url: '<?php echo admin_url('admin-ajax.php'); ?>',
                          dataType: 'json',
                          type: 'POST',
                          data: {
                              action: 'bundle_product_search_results',
                              search_query: request.term
                          },
                          success: function(data) {
                              var filteredData = $.map(data, function(item) {
                                  if (item.label.toLowerCase().includes(request.term.toLowerCase())) {
                                      return item;
                                  }
                              });
                              response(filteredData);
                          }
                      });
                  }
              },
              select: function(event, ui) {
                  var selectedProduct = ui.item;
 
                  if (selectedProduct && selectedProduct.value) {
                      selectedProducts.push(selectedProduct);
 
                      $.ajax({
                          url: '<?php echo admin_url('admin-ajax.php'); ?>',
                          type: 'POST',
                          data: {
                              action: 'save_selected_products',
                              selected_products: selectedProducts,
                              post_id: <?php echo get_the_ID(); ?>,
                          },
                          success: function(response) {
                              console.log(response);
                          }
                      });
 
                      var productDetail = '<div class="selected-product">';
                      productDetail += '<img src="' + selectedProduct.image + '" alt="' + selectedProduct.label + '" style="width:70px; margin-left: 50px;">';
                      productDetail += '<p style="font-size:20px; margin-left:80px; display:inline;">' + selectedProduct.label + '</p>';
                      productDetail += '<button class="remove-product" data-product-id="' + selectedProduct.value + '" style="color: #1d2327; position: relative; left: 700px; bottom: 50px; background-color: white; text-color: white;">Remove product</button>';
                      productDetail += '</div>';
                      selectedProductsContainer.append(productDetail);
 
                      $('#loader').css({
                          "width": "40%",
                          "height": "40%",
                          "display": "block",
                          "position": "absolute",
                          "z-index": "9999",
                      }).show();
                      setTimeout(function() {
                          $('#loader').hide();
                      }, 3000);
 
                      searchField.val('');
                      return false;
                  }
              }
          });
 
          // Add click event for Remove button
          selectedProductsContainer.on('click', '.remove-product', function() {
              var productId = $(this).data('product-id');
              selectedProducts = selectedProducts.filter(function(product) {
                  return product.value !== productId;
              });

              $(this).parent('.selected-product').remove();
          });
      });

      
  </script>
  <?php
}
add_action('admin_footer', 'bundle_product_search_script');

 
 
// AJAX callback to fetch search results
function bundle_product_search_results_callback() {
  $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';
  $args = array(
      'post_type' => 'product',
      'post_status' => 'publish',
      's' => $search_query,
      'posts_per_page' => -1
  );
 
  $query = new WP_Query($args);
  $products = array();
 
  if ($query->have_posts()) {
      while ($query->have_posts()) {
          $query->the_post();
          $product_id = get_the_ID();
          $product_title = get_the_title();
          $product_image = get_the_post_thumbnail_url($product_id);
          $products[] = array(
              'label' => $product_title,
              'value' => $product_title,
              'image' => $product_image,
          );
      }
  } else {
      $products = 'No products found.';
  }
  wp_reset_postdata();
  wp_send_json($products);
  wp_die();
}
 
add_action('wp_ajax_bundle_product_search_results', 'bundle_product_search_results_callback');
add_action('wp_ajax_nopriv_bundle_product_search_results', 'bundle_product_search_results_callback');