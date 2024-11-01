<?php
/**
* Plugin Name: SW3 WC Purchase History Grid
* Plugin URI: https://www.solutionsw3.com
* Description: Custom Woocommerce plugin to show recently purchased items of the logged in user.	
* Author: Hasi Weragala
* Text Domain: 
* Domain Path: 
* Version:1.2.1
* WC tested up to: 5.6.0
*/

include_once dirname(__FILE__).'/options.php'; 

/**
 * Show current user's purchase history
 */

add_shortcode( 'my_purchased_products', 'phg_products_bought_by_curr_user' );
   
function phg_products_bought_by_curr_user() {
   
    // GET CURR USER
    $current_user = wp_get_current_user();
    if ( 0 == $current_user->ID ) return;
   
    // GET USER ORDERS (COMPLETED + PROCESSING)
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $current_user->ID,
        'post_type'   => wc_get_order_types(), //shop_order
        'post_status' => array_keys(wc_get_is_paid_statuses()), // 0 = Processing, 1 = Completed
        'orderby'     => 'date',
        'order'       => 'DESC'  
    ) );

   
    // LOOP THROUGH ORDERS AND GET PRODUCT IDS
    if ( ! $customer_orders ) return;
    
    // STORE ALL PRODUCT IDS OF THE PURCHASED ITEMS
    $product_ids = array();

    foreach ( $customer_orders as $customer_order ) {

        $order = wc_get_order( $customer_order->ID ); //WC_Order

        $items = $order->get_items(); //WC_Order_Item

        foreach ( $items as $item ) {

            $product_id = $item->get_product_id();
            $product_ids[] = $product_id;

        }

    }

    // SOME HOUSEKEEPING
    $product_ids = array_unique( $product_ids );
    $product_ids_str = implode( ",", $product_ids );

    // NO PRODUCTS TO SHOW
    if(empty($product_ids)) return 'You haven\'t bought any products yet. Why not take a plunge?';

    // GET OPTIONS
    $cols = get_option('num_of_columns',3);
    $products = get_option('num_of_products',6);
    $product_cats = get_option('phg_cats'); //yield an array of the format  array ( slug1 => on, slug2 => on)
    $cat_operator = get_option('cat_operator');
    $order_by = get_option('phg_order_by');
    $order = get_option('phg_order');
    $visibility = get_option('product_visibility');
    $product_tag = get_option('phg_tags');
    $tag_operator = get_option('tag_operator');

    // FORMATTING OPTIONS
    $product_cats = array_keys($product_cats); //yield an array of slugs
    $product_cats = implode(',', $product_cats); // slug1, slug2

    $product_tag = array_keys($product_tag); //yield an array of slugs
    $product_tag = implode(',', $product_tag); // slug1, slug2

    // CONSTRUCT THE SHORTCODE
    $sc = "[products ids='$product_ids_str' limit='".$products."' orderby='".$order_by."' order='".$order."' columns='".$cols."' category='".$product_cats."' cat_operator='".$cat_operator."' visibility='".$visibility."' tag='".$product_tag."' tag_operator='".$tag_operator."']";

    error_log(print_r($sc,true));

    // RENDER OUTPUTS
    // Ref : https://docs.woocommerce.com/document/woocommerce-shortcodes/
    
    $output = '<h2>Hello, '.$current_user->data->display_name.' You\'ve recently bought</h2>';

    $output .=  do_shortcode($sc);
   
    return $output;
   
}