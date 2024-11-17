<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */




/**
 * @snippet       Hide Price & Add to Cart for Logged Out Users
 * @author        Rodolfo Melogli, BusinessBloomer.com
 * @testedwith    WooCommerce 7
 */
add_filter( 'woocommerce_get_price_html', 'bbloomer_hide_price_addcart_not_logged_in', 9999, 2 );
 
function bbloomer_hide_price_addcart_not_logged_in( $price, $product ) {
   if ( ! is_user_logged_in() ) { 
      $price = '<div><a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">' . __( 'Logga in för att se priser', 'bbloomer' ) . '</a></div>';
      remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
      remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
      add_filter( 'woocommerce_is_purchasable', '__return_false' );
   }
   return $price;
}


/***** -------- Edit Footer text ------- *******/
if ( ! function_exists( 'storefront_credit' ) ) {
    /**
    * Display the theme credit
    *
    * @since 1.0.0
    * @return void
    */
    function storefront_credit() {?>
    
      
      <?php
      }
}

/***** -------- Remove sidebar on single product ------- *******/
function remove_storefront_sidebar() {
    if ( is_product() ) {
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    }
}
add_action( 'get_header', 'remove_storefront_sidebar' );
    


/**
* @snippet       Sort Products Alphabetically @ WooCommerce Cart
* @author        Rodolfo Melogli
* @testedwith    Woo 3.7
*/
add_action( 'woocommerce_cart_loaded_from_session', 'bbloomer_sort_cart_items_alphabetically' );
 
function bbloomer_sort_cart_items_alphabetically() {
    // READ CART ITEMS
    $products_in_cart = array();
    foreach ( WC()->cart->get_cart_contents() as $key => $item ) {
        $products_in_cart[ $key ] = $item['data']->get_title();
    }
    
    // SORT CART ITEMS
    natsort( $products_in_cart );
    
    // ASSIGN SORTED ITEMS TO CART
    $cart_contents = array();
    foreach ( $products_in_cart as $cart_key => $product_title ) {
        $cart_contents[ $cart_key ] = WC()->cart->cart_contents[ $cart_key ];
    }
    WC()->cart->cart_contents = $cart_contents;
}



 /**
 * Disable messages about the mobile apps in WooCommerce emails.
 * https://wordpress.org/support/topic/remove-process-your-orders-on-the-go-get-the-app/
 */
function mtp_disable_mobile_messaging( $mailer ) {
    remove_action( 'woocommerce_email_footer', array( $mailer->emails['WC_Email_New_Order'], 'mobile_messaging' ), 9 );
}
add_action( 'woocommerce_email', 'mtp_disable_mobile_messaging' );


// Empty the additional content of the order emails
add_filter( 'woocommerce_email_additional_content_customer_processing_order', 'custom_additional_content_customer_processing_order', 99, 3 );
function custom_additional_content_customer_processing_order( $content, $object, $email ) {
    $content = '';
    return $content;
}
add_filter( 'woocommerce_email_additional_content_customer_completed_order', 'custom_additional_content_customer_completed_order', 99, 3 );
function custom_additional_content_customer_completed_order( $content, $object, $email ) {
    $content = '';
    return $content;
}
add_filter( 'woocommerce_email_additional_content_new_order', 'custom_additional_content_new_order', 99, 3 );
function custom_additional_content_new_order( $content, $object, $email ) {
    $content = '';
    return $content;
}

/**
 * @snippet       Also Search by SKU @ Shop
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 7
 */
 
 add_filter( 'posts_search', 'bbloomer_product_search_by_sku', 9999, 2 );
  
 function bbloomer_product_search_by_sku( $search, $wp_query ) {
    global $wpdb;
    if ( is_admin() || ! is_search() || ! isset( $wp_query->query_vars['s'] ) || ( ! is_array( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] !== "product" ) || ( is_array( $wp_query->query_vars['post_type'] ) && ! in_array( "product", $wp_query->query_vars['post_type'] ) ) ) return $search;   
    $product_id = wc_get_product_id_by_sku( $wp_query->query_vars['s'] );
    if ( ! $product_id ) return $search;
    $product = wc_get_product( $product_id );
    if ( $product->is_type( 'variation' ) ) {
       $product_id = $product->get_parent_id();
    }
    $search = str_replace( 'AND (((', "AND (({$wpdb->posts}.ID IN (" . $product_id . ")) OR ((", $search );   
    return $search;   
 }

 /**
 * @snippet       Disable Single Search Result Redirect | WooCommerce
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 6
 */
 
add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

/**
 * @snippet       Add Inline Field Error Notifications @ WooCommerce Checkout
 * @sourcecode    https://businessbloomer.com/?p=86570
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.5.4
 */
 
 add_action( 'woocommerce_shop_loop_item_title', 'bbloomer_new_badge_shop_page', 3 );
          
 function bbloomer_new_badge_shop_page() {
    global $product;
    $newness_days = 30;
    $created = strtotime( $product->get_date_created() );
    if ( ( time() - ( 60 * 60 * 24 * $newness_days ) ) < $created ) {
       echo '<span class="itsnew onsale">' . esc_html__( 'NYHET!', 'woocommerce' ) . '</span>';
    }
 }

/* Adds code to header to preload fonts */
add_action('wp_head', 'your_function_name');
function your_function_name(){
?>
<link rel="preload" href="/wp-content/themes/videtur-storefront-child-theme/assets/fonts/dm-sans/DMSans-Regular.woff2" as="font" type="font/woff2" crossorigin="anonymous"> 
<link rel="preload" href="/wp-content/themes/videtur-storefront-child-theme/assets/fonts/dm-sans/DMSans-Regular-Italic.woff2" as="font" type="font/woff2" crossorigin="anonymous"> 
<link rel="preload" href="/wp-content/themes/videtur-storefront-child-theme/assets/fonts/dm-sans/DMSans-Bold.woff2" as="font" type="font/woff2" crossorigin="anonymous"> 
<link rel="preload" href="/wp-content/themes/videtur-storefront-child-theme/assets/fonts/dm-sans/DMSans-Bold-Italic.woff2" as="font" type="font/woff2" crossorigin="anonymous"> 

<?php
};

/*-------------------------- Increase Woocommerce Variation Threshold -----------------------------------*/
function wc_ajax_variation_threshold_modify( $threshold, $product ){
	$threshold = '70'; //default is 30
	return  $threshold;
  }
  add_filter( 'woocommerce_ajax_variation_threshold', 'wc_ajax_variation_threshold_modify', 10, 2 );


/*-------------------------- Add ACF field "Lagerplats" to New order email -----------------------------------*/
/****************************** BEHÖVER FIXAS - ORSAKAR KRASCH ********************************************* */

/*   // Setting global variable
function action_woocommerce_email_before_order_table( $order, $sent_to_admin, $plain_text, $email ) {
    $GLOBALS['email_data'] = array(
        'email_id'  => $email->id, // The email ID (to target specific email notification)
        'is_email'  => true // When it concerns a WooCommerce email notification
    );
}
add_action( 'woocommerce_email_before_order_table', 'action_woocommerce_email_before_order_table', 1, 4 );
 
function action_woocommerce_order_item_meta_start( $item_id, $item, $order, $plain_text ) {
    // Getting the custom 'email_data' global variable
    $ref_name_globals_var = $GLOBALS;
    
    // Isset & NOT empty
    if ( isset ( $ref_name_globals_var ) && ! empty( $ref_name_globals_var ) ) {
        // Isset
        $email_data = isset( $ref_name_globals_var['email_data'] ) ? $ref_name_globals_var['email_data'] : '';
        
        // NOT empty
        if ( ! empty( $email_data ) ) {
            // Target specific emails, several can be added in the array, separated by a comma
            $target_emails = array( 'new_order' );
            
            // Target specific WooCommerce email notifications
            if ( in_array( $email_data['email_id'], $target_emails ) ) {
                // Get product ID
                $product_id = $item->get_product_id();
                
                // Get field
                $lagerplats = get_field( 'lagerplats', $product_id );
                
                // Has some value
                if ( $lagerplats ) {
                    echo ' <br><span class="email_lagerplats">Lagerplats: ' . $lagerplats . '</span>';
                }
            }           
        }
    }
}
add_action( 'woocommerce_order_item_meta_start', 'action_woocommerce_order_item_meta_start', 10, 4 ); */