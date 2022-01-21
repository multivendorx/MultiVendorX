<?php
/**
 * The template for displaying vendor orders
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/vendor-orders.php
 *
 * @author 		Multivendor X
 * @package 	MVX/Templates
 * @version   2.2.0
 */
if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
global $woocommerce, $MVX;

$orders_list_table_headers = apply_filters('mvx_datatable_order_list_table_headers', array(
    'select_order'  => array('label' => '', 'class' => 'text-center'),
    'order_id'      => array('label' => __( 'Order ID', 'dc-woocommerce-multi-vendor' )),
    'order_date'    => array('label' => __( 'Date', 'dc-woocommerce-multi-vendor' )),
    'vendor_earning'=> array('label' => __( 'Earnings', 'dc-woocommerce-multi-vendor' )),
    'order_status'  => array('label' => __( 'Status', 'dc-woocommerce-multi-vendor' )),
    'action'        => array('label' => __( 'Action', 'dc-woocommerce-multi-vendor' )),
), get_current_user_id());
?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="order_search pull-right">
                <input type="text" class="pro_search_key no_input form-control inline-input" id="pro_search_key" name="search_keyword" />
                <button class="mvx_black_btn btn btn-secondary" type="button" id="pro_search_btn"><?php _e('Search', 'dc-woocommerce-multi-vendor'); ?></button>
            </div>
            <form name="mvx_vendor_dashboard_orders" method="POST" class="form-inline">
                <div class="form-group">
                    <input type="date" name="mvx_start_date_order" class="pickdate gap1 mvx_start_date_order form-control" placeholder="<?php esc_attr_e('from', 'dc-woocommerce-multi-vendor'); ?>" value="<?php echo isset($_POST['mvx_start_date_order']) ? wc_clean($_POST['mvx_start_date_order']) : date('Y-m-01'); ?>" />
                    <!-- <span class="between">&dash;</span> -->
                </div>
                <div class="form-group">
                    <input type="date" name="mvx_end_date_order" class="pickdate mvx_end_date_order form-control" placeholder="<?php esc_attr_e('to', 'dc-woocommerce-multi-vendor'); ?>" value="<?php echo isset($_POST['mvx_end_date_order']) ? wc_clean($_POST['mvx_end_date_order']) : date('Y-m-d'); ?>" />
                </div>
                <button class="mvx_black_btn btn btn-default" type="submit" name="mvx_order_submit"><?php esc_html_e('Show', 'dc-woocommerce-multi-vendor'); ?></button>
            </form>
            <form method="post" name="mvx_vendor_dashboard_completed_stat_export" id="mvx_order_list_form">
                <div class="order-filter-actions alignleft actions">
                    <select id="order_bulk_actions" name="bulk_action" class="bulk-actions form-control inline-input">
                        <option value=""><?php esc_html_e('Bulk Actions', 'dc-woocommerce-multi-vendor'); ?></option>
                        <?php
                        $disallow_vendor_order_status = get_mvx_vendor_settings('disallow_vendor_order_status', 'capabilities', 'product') && get_mvx_vendor_settings('disallow_vendor_order_status', 'capabilities', 'product') == 'Enable' ? true : false;
                        if ($disallow_vendor_order_status) {
                            unset($bulk_actions['mark_processing'], $bulk_actions['mark_on-hold'], $bulk_actions['mark_completed']);
                        }
                        $bulk_actions['bulk_mark_shipped'] = __('Bulk Mark Shipped', 'dc-woocommerce-multi-vendor');
                        if( $bulk_actions ) :
                            foreach ( $bulk_actions as $key => $action ) {
                                echo '<option value="' . $key . '">' . $action . '</option>';
                            }
                        endif;
                        ?>
                    </select>
                    <button class="mvx_black_btn btn btn-secondary" type="button" id="order_list_do_bulk_action"><?php esc_html_e('Apply', 'dc-woocommerce-multi-vendor'); ?></button>
                    <?php 
                    $filter_by_status = apply_filters( 'mvx_vendor_dashboard_order_filter_status_arr', array_merge( 
                        array( 'all' => __('All', 'dc-woocommerce-multi-vendor'), 'request_refund' => __('Request Refund', 'dc-woocommerce-multi-vendor') ), 
                        wc_get_order_statuses()
                    ) ); 
                    echo '<select id="filter_by_order_status" name="order_status" class="mvx-filter-dtdd mvx_filter_order_status form-control inline-input">';
                    if( $filter_by_status ) :
                    foreach ( $filter_by_status as $key => $status ) {
                        echo '<option value="' . $key . '">' . $status . '</option>';
                    }
                    endif;
                    echo '</select>';
                    ?>
                    <?php do_action( 'mvx_vendor_order_list_add_extra_filters', get_current_user_id() ); ?>
                    <button class="mvx_black_btn btn btn-secondary" type="button" id="order_list_do_filter"><?php esc_html_e('Filter', 'dc-woocommerce-multi-vendor'); ?></button>
                </div><br>
                <table class="table table-striped table-bordered" id="mvx-vendor-orders" style="width:100%;">
                    <thead>
                        <tr>
                        <?php 
                            if($orders_list_table_headers) :
                                foreach ($orders_list_table_headers as $key => $header) {
                                    if($key == 'select_order'){ ?>
                            <th class="<?php if(isset($header['class'])) echo $header['class']; ?>"><input type="checkbox" class="select_all_all" onchange="toggleAllCheckBox(this, 'mvx-vendor-orders');" /></th>
                                <?php }else{ ?>
                            <th class="<?php if(isset($header['class'])) echo $header['class']; ?>"><?php if(isset($header['label'])) echo $header['label']; ?></th>         
                                <?php }
                                }
                            endif;
                        ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            <?php if(apply_filters('can_mvx_vendor_export_orders_csv', true, get_current_vendor_id())) : ?>
            <div class="mvx-action-container">
                <input class="btn btn-default" type="submit" name="mvx_download_vendor_order_csv" value="<?php esc_attr_e('Download CSV', 'dc-woocommerce-multi-vendor') ?>" />
            </div>
            <?php endif; ?>
            <?php if (isset($_POST['mvx_start_date_order'])) : ?>
                <input type="hidden" name="mvx_start_date_order" value="<?php echo isset($_POST['mvx_start_date_order']) ? wc_clean($_POST['mvx_start_date_order']) : date('Y-m-d'); ?>" />
            <?php endif; ?>
            <?php if (isset($_POST['mvx_end_date_order'])) : ?>
                <input type="hidden" name="mvx_end_date_order" value="<?php echo isset($_POST['mvx_end_date_order']) ? wc_clean($_POST['mvx_end_date_order']) : date('Y-m-d'); ?>" />
            <?php endif; ?>    
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="marke-as-ship-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?php esc_html_e('Shipment Tracking Details', 'dc-woocommerce-multi-vendor'); ?></h4>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tracking_url"><?php esc_html_e('Enter Tracking Url', 'dc-woocommerce-multi-vendor'); ?> *</label>
                            <input type="url" class="form-control" id="email" name="tracking_url" required="">
                        </div>
                        <div class="form-group">
                            <label for="tracking_id"><?php esc_html_e('Enter Tracking ID', 'dc-woocommerce-multi-vendor'); ?> *</label>
                            <input type="text" class="form-control" id="pwd" name="tracking_id" required="">
                        </div>
                    </div>
                    <input type="hidden" name="order_id" id="mvx-marke-ship-order-id" />
                    <?php if (isset($_POST['mvx_start_date_order'])) : ?>
                        <input type="hidden" name="mvx_start_date_order" value="<?php echo wc_clean($_POST['mvx_start_date_order']); ?>" />
                    <?php endif; ?>
                    <?php if (isset($_POST['mvx_end_date_order'])) : ?>
                        <input type="hidden" name="mvx_end_date_order" value="<?php echo wc_clean($_POST['mvx_end_date_order']); ?>" />
                    <?php endif; ?>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="mvx-submit-mark-as-ship"><?php esc_html_e('Submit', 'dc-woocommerce-multi-vendor'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var orders_table;
        var columns = [];
        <?php if($orders_list_table_headers) {
        foreach ($orders_list_table_headers as $key => $header) { ?>
        obj = {};
        obj['data'] = '<?php echo esc_js($key); ?>';
        obj['className'] = '<?php if(isset($header['class'])) echo esc_js($header['class']); ?>';
        columns.push(obj);
        <?php }
        }
        ?>
        orders_table = $('#mvx-vendor-orders').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            responsive: true,
            drawCallback: function (settings) {
                if(settings.json.notices.length > 0 ){
                    $('.mvx-wrapper .notice-wrapper').html('');
                    $.each(settings.json.notices, function( index, notice ) {
                        if(notice.type == 'success'){
                            $('.mvx-wrapper .notice-wrapper').append('<div class="woocommerce-message" role="alert">'+notice.message+'</div>');
                        }else{
                            $('.mvx-wrapper .notice-wrapper').append('<div class="woocommerce-error" role="alert">'+notice.message+'</div>');
                        }
                    });
                }
            },
            language: {
                emptyTable: "<?php echo trim(__('No orders found!', 'dc-woocommerce-multi-vendor')); ?>",
                processing: "<?php echo trim(__('Processing...', 'dc-woocommerce-multi-vendor')); ?>",
                info: "<?php echo trim(__('Showing _START_ to _END_ of _TOTAL_ orders', 'dc-woocommerce-multi-vendor')); ?>",
                infoEmpty: "<?php echo trim(__('Showing 0 to 0 of 0 orders', 'dc-woocommerce-multi-vendor')); ?>",
                lengthMenu: "<?php echo trim(__('Number of rows _MENU_', 'dc-woocommerce-multi-vendor')); ?>",
                zeroRecords: "<?php echo trim(__('No matching orders found', 'dc-woocommerce-multi-vendor')); ?>",
                paginate: {
                    next: "<?php echo trim(__('Next', 'dc-woocommerce-multi-vendor')); ?>",
                    previous: "<?php echo trim(__('Previous', 'dc-woocommerce-multi-vendor')); ?>"
                }
            },
            ajax: {
                url: '<?php echo add_query_arg( 'action', 'mvx_datatable_get_vendor_orders', $MVX->ajax_url() ); ?>',
                type: "post",
                data: function (data) {
                    data.orders_filter_action = $('form#mvx_order_list_form').serialize();
                    data.start_date = '<?php echo $start_date; ?>';
                    data.end_date = '<?php echo $end_date; ?>';
                    data.bulk_action = $('#order_bulk_actions').val();
                    data.order_status = $('#filter_by_order_status').val();
                    data.search_keyword = $('#pro_search_key').val();
                },
                error: function(xhr, status, error) {
                    $("#mvx-vendor-orders tbody").append('<tr class="odd"><td valign="top" colspan="6" class="dataTables_empty" style="text-align:center;">'+error+' - <a href="javascript:window.location.reload();"><?php _e('Reload', 'dc-woocommerce-multi-vendor'); ?></a></td></tr>');
                    $("#mvx-vendor-orders_processing").css("display","none");
                }
            },
            columns: columns
        });
        new $.fn.dataTable.FixedHeader( orders_table );
        $(document).on('click', '#order_list_do_filter', function (e) {
            orders_table.ajax.reload();
        });
        $(document).on('click', '#order_list_do_bulk_action', function (e) {
            orders_table.ajax.reload();
        });
        // Bulk mark as shipped
        $(document).on('change', '#order_bulk_actions', function () {
            if ($(this).val() == 'bulk_mark_shipped') {
                $('#mvx-marke-ship-order-id').val($('form#mvx_order_list_form').serialize());
                $('#marke-as-ship-modal').modal('show');
            }
        });
        // order search
        $(document).on('click', '#pro_search_btn', function () {
            orders_table.ajax.reload();
        });
    });

    function mvxMarkeAsShip(self, order_id) {
        jQuery('#mvx-marke-ship-order-id').val(order_id);
        jQuery('#marke-as-ship-modal').modal('show');
    }
</script>