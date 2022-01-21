<?php

/*
 * The template for displaying vendor pending shipping table dashboard widget
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/dashboard-widgets/mvx_vendor_product_sales_report.php
 *
 * @author 	Multivendor X
 * @package 	MVX/Templates
 * @version   3.0.0
 */
if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
global $MVX;
$product_sales_report_table_headers = apply_filters('mvx_datatable_widget_product_sales_report_table_headers', array(
    'product'      => array('label' => __( 'Product', 'dc-woocommerce-multi-vendor' )),
    'revenue'    => array('label' => __( 'Revenue', 'dc-woocommerce-multi-vendor' )),
    'unique_purchase'=> array('label' => __( 'Unique Purchases', 'dc-woocommerce-multi-vendor' )),
), get_current_user_id());
?>
<table id="widget_product_sales_report" class="table table-striped product_sold_last_week table-bordered mvx-widget-dt" width="100%">
    <thead>
        <tr>
        <?php 
            if($product_sales_report_table_headers) :
                foreach ($product_sales_report_table_headers as $key => $header) { ?>
            <th class="<?php if(isset($header['class'])) echo $header['class']; ?>"><?php if(isset($header['label'])) echo $header['label']; ?></th>         
                <?php }
            endif;
        ?>
            <!--th><?php _e('Product', 'dc-woocommerce-multi-vendor'); ?></th>
            <th><?php _e('Revenue', 'dc-woocommerce-multi-vendor'); ?></th>
            <th><?php _e('Unique Purchases', 'dc-woocommerce-multi-vendor'); ?></th-->
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<script>
jQuery(document).ready(function($) {
    var product_sales_report_wgt;
    var columns = [];
    <?php if($product_sales_report_table_headers) {
     foreach ($product_sales_report_table_headers as $key => $header) { ?>
        obj = {};
        obj['data'] = '<?php echo esc_js($key); ?>';
        obj['className'] = '<?php if(isset($header['class'])) echo esc_js($header['class']); ?>';
        columns.push(obj);
     <?php }
        } ?>
    product_sales_report_wgt = $('#widget_product_sales_report').DataTable({
        ordering  : false,
        paging: false,
        info: false,
        searching  : false,
        processing: false,
        serverSide: true,
        responsive: true,
        language: {
            "emptyTable": "<?php echo trim(__('Not enough data.','dc-woocommerce-multi-vendor')); ?>",
            "zeroRecords": "<?php echo trim(__('Not enough data.','dc-woocommerce-multi-vendor')); ?>",
            
        },
        ajax:{
            url : '<?php echo add_query_arg( 'action', 'mvx_widget_vendor_product_sales_report', $MVX->ajax_url() ); ?>', 
            type: "post",
            error: function(xhr, status, error) {
                $("#widget_product_sales_report tbody").append('<tr class="odd"><td valign="top" colspan="<?php if(is_array($product_sales_report_table_headers)) count($product_sales_report_table_headers); ?>" class="dataTables_empty" style="text-align:center;">'+error+' - <a href="javascript:window.location.reload();"><?php _e('Reload', 'dc-woocommerce-multi-vendor'); ?></a></td></tr>');
                $("#widget_product_sales_report").css("display","none");
            }
        },
        columns: columns
    });
    new $.fn.dataTable.FixedHeader( product_sales_report_wgt );
});
</script>