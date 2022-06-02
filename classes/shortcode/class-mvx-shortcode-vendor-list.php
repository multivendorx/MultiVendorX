<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MVX_Shortcode_Vendor_List')) {

    class MVX_Shortcode_Vendor_List {
        
        /**
         * Filter vendor list
         * @global object $MVX
         * @param string $orderby
         * @param string $order
         * @param string $product_category
         * @return array
         */
        public static function get_vendors_query($args, $request = array(), $ignore_pagi = false) {
            global $wpdb;
            $block_vendors = wp_list_pluck(mvx_get_all_blocked_vendors(), 'id');
            $include_vendors = array();
            $default = array (
                'role'      => 'dc_vendor',
                'fields'    => 'ids',
                'exclude'   => $block_vendors,
                'orderby'   => $args['orderby'],
                'order'     => $args['order'],
                'number'    => 12,
            );
            if (isset($request['vendor_sort_type']) && $request['vendor_sort_type'] == 'category' && isset($request['vendor_sort_category'])) {
                $pro_args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'product',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'term_id',
                            'terms' => absint($request['vendor_sort_category'])
                        )
                    )
                );
                $products = get_posts($pro_args);
                $product_ids = wp_list_pluck($products, 'ID');
                foreach ($product_ids as $product_id) {
                    $vendor = get_mvx_product_vendors($product_id);
                    if ($vendor && !in_array($vendor->id, $block_vendors)) {
                        $include_vendors[] = $vendor->id;
                    }
                }
                //
                $args['include'] = $include_vendors;
                $vendor_args = wp_parse_args($args, $default);
            } elseif (isset($request['vendor_sort_type']) && $request['vendor_sort_type'] == 'shipping' && isset($request['vendor_sort_category'])) {
                $get_vendor = false;
                $zone_id = '';
                $location_code = '';
                if (isset( $request['vendor_postcode_list'] ) && !empty( $request['vendor_postcode_list'] ) ) {
                    $location_code = $request['vendor_postcode_list'];
                } elseif ( isset( $request['vendor_country'] ) && isset($request['vendor_state']) && !empty($request['vendor_state']) && !empty($request['vendor_country']) ) {

                    $location_code = $request['vendor_country'].':'.$request['vendor_state'];
                } elseif (isset( $request['vendor_country'] )) {
                    $search_zone_id = $wpdb->get_results(
                    // phpcs:disable
                    "SELECT location_code 
                    FROM {$wpdb->prefix}woocommerce_shipping_zone_locations"
                    );
                    $woocommerce_zone_ids = is_array( $search_zone_id ) ? wp_list_pluck( $search_zone_id, 'location_code' ) : array();
                    if ( in_array($request['vendor_country'], $woocommerce_zone_ids) ) {
                        $country_code = $request['vendor_country'];
                        $search_zone = $wpdb->get_results(
                            // phpcs:disable
                            $wpdb->prepare(

                            "SELECT zone_id 
                            FROM {$wpdb->prefix}woocommerce_shipping_zone_locations where location_code = %s", $country_code
                            )
                        );
                        $zone_id = $search_zone[0]->zone_id;
                        $get_vendor = true;
                    }
                }
                $search_results = $wpdb->get_results(
                    "SELECT location_code
                    FROM {$wpdb->prefix}mvx_shipping_zone_locations"
                    );
                foreach ($search_results as $key => $value) {
                  if ( $value->location_code == $location_code ) {
                    $get_vendor = true;
                  }
                }
                $vendor_ids = array();
                if ( $get_vendor ) {
                    $shipping_vendor = new MVX_Vendor_Query(array(
                        'number' => -1,
                        'shipping_zone' => $zone_id,
                        'shipping_location' => $location_code
                        ));
                    $vendor_ids = wp_list_pluck( $shipping_vendor->get_results(), 'ID' );
                }
                $mvx_vendor_ids = get_mvx_vendors(array(), 'ids');
                if ( empty( $vendor_ids ) ) {
                    $args['exclude'] = $mvx_vendor_ids;
                } else {
                    $args['include'] = $vendor_ids;
                }
                $vendor_args = wp_parse_args($args, $default);
            } else {
                $vendor_args = wp_parse_args($args, $default);
            }
            $query_paged = is_front_page() ? get_query_var('page') : get_query_var('paged');
            if( $query_paged ) :
                $current_page = max( 1, $query_paged );
                $offset = ($current_page - 1) * $vendor_args['number'];
                $vendor_args['offset'] = $offset;
            endif;
            
            if(!$ignore_pagi){
                $vendors_query = new WP_User_Query( $vendor_args );
            }else{
                unset($vendor_args['offset']);
                $vendor_args['number'] = -1;
                $vendors_query = new WP_User_Query( $vendor_args );
            }
            
            return $vendors_query;
            
        }

        /**
         * Output vendor list shortcode
         * @global object $MVX
         * @param array $atts
         */
        public static function output($atts) {
            global $MVX;
            $frontend_assets_path = $MVX->plugin_url . 'assets/frontend/';
            $frontend_assets_path = str_replace(array('http:', 'https:'), '', $frontend_assets_path);
            $suffix = defined('MVX_SCRIPT_DEBUG') && MVX_SCRIPT_DEBUG ? '' : '.min';
            wp_register_style('mvx_vendor_list', $frontend_assets_path . 'css/vendor-list' . $suffix . '.css', array(), $MVX->version);
            if (mvx_mapbox_api_enabled()) {
                $MVX->library->load_mapbox_api();
                wp_register_script('mvx_vendor_list', $frontend_assets_path . 'js/vendor-list' . $suffix . '.js', array('jquery'), $MVX->version, true);
            } else {
                $MVX->library->load_gmap_api();
                wp_register_script('mvx_vendor_list', $frontend_assets_path . 'js/vendor-list' . $suffix . '.js', array('jquery','mvx-gmaps-api'), $MVX->version, true);
            }            
            // country js
            wp_enqueue_script( 'wc-country-select' );
            wp_enqueue_script('mvx_country_state_js');

            wp_enqueue_script('frontend_js');
            wp_enqueue_script('mvx_vendor_list');
            wp_style_add_data('mvx_vendor_list', 'rtl', 'replace');
            wp_enqueue_style('mvx_vendor_list');
            wp_enqueue_style('dashicons');
            if( !apply_filters( 'mvx_load_default_vendor_list', false ) ){
                wp_register_style('mvx_vendor_list_new', $frontend_assets_path . 'css/vendor-nlist.css', array(), $MVX->version);
                wp_enqueue_style('mvx_vendor_list_new');
            }

            extract(shortcode_atts(array('orderby' => 'registered', 'order' => 'ASC'), $atts));
            $order_by = isset($_REQUEST['vendor_sort_type']) ? wc_clean($_REQUEST['vendor_sort_type']) : $orderby;
            
            $query = apply_filters('mvx_vendor_list_vendors_query_args', array(
                'number' => 12,
                'orderby' => $order_by, 
                'order' => $order,
            ));
            if($order_by == 'name'){
                $query['meta_key'] = '_vendor_page_title';
                $query['orderby'] = 'meta_value';
            }
            // backward supports
            $query = apply_filters('mvx_vendor_list_get_mvx_vendors_args', $query, $order_by, $_REQUEST, $atts);

            $vendors_query = self::get_vendors_query($query, $_REQUEST, apply_filters('mvx_vendor_list_ignore_pagination', false));
            $vendors = $vendors_query->get_results();
            $vendors_total = $vendors_query->get_total();
            if(isset($_REQUEST['mvx_vlist_center_lat']) && isset($_REQUEST['mvx_vlist_center_lng'])){
                $vendors_query_all = self::get_vendors_query($query, $_REQUEST, true);
                $vendors = $vendors_query_all->get_results();
            }
            // map data
            $listed_stores = mvx_get_vendor_list_map_store_data($vendors, $_REQUEST);

            if(isset($_REQUEST['mvx_vlist_center_lat']) && isset($_REQUEST['mvx_vlist_center_lng'])){
                $vendors = $listed_stores['vendors'];
                $vendors_total = count($listed_stores['vendors']);
            }

            $script_param = array(
                'mapbox_emable' => mvx_mapbox_api_enabled(),
                'stores'    => $listed_stores['stores'],
                'lang'      => array(
                    'geolocation_service_failed' => __('Error: The Geolocation service failed.', 'multivendorx'),
                    'geolocation_permission_denied' => __('Error: User denied the request for Geolocation.', 'multivendorx'),
                    'geolocation_position_unavailable' => __('Error: Location information is unavailable.', 'multivendorx'),
                    'geolocation_timeout' => __('Error: The request to get user location timed out.', 'multivendorx'),
                    'geolocation_doesnt_support' => __('Error: Your browser does not support geolocation.', 'multivendorx'),
                ),
                'map_data' => array(
                    'map_options' => array('zoom' => 10, 'mapTypeControlOptions' => array('mapTypeIds' => array('roadmap', 'satellite'))),
                    'marker_icon' => $MVX->plugin_url . 'assets/images/store-marker.png',
                    'map_style' => true,
                    'map_style_data'  => array(
                        array(
                            'stylers' => array(
                                array('saturation' => -100),
                                array('gamma' => 1),
                            ),
                        ),
                        array(
                            'featureType' => 'water',
                            'stylers' => array(
                                array('lightness' => -12)
                            )
                        ),
                    ),
                    'map_style_title' => __('Styled Map', 'multivendorx'),
                ),
                'autocomplete' => true,
            );
            $MVX->localize_script('mvx_vendor_list', apply_filters('mvx_vendor_list_script_data_params',$script_param, $_REQUEST));
            $radius = apply_filters('mvx_vendor_list_filter_radius_data', array(5,10,20,30,50));
            $big_pagi = 999999999; // need an unlikely integer
            $data = apply_filters('mvx_vendor_list_data', array(
                'total'   => ceil($vendors_total/$query['number']),
                'current' => is_front_page() ? max( 1, ( get_query_var('page') ) ) : max( 1, get_query_var('paged') ),
                'per_page' => $query['number'],
                //'base'    => get_pagenum_link(1) . '%_%',
                'base'    => str_replace( $big_pagi, '%#%', esc_url( get_pagenum_link( $big_pagi ) ) ),
                'format'  => 'page/%#%/',
                'vendors'   => $vendors,
                'vendor_total' => $vendors_total,
                'radius' => $radius,
                'request' => isset($_REQUEST) ? wc_clean($_REQUEST) : '',
            ));
            if ( apply_filters( 'mvx_load_default_vendor_list', false ) ) {
                $MVX->template->get_template('shortcode/vendor_lists.php', $data);
            } else {
                $GLOBALS['vendor_list'] = $data;
                $MVX->template->get_template('shortcode/vendor-list.php');
            }
        }
    }

}