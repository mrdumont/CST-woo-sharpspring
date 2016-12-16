<?php
/*
  Plugin Name: CST - SharpSpring/WooCommerce cart integration
  Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
  Description: Orders and details are sent to SharpSpring on successful payment
  Version:     1.0
  Author:      Clear Sky Technologies
  Author URI:  https://clearsky-tech.com/
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: CST
  Domain Path: /languages
 */

defined('ABSPATH') or die('No access.');

add_action('woocommerce_thankyou', 'CST_SharpSpring_integration');

function CST_SharpSpring_integration($order_id) {
  $order = wc_get_order($order_id);
  $billing = $order->get_address('billing');
  ?>
  <!-- Assumes the SharpSpring TrackingCode already injected into header  -->
  <!-- SharpSpring ShoppingCart-->
  <script type='text/javascript'>
    _ss.push(['_setTransaction', {
        'transactionID': '<?php echo $order->get_order_number(); ?>',
        'storeName': 'ICEHOLE - icehole.com shopping cart',
        'total': '<?php echo $order->get_total(); ?>',
        'tax': '<?php echo $order->get_total_tax(); ?>',
        'shipping': '<?php echo $order->get_total_shipping(); ?>',
        'city': '<?php echo $billing['city']; ?>',
        'state': '<?php echo $billing['state']; ?>',
        'zipcode': '<?php echo $billing['postcode']; ?>',
        'country': '<?php echo $billing['country']; ?>',
        'firstName': '<?php echo $billing['first_name']; ?>', // optional parameter
        'lastName': '<?php echo $billing['last_name']; ?>', // optional parameter
        'emailAddress': '<?php echo $billing['email']; ?>' // optional parameter
      }]);
  <?php
  $order_item = $order->get_items();
  foreach ($order_item as $product) {
    $product_cats = wp_get_post_terms($product['item_meta']['_product_id'][0], 'product_cat', array('fields'=>'names'));
    $category_list = implode(', ',$product_cats);
    $categroy_list = ($category_list === '') ? 'none':$category_list;
    $product_variation_id = $product['variation_id'];
    // Check if product has variation.
    if ($product_variation_id) {
      $product_variation = new WC_Product($product['variation_id']);
      $product_sku = $product_variation->get_sku();
    } else {
      $product_variation = new WC_Product($product['item_meta']['_product_id'][0]);
      $product_sku = $product_variation->get_sku();
    }
    $billing = $order->get_address('billing');
    ?>
      _ss.push(['_addTransactionItem', {
          'transactionID': '<?php echo $order->get_order_number(); ?>',
          'itemCode': '<?php echo $product_sku; ?>',
          'productName': '<?php echo $product['name']; ?>',
          'category': '<?php echo $category_list; ?>',
          'price': '<?php echo $product['line_total']; ?>',
          'quantity': '<?php echo $product['qty']; ?>'
        }]);

    <?php
  }
  ?>
  <!-- Start THE MOST IMPORTANT CODE OF THIS STEP -->
    _ss.push(['_completeTransaction', {
        'transactionID': '<?php echo $order->get_order_number(); ?>'
      }]);
  <!-- End THE MOST IMPORTANT CODE OF THIS STEP -->
  </script>
  <?php
}
