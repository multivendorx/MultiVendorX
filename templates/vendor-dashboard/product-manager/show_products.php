<?php
$view_products_list_table_headers = array(
    'image'  => array('label' => __( 'Image', 'multivendorx' )),
    'name'   => array('label' => __( 'Name', 'multivendorx' )),
    'price'  => array('label' => __( 'Price', 'multivendorx' )),
    'action' => array('label' => __( 'Action', 'multivendorx' )),
);
?>
<table class='table table-striped table-bordered'style='width:100%;'>
    <tr>
    <?php 
        if($view_products_list_table_headers) :
            foreach ($view_products_list_table_headers as $key => $header) { ?>
                <th><?php if(isset($header['label'])) echo $header['label']; ?></th>         
            <?php }
        endif;
    ?>
    </tr>
<?php
if ( !empty($query->get_posts()) ) {
    foreach ( $query->get_posts() as $value_post ) {
        if ( $value_post->post_author != $user_id ) {
            $product_id= $value_post->ID;
            $row = array();
            $product = wc_get_product($product_id);
            echo '<tr>'. '<td>' . $row ['image'] = $product->get_image(apply_filters('mvx_vendor_product_list_image_size', array(40, 40))). '</td>' ;
            echo '<td>' .$row ['name'] = '<strong><a href="' . esc_url( $product->get_permalink() ) . '" target="_blank">'. $product->get_title().'</a></strong></td>';
            echo '<td>' .$row ['price'] =  $product->get_price_html() . '</td>' ;
            echo '<td>' .$row ['action'] = '<a href="javascript:void(0)" data-product_id="' . $product->get_id() . '" class="mvx-create-product-duplicate-btn btn btn-default">' . __('Sell yours', 'multivendorx') . '</a>' . '</td>' . '</tr>';
            }
        }
        echo '</table>';
    }
