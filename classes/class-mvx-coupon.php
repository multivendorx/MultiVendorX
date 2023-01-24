<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class 		MVX_Coupon
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */ 
class MVX_Coupon {
    
    public function __construct() {
        add_filter( 'woocommerce_json_search_found_products', array( &$this, 'json_filter_report_products' ) );
        /* Filter coupon list */
        add_action( 'request', array( &$this, 'filter_coupon_list' ) );
        add_filter( 'wp_count_posts', array( &$this, 'vendor_count_coupons' ), 10, 3 );

        // Validate vendor coupon in cart and checkout
        add_filter( 'woocommerce_coupon_is_valid', array(&$this, 'woocommerce_coupon_is_valid' ), 30, 2);
        add_filter( 'woocommerce_coupon_is_valid_for_product', array(&$this, 'woocommerce_coupon_is_valid_for_product' ), 30, 4);

        //add vendor tab in coupons section backend
        add_filter('woocommerce_coupon_data_tabs', array(&$this, 'add_vendor_tab_backend'));
        //show content in vendor tab
        add_action('woocommerce_coupon_data_panels', array(&$this, 'add_content_in_vendor_tab'));
        //save data in database
        add_action('woocommerce_coupon_options_save', array(&$this, 'save_data_from_vendor_tab'), 10, 2);

        // coupon delete action
        $this->mvx_delete_coupon_action();
    }

        /**
    * validate vendor coupon
    *
    * @param boolean $true
    * @return abject $coupon
    */
    public function woocommerce_coupon_is_valid_for_product( $valid, $product, $coupon, $values) {
      if ( $coupon->is_type(apply_filters('mvx_vendor_coupon_types_valid_for_product', array( 'fixed_product' ), $coupon) ) ) {
        $current_coupon = get_post( $coupon->get_id() );
        if(is_user_mvx_vendor($current_coupon->post_author)) {
          $current_product = get_post($product->get_id());
          if($current_product->post_author != $current_coupon->post_author) $valid = false;
        }
      }
      return $valid;
    }
    
    /**
    * validate vendor coupon
    *
    * @param boolean $true
    * @return abject $coupon
    */
    public function woocommerce_coupon_is_valid($true, $coupon) {
        $current_coupon = get_post( $coupon->get_id() );
        if(is_user_mvx_vendor($current_coupon->post_author)) {
            if ($coupon->is_type( apply_filters('mvx_vendor_coupon_types_valid_for_product', array( 'fixed_product' ), $coupon) ) ) {
                $is_coupon_valid = false;
                if ( ! WC()->cart->is_empty() ) {
                    foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        if(isset($cart_item['product_id'])) {
                            $vendor_product = get_mvx_product_vendors($cart_item['product_id']);
                            if($vendor_product) {
                                if( $vendor_product->id ==  $current_coupon->post_author) {
                                    $is_coupon_valid = true;
                                }
                            }
                        }
                    }
                    if(!$is_coupon_valid) $true = false;
                }
            }
        }
        return $true;
    }

    public function filter_coupon_list( $request ) {
        global $typenow;

        $current_user = get_current_vendor_id();

        if ( is_admin() && is_user_mvx_vendor($current_user) && 'shop_coupon' == $typenow ) {
                $request[ 'author' ] = $current_user;
        }

        return $request;
    }

    
  /**
   * Get vendor coupon count
   */
    public function vendor_count_coupons( $counts, $type, $perm ) {
        $current_user = get_current_vendor_id();

        if ( is_user_mvx_vendor($current_user) && 'shop_coupon' == $type ) {
                $args = array(
                        'post_type'     => $type,
                        'author'    => $current_user
                );

                /**
                 * Get a list of post statuses.
                 */
                $stati = get_post_stati();

                // Update count object
                foreach ( $stati as $status ) {
                        $args['post_status'] = $status;
                        $posts               = get_posts( $args );
                        $counts->$status     = count( $posts );
                }
        }

        return $counts;
    }
    
    
    /**
     * Filter product search with vendor specific
     *
     * @access public
     * @return void
    */	
    function json_filter_report_products($products) {
        $current_userid = get_current_vendor_id();
        
        $filtered_product = array();

        if ( is_user_mvx_vendor($current_userid) ) {
            $vendor = get_mvx_vendor($current_userid);
                $vendor_products = $vendor->get_products_ids();
                if(!empty($vendor_products)) {
                    foreach( $vendor_products as $vendor_product ) {
                        if( isset( $products[ $vendor_product->ID ] ) ){
                                $filtered_product[ $vendor_product->ID ] = $products[ $vendor_product->ID ];
                        }
                    }
                }
                $products = $filtered_product;
        }
        
        return $products;
    }
        
        /**
     * Coupon Delete actions
     *
     * @access public
     * @return void
    */	
        function mvx_delete_coupon_action(){
            $coupons_url = mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_coupons_endpoint', 'seller_dashbaord', 'coupons'));
            $delete_coupon_redirect_url = apply_filters('mvx_vendor_redirect_after_delete_coupon_action', $coupons_url);
            $wpnonce = isset( $_REQUEST['_wpnonce'] ) ? wc_clean($_REQUEST['_wpnonce']) : '';
            $coupon_id = isset( $_REQUEST['coupon_id'] ) ? absint($_REQUEST['coupon_id']) : 0;
            $coupon = get_post($coupon_id);
            $current_user_ids = apply_filters( 'mvx_coupon_current_id' , array( get_current_user_id() ) , $coupon );
            if($coupon && is_user_mvx_vendor(apply_filters( 'mvx_coupon_vendor', $coupon->post_author ))){
                if ( $wpnonce && wp_verify_nonce( $wpnonce, 'mvx_delete_coupon' ) && $coupon_id && in_array($coupon->post_author, $current_user_ids ) ) {
                    wp_delete_post( $coupon_id );
                    wc_add_notice(__('Coupon Deleted!', 'multivendorx'), 'success');
                    wp_redirect( $delete_coupon_redirect_url );
                    exit;
                }
                if($wpnonce && wp_verify_nonce($wpnonce, 'mvx_untrash_coupon') && $coupon_id && in_array($coupon->post_author, $current_user_ids )){
                    wp_untrash_post($coupon_id);
                    wc_add_notice(__('Coupon restored from the Trash', 'multivendorx'), 'success');
                    wp_redirect($delete_coupon_redirect_url);
                    exit;
                }
                if($wpnonce && wp_verify_nonce($wpnonce, 'mvx_trash_coupon') && $coupon_id && in_array($coupon->post_author, $current_user_ids )){
                    wp_trash_post($coupon_id);
                    wc_add_notice(__('Coupon moved to the Trash', 'multivendorx'), 'success');
                    wp_redirect($delete_coupon_redirect_url);
                    exit;
                }
            }
        }

    function add_vendor_tab_backend( $coupon_data_tabs ){
        $coupon_data_tabs['vendor'] = array(
            'label'  => __( 'Vendor', 'multivendorx' ),
            'target' => 'vendor_coupon_data',
            'class'  => 'vendor_coupon_data',
        );	
        return $coupon_data_tabs; 
    }

    function add_content_in_vendor_tab(){
        $html = '';
        $option = '<option></option>';
        $vendors = get_mvx_vendors();
        if ( !empty($vendors) ) {
            foreach($vendors as $vendor) {
                $option .= '<option value="' . esc_attr($vendor->id). '"' . selected(esc_attr( $vendor->id )) . '>' . esc_html( $vendor->page_title ) . '</option>';
            }
        }
        $html .= '<div class="options_group"> <table class="form-field form-table">';
        $html .= '<tbody>';
        $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" style="margin:0px;">' . __("Vendor", 'multivendorx') . '</label></td><td>';
        $html .= '<select name="' . esc_attr('select_vendor') . '" data-placeholder="'. esc_attr("Choose vendor", "multivendorx").'" style="width:300px;">' . $option . '</select>';
        $html .= '</td></tr>';
        $html = apply_filters('mvx_additional_fields_coupon_vendor_tab', $html);
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        echo '<div id="vendor_coupon_data" class="panel woocommerce_options_panel">';
        echo $html;
        echo '</div>';
    }

    function save_data_from_vendor_tab( $post_id, $coupon ){
        $select_vendor = isset( $_POST['select_vendor'] ) ? wc_clean( wp_unslash($_POST['select_vendor'])) : '';
        if ( !empty($select_vendor) ) {
            wp_update_post(array('ID' => $post_id, 'post_author' => $select_vendor));
        }
    }
}
?>
