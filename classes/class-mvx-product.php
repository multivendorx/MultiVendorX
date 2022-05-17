<?php
if (!defined('ABSPATH'))
    exit;

/**
 * @class       MVX Product Class
 *
 * @version     2.2.0
 * @package     MVX
 * @author      Multivendor X
 */
class MVX_Product {

    public $loop;
    public $variation_data = array();
    public $variation;
    public $more_product_array;

    public function __construct() {
        global $MVX;
        if (!is_user_mvx_vendor(get_current_user_id())) {
            add_action('woocommerce_product_write_panel_tabs', array(&$this, 'add_vendor_tab'), 30);
            add_action('woocommerce_product_data_panels', array(&$this, 'output_vendor_tab'), 30);
            add_action( 'add_meta_boxes', array( $this, 'product_comment_note_metabox' ) );
        }
        add_action('save_post', array(&$this, 'process_vendor_data'));
        if (mvx_is_module_active('store-policy')) {
            if (current_user_can('manage_woocommerce') || (is_user_mvx_vendor(get_current_user_id()) && apply_filters('mvx_vendor_can_overwrite_policies', true))) {
                add_action('woocommerce_product_write_panel_tabs', array(&$this, 'add_policies_tab'), 30);
                add_action('woocommerce_product_data_panels', array(&$this, 'output_policies_tab'), 30);
                add_action('save_post', array(&$this, 'process_policies_data'));
            }
            add_filter('woocommerce_product_tabs', array(&$this, 'product_policy_tab'));
        }
        add_action('woocommerce_ajax_save_product_variations', array($this, 'save_variation_commission'));
        add_action('woocommerce_product_after_variable_attributes', array(&$this, 'add_variation_settings'), 10, 3);
        add_filter('pre_get_posts', array(&$this, 'convert_business_id_to_taxonomy_term_in_query'));

        add_action('transition_post_status', array(&$this, 'on_all_status_transitions'), 10, 3);

        add_action('woocommerce_product_meta_start', array(&$this, 'add_report_abuse_link'), 30);
        //if ($MVX->vendor_caps->vendor_frontend_settings('enable_vendor_tab')) {
        add_filter('woocommerce_product_tabs', array(&$this, 'product_vendor_tab'));
        //}
        add_filter('wp_count_posts', array(&$this, 'vendor_count_products'), 10, 3);
        /* Related Products */
        add_filter('woocommerce_product_related_posts_query', array($this, 'show_related_products'), 99, 2);
        // bulk edit vendor set
        add_action('woocommerce_product_bulk_edit_end', array($this, 'add_product_vendor'));
        add_action('woocommerce_product_bulk_edit_save', array($this, 'save_vendor_bulk_edit'));
        /* Frontend Products Edit option */
        add_action('woocommerce_before_shop_loop_item', array(&$this, 'frontend_product_edit'), 5);
        add_action('woocommerce_before_single_product_summary', array(&$this, 'frontend_product_edit'), 5);

        // Filters
        add_action('restrict_manage_posts', array($this, 'restrict_manage_posts'));
        add_filter('parse_query', array($this, 'product_vendor_filters_query'));
        add_action('save_post', array(&$this, 'check_sku_is_unique'));
        add_action("save_post_product", array($this, 'set_vendor_added_product_flag'), 10, 3);

        add_action('woocommerce_variation_options_dimensions', array($this, 'add_filter_for_shipping_class'), 10, 3);
        add_action('woocommerce_variation_options_tax', array($this, 'remove_filter_for_shipping_class'), 10, 3);
        add_action('admin_footer', array($this, 'mvx_edit_product_footer'));

        add_action('pre_get_comments', array($this, 'review_lists'));
        add_filter('woocommerce_reviews_title',  array($this, 'review_title'), 10, 3);
        add_filter('woocommerce_product_tabs',  array($this, 'review_tab'));

        if (mvx_is_module_active('spmv') && get_mvx_vendor_settings('is_singleproductmultiseller', 'spmv_pages')) {
            add_filter('woocommerce_duplicate_product_exclude_taxonomies', array($this, 'exclude_taxonomies_copy_to_draft'), 10, 1);
            add_filter('woocommerce_duplicate_product_exclude_meta', array($this, 'exclude_postmeta_copy_to_draft'), 10, 1);
            add_action('woocommerce_product_duplicate', array($this, 'mvx_product_duplicate_update_meta'), 10, 2);
            add_action('save_post_product', array($this, 'update_duplicate_product_title'), 10, 3);
            add_filter('woocommerce_product_tabs', array(&$this, 'product_single_product_multivendor_tab'));
            add_action('woocommerce_single_product_summary', array($this, 'product_single_product_multivendor_tab_link'), 60);
            add_filter( 'wp_insert_post_data', array( $this, 'override_wc_product_post_parent' ), 99, 2 );

            if (!defined('MVX_HIDE_MULTIPLE_PRODUCT')) {
                add_action('woocommerce_shop_loop', array(&$this, 'woocommerce_shop_loop_callback'), 5);
                add_action('woocommerce_product_query', array(&$this, 'woocommerce_product_query'), 10);
            }
            // SPMV terms updates
            add_action( 'woocommerce_product_quick_edit_save', array( $this, 'mvx_spmv_bulk_quick_edit_save_post' ), 99 );
            add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'mvx_spmv_bulk_quick_edit_save_post' ), 99 );
            add_action( 'save_post_product', array( $this, 'mvx_spmv_bulk_quick_edit_save_post' ), 99 );
            add_action( 'mvx_create_duplicate_product', array( $this, 'mvx_spmv_bulk_quick_edit_save_post' ), 99 );
            add_action( 'woocommerce_update_product', array( $this, 'mvx_spmv_bulk_quick_edit_save_post' ), 99 );
            add_action( 'woocommerce_product_duplicate_before_save', array( $this, 'mvx_product_duplicate_before_save' ), 99, 2 );
        }
        add_action('woocommerce_product_query_tax_query', array(&$this, 'mvx_filter_product_category'), 10);
        $this->vendor_product_restriction();
        $this->mvx_delete_product_action();
        // vendor Q & A tab
        add_filter('woocommerce_product_tabs', array(&$this, 'mvx_customer_questions_and_answers_tab'));

        add_action('product_cat_add_form_fields', array($this, 'add_product_cat_commission_fields'));
        add_action('product_cat_edit_form_fields', array($this, 'edit_product_cat_commission_fields'), 10);
        add_action('created_term', array($this, 'save_product_cat_commission_fields'), 10, 3);
        add_action('edit_term', array($this, 'save_product_cat_commission_fields'), 10, 3);
        // GTIN
        if (get_mvx_vendor_settings('is_gtin_enable', 'general') == 'Enable') {
            add_action( 'woocommerce_product_options_sku', array( $this, 'mvx_gtin_product_option') );
            add_action( 'save_post_product', array( $this, 'mvx_save_gtin_product_option'), 99 );
            if(apply_filters( 'mvx_enable_product_search_by_gtin_code', true) ){
                add_action( 'pre_get_posts', array( $this, 'mvx_gtin_product_search'), 99 );
                add_filter( 'get_search_query', array($this, 'mvx_gtin_get_search_query_vars'));
            }
            //add the column GTIN on product list
            add_filter( 'manage_product_posts_columns', array( $this, 'manage_product_columns' ), 99 );
            add_action( 'manage_product_posts_custom_column', array( $this, 'show_gtin_code' ) );
        }
        // product classify
        add_filter( 'mvx_get_product_terms_html_selected_terms', array($this, 'mvx_get_product_terms_html_selected_terms'), 99, 3);
        add_action( 'mvx_process_product_object', array($this, 'reset_vendor_classified_product_terms'), 99 );
        add_action( 'before_mvx_vendor_dashboard_content', array($this, 'reset_vendor_classified_product_terms'), 99  );
        // Hide products backend fields as per new product modifications
        add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 99 );
        // show default product categories
        if( apply_filters( 'mvx_disable_product_default_categories_hierarchy', get_mvx_vendor_settings('category_pyramid_guide', 'settings_general') ) ) {
            add_filter( 'mvx_vendor_product_list_row_product_categories', array($this, 'show_default_product_cats_in_vendor_list'), 10, 2);
            add_filter( 'woocommerce_admin_product_term_list', array($this, 'show_default_product_cats_in_wp_backend'), 99, 5);
            add_filter( 'term_links-product_cat', array($this, 'show_default_product_cats_product_single'), 99);
        }
        // Woocommerce Block 
        add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'woocommerce_blocks_product_grid_item_html' ), 99, 3 );
    }
    
    public function override_wc_product_post_parent( $data, $postarr ){
        if ( 'product' === $data['post_type'] && isset( $_POST['product-type'] ) ) { 
            $product_type = wc_clean( wp_unslash( $_POST['product-type'] ) ); 
            switch ( $product_type ) {
                case 'variable':
                    $data['post_parent'] = $postarr['post_parent'];
                    break;
            }
        }
        return $data;
    }
    
    public function mvx_spmv_bulk_quick_edit_save_post( $product ){
        global $MVX, $wpdb;
        if (!is_object($product)) {
            $product = wc_get_product(absint($product));
        }
        $is_mvx_spmv_product = get_post_meta($product->get_id(), '_mvx_spmv_product', true);
        $has_mvx_spmv_map_id = get_post_meta($product->get_id(), '_mvx_spmv_map_id', true);
        if($is_mvx_spmv_product){
            wp_schedule_single_event( time(), 'mvx_reset_product_mapping_data', array( $has_mvx_spmv_map_id ) );
        }else{
            $data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}mvx_products_map WHERE product_id=%d", $product->get_id()) );
            if($data && $data->product_map_id){
                update_post_meta($product->get_id(), '_mvx_spmv_product', true);
                update_post_meta($product->get_id(), '_mvx_spmv_map_id', $data->product_map_id);
                wp_schedule_single_event( time(), 'mvx_reset_product_mapping_data', array( $data->product_map_id ) );
            }
        }
    }

    function product_single_product_multivendor_tab_link() {
        global $MVX;
        if (is_product()) {
            $MVX->template->get_template('single-product/multiple_vendors_products_link.php');
        }
    }

    /**
     * Add vendor tab on single product page
     *
     * @return void
     */
    function product_single_product_multivendor_tab($tabs) {
        global $product, $MVX;
        $title = apply_filters('mvx_more_vendors_tab', __('More Offers', 'dc-woocommerce-multi-vendor'));
        $tabs['singleproductmultivendor'] = array(
            'title' => $title,
            'priority' => 80,
            'callback' => array($this, 'product_single_product_multivendor_tab_template')
        );

        return $tabs;
    }

    /**
     * Add vendor tab html
     *
     * @return void
     */
    function product_single_product_multivendor_tab_template() {
        global $woocommerce, $MVX, $post, $wpdb;
        $more_product_array = array();
        $results = array();
        $more_products = apply_filters('mvx_single_product_multiple_vendor_products_array', $this->get_multiple_vendors_array_for_single_product($post->ID), $post->ID);
        $more_product_array = $more_products['more_product_array'];
        $results = $more_products['results'];
        $MVX->template->get_template('single-product/multiple_vendors_products.php', array('results' => $results, 'more_product_array' => $more_product_array));
    }

    function get_multiple_vendors_array_for_single_product($post_id) {
        global $woocommerce, $MVX, $wpdb;
        $product = wc_get_product( $post_id );
        $more_product_array = $mapped_products = array();
        $has_product_map_id = get_post_meta( $product->get_id(), '_mvx_spmv_map_id', true );
        if( $has_product_map_id ){
            $products_map_data_ids = get_mvx_spmv_products_map_data( $has_product_map_id );
            $mapped_products = array_diff( $products_map_data_ids, array( $product->get_id() ) );
            $more_product_array = get_mvx_more_spmv_products( $product->get_id() );
        }
        return array('results' => $mapped_products, 'more_product_array' => $more_product_array);
    }

    function update_duplicate_product_title($post_ID, $post, $update) {
        global $wpdb;
        $is_spmv_pro = get_post_meta($post_ID, '_mvx_spmv_product', true);
        if ($is_spmv_pro && apply_filters('mvx_singleproductmultiseller_edit_product_title_disabled', true)) {
            $post = get_post(absint($post_ID));
            if($post){
                $title = str_replace(" (Copy)","",$post->post_title);
                $wpdb->update($wpdb->posts, array('post_title' => $title), array('ID' => $post_ID));
            }
        }
    }

    function exclude_postmeta_copy_to_draft($arr = array()) {
        $exclude_arr = array('_sku', '_sale_price', '_sale_price_dates_from', '_sale_price_dates_to', '_sold_individually', '_backorders', '_upsell_ids', '_crosssell_ids', '_commission_per_product');
        $final_arr = array_merge($arr, $exclude_arr);
        return $final_arr;
    }

    function exclude_taxonomies_copy_to_draft($arr = array()) {
        global $MVX;
        $exclude_arr = array('product_shipping_class', $MVX->taxonomy->taxonomy_name);
        $final_arr = array_merge($arr, $exclude_arr);
        return $final_arr;
    }

    function mvx_product_duplicate_update_meta($duplicate, $product) {
        global $MVX;
        $singleproductmultiseller = isset($_REQUEST['singleproductmultiseller']) ? absint($_REQUEST['singleproductmultiseller']) : '';
        if ($singleproductmultiseller == 1) {
            $has_mvx_spmv_map_id = get_post_meta($product->get_id(), '_mvx_spmv_map_id', true);
            if($has_mvx_spmv_map_id){
                $data = array('product_id' => $duplicate->get_id(), 'product_map_id' => $has_mvx_spmv_map_id);
                update_post_meta($duplicate->get_id(), '_mvx_spmv_map_id', $has_mvx_spmv_map_id);
                mvx_spmv_products_map($data, 'insert');
            }else{
                $data = array('product_id' => $duplicate->get_id());
                $map_id = mvx_spmv_products_map($data, 'insert');
                if($map_id){
                    update_post_meta($duplicate->get_id(), '_mvx_spmv_map_id', $map_id);
                    // Enroll in SPMV parent product too 
                    $data = array('product_id' => $product->get_id(), 'product_map_id' => $map_id);
                    mvx_spmv_products_map($data, 'insert');
                    update_post_meta($product->get_id(), '_mvx_spmv_map_id', $map_id);
                    update_post_meta($product->get_id(), '_mvx_spmv_product', true);
                }
            }
            update_post_meta($duplicate->get_id(), '_mvx_spmv_product', true);

            $duplicate->save();
        }
        // Update GTIN if available
        $gtin_data = wp_get_post_terms($product->get_id(), $MVX->taxonomy->mvx_gtin_taxonomy);
        if ($gtin_data) {
            $gtin_type = isset($gtin_data[0]->term_id) ? $gtin_data[0]->term_id : '';
            wp_set_object_terms($duplicate->get_id(), $gtin_type, $MVX->taxonomy->mvx_gtin_taxonomy, true);
        }
        $gtin_code = get_post_meta($product->get_id(), '_mvx_gtin_code', true);
        if ($gtin_code)
            update_post_meta($duplicate->get_id(), '_mvx_gtin_code', $gtin_code);
    }

    public function add_filter_for_shipping_class($loop, $variation_data, $variation) {
        $this->loop = $loop;
        $this->variation_data = $variation_data;
        $this->variation = $variation;
        add_filter('wp_dropdown_cats', array($this, 'filter_shipping_class_for_variation'), 10, 2);
    }

    public function remove_filter_for_shipping_class($loop, $variation_data, $variation) {
        remove_filter('wp_dropdown_cats', array($this, 'filter_shipping_class_for_variation'), 10, 2);
    }

    function mvx_edit_product_footer() {
        $screen = get_current_screen();
        // disable product title from being edit
        if (isset($_GET['post'])) {
            $current_post_id = intval( $_GET['post'] );
            if (get_post_type($current_post_id) == 'product') {
                $product = wc_get_product($current_post_id);
                $is_spmv_pro = get_post_meta($current_post_id, '_mvx_spmv_product', true);
                if (in_array($screen->id, array('product', 'edit-product')) && $is_spmv_pro && apply_filters('mvx_singleproductmultiseller_edit_product_title_disabled', true)) {
                    wp_add_inline_script('mvx-admin-product-js', '(function ($) { 
                      $("#titlewrap #title").prop("disabled", true);
                  })(jQuery)');
                }
            }
        }
    }

    public function review_lists(\WP_Comment_Query $query) {
        if ( $query->query_vars['type'] !== 'product_note' ) {
            $query->query_vars['type__not_in'] = array_merge( (array) $query->query_vars['type__not_in'], array('product_note') );
        }
    }

    public function review_title($reviews_title, $count, $product) {
        $count = get_comments(array('post_id' => $product->get_id(), 'type__not_in' => array('product_note', 'comment'), 'count' => true));
        $reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'dc-woocommerce-multi-vendor' ) ), esc_html( $count ), '<span>' . $product->get_title() . '</span>' );
        return $reviews_title;
    }

    public function review_tab($tabs) {
        global $product;
        if(isset($tabs['reviews'])) {
            $count = get_comments(array('post_id' => $product->get_id(), 'type__not_in' => array('product_note', 'comment'), 'count' => true));

            $tabs['reviews'] = array(
                    'title'    => sprintf( __( 'Reviews (%d)', 'dc-woocommerce-multi-vendor' ), $count),
                    'priority' => 30,
                    'callback' => 'comments_template',
                );
        }
        return $tabs;
    }

    public function filter_shipping_class_for_variation($output, $arg) {
        global $MVX;
        $loop = $this->loop;
        $variation_data = $this->variation_data;
        $variation = $this->variation;
        if (is_array($arg) && !empty($arg) && isset($arg['taxonomy']) && ($arg['taxonomy'] == 'product_shipping_class')) {
            $html = '';
            $classes = get_the_terms($variation->ID, 'product_shipping_class');
            if ($classes && !is_wp_error($classes)) {
                $current_shipping_class = current($classes)->term_id;
            } else {
                $current_shipping_class = false;
            }
            $product_shipping_class = get_terms('product_shipping_class', array('hide_empty' => 0));
            $current_user_id = get_current_vendor_id();
            $option = '<option value="-1">Same as parent</option>';

            if (!empty($product_shipping_class)) {
                $shipping_option_array = array();
                $vednor_shipping_option_array = array();
                if (is_user_mvx_vendor($current_user_id)) {
                    $shipping_class_id = get_user_meta($current_user_id, 'shipping_class_id', true);
                    if (!empty($shipping_class_id)) {
                        $term_shipping_obj = get_term_by('id', $shipping_class_id, 'product_shipping_class');
                        $shipping_option_array[$term_shipping_obj->term_id] = $term_shipping_obj->name;
                    }
                } else {
                    foreach ($product_shipping_class as $product_shipping) {
                        $shipping_option_array[$product_shipping->term_id] = $product_shipping->name;
                    }
                }
                if (!empty($vednor_shipping_option_array)) {
                    $shipping_option_array = array();
                    $shipping_option_array = $vednor_shipping_option_array;
                }
                if (!empty($shipping_option_array)) {
                    foreach ($shipping_option_array as $shipping_option_array_key => $shipping_option_array_val) {
                        if ($current_shipping_class && $shipping_option_array_key == $current_shipping_class) {
                            $option .= '<option selected value="' . $shipping_option_array_key . '">' . $shipping_option_array_val . '</option>';
                        } else {
                            $option .= '<option value="' . $shipping_option_array_key . '">' . $shipping_option_array_val . '</option>';
                        }
                    }
                }
            }
            $html .= '<select name="dc_variable_shipping_class[' . $loop . ']" id="dc_variable_shipping_class[' . $loop . ']" class="postform">';
            $html .= $option;
            $html .= '</select>';
            return $html;
        } else {
            return $output;
        }
    }

    function check_sku_is_unique($post_id) {
        global $MVX;
        if (isset($_POST) && !empty($_POST)) {
            $sku = isset( $_POST['_sku'] ) ? wc_clean( wp_unslash( $_POST['_sku'] ) ) : null;
            $post = get_post($post_id);
            if ($post->post_type == 'product' && !empty($sku)) {
                $args = array(
                    'posts_per_page' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_key' => '_sku',
                    'meta_value' => $sku,
                    'post_type' => 'product',
                    'post__not_in' => array($post_id),
                    'post_status' => 'any',
                    'suppress_filters' => true
                );
                $posts_array = get_posts($args);
                $count_find = count($posts_array);
                if ($posts_array > 0) {
                    add_action('admin_notices', array($this, 'error_notice_for_sku_not_available'));
                }
            }
        }
    }

    function error_notice_for_sku_not_available() {
        global $MVX;
        $class = "error";
        $message = __("SKU must be unique", 'dc-woocommerce-multi-vendor');
        echo"<div class=\"$class\"> <p>$message</p></div>";
    }

    function vendor_product_restriction() {
        global $MVX;
        if (wp_doing_ajax())
            return;
        $current_user_id = get_current_vendor_id();
        if (is_user_mvx_vendor($current_user_id)) {
            add_filter('manage_product_posts_columns', array($this, 'remove_featured_star'), 15);
            if (isset($_GET['post'])) {
                $current_post_id = intval( $_GET['post'] );
                if (get_post_type($current_post_id) == 'product') {

                    if (in_array(get_post_status($current_post_id), array('draft', 'publish', 'pending'))) {
                        $product_vendor_obj = get_mvx_product_vendors($current_post_id);
                        if ($product_vendor_obj && $product_vendor_obj->id != $current_user_id) {
                            if (isset($_GET['action']) && $_GET['action'] == 'duplicate_product') {
                                
                            } else {
                                wp_redirect(admin_url() . 'edit.php?post_type=product');
                                exit;
                            }
                        }
                    }
                } else if (get_post_type($current_post_id) == 'shop_coupon') {
                    $coupon_obj = get_post($current_post_id);
                    if ($coupon_obj->post_author != $current_user_id) {
                        wp_redirect(admin_url() . 'edit.php?post_type=shop_coupon');
                        exit;
                    }
                }
            }
        }
    }

    public function remove_featured_star($existing_columns) {
        if (empty($existing_columns) && !is_array($existing_columns)) {
            $existing_columns = array();
        }
        unset($existing_columns['featured']);
        return $existing_columns;
    }

    function product_vendor_filters_query($query) {
        global $typenow, $MVX;

        $taxonomy = $MVX->taxonomy->taxonomy_name;
        $q_vars = &$query->query_vars;
        if ('product' == $typenow) {
            if (isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
                if (isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                    $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                    $q_vars[$taxonomy] = $term->slug;
                }
            }
        }
    }

    function restrict_manage_posts() {
        global $typenow, $MVX;

        $post_type = 'product';
        $taxonomy = $MVX->taxonomy->taxonomy_name;

        if (!is_user_mvx_vendor(get_current_vendor_id())) {
            if ('product' == $typenow) {
                if ($typenow == $post_type) {
                    $selected = isset($_GET[$taxonomy]) ? wc_clean( wp_unslash( $_GET[$taxonomy] ) ) : '';
                    $info_taxonomy = get_taxonomy($taxonomy);
                    wp_dropdown_categories(array(
                        'show_option_all' => __("Show All {$info_taxonomy->label}", 'dc-woocommerce-multi-vendor'),
                        'taxonomy' => $taxonomy,
                        'name' => $taxonomy,
                        'orderby' => 'name',
                        'selected' => $selected,
                        'show_count' => true,
                        'hide_empty' => true,
                    ));
                };
            }
        }
    }

    /**
     * Save product vendor by bluk edit
     *
     * @param object $product
     */
    function save_vendor_bulk_edit($product) {
        global $MVX;

        $product_id = $product->get_id();

        $current_user_id = get_current_vendor_id();
        if (!is_user_mvx_vendor($current_user_id)) {

            if (isset($_REQUEST['choose_vendor_bulk']) && !empty($_REQUEST['choose_vendor_bulk'])) {
                if (is_numeric($_REQUEST['choose_vendor_bulk'])) {
                    $vendor_term = wc_clean($_REQUEST['choose_vendor_bulk']);

                    $term = get_term($vendor_term, $MVX->taxonomy->taxonomy_name);
                    wp_delete_object_term_relationships($product_id, $MVX->taxonomy->taxonomy_name);
                    wp_set_object_terms($product_id, (int) $term->term_id, $MVX->taxonomy->taxonomy_name, true);

                    $vendor = get_mvx_vendor_by_term($vendor_term);
                    if (!wp_is_post_revision($product_id)) {
                        // unhook this function so it doesn't loop infinitely
                        remove_action('save_post', array($this, 'process_vendor_data'));
                        // update the post, which calls save_post again
                        wp_update_post(array('ID' => $product_id, 'post_author' => $vendor->id));
                        // re-hook this function
                        add_action('save_post', array($this, 'process_vendor_data'));
                    }
                }
            }
        }
    }

    /**
     * Add product vendor
     */
    function add_product_vendor() {
        global $MVX;

        $current_user_id = get_current_vendor_id();
        if (!is_user_mvx_vendor($current_user_id)) {
            $option = '<option></option>';
            $vendors = get_mvx_vendors();
            if ($vendors) {
                foreach($vendors as $vendor_key => $vendor) {
                    $option .= '<option value="' . esc_attr($vendor->term_id) . '">' . esc_html($vendor->page_title) . '</option>';
                }
            }
            ?>
            <label>
                <span class="title"><?php esc_html_e('Vendor', 'dc-woocommerce-multi-vendor'); ?></span>
                <span class="input-text-wrap vendor_bulk">
                    <select name="choose_vendor_bulk" id="choose_vendor_ajax_bulk" class="mvx_select_vendor" data-placeholder="<?php esc_attr_e('Search for vendor', 'dc-woocommerce-multi-vendor') ?>" style="width:300px;" >
                        <?php echo $option; ?>
                    </select>
                </span>
            </span>
            </label>

            <?php
        }
    }

    /**
     * Show related products or not
     *
     * @return arg
     */
    function show_related_products($query, $product_id) {
        $vendor = get_mvx_product_vendors($product_id);
        $related = get_mvx_vendor_settings('show_related_products', 'general', '', 'all_related');
        if ('disable' == $related) {
            return array();
        } elseif ('all_related' == $related) {
            return $query;
        } elseif ('vendors_related' == $related) {
            if ($vendor) {
                $query['where'] .= ' AND p.post_author = ' . $vendor->id;
            }
            return $query;
        }
    }

    /**
     * Filter product list as per vendor
     */
    public function filter_products_list($request) {
        global $typenow, $MVX;

        $current_user = wp_get_current_user();

        if (is_admin() && is_user_mvx_vendor($current_user) && 'product' == $typenow) {
            $request['author'] = $current_user->ID;
            $term_id = get_user_meta($current_user->ID, '_vendor_term_id', true);
            $taxquery = array(
                array(
                    'taxonomy' => $MVX->taxonomy->taxonomy_name,
                    'field' => 'id',
                    'terms' => array($term_id),
                    'operator' => 'IN'
                )
            );

            $request['tax_query'] = $taxquery;
        }

        return $request;
    }

    /**
     * Count vendor products
     */
    public function vendor_count_products($counts, $type, $perm) {
        global $MVX;
        $current_user = wp_get_current_user();

        if (is_user_mvx_vendor($current_user) && 'product' == $type) {
            $term_id = get_user_meta($current_user->ID, '_vendor_term_id', true);

            $args = array(
                'post_type' => $type,
                'posts_per_page' => -1,
                'author' => $current_user->ID,
                'tax_query' => array(
                    array(
                        'taxonomy' => $MVX->taxonomy->taxonomy_name,
                        'field' => 'id',
                        'terms' => array($term_id),
                        'operator' => 'IN'
                    ),
                ),
            );

            /**
             * Get a list of post statuses.
             */
            $stati = get_post_stati();

            // Update count object
            foreach ($stati as $status) {
                $args['post_status'] = $status;
                $query = new WP_Query($args);
                $posts = $query->get_posts();
                $counts->$status = count($posts);
            }
        }

        return $counts;
    }

    /**
     * Notify admin on publish product by vendor
     *
     * @return void
     */
    function on_all_status_transitions($new_status, $old_status, $post) {
        global $MVX;
        if ('product' !== $post->post_type || $new_status === $old_status) {
            return;
        }
        // skip for new posts and auto drafts
        if ('new' === $old_status || 'auto-draft' === $new_status) {
            return;
        }

        if ($new_status != $old_status && $post->post_status == 'pending') {
            $current_user = get_current_vendor_id();
            if ($current_user)
                $current_user_is_vendor = is_user_mvx_vendor($current_user);
            if ($current_user_is_vendor) {
                //send mails to admin for new vendor product
                $vendor = get_mvx_vendor_by_term(get_user_meta($current_user, '_vendor_term_id', true));
                $email_admin = WC()->mailer()->emails['WC_Email_Vendor_New_Product_Added'];
                $email_admin->trigger($post->post_id, $post, $vendor);
            }
        } else if ($new_status != $old_status && $post->post_status == 'publish') {
            $current_user = get_current_vendor_id();
            if ($current_user)
                $current_user_is_vendor = is_user_mvx_vendor($current_user);
            if ($current_user_is_vendor) {
                //send mails to admin for new vendor product
                $vendor = get_mvx_vendor_by_term(get_user_meta($current_user, '_vendor_term_id', true));
                $email_admin = WC()->mailer()->emails['WC_Email_Vendor_New_Product_Added'];
                $email_admin->trigger($post->post_id, $post, $vendor);
            }
        }
        if (current_user_can('administrator') && $new_status != $old_status && $post->post_status == 'publish') {
            if (isset($_POST['choose_vendor']) && !empty($_POST['choose_vendor'])) {
                $term = get_term($_POST['choose_vendor'], $MVX->taxonomy->taxonomy_name);
                $is_first_time = isset($_POST['auto_draft']) ? $_POST['auto_draft'] : '';
                if ($term) {
                    $vendor = get_mvx_vendor_by_term($term->term_id);
                    $email_admin = WC()->mailer()->emails['WC_Email_Admin_Added_New_Product_to_Vendor'];
                    $email_admin->trigger($post->post_id, $post, $vendor, $is_first_time);
                }
            }
        }
    }

    /**
     * Add Vendor tab in single product page 
     *
     * @return void
     */
    function add_vendor_tab() {
        global $MVX;
        ?>
        <li class="vendor_icon vendor_icons"><a href="#choose_vendor"><span><?php _e('Vendor', 'dc-woocommerce-multi-vendor'); ?></span></a></li>
        <?php
    }

    /**
     * Output of Vendor tab in single product page 
     *
     * @return void
     */
    function output_vendor_tab() {
        global $post, $MVX, $woocommerce;
        $html = '';
        $vendor = get_mvx_product_vendors($post->ID);
        $commission_per_poduct = get_post_meta($post->ID, '_commission_per_product', true);
        $current_user = get_current_vendor_id();
        if ($current_user)
            $current_user_is_vendor = is_user_mvx_vendor($current_user);
        $html .= '<div class="options_group" > <table class="form-field form-table">';
        $html .= '<tbody>';
        if ($vendor) {
            $option = '<option value="' . $vendor->term_id . '" selected="selected">' . $vendor->page_title . '</option>';
        } else if ($current_user_is_vendor) {
            $vendor = get_mvx_vendor_by_term(get_user_meta($current_user, '_vendor_term_id', true));
            $option = '<option value="' . $vendor->term_id . '" selected="selected">' . $vendor->page_title . '</option>';
        } else {
            $option = '<option></option>';
            $vendors = get_mvx_vendors();
            if ($vendors) {
                foreach($vendors as $vendor_key => $vendor) {
                    $option .= '<option value="' . esc_attr($vendor->term_id) . '">' . esc_html($vendor->page_title) . '</option>';
                }
            }
        }
        $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for="' . esc_attr('vendor') . '">' . __("Vendor", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
        if (!$current_user_is_vendor) {
            $html .= '<select name="' . esc_attr('choose_vendor') . '" data-placeholder="'. esc_attr("Choose vendor", "dc-woocommerce-multi-vendor").'" id="' . esc_attr('choose_vendor_ajax') . '" class="mvx_select_vendor" style="width:300px;" >' . $option . '</select>';
            $html .= '<p class="description">' . 'choose vendor' . '</p>';
        } else {
            $html .= '<label id="vendor-label" for="' . esc_attr('vendor') . '">' . $vendor->page_title . '</label>';
            $html .= '<input type="hidden" name="' . esc_attr('choose_vendor') . '"   value="' . $vendor->term_id . '" />';
        }
        $html .= '</td><tr/>';

        $commission_percentage_per_poduct = get_post_meta($post->ID, '_commission_percentage_per_product', true);
        $commission_fixed_with_percentage = get_post_meta($post->ID, '_commission_fixed_with_percentage', true);
        $commission_fixed_with_percentage_qty = get_post_meta($post->ID, '_commission_fixed_with_percentage_qty', true);
        if ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {

            if (!$current_user_is_vendor) {
                $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Percentage", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                $html .= '<input class="input-commision" type="text" name="commission_percentage" value="' . $commission_percentage_per_poduct . '"% />';
            } else {
                if (!empty($commission_percentage_per_poduct)) {
                    $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Percentage", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                    $html .= '<span>' . $commission_percentage_per_poduct . '%</span>';
                }
            }
            $html .= '</td></tr>';

            if (!$current_user_is_vendor) {
                $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Fixed per transaction", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                $html .= '<input class="input-commision" type="text" name="fixed_with_percentage" value="' . $commission_fixed_with_percentage . '" />';
            } else {
                if (!empty($commission_fixed_with_percentage)) {
                    $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Fixed per transaction", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                    $html .= '<span>' . $commission_fixed_with_percentage . '</span>';
                }
            }
            $html .= '</td></tr>';
        } else if ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {

            if (!$current_user_is_vendor) {
                $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Percentage", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                $html .= '<input class="input-commision" type="text" name="commission_percentage" value="' . $commission_percentage_per_poduct . '"% />';
            } else {
                if (!empty($commission_percentage_per_poduct)) {
                    $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission Percentage", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                    $html .= '<span>' . $commission_percentage_per_poduct . '%</span>';
                }
            }
            $html .= '</td></tr>';

            if (!$current_user_is_vendor) {
                $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "fixed amount">' . __("Commission Fixed per unit", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                $html .= '<input class="input-commision" type="text" name="fixed_with_percentage_qty" value="' . $commission_fixed_with_percentage_qty . '" />';
            } else {
                if (!empty($commission_fixed_with_percentage_qty)) {
                    $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "fixed amount">' . __("Commission Fixed per unit", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                    $html .= '<span>' . $commission_fixed_with_percentage_qty . '</span>';
                }
            }
            $html .= '</td></tr>';
        } else {

            if (!$current_user_is_vendor) {
                $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                $html .= '<input class="input-commision" type="text" name="commision" value="' . $commission_per_poduct . '" />';
            } else {
                if (!empty($commission_per_poduct)) {
                    $html .= '<tr valign="top"><td scope="row"><label id="vendor-label" for= "Commission">' . __("Commission", 'dc-woocommerce-multi-vendor') . '</label></td><td>';
                    $html .= '<span>' . $commission_per_poduct . '</span>';
                }
            }
            $html .= '</td></tr>';
        }

        $html = apply_filters('mvx_additional_fields_product_vendor_tab', $html);

        if ($vendor) {
            if (current_user_can('manage_options')) {
                $html .= '<tr valign="top"><td scope="row"><input type="button" class="delete_vendor_data button" value="' . __("Unassign vendor", 'dc-woocommerce-multi-vendor') . '" /></td></tr>';

                wp_localize_script('mvx-admin-product-js', 'unassign_vendors_data', array('current_product_id' => $post->ID, 'current_user_id' => get_current_vendor_id()));
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        ?>
        <div id="choose_vendor" class="panel woocommerce_options_panel">
            <?php echo $html; ?>
        </div>
        <?php
    }

    function product_comment_note_metabox( $post_type ) {
        global $post;
        $post_types = array('product');   
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'wf_child_letters'
                ,__( 'Rejection History', 'dc-woocommerce-multi-vendor' )
                ,array( $this, 'render_meta_box_content' )
                ,$post_type
                ,'side'
                ,'low'
            );
        }
    }

    function render_meta_box_content() {
        global $post;
         $notes = $this->get_product_note($post->ID);
         $user_id = get_current_user_id();
        if ( apply_filters('is_admin_can_add_product_notes', true, $user_id) && $post->post_status == 'pending' ) : ?>
             <?php wp_nonce_field('dc-vendor-add-product-comment', 'vendor_add_product_nonce'); ?> 
             <div class="add_note">
                 <p>
                     <label for="add_order_note"><?php esc_html_e( 'Add note', 'dc-woocommerce-multi-vendor' ); ?> <?php echo wc_help_tip( __( 'Add a note for your reference, or add a customer note (the user will be notified).', 'dc-woocommerce-multi-vendor' ) ); ?></label>
                     <textarea placeholder="<?php esc_attr_e('Enter text ...', 'dc-woocommerce-multi-vendor'); ?>" class="form-control" name="product_comment_text"></textarea>
                 </p>
                 <p>
                     <input class="add_note button mvx-add-order-note" type="submit" name="mvx_submit_product_comment" value="<?php _e('Submit', 'dc-woocommerce-multi-vendor'); ?>">
                 </p>
             </div>
             <input type="hidden" name="product_id" value="<?php echo $post->ID; ?>">
             <input type="hidden" name="current_user_id" value="<?php echo $user_id; ?>">
         <?php endif; 
         $log_statuses = apply_filters('admin_product_logs_status', array('pending', 'publish'));
         if( in_array($post->post_status, $log_statuses) ) { ?>
             <div><b><?php echo esc_html_e( 'Communication Log', 'dc-woocommerce-multi-vendor' ); ?></b></div>
             <ul class="order_notes">
                 <?php
                 if ($notes) {
                     foreach ($notes as $note) {
                         $author = get_comment_meta( $note->comment_ID, '_author_id', true );
                         $Seller = is_user_mvx_vendor($author) ? "(Seller)" : '';
                         ?>
                         <li class="note">
                             <div class="note_content <?php echo $style; ?>">
                                 <?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
                             </div>
                             <p ><?php echo esc_html($note->comment_author); ?><?php echo $Seller; ?> - <?php echo esc_html( date_i18n(wc_date_format() . ' ' . wc_time_format(), strtotime($note->comment_date) ) ); ?></p>
                         </li>
                         <?php
                     }
                 }else{
                     echo '<li class="list-group-item list-group-item-action flex-column align-items-start order-notes">' . __( 'There are no notes yet.', 'dc-woocommerce-multi-vendor' ) . '</li>';
                 }
                 ?>
             </ul>
             <?php
        }
    } 

    function add_policies_tab() {
        ?>
        <li class="policy_icon policy_icons"><a href="#set_policies"><span><?php echo apply_filters('mvx_policies_tab_title', __('Policies', 'dc-woocommerce-multi-vendor')); ?></span></a></li>
        <?php
    }

    function output_policies_tab() {
        global $post, $MVX;
        $_mvx_cancallation_policy = get_post_meta($post->ID, '_mvx_cancallation_policy', true) ? get_post_meta($post->ID, '_mvx_cancallation_policy', true) : '';
        $_mvx_refund_policy = get_post_meta($post->ID, '_mvx_refund_policy', true) ? get_post_meta($post->ID, '_mvx_refund_policy', true) : '';
        $_mvx_shipping_policy = get_post_meta($post->ID, '_mvx_shipping_policy', true) ? get_post_meta($post->ID, '_mvx_shipping_policy', true) : '';
        ?>
        <div id="set_policies" class="panel woocommerce_options_panel">
            <div class="options_group" >
                <table class="form-field form-table">
                    <tbody>
                        <?php
                        $MVX->library->load_wp_fields()->dc_generate_form_field(
                                array(
                                    "_mvx_shipping_policy" => array('label' => __('Shipping Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => '_mvx_shipping_policy', 'label_for' => '_mvx_shipping_policy', 'name' => '_mvx_shipping_policy', 'class' => 'regular-text', 'value' => $_mvx_shipping_policy, 'in_table' => true),
                                    "_mvx_refund_policy" => array('label' => __('Refund Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => '_mvx_refund_policy', 'label_for' => '_mvx_refund_policy', 'name' => '_mvx_refund_policy', 'class' => 'regular-text', 'value' => $_mvx_refund_policy, 'in_table' => true),
                                    "_mvx_cancallation_policy" => array('label' => __('Cancellation/Return/Exchange Policy', 'dc-woocommerce-multi-vendor'), 'type' => 'wpeditor', 'id' => '_mvx_cancallation_policy', 'label_for' => '_mvx_cancallation_policy', 'name' => '_mvx_cancallation_policy', 'class' => 'regular-text', 'value' => $_mvx_cancallation_policy, 'in_table' => true),
                                )
                        );
                        do_action('mvx_product_options_policy_product_data');
                        ?>
                    </tbody>
                </table>            
            </div>
        </div>
        <?php
    }

    function process_policies_data($post_id) {
        $post = get_post($post_id);
        if ($post->post_type == 'product') {
            if (isset($_POST['_mvx_cancallation_policy'])) {
                update_post_meta($post_id, '_mvx_cancallation_policy', stripslashes( html_entity_decode( $_POST['_mvx_cancallation_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if (isset($_POST['_mvx_refund_policy'])) {
                update_post_meta($post_id, '_mvx_refund_policy', stripslashes( html_entity_decode( $_POST['_mvx_refund_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if (isset($_POST['_mvx_shipping_policy'])) {
                update_post_meta($post_id, '_mvx_shipping_policy', stripslashes( html_entity_decode( $_POST['_mvx_shipping_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
        }
    }

    /**
     * Save vendor related data
     *
     * @return void
     */
    function process_vendor_data($post_id) {
        global $MVX;
        $post = get_post($post_id);
        
        if ($post->post_type == 'product') { 
            $_product = wc_get_product($post_id);
            if ($_product->is_type('variable')) {
                $children = $_product->get_children();
                if(!empty($children)) {
                    array_push($children, $post_id);
                    $products = $children;
                } else {
                    $products = array($post_id);
                }
            } else {
                $products = array($post_id);
            }

            if( !empty($products) ) {
                foreach( $products as $product ) {
                    if (isset($_POST['commision'])) {
                        update_post_meta($product, '_commission_per_product', floatval(sanitize_text_field( $_POST['commision'])));
                    }

                    if (isset($_POST['commission_percentage'])) {
                        update_post_meta($product, '_commission_percentage_per_product', floatval(sanitize_text_field($_POST['commission_percentage'])));
                    }

                    if (isset($_POST['fixed_with_percentage_qty'])) {
                        update_post_meta($product, '_commission_fixed_with_percentage_qty', floatval(sanitize_text_field($_POST['fixed_with_percentage_qty'])));
                    }

                    if (isset($_POST['fixed_with_percentage'])) {
                        update_post_meta($product, '_commission_fixed_with_percentage', floatval(sanitize_text_field($_POST['fixed_with_percentage'])));
                    }

                    if (isset($_POST['choose_vendor']) && !empty($_POST['choose_vendor'])) {

                        $term = get_term($_POST['choose_vendor'], $MVX->taxonomy->taxonomy_name);
                        if ($term) {
                            wp_delete_object_term_relationships($product, $MVX->taxonomy->taxonomy_name);
                            //wp_set_post_terms($post_id, $term->slug, $MVX->taxonomy->taxonomy_name, true);
                            wp_set_object_terms($product, (int) $term->term_id, $MVX->taxonomy->taxonomy_name, true);

                        }

                        $vendor = get_mvx_vendor_by_term(absint($_POST['choose_vendor']));
                        if (!wp_is_post_revision($product)) {
                            // unhook this function so it doesn't loop infinitely
                            remove_action('save_post', array($this, 'process_vendor_data'));
                            // update the post, which calls save_post again
                            wp_update_post(array('ID' => $product, 'post_author' => $vendor->id));
                            // re-hook this function
                            add_action('save_post', array($this, 'process_vendor_data'));
                        }
                    }elseif(!isset($_POST['woocommerce-process-checkout-nonce'])){
                        // vendor assign with product
                        if(is_user_mvx_vendor(get_current_user_id())){
                            $vendor = get_mvx_vendor(get_current_user_id());
                            wp_delete_object_term_relationships($product, $MVX->taxonomy->taxonomy_name);
                            $term = get_term($vendor->term_id, $MVX->taxonomy->taxonomy_name);
                            //wp_set_post_terms($post_id, $term->name, $MVX->taxonomy->taxonomy_name, false);
                            if($term)
                                wp_set_object_terms($product, (int) $term->term_id, $MVX->taxonomy->taxonomy_name, true);
                            $vendor = get_mvx_vendor_by_term($vendor->term_id);
                            if (!wp_is_post_revision($product) && $vendor) {
                                // unhook this function so it doesn't loop infinitely
                                remove_action('save_post', array($this, 'process_vendor_data'));
                                // update the post, which calls save_post again
                                wp_update_post(array('ID' => $product, 'post_author' => $vendor->id));
                                // re-hook this function
                                add_action('save_post', array($this, 'process_vendor_data'));
                            }
                        }
                    }
                }
            }

            if (isset($_POST['variable_post_id']) && !empty($_POST['variable_post_id'])) {
                foreach ($_POST['variable_post_id'] as $post_key => $value) {
                    if (isset($_POST['variable_product_vendors_commission'][$post_key])) {
                        $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission'][$post_key]));
                        update_post_meta($value, '_product_vendors_commission', $commission);
                    }

                    if (isset($_POST['variable_product_vendors_commission_percentage'][$post_key])) {
                        $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_percentage'][$post_key]));
                        update_post_meta($value, '_product_vendors_commission_percentage', $commission);
                    }

                    if (isset($_POST['variable_product_vendors_commission_fixed_per_trans'][$post_key])) {
                        $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_fixed_per_trans'][$post_key]));
                        update_post_meta($value, '_product_vendors_commission_fixed_per_trans', $commission);
                    }

                    if (isset($_POST['variable_product_vendors_commission_fixed_per_qty'][$post_key])) {
                        $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_fixed_per_qty'][$post_key]));
                        update_post_meta($value, '_product_vendors_commission_fixed_per_qty', $commission);
                    }

                    if (isset($_POST['dc_variable_shipping_class'][$post_key])) {
                        $_POST['dc_variable_shipping_class'][$post_key] = !empty($_POST['dc_variable_shipping_class'][$post_key]) ? (int) $_POST['dc_variable_shipping_class'][$post_key] : '';
                        $array = wp_set_object_terms($value, absint($_POST['dc_variable_shipping_class'][$post_key]), 'product_shipping_class');
                        unset($_POST['dc_variable_shipping_class'][$post_key]);
                    }
                }
            }
            
            // Default cat hierarchy reset
            $has_default_cat = get_post_meta( $post_id, '_default_cat_hierarchy_term_id', false );
            $catagories = isset( $_POST['tax_input']['product_cat'] ) ? array_filter( array_map( 'intval', (array) $_POST['tax_input']['product_cat'] ) ) : array();
            if( $has_default_cat && !in_array( $has_default_cat, $catagories ) ){
                delete_post_meta( $post_id, '_default_cat_hierarchy_term_id' );
            }
        }
    }

    /**
     * Save variation product commission
     *
     * @return void
     */
    function save_variation_commission() {
        if (isset($_POST['variable_post_id']) && !empty($_POST['variable_post_id'])) {
            foreach ($_POST['variable_post_id'] as $post_key => $value) {
                if (isset($_POST['variable_product_vendors_commission'][$post_key])) {
                    $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission'][$post_key]));
                    update_post_meta($value, '_product_vendors_commission', $commission);
                    unset($_POST['variable_product_vendors_commission'][$post_key]);
                }

                if (isset($_POST['variable_product_vendors_commission_percentage'][$post_key])) {
                    $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_percentage'][$post_key]));
                    update_post_meta($value, '_product_vendors_commission_percentage', $commission);
                    unset($_POST['variable_product_vendors_commission_percentage'][$post_key]);
                }

                if (isset($_POST['variable_product_vendors_commission_fixed_per_trans'][$post_key])) {
                    $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_fixed_per_trans'][$post_key]));
                    update_post_meta($value, '_product_vendors_commission_fixed_per_trans', $commission);
                    unset($_POST['variable_product_vendors_commission_fixed_per_trans'][$post_key]);
                }

                if (isset($_POST['variable_product_vendors_commission_fixed_per_qty'][$post_key])) {
                    $commission = floatval(sanitize_text_field($_POST['variable_product_vendors_commission_fixed_per_qty'][$post_key]));
                    update_post_meta($value, '_product_vendors_commission_fixed_per_qty', $commission);
                    unset($_POST['variable_product_vendors_commission_fixed_per_qty'][$post_key]);
                }
                if (isset($_POST['dc_variable_shipping_class'][$post_key])) {
                    $_POST['dc_variable_shipping_class'][$post_key] = !empty($_POST['dc_variable_shipping_class'][$post_key]) ? absint($_POST['dc_variable_shipping_class'][$post_key]) : '';
                    $array = wp_set_object_terms($value, absint($_POST['dc_variable_shipping_class'][$post_key]), 'product_shipping_class');
                    unset($_POST['dc_variable_shipping_class'][$post_key]);
                }
            }
        }
    }

    /**
     * Save vendor related data for variation
     *
     * @return void
     */
    public function add_variation_settings($loop, $variation_data, $variation) {
        global $MVX;

        $html = '';
        $commission = $commission_percentage = $commission_fixed_per_trans = $commission_fixed_per_qty = '';
        $commission = get_post_meta($variation->ID, '_product_vendors_commission', true);
        $commission_percentage = get_post_meta($variation->ID, '_product_vendors_commission_percentage', true);
        $commission_fixed_per_trans = get_post_meta($variation->ID, '_product_vendors_commission_fixed_per_trans', true);
        $commission_fixed_per_qty = get_post_meta($variation->ID, '_product_vendors_commission_fixed_per_qty', true);

        if ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {

            if (is_user_mvx_vendor(get_current_vendor_id())) {
                if (isset($commission_percentage) && !empty($commission_percentage)) {
                    $html .= '<tr>
                                            <td>
                                                <div class="_product_vendors_commission_percentage">
                                                    <label for="_product_vendors_commission_percentage_' . $loop . '">' . __('Commission (percentage)', 'dc-woocommerce-multi-vendor') . ':</label>
                                                    <span class="variable_commission_cls">' . $commission_percentage . '</span>
                                                </div>
                                            </td>
                                        </tr>';
                }
                if (isset($commission_percentage) && !empty($commission_percentage)) {
                    $html .= '<tr>
                                            <td>
                                                <div class="_product_vendors_commission_fixed_per_trans">
                                                    <label for="_product_vendors_commission_fixed_per_trans_' . $loop . '">' . __('Commission (fixed) Per Transaction', 'dc-woocommerce-multi-vendor') . ':</label>
                                                    <span class="variable_commission_cls">' . $commission_fixed_per_trans . '</span>
                                                </div>
                                            </td>
                                        </tr>';
                }
            } else {
                $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission_percentage">
                                                <label for="_product_vendors_commission_percentage_' . $loop . '">' . __('Commission (percentage)', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <input size="4" type="text" name="variable_product_vendors_commission_percentage[' . $loop . ']" id="_product_vendors_commission_percentage_' . $loop . '" value="' . $commission_percentage . '" />
                                            </div>
                                        </td>
                                    </tr>';
                $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission_fixed_per_trans">
                                                <label for="_product_vendors_commission_fixed_per_trans_' . $loop . '">' . __('Commission (fixed) Per Transaction', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <input size="4" type="text" name="variable_product_vendors_commission_fixed_per_trans[' . $loop . ']" id="_product_vendors_commission_fixed_per_trans__' . $loop . '" value="' . $commission_fixed_per_trans . '" />
                                            </div>
                                        </td>
                                    </tr>';
            }
        } else if ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {

            if (is_user_mvx_vendor(get_current_vendor_id())) {
                if (isset($commission_percentage) && !empty($commission_percentage)) {
                    $html .= '<tr>
                                            <td>
                                                <div class="_product_vendors_commission_percentage">
                                                    <label for="_product_vendors_commission_percentage_' . $loop . '">' . __('Commission Percentage', 'dc-woocommerce-multi-vendor') . ':</label>
                                                    <span class="variable_commission_cls">' . $commission_percentage . '</span>
                                                </div>
                                            </td>
                                        </tr>';
                }

                if (isset($commission_fixed_per_qty) && !empty($commission_fixed_per_qty)) {
                    $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission_fixed_per_qty">
                                                <label for="_product_vendors_commission_fixed_per_qty_' . $loop . '">' . __('Commission Fixed per unit', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <span class="variable_commission_cls">' . $commission_fixed_per_qty . '</span>
                                            </div>
                                        </td>
                                    </tr';
                }
            } else {
                $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission_percentage">
                                                <label for="_product_vendors_commission_percentage_' . $loop . '">' . __('Commission Percentage', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <input size="4" type="text" name="variable_product_vendors_commission_percentage[' . $loop . ']" id="_product_vendors_commission_percentage_' . $loop . '" value="' . $commission_percentage . '" />
                                            </div>
                                        </td>
                                    </tr>';

                $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission_fixed_per_qty">
                                                <label for="_product_vendors_commission_fixed_per_qty_' . $loop . '">' . __('Commission Fixed per unit', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <input size="4" type="text" name="variable_product_vendors_commission_fixed_per_qty[' . $loop . ']" id="_product_vendors_commission_fixed_per_qty__' . $loop . '" value="' . $commission_fixed_per_qty . '" />
                                            </div>
                                        </td>
                                    </tr';
            }
        } else {
            if (is_user_mvx_vendor(get_current_vendor_id())) {
                if (isset($commission) && !empty($commission)) {
                    $html .= '<tr>
                                            <td>
                                                <div class="_product_vendors_commission">
                                                    <label for="_product_vendors_commission_' . $loop . '">' . __('Commission', 'dc-woocommerce-multi-vendor') . ':</label>
                                                    <span class="variable_commission_cls">' . $commission . '</span>
                                                </div>
                                            </td>
                                        </tr>';
                }
            } else {
                $html .= '<tr>
                                        <td>
                                            <div class="_product_vendors_commission">
                                                <label for="_product_vendors_commission_' . $loop . '">' . __('Commission', 'dc-woocommerce-multi-vendor') . ':</label>
                                                <input size="4" type="text" name="variable_product_vendors_commission[' . $loop . ']" id="_product_vendors_commission_' . $loop . '" value="' . $commission . '" />
                                            </div>
                                        </td>
                                    </tr>';
            }
        }

        echo $html;
    }

    /**
     * Add vendor tab on single product page
     *
     * @return void
     */
    function product_vendor_tab($tabs) {
        global $product;
        if ($product) {
            $vendor = get_mvx_product_vendors($product->get_id());
            if ($vendor) {
                $title = __('Vendor', 'dc-woocommerce-multi-vendor');
                $tabs['vendor'] = array(
                    'title' => $title,
                    'priority' => 20,
                    'callback' => array($this, 'woocommerce_product_vendor_tab')
                );
            }
        }
        return $tabs;
    }

    /**
     * Add vendor tab html
     *
     * @return void
     */
    function woocommerce_product_vendor_tab() {
        global $woocommerce, $MVX;
        $MVX->template->get_template('vendor_tab.php');
    }

    /**
     * Add policies tab on single product page
     *
     * @return void
     */
    function product_policy_tab($tabs) {
        global $product;
        if ($product) {
            $policies = get_mvx_product_policies($product->get_id());
            if (count($policies) > 0) {
                $tabs['policies'] = array(
                    'title' => apply_filters('mvx_policies_tab_title', __('Policies', 'dc-woocommerce-multi-vendor')),
                    'priority' => 30,
                    'callback' => array($this, 'woocommerce_product_policies_tab')
                );
            }
        }

        return $tabs;
    }

    /**
     * Add Polices tab html
     *
     * @return void
     */
    function woocommerce_product_policies_tab() {
        global $MVX;
        $MVX->template->get_template('policies_tab.php');
    }

    /**
     * add tax query on product page
     * @return void
     */
    function convert_business_id_to_taxonomy_term_in_query($query) {
        global $pagenow, $MVX;
        if (is_admin()) {
            if (isset($_GET['post_type']) && $_GET['post_type'] == 'product' && $pagenow == 'edit.php') {
                $current_user_id = get_current_vendor_id();
                $current_user = get_user_by('id', $current_user_id);
                if (!in_array('dc_vendor', $current_user->roles))
                    return $query;
                $term_id = get_user_meta($current_user_id, '_vendor_term_id', true);

                $taxquery = array(
                    array(
                        'taxonomy' => $MVX->taxonomy->taxonomy_name,
                        'field' => 'id',
                        'terms' => array($term_id),
                        'operator' => 'IN'
                    )
                );

                $query->set('tax_query', $taxquery);
            }
        } else {
            if (!is_tax($MVX->taxonomy->taxonomy_name) && (isset($query->query_vars['wc_query']) && $query->query_vars['wc_query'] == 'product_query') || (isset($query->query['post_type']) && $query->query['post_type'] == 'product')) {
                $get_block_array = array();
                $get_blocked = mvx_get_all_blocked_vendors();
                if (!empty($get_blocked)) {
                    foreach ($get_blocked as $get_block) {
                        $get_block_array[] = (int) $get_block->term_id;
                    }
                    $taxquery = ($query->get('tax_query')) ? $query->get('tax_query') : array();
                    $taxquery[] = array(
                            'taxonomy' => $MVX->taxonomy->taxonomy_name,
                            'field' => 'id',
                            'terms' => $get_block_array,
                            'operator' => 'NOT IN'
                        );

                    $query->set('tax_query', $taxquery);
                }
            }
        }
        return $query;
    }

    /**
     * Vendor report abuse option
     */
    function add_report_abuse_link() {
        global $product;
        if (apply_filters('mvx_show_report_abuse_link', true, $product) && mvx_is_module_active('report-abuse')) {
            $report_abuse_text = apply_filters('mvx_report_abuse_text', __('Report Abuse', 'dc-woocommerce-multi-vendor'), $product);
            $show_in_popup = apply_filters('mvx_show_report_abuse_form_popup', true, $product)
            ?>
            <div class="mvx-report-abouse-wrapper">
                <a href="javascript:void(0);" id="report_abuse"><?php echo esc_html($report_abuse_text); ?></a>
                <div id="report_abuse_form"  class="<?php echo ( $show_in_popup ) ? 'report-abouse-modal' : ''; ?>" tabindex="-1" style="display: none;">
                    <div class="<?php echo ( $show_in_popup ) ? 'modal-content' : 'toggle-content'; ?>">
                        <div class="modal-header">
                            <button type="button" class="close">&times;</button>
                            <h2 class="mvx-abuse-report-title1"><?php esc_html_e('Report an abuse for product ', 'dc-woocommerce-multi-vendor') . ' ' . the_title(); ?> </h2>
                        </div>
                        <div class="modal-body">
                            <p class="field-row">
                                <input type="text" class="report_abuse_name" id="report_abuse_name" name="report_abuse[name]" value="" style="width: 100%;" placeholder="<?php esc_attr_e('Name', 'dc-woocommerce-multi-vendor'); ?>" required="">
                                <span class="mvx-report-abuse-error"></span>
                            </p>
                            <p class="field-row">
                                <input type="email" class="report_abuse_email" id="report_abuse_email" name="report_abuse[email]" value="" style="width: 100%;" placeholder="<?php esc_attr_e('Email', 'dc-woocommerce-multi-vendor'); ?>" required="">
                                <span class="mvx-report-abuse-error"></span>
                            </p>
                            <p class="field-row">
                                <textarea name="report_abuse[message]" class="report_abuse_msg" id="report_abuse_msg" rows="5" style="width: 100%;" placeholder="<?php esc_attr_e('Leave a message explaining the reasons for your abuse report', 'dc-woocommerce-multi-vendor'); ?>" required=""></textarea>
                                <span class="mvx-report-abuse-error"></span>
                            </p>
                        </div> 
                        <div class="modal-footer">
                            <input type="hidden" class="report_abuse_product_id" value="<?php echo $product->get_id(); ?>">
                            <button type="button" class="btn btn-primary submit-report-abuse" name="report_abuse[submit]"><?php esc_html_e('Report', 'dc-woocommerce-multi-vendor'); ?></button>
                        </div>
                    </div>
                </div>
            </div>                          
            <?php
        }
    }

    public function woocommerce_shop_loop_callback($product = null) {
        global $MVX, $post;
        if (mvx_is_store_page()) {
            return;
        }
        if (!is_object($product)) {
            global $product;
        }

        if (!is_a($product, 'WC_Product')) {
            return;
        }
        $child_products = wc_get_products(array('post_parent' => $product->get_id(), 'posts_per_page' => -1));
        if ($child_products) {
            $product_array_price[$product->get_id()] = $product->get_price();
            foreach ($child_products as $child_product) {
                if ($child_product->is_in_stock() && $product->get_price()) {
                    $product_array_price[$child_product->get_id()] = $child_product->get_price();
                }
            }
            $filtered_product = apply_filters('mvx_spmv_filtered_product', array_search(min($product_array_price), $product_array_price), $product->get_id(), array_keys($product_array_price));
            $post = get_post($filtered_product);
            $product = wc_get_product($filtered_product);
        }
    }

    /**
     * filter shop loop for single product multiple vendor
     * @global Object $wpdb
     * @param WC_Query object $q
     */
    public function woocommerce_product_query($q) {
        global $MVX;
        if (mvx_is_store_page()) {
            return;
        }
        //$q->set('post_parent', 0);
        // new
        if (get_transient('mvx_spmv_exclude_products_data')) {
            $spmv_excludes = get_transient('mvx_spmv_exclude_products_data');
            $excluded_order = mvx_get_settings_value(get_mvx_vendor_settings('singleproductmultiseller_show_order', 'spmv_pages'), 'min-price');
            $post__not_in = ( isset( $spmv_excludes[$excluded_order] ) ) ? $spmv_excludes[$excluded_order] : array();
            $q->set('post__not_in', $post__not_in );
        }
    }

    /**
     * Filter product on select category from MVX_Widget_Vendor_Product_Categories widget
     * @param array $tax_query
     * @return array
     */
    public function mvx_filter_product_category($tax_query) {
        global $MVX;
        $category = filter_input(INPUT_GET, 'category');
        if (!mvx_is_store_page() || is_null($category)) {
            return $tax_query;
        }
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $category
        );
        return $tax_query;
    }

    function frontend_product_edit() {
        global $MVX, $post;
        $vendor = get_mvx_product_vendors($post->ID);
        if ($vendor && $vendor->id == get_current_vendor_id() && current_user_can('edit_products') && (current_user_can('edit_published_products') || current_user_can('delete_published_products'))) {
            $pro_id = $post->ID;
            ?>
            <div class="mvx_fpm_buttons">
                <?php if (current_user_can('edit_published_products')) { ?>
                    <a class="mvx_fpm_button" href="<?php echo esc_url(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_edit_product_endpoint', 'seller_dashbaord', 'edit-product'), $pro_id)); ?>">
                        <img width="16" height="16" src="<?php echo $MVX->plugin_url; ?>/assets/images/edit.png" />
                    </a>
                <?php } ?>
                <?php if (current_user_can('delete_published_products')) { ?>
                    <span class="mvx_fpm_button_separator">--</span>
                    <a class="mvx_fpm_button mvx_fpm_delete" href="#" data-proid="<?php echo $pro_id; ?>">
                        <img width="16" height="16" src="<?php echo $MVX->plugin_url; ?>/assets/images/trash.png" />
                    </a>
                <?php } ?>
            </div>
            <?php
        }
    }

    function set_vendor_added_product_flag($post_ID, $post, $update) {
        if ($post_ID && $post) {
            $author_id = $post->post_author;
            if (is_user_mvx_vendor($author_id)) {
                $already_added_product = get_user_meta($author_id, '_vendor_added_product', true);
                if (!$already_added_product) {
                    update_user_meta($author_id, '_vendor_added_product', 1);
                }
            }
        }
    }

    function mvx_delete_product_action() {
        $products_url = mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_products_endpoint', 'seller_dashbaord', 'products'));
        $delete_product_redirect_url = apply_filters('mvx_vendor_redirect_after_delete_product_action', $products_url);
        $wpnonce = isset($_REQUEST['_wpnonce']) ? wc_clean($_REQUEST['_wpnonce']) : '';
        $product_id = isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : 0;
        $vendor = get_mvx_product_vendors($product_id);
        $current_user_ids = apply_filters( 'mvx_product_current_id' , array( get_current_user_id() ) , $vendor );
        if ($wpnonce && wp_verify_nonce($wpnonce, 'mvx_delete_product') && $product_id && in_array($vendor->id, $current_user_ids )) {
            if (current_user_can('delete_published_products')) {
                wp_delete_post($product_id);
                wc_add_notice(__('Product Deleted!', 'dc-woocommerce-multi-vendor'), 'success');
                wp_redirect($delete_product_redirect_url);
                exit;
            }
        }
        if($wpnonce && wp_verify_nonce($wpnonce, 'mvx_untrash_product') && $product_id && in_array($vendor->id, $current_user_ids )){
            wp_untrash_post($product_id);
            wc_add_notice(__('Product restored from the Trash', 'dc-woocommerce-multi-vendor'), 'success');
            wp_redirect($delete_product_redirect_url);
            exit;
        }
        if($wpnonce && wp_verify_nonce($wpnonce, 'mvx_trash_product') && $product_id && in_array($vendor->id, $current_user_ids )){
            wp_trash_post($product_id);
            wc_add_notice(__('Product moved to the Trash', 'dc-woocommerce-multi-vendor'), 'success');
            wp_redirect($delete_product_redirect_url);
            exit;
        }
    }

    /**
     * Customer Questions and Answers tab
     * @param array $tabs
     * @return array
     */
    function mvx_customer_questions_and_answers_tab($tabs) {
        global $product;
        if($product) :
            $vendor = get_mvx_product_vendors($product->get_id());
            if ($vendor && apply_filters('mvx_customer_questions_and_answers_enabled', true, $product->get_id())) {
                $tabs['mvx_customer_qna'] = array(
                    'title' => __('Questions and Answers', 'dc-woocommerce-multi-vendor'),
                    'priority' => 40,
                    'callback' => array($this, 'mvx_customer_questions_and_answers_content')
                );
            }
        endif;
        return $tabs;
    }

    /**
     * Customer Questions and Answers tab content
     * @return html
     */
    function mvx_customer_questions_and_answers_content() {
        global $MVX, $product;
        $vendor = get_mvx_product_vendors($product->get_id());
        if ($vendor && apply_filters('mvx_customer_questions_and_answers_enabled', true, $product->get_id())) {
            $cust_qna_data = $MVX->product_qna->get_Product_QNA($product->get_id(), array('sortby'=>'vote'));
            $MVX->template->get_template('mvx-customer-qna-form.php', array('cust_qna_data' => $cust_qna_data));
        }
    }

    /**
     * Add commission field in create new category page
     */
    public function add_product_cat_commission_fields() {
        ?>
        <?php if ('fixed' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed') || 'percent' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed')): ?>
            <div class="form-field term-display-type-wrap">
                <label for="commision"><?php _e('Commission', 'dc-woocommerce-multi-vendor'); ?></label>
                <input type="number" class="short" style="" name="commision" id="commision" value="" placeholder="">
            </div>
        <?php endif; ?>
        <?php if ('fixed_with_percentage' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed') || 'fixed_with_percentage_qty' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed')): ?>
            <div class="form-field term-display-type-wrap">
                <label for="commission_percentage"><?php _e('Commission Percentage', 'dc-woocommerce-multi-vendor'); ?></label>
                <input type="number" class="short" style="" name="commission_percentage" id="commission_percentage" value="" placeholder="">
            </div>
        <?php endif; ?>
        <?php if ('fixed_with_percentage' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed')): ?>
            <div class="form-field term-display-type-wrap">
                <label for="fixed_with_percentage"><?php _e('Commission Fixed per transaction', 'dc-woocommerce-multi-vendor'); ?></label>
                <input type="number" class="short" style="" name="fixed_with_percentage" id="fixed_with_percentage" value="" placeholder="">
            </div>
        <?php endif; ?>
        <?php if ('fixed_with_percentage_qty' === get_mvx_vendor_settings('commission_type', 'payment', '', 'fixed')): ?>
            <div class="form-field term-display-type-wrap">
                <label for="fixed_with_percentage_qty"><?php _e('Commission Fixed per unit', 'dc-woocommerce-multi-vendor'); ?></label>
                <input type="number" class="short" style="" name="fixed_with_percentage_qty" id="fixed_with_percentage_qty" value="" placeholder="">
            </div>
        <?php endif; ?>
        <?php
    }

    /**
     * Add commission field in edit category page
     * @param Object $term
     */
    public function edit_product_cat_commission_fields($term) {
        $commision = get_term_meta($term->term_id, 'commision', true);
        $commission_percentage = get_term_meta($term->term_id, 'commission_percentage', true);
        $fixed_with_percentage = get_term_meta($term->term_id, 'fixed_with_percentage', true);
        $fixed_with_percentage_qty = get_term_meta($term->term_id, 'fixed_with_percentage_qty', true);
        $commission_type_value = get_mvx_vendor_settings('commission_type', 'commissions') ? get_mvx_vendor_settings('commission_type', 'commissions')['value'] : '';
        ?>
        <?php if ('fixed' === $commission_type_value || 'percent' === $commission_type_value): ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="commision"><?php _e('Commission', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td><input type="text" class="short" style="" name="commision" id="commision" value="<?php echo $commision; ?>" placeholder=""></td>
            </tr>
        <?php endif; ?>
        <?php if ('fixed_with_percentage' === $commission_type_value || 'fixed_with_percentage_qty' === $commission_type_value): ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="commission_percentage"><?php _e('Commission Percentage', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td><input type="number" class="short" style="" name="commission_percentage" id="commission_percentage" value="<?php echo $commission_percentage; ?>" placeholder=""></td>
            </tr>
        <?php endif; ?>
        <?php if ('fixed_with_percentage' === $commission_type_value): ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="fixed_with_percentage"><?php _e('Commission Fixed per transaction', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td><input type="number" class="short" style="" name="fixed_with_percentage" id="fixed_with_percentage" value="<?php echo $fixed_with_percentage; ?>" placeholder=""></td>
            </tr>
        <?php endif; ?>
        <?php if ('fixed_with_percentage_qty' === $commission_type_value): ?>
            <tr class="form-field">
                <th scope="row" valign="top"><label for="fixed_with_percentage_qty"><?php _e('Commission Fixed per unit', 'dc-woocommerce-multi-vendor'); ?></label></th>
                <td><input type="number" class="short" style="" name="fixed_with_percentage_qty" id="fixed_with_percentage_qty" value="<?php echo $fixed_with_percentage_qty; ?>" placeholder=""></td>
            </tr>
        <?php endif; ?>
        <?php
    }

    /**
     * Save commission settings for product category
     * @param int $term_id
     * @param int $tt_id
     * @param string $taxonomy
     */
    public function save_product_cat_commission_fields($term_id, $tt_id = '', $taxonomy = '') {
        if (isset($_POST['commision']) && 'product_cat' === $taxonomy) {
            update_term_meta($term_id, 'commision', floatval(sanitize_text_field($_POST['commision'])));
        }
        if (isset($_POST['commission_percentage']) && 'product_cat' === $taxonomy) {
            update_term_meta($term_id, 'commission_percentage', floatval(sanitize_text_field($_POST['commission_percentage'])));
        }
        if (isset($_POST['fixed_with_percentage']) && 'product_cat' === $taxonomy) {
            update_term_meta($term_id, 'fixed_with_percentage', floatval(sanitize_text_field($_POST['fixed_with_percentage'])));
        }
        if (isset($_POST['fixed_with_percentage_qty']) && 'product_cat' === $taxonomy) {
            update_term_meta($term_id, 'fixed_with_percentage_qty', floatval(sanitize_text_field($_POST['fixed_with_percentage_qty'])));
        }
    }
    
    /**
     * Add GTIN fields for product
     */
    public function mvx_gtin_product_option() {
        global $MVX, $post;
        $gtin_data = wp_get_post_terms($post->ID, $MVX->taxonomy->mvx_gtin_taxonomy);
        $gtin_type = '';
        if($gtin_data){
            $gtin_type = isset($gtin_data[0]->term_id) ? $gtin_data[0]->term_id : '';
        }
        $custom_attributes = array();
        if(is_user_mvx_vendor(get_current_user_id()) && isset( $_REQUEST['post'] ) && isset( $_REQUEST['action'] ) &&  $_REQUEST['action'] == 'edit' ){
            $custom_attributes['disabled'] = 'disabled';
        }
        $gtin_type_options = array('' => __( 'Select type', 'dc-woocommerce-multi-vendor' )) + $MVX->taxonomy->get_mvx_gtin_terms(array('fields' => 'id=>name', 'orderby' => 'id'));
        woocommerce_wp_select( array(
                'id'            => '_mvx_gtin_type',
                'value'         => $gtin_type,
                'wrapper_class' => 'mvx_gtin_type',
                'label'         => __( 'GTIN type', 'dc-woocommerce-multi-vendor' ),
                'options'       => $gtin_type_options,
                'desc_tip'      => true,
                'description'   => __( 'Add the GTIN code for this product.', 'dc-woocommerce-multi-vendor' ),
                'custom_attributes' => $custom_attributes,
        ) );
        woocommerce_wp_text_input( array(
                'id'          => '_mvx_gtin_code',
                'label'       => __( 'GTIN Code:', 'dc-woocommerce-multi-vendor' ),
                'placeholder' => '',
                'desc_tip'    => true,
                'description' => __( 'Add the GTIN code for this product.', 'dc-woocommerce-multi-vendor' ),
                'custom_attributes' => $custom_attributes,
        ) );
    }
    
    /**
     * Save the GTIN Code of product.
     *
     * @param $product_id Product ID
     */
    public function mvx_save_gtin_product_option( $product_id ) {
        global $MVX;
        if( isset( $_POST['_mvx_gtin_type'] ) && !empty( $_POST['_mvx_gtin_type'] ) ){
            $term = get_term( $_POST['_mvx_gtin_type'], $MVX->taxonomy->mvx_gtin_taxonomy );
            if ($term && !is_wp_error( $term )) {
                wp_delete_object_term_relationships( $product_id, $MVX->taxonomy->mvx_gtin_taxonomy );
                wp_set_object_terms( $product_id, $term->term_id, $MVX->taxonomy->mvx_gtin_taxonomy, true );
            }
        }
        if ( isset( $_POST['_mvx_gtin_code'] ) ) {
            update_post_meta( $product_id, '_mvx_gtin_code', wc_clean( wp_unslash( $_POST['_mvx_gtin_code'] ) ) );
        }

        // if product has different multi level categories hierarchy, save the default
        if( isset( $_POST['_default_cat_hierarchy_term_id'] ) ){
            update_post_meta( $product_id, '_default_cat_hierarchy_term_id', absint( $_POST['_default_cat_hierarchy_term_id'] ) );
        }
        // Or update default cat if someone remove the default cat
        if( get_post_meta( $product_id, '_default_cat_hierarchy_term_id', true) ){
            $default_cat_id = ( get_post_meta( $product_id, '_default_cat_hierarchy_term_id', true ) ) ? (int) get_post_meta( $product_id, '_default_cat_hierarchy_term_id', true ) : 0;
            $catagories = isset( $_POST['tax_input']['product_cat'] ) ? array_filter( array_map( 'intval', (array) $_POST['tax_input']['product_cat'] ) ) : array();
            if( !in_array($default_cat_id, $catagories) ){
                $get_different_terms_hierarchy = get_mvx_different_terms_hierarchy( $catagories );
                $new_default_id = reset(array_values($get_different_terms_hierarchy));
                update_post_meta( $product_id, '_default_cat_hierarchy_term_id', absint( $new_default_id ) );
            }
        }
    }
    
    /**
     * Search products by GTIN Code
     * @param $query
     */
    public function mvx_gtin_product_search( $query  ) {
        global $wpdb;
        $search_keyword = ((isset($query->query['s']) && !empty($query->query['s'])) ? $query->query['s'] : (isset($_REQUEST['s']) && !empty($_REQUEST['s']))) ? wc_clean($_REQUEST['s']) : '';
        if( empty($search_keyword) || !isset( $query->query['post_type'] ) || $query->query['post_type'] != 'product'){
            return;
        }
        if(!empty($query->query['s']) || (isset($_REQUEST['s']) && !empty($_REQUEST['s']))){ 
            
            $posts = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_mvx_gtin_code' AND meta_value LIKE %s;", esc_sql( '%'.$search_keyword.'%' ) ) );
            if ( ! $posts ) {
                    return;
            }

            unset( $query->query['s'] );
            unset( $query->query_vars['s'] );
            $query->query['post__in'] = array();
            foreach($posts as $id){
                $post = get_post($id);
                if($post->post_type == 'product_variation'){
                    $query->query['post__in'][] = $post->post_parent;
                    $query->query_vars['post__in'][] = $post->post_parent;
                } else {
                    $query->query_vars['post__in'][] = $post->ID;
                }
            }
        }
    }
    
    public function mvx_gtin_get_search_query_vars(){
        if(isset($_REQUEST['s']))
            return wc_clean($_REQUEST['s']);
    }
    
    /**
     * Add the column GTIN inside the product list table.
     *
     * @param $columns
     *
     * @return array
     */
    public function manage_product_columns( $columns ){
        $product_items = array( 'mvx_product_gtin' => __( 'GTIN', 'dc-woocommerce-multi-vendor' ) );
        $ref_pos       = array_search ( 'sku', array_keys ( $columns ) );
        $columns = array_slice ( $columns, 0, $ref_pos + 1, true ) + $product_items + array_slice ( $columns, $ref_pos + 1, count ( $columns ) - 1, true );
        return $columns;
    }
    
    /**
     * Show the GTIN code inside the product list.
     *
     * @param $column
     * @return void
     *
     */
    public function show_gtin_code( $column ) {
        global $MVX, $post;
        if( $post->post_type != 'product' ) return;
        
        if ( 'mvx_product_gtin' == $column ) {

            $gtin_terms = wp_get_post_terms( $post->ID, $MVX->taxonomy->mvx_gtin_taxonomy);
            $gtin_label = '';
            if($gtin_terms && isset($gtin_terms[0])){
                $gtin_label = $gtin_terms[0]->name;
            }
            $gtin_code = get_post_meta( $post->ID, '_mvx_gtin_code', true );
            
            echo ( $gtin_label || $gtin_code ) ? esc_html( $gtin_label . ' - '. $gtin_code ) : '<span class="na">&ndash;</span>';
        }
    }
    
    public function mvx_get_product_terms_html_selected_terms( $terms, $taxonomy = '', $id = '' ){
        $user_id = get_current_user_id();
        if(is_user_mvx_vendor($user_id) && get_transient( 'classified_product_terms_vendor'.$user_id )){
            $classified_terms = get_transient( 'classified_product_terms_vendor'.$user_id );
            if( isset($classified_terms['taxonomy']) && $classified_terms['taxonomy'] == $taxonomy ){
                $hierarchy_ids = get_ancestors( $classified_terms['term_id'], $taxonomy );
                $hierarchy_ids[] = $classified_terms['term_id'];
                return $hierarchy_ids;
            }
        }
        return $terms;
    }
    
    public function reset_vendor_classified_product_terms( $maybe_product_or_endpoints ){
        global $MVX;
        if ( 'edit-product' === $MVX->endpoints->get_current_endpoint() ) {
            return;
        }
        $user_id = get_current_user_id();
        if(is_user_mvx_vendor($user_id) && get_transient( 'classified_product_terms_vendor' . $user_id )){
            delete_transient( 'classified_product_terms_vendor' . $user_id );
        }
    }
    
    public function remove_meta_boxes(){
        global $post;
        if( $post && $post->post_type != 'product' ) return;
        if( !is_user_mvx_vendor( get_current_user_id() ) ) return;
       if( get_mvx_vendor_settings('category_pyramid_guide', 'settings_general') == false ) return;
        
        if( isset( $_REQUEST['post'] ) && isset( $_REQUEST['action'] ) &&  $_REQUEST['action'] == 'edit' ){
            // product category
            remove_meta_box( 'product_catdiv', 'product', 'side' );
            
            add_meta_box( 'mvx_product_cat_hierarchy', __( 'Category hierarchy', 'dc-woocommerce-multi-vendor' ), array( $this, 'mvx_product_cat_hierarchy_meta_box' ), $post->post_type, 'side' );
        }
    }
    
    public function mvx_product_cat_hierarchy_meta_box(){
        global $post;
        if( $post && $post->post_type == 'product' ) {
            $terms = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'ids' ) );
            $get_different_terms_hierarchy = get_mvx_different_terms_hierarchy( $terms );
            if( $get_different_terms_hierarchy ) {
                $nos_hierarchy = count( $get_different_terms_hierarchy );
                $default_cat_hierarchy = get_post_meta( $post->ID, '_default_cat_hierarchy_term_id', true );
                echo '<div class="mvx-pro-cat-hierarchy" id="mvx-pro-cat-hierarchy">';
                if( $nos_hierarchy > 1 ){
                    echo '<p class="howto" id="new-tag-product_tag-desc">'.__( 'This product has multiple categories hierarchy.', 'dc-woocommerce-multi-vendor' ) . " " . __( 'Choose default', 'dc-woocommerce-multi-vendor' ) . '-</p>';
                }
                echo '<ul class="hierarchy-wrapper">';
                foreach ( $get_different_terms_hierarchy as $term_id ) {
                    echo '<li>' 
                        . '<label>'
                        . '<input type="radio" name="_default_cat_hierarchy_term_id" id="_default_cat_hierarchy_term_id_' . esc_attr( $term_id ) . '" value="' . esc_attr( $term_id ) . '" ' . checked( $default_cat_hierarchy, $term_id, false ) . ' data-label="' . esc_attr( $term_id ) . '" /> '
                        . '<span for="_visibility_hierarchy_' . esc_attr( $term_id ) . '">'
                        . mvx_generate_term_breadcrumb_html( 
                            array( 
                                'term_id' => $term_id, 
                                'taxonomy' => 'product_cat',
                                'wrap_before'           => '',
                                'wrap_after'            => '',
                                'wrap_child_before'     => '',
                                'wrap_child_after'      => '',
                            ) ).'</span>' 
                        . '</label>'
                        . '</li>';
                }
                echo '</ul></div>';
            }
        }
    }
    
    public function show_default_product_cats_in_vendor_list($termlist = array(), $product = null){
        if($product){
            $taxonomy = 'product_cat';
            $default_cat_hierarchy = get_post_meta( $product->get_id(), '_default_cat_hierarchy_term_id', true );
            if( !$default_cat_hierarchy ) return $termlist;
            
            $hierarchy = get_ancestors( $default_cat_hierarchy, $taxonomy );
            $hierarchy = array_reverse( $hierarchy );
            $hierarchy[] = $default_cat_hierarchy;
            $terms = array();
            foreach ( $hierarchy as $id ) {
                $terms[] = get_term( $id, $taxonomy );
            }
            return $terms;
        }
    }
    
    public function show_default_product_cats_in_wp_backend( $termlist_html, $taxonomy = 'product_cat', $product_id = 0, $termlist = array(), $terms = array() ){
        $default_cat_hierarchy = get_post_meta( $product_id, '_default_cat_hierarchy_term_id', true );
        if( $taxonomy != 'product_cat' ) return $termlist_html;
        if( !$default_cat_hierarchy ) return $termlist_html;

        $hierarchy = get_ancestors( $default_cat_hierarchy, $taxonomy );
        $hierarchy = array_reverse( $hierarchy );
        $hierarchy[] = $default_cat_hierarchy;
        $termlist = array();
        foreach ( $hierarchy as $id ) {
            $term = get_term( $id, $taxonomy );
            if($term) {
                $termlist[] = '<a href="' . esc_url( admin_url( 'edit.php?product_cat=' . $term->slug . '&post_type=product' ) ) . ' ">' . esc_html( $term->name ) . '</a>';
            }
        }
        
        return implode( ', ', $termlist );
    }
    
    public function show_default_product_cats_product_single( $terms ){
        global $product;
        if( !is_object( $product )) $product = wc_get_product( get_the_ID() );
        if(is_product() && $product){
            $default_cat_hierarchy = get_post_meta( $product->get_id(), '_default_cat_hierarchy_term_id', true );
            if( !$default_cat_hierarchy ) return $terms;
            $taxonomy = 'product_cat';
            $hierarchy = get_ancestors( $default_cat_hierarchy, $taxonomy );
            $hierarchy = array_reverse( $hierarchy );
            $hierarchy[] = $default_cat_hierarchy;
            $links = array();
            foreach ( $hierarchy as $id ) {
                $term = get_term( $id, $taxonomy );
                $link = get_term_link( $term, $taxonomy );
                if ( is_wp_error( $link ) ) {
                        return $link;
                }
                $links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
            }

            return $links;
        }
        return $terms;
    }
    
    public function mvx_product_duplicate_before_save($duplicate, $product){
        $duplicate->set_name( $product->get_name() ); // remove duplicate (copy) strings
    }
    
    public function woocommerce_blocks_product_grid_item_html( $html, $data, $product ) {
        $vendor = get_mvx_product_vendors( $product->get_id() );
        if( !$vendor ) return $html;
        if ( get_mvx_vendor_settings('display_product_seller', 'settings_general') && apply_filters( 'mvx_enable_sold_by_on_wc_blocks_product_grid', true, $product ) ) {
            $sold_by_text = apply_filters( 'mvx_sold_by_text', __('Sold By', 'dc-woocommerce-multi-vendor'), $product->get_id() );
            $html = "<li class=\"wc-block-grid__product\">
                    <a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
                            {$data->image}
                            {$data->title}
                    </a>
                    {$data->badge}
                    {$data->price}
                    {$data->rating}
                    <a href=\"{$vendor->permalink}\" class=\"by-vendor-name-link\" style=\"display: block;\">
                        {$sold_by_text} {$vendor->page_title}
                    </a>
                    {$data->button}
            </li>";
        }
        return $html;
    }

    public static function add_product_note($product_id, $note, $user_id = 0) {
        if (!$product_id) {
            return 0;
        }

        if(is_user_mvx_vendor($user_id)){
            $vendor = get_mvx_vendor($user_id);
            $comment_author = $vendor->page_title;
            $comment_author_email = $vendor->user_data->user_email;
        } else {
            $user                 = get_user_by( 'id', $user_id );
            $comment_author       = $user->display_name;
            $comment_author_email = $user->user_email;
        }

        $commentdata = apply_filters('mvx_new_product_note_data', array(
            'comment_post_ID' => $product_id,
            'comment_author' => $comment_author,
            'comment_author_email' => $comment_author_email,
            'comment_author_url' => '',
            'comment_content' => $note,
            'comment_agent' => 'MVX',
            'comment_type' => 'product_note',
            'comment_parent' => 0,
            'comment_approved' => 1,
                ), $product_id, $user_id);

        $comment_id = wp_insert_comment($commentdata);

        do_action('mvx_new_product_note', $comment_id, $product_id, $user_id);

        return $comment_id;
    }

    public static function get_product_note($product_id) {
        global $MVX;
        $args = apply_filters('mvx_new_product_get_note_data', array(
            'post_id' => $product_id,
            'type' => 'product_note',
            'status' => 'approve',
            'orderby' => 'comment_ID'
        ));

        $notes = get_comments($args);
        return $notes;
    }
    
}