<?php

if (!defined('ABSPATH'))
    exit;

/**
 * @class 		MVX Taxonomy Class
 *
 * @version		2.2.0
 * @package		MVX
 * @author 		Multivendor X
 */
class MVX_Taxonomy {

    public $taxonomy_name;
    public $taxonomy_slug;
    public $mvx_spmv_taxonomy;
    public $mvx_gtin_taxonomy;

    public function __construct() {
        $permalinks = get_option('dc_vendors_permalinks');
        $this->taxonomy_name = 'dc_vendor_shop';
        $this->taxonomy_slug = empty($permalinks['vendor_shop_base']) ? _x('vendor', 'slug', 'dc-woocommerce-multi-vendor') : $permalinks['vendor_shop_base'];
        $this->register_post_taxonomy();
        //add_action('created_term', array($this, 'created_term'), 10, 3);
        add_filter('get_the_terms', array(&$this, 'mvx_get_the_terms'), 10, 3);
        // register MVX single product multiple vendors (SPMV) taxonomy
        $this->init_mvx_spmv_taxonomy();
        // register MVX GTIN
        if (get_mvx_vendor_settings('is_gtin_enable', 'general') == 'Enable') {
            $this->init_mvx_gtin_taxonomy();
        }
    }

    /**
     * Register MVX taxonomy
     *
     * @author Multivendor X
     * @access private
     * @package MVX
     */
    public function register_post_taxonomy() {
        $labels = array(
            'name' => apply_filters('mvx_vendor_taxonomy_name', __('Vendor', 'dc-woocommerce-multi-vendor')),
            'singular_name' => __('Vendor', 'dc-woocommerce-multi-vendor'),
            'menu_name' => __('Vendors', 'dc-woocommerce-multi-vendor'),
            'search_items' => __('Search Vendors', 'dc-woocommerce-multi-vendor'),
            'all_items' => __('All Vendors', 'dc-woocommerce-multi-vendor'),
            'parent_item' => __('Parent Vendor', 'dc-woocommerce-multi-vendor'),
            'parent_item_colon' => __('Parent Vendor:', 'dc-woocommerce-multi-vendor'),
            'view_item' => __('View Vendor', 'dc-woocommerce-multi-vendor'),
            'edit_item' => __('Edit Vendor', 'dc-woocommerce-multi-vendor'),
            'update_item' => __('Update Vendor', 'dc-woocommerce-multi-vendor'),
            'add_new_item' => __('Add New Vendor', 'dc-woocommerce-multi-vendor'),
            'new_item_name' => __('New Vendor Name', 'dc-woocommerce-multi-vendor'),
            'popular_items' => __('Popular Vendors', 'dc-woocommerce-multi-vendor'),
            'separate_items_with_commas' => __('Separate vendors with commas', 'dc-woocommerce-multi-vendor'),
            'add_or_remove_items' => __('Add or remove vendors', 'dc-woocommerce-multi-vendor'),
            'choose_from_most_used' => __('Choose from most used vendors', 'dc-woocommerce-multi-vendor'),
            'not_found' => __('No vendors found', 'dc-woocommerce-multi-vendor'),
        );

        $vendor_slug = apply_filters('mvx_vendor_slug', $this->taxonomy_slug);

        $args = array(
            'public' => true,
            'hierarchical' => false,
            'rewrite' => array('slug' => $vendor_slug),
            'show_admin_column' => true,
            'show_ui' => false,
            'labels' => $labels
        );
        register_taxonomy($this->taxonomy_name, 'product', $args);
    }

    /**
     * Function created_term
     */
    function created_term($term_id, $tt_id, $taxonomy) {
        if ($taxonomy == $this->taxonomy_name) {
            $term = get_term_by('id', $term_id, $this->taxonomy_name, 'ARRAY_A');
            $random_password = wp_generate_password(12);
            $unique_username = $this->generate_unique_username($term['name']);
            $user_id = wp_create_user($unique_username, $random_password);
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('dc_vendor');
            }
        }
    }

    /**
     * Function generate_unique_username
     */
    function generate_unique_username($term_name, $count = '') {
        if (!username_exists($term_name . $count)) {
            return $term_name . $count;
        }

        $count = ( $count == '' ) ? 1 : absint($count) + 1;

        $this->generate_unique_username($term_name, $count);
    }
    /**
     * Prevent term display in woocommerce product page if not vendor
     * 
     * @param array of WP_Term $terms
     * @param int $post_id
     * @param string $taxonomy
     * @return array of WP_Term
     */
    public function mvx_get_the_terms($terms, $post_id, $taxonomy) {
        if ($taxonomy == $this->taxonomy_name && get_post_type($post_id) == 'product' && $terms) {
            foreach ($terms as $index => $term) {
                $term_id = $term->term_id;
                $vendor = get_mvx_vendor_by_term($term_id);
                if (!$vendor) {
                    unset($terms[$index]);
                }
            }
        }
        return $terms;
    }
    
    public function init_mvx_spmv_taxonomy(){
        // register MVX single product multiple vendors (SPMV) taxonomy
        $this->mvx_spmv_taxonomy = apply_filters('mvx_spmv_taxonomy_slug', 'mvx_spmv');
        register_taxonomy(
            $this->mvx_spmv_taxonomy,
            'product',
            array(
                'label' => __( 'MVX SPMV', 'dc-woocommerce-multi-vendor' ),
                'public' => false,
                'rewrite' => false,
                'hierarchical' => false,
                'show_admin_column' => false,
                'show_ui' => false,
            )
        );
        
        // Add default spmv terms
        $mvx_spmv_default_terms = apply_filters('mvx_spmv_default_terms', array(
            'min-price' => array(
                'label'=> __('Min Price', 'dc-woocommerce-multi-vendor'), 
                'description' => __('Used for minimum price products under all single product multi vendor concept.', 'dc-woocommerce-multi-vendor'),
            ),
            'max-price' => array(
                'label'=> __('Max Price', 'dc-woocommerce-multi-vendor'), 
                'description' => __('Used for maximum price products under all single product multi vendor concept.', 'dc-woocommerce-multi-vendor'),
            ),
            'top-rated-vendor' => array(
                'label'=> __('Top rated vendor', 'dc-woocommerce-multi-vendor'), 
                'description' => __('Used for top rated vendor products under all single product multi vendor concept.', 'dc-woocommerce-multi-vendor'),
            ),
        ));
        
        if( $mvx_spmv_default_terms ) :
            foreach ($mvx_spmv_default_terms as $slug => $term_data) {
                $name = isset($term_data['label']) ? $term_data['label'] : $slug;
                $desc = isset($term_data['description']) ? $term_data['description'] : '';
                $term = term_exists( $name, $this->mvx_spmv_taxonomy );
                
                if ( 0 === $term || NULL === $term ) { 
                    wp_insert_term(
                        $name, // the term 
                        $this->mvx_spmv_taxonomy, // the taxonomy
                        array(
                            'description'=> $desc,
                            'slug' => $slug,
                            'parent'=> $term 
                        )
                    );
                }
            }
        endif;
    }
    
    public function init_mvx_gtin_taxonomy(){
        // register GTIN taxonomy
        $this->mvx_gtin_taxonomy = apply_filters('mvx_gtin_taxonomy_slug', 'mvx_gtin');
        register_taxonomy(
            $this->mvx_gtin_taxonomy,
            'product',
            array(
                'label' => apply_filters('mvx_taxonomy_gtin_label_text',__( 'GTIN', 'dc-woocommerce-multi-vendor' )),
                'public' => false,
                'rewrite' => false,
                'hierarchical' => false,
                'show_admin_column' => false,
                'show_ui' => false,
            )
        );
        
        // Add default spmv terms
        $mvx_gtin_default_terms = apply_filters('mvx_gtin_default_terms', array(
            'upc'   => __( 'UPC', 'dc-woocommerce-multi-vendor' ),
            'ean'   => __( 'EAN', 'dc-woocommerce-multi-vendor' ),
            'isbn'  => __( 'ISBN', 'dc-woocommerce-multi-vendor' ),
            'issn'  => __( 'ISSN', 'dc-woocommerce-multi-vendor' ),
            'ismn'  => __( 'ISMN', 'dc-woocommerce-multi-vendor' ),
            'jan'   => __( 'JAN', 'dc-woocommerce-multi-vendor' ),
            'itf-14'=> __( 'ITF-14', 'dc-woocommerce-multi-vendor' ),
            'mpuin' => apply_filters( 'mvx_gtin_default_marketplace_unique_item_number_label', __( 'MPUIN', 'dc-woocommerce-multi-vendor' )),
            
        ));
        
        if( $mvx_gtin_default_terms ) :
            foreach ($mvx_gtin_default_terms as $slug => $label) {
                $name = isset($label) ? $label : $slug;
                $term = term_exists( $name, $this->mvx_gtin_taxonomy );
                
                if ( 0 === $term || NULL === $term ) { 
                    wp_insert_term(
                        $name, // the term 
                        $this->mvx_gtin_taxonomy, // the taxonomy
                        array(
                            'slug' => $slug,
                            'parent'=> $term 
                        )
                    );
                }
            }
        endif;
    }
    
    /**
     * Get SPMV terms
     * 
     * @param array $args
     * @return array of WP_Term
     */
    public function get_mvx_spmv_terms($args = array()) {
        $default = array(
            'taxonomy' => $this->mvx_spmv_taxonomy,
            'hide_empty' => false,
        );
        $args = wp_parse_args($args, $default);
        if( isset($args['taxonomy']) && $args['taxonomy'] != $this->mvx_spmv_taxonomy )
            $args['taxonomy'] = $this->mvx_spmv_taxonomy;
        $terms = get_terms( $args );
        return $terms;
    }
    
    /**
     * Get GTIN terms
     * 
     * @param array $args
     * @return array of WP_Term
     */
    public function get_mvx_gtin_terms($args = array()) {
        $default = array(
            'taxonomy' => $this->mvx_gtin_taxonomy,
            'hide_empty' => false,
        );
        $args = wp_parse_args($args, $default);
        if( isset($args['taxonomy']) && $args['taxonomy'] != $this->mvx_gtin_taxonomy )
            $args['taxonomy'] = $this->mvx_gtin_taxonomy;
        $terms = get_terms( $args );
        return $terms;
    }

}
