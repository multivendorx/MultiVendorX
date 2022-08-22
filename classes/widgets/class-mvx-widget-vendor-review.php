<?php
/**
 * MVX Vendor Quick Info Widget
 *
 * @author    Multivendor X
 * @category  Widgets
 * @package MultiVendorX/Widgets
 * @version   2.2.0
 * @extends   WP_Widget
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class MVX_Widget_Vendor_Review_Widget extends WP_Widget {
    /**
     * Construct
     */
    function __construct() {
        global $MVX, $wp_version;

        // Widget variable settings
        $this->widget_idbase = 'dc-vendor-vendor-review';
        $this->widget_title = __('MVX: Vendor Review', 'multivendorx');
        $this->widget_description = __('Add a vendor review widget on vendor\'s shop page and single product page.', 'multivendorx');
        $this->widget_cssclass = 'widget_mvx_vendor_review';

        // Widget settings
        $widget_ops = array('classname' => $this->widget_cssclass, 'description' => $this->widget_description);

        // Widget control settings
        $control_ops = array('width' => 250, 'height' => 350, 'id_base' => $this->widget_idbase);

        // Create the widget
        if ($wp_version >= 4.3) {
            parent::__construct($this->widget_idbase, $this->widget_title, $widget_ops, $control_ops);
        } else {
            $this->WP_Widget($this->widget_idbase, $this->widget_title, $widget_ops, $control_ops);
        }
    }

    /**
     * widget function.
     *
     * @see WP_Widget
     * @access public
     * @param array $args
     * @param array $instance
     * @return void
     */
    function widget($args, $instance) {
        global $MVX, $woocommerce;
        extract($args, EXTR_SKIP);
        $vendor_id = false;
        $vendor = false;
        $comments = array();
        $review_count = 0;
        // Only show current vendor widget when showing a vendor's product(s)
        $show_widget = true;
        if (is_singular('product')) {
            global $post;
            $vendor = get_mvx_product_vendors($post->ID);
            if (!$vendor) {
                $show_widget = false;
            }
        }
        if (!mvx_is_store_page()) {
            $show_widget = false;
        }
        if ($show_widget) {
            if (mvx_is_store_page()) {
                $vendor_id = mvx_find_shop_page_vendor();
                if ($vendor_id) {
                    $vendor = get_mvx_vendor($vendor_id);
                }
            }
            if ($vendor) {
                 $reviews_lists = $vendor->get_reviews_and_rating(0); 
                if(isset($reviews_lists) && count($reviews_lists) > 0) {
                    foreach($reviews_lists as $comment) {
                        $reviews_number = isset($instance['reviews_number']) ? $instance['reviews_number'] : 0;
                        if($review_count >= $reviews_number)
                        break;
                        $rating   = intval( get_comment_meta( $comment->comment_ID, 'vendor_rating', true ) );
                        if ( $rating && get_option( 'woocommerce_enable_review_rating' ) === 'yes' && $rating >= intval(apply_filters('mvx_vendor_rating_widget_set_avg',3)) ){
                            $review_count++;
                            $comments[] = $comment;
                        }
                    }
                }
               
                // Set up widget title
                if ( isset( $instance['title'] ) ) {
                    $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
                } else {
                    $title = false;
                }
                // Before widget (defined by themes)
                echo $before_widget;
                // Display the widget title if one was input (before and after defined by themes).
                if ($title) {
                    echo $before_title . $title . $after_title;
                }
                // Widget content
                // Action for plugins/themes to hook onto
                do_action($this->widget_cssclass . '_top');

                $MVX->template->get_template('widget/vendor-review.php', array('vendor' => $vendor,'comments' => $comments));

                // Action for plugins/themes to hook onto
                do_action($this->widget_cssclass . '_bottom');

                // After widget (defined by themes).
                echo $after_widget;
            }
        }
    }

    /**
     * update function.
     *
     * @see WP_Widget->update
     * @access public
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Sanitise inputs
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['reviews_number'] = isset($new_instance['reviews_number']) && $new_instance['reviews_number'] > 0 ? ($new_instance['reviews_number']) : 1;
        return $instance;
    }

    /**
     * The form on the widget control in the widget administration area
     * @since  1.0.0
     * @param  array $instance The settings for this instance.
     * @return void
     */
    public function form($instance) {
        global $MVX;
        // Set up the default widget settings
        $defaults = array(
            'title' => '',
            'reviews_number' => 3,
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title (optional):', 'multivendorx'); ?></label>
            <input type="text" name="<?php echo esc_attr($this->get_field_name('title')); ?>"  value="<?php echo esc_attr($instance['title']); ?>" class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('reviews_number')); ?>"><?php esc_html_e('Number of reviews', 'multivendorx') ?>:
                <input type="number" id="<?php echo esc_attr($this->get_field_id('reviews_number')); ?>" name="<?php echo esc_attr($this->get_field_name('reviews_number')); ?>" value="<?php echo esc_attr($instance['reviews_number']); ?>" class="widefat" min="1"/>
            </label>
        </p>
        <span class="description"><?php esc_html_e('This widget shows vendor reviews.', 'multivendorx') ?> </span>
        <?php
    }
}