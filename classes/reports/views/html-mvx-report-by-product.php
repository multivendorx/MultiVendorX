<?php
/**
 * Admin View: Report by Product (with date filters)
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $MVX;

?>

<div id="poststuff" class="woocommerce-reports-wide">
	<div class="postbox">
		<h3 class="stats_range">
			<ul>
				<?php
					foreach ( $ranges as $range => $name ) {
						echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . '"><a href="' . esc_url( remove_query_arg( array( 'start_date', 'end_date' ), add_query_arg( 'range', $range ) ) ) . '">' . $name . '</a></li>';
					}
				?>
				<li class="custom <?php echo $current_range == 'custom' ? 'active' : ''; ?>">
					<?php _e( 'Custom', 'multivendorx' ); ?>
					<form method="GET">
						<div>
							<?php
								// Maintain query string
								foreach ( $_GET as $key => $value ) {
									if ( is_array( $value ) ) {
										foreach ( $value as $v ) {
											echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '[]" value="' . esc_attr( sanitize_text_field( $v ) ) . '" />';
										}
									} else {
										echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" />';
									}
								}
							?>
							<input type="hidden" name="range" value="custom" />
							<input type="date" size="9" placeholder="<?php esc_attr_e('yyyy-mm-dd', 'multivendorx' ); ?>" value="<?php if ( ! empty( $_GET['start_date'] ) ) echo esc_attr( $_GET['start_date'] ); ?>" name="start_date" class="range_datepicker from" />
							<input type="date" size="9" placeholder="<?php esc_attr_e('yyyy-mm-dd', 'multivendorx' ); ?>" value="<?php if ( ! empty( $_GET['end_date'] ) ) echo esc_attr( $_GET['end_date'] ); ?>" name="end_date" class="range_datepicker to" />
							<input type="submit" class="button" value="<?php esc_attr_e( 'Go', 'multivendorx' ); ?>" />
						</div>
					</form>
				</li>
			</ul>
		</h3>
		<div class="left_align pad_left">
			<form name="search_product_form" method="post" action="">
				<p>
					<select id="search_product" name="search_product" class="wc-product-search" data-placeholder="<?php esc_attr_e('Search for a product...', 'multivendorx') ?>" data-action="mvx_json_search_products_and_variations" style="min-width:210px;">
						<?php echo $option; ?>
					</select> 
					<input type="button" style="vertical-align: top;" class="product_report_search submit button" value="<?php esc_attr_e( 'Show', 'multivendorx' ); ?>" />
					<?php do_action( 'mvx_frontend_report_product_filter', $start_date, $end_date ); ?>
				</p>
			</form>
		</div>
	</div>
	<div class="postbox sort_chart box_data">
                <div class="sorting_box">
                        <span><b><?php _e( 'Sort By : ', 'multivendorx' ); ?></b></span>
                        <select name="product_report_sort" class="product_report_sort">
                                <option value="total_sales"><?php _e( 'Total Sales', 'multivendorx' ); ?></option>
                                <option value="admin_earning"><?php _e( 'Admin Earnings', 'multivendorx' ); ?></option>
                                <option value="vendor_earning"><?php _e( 'Vendor Earnings', 'multivendorx' ); ?></option>
                        </select>
                        <input type="checkbox" class="low_to_high" name="low_to_high" value="checked" />
                        <button class="low_to_high_btn_product"><i class="dashicons dashicons-arrow-up-alt"></i></button>
                        <input type="checkbox" class="high_to_low" name="high_to_low" value="checked" checked />
                        <button class="high_to_low_btn_product"><i class="dashicons dashicons-arrow-down-alt"></i></button>
                </div>
		<div class="product_sort_chart">
			<?php echo $report_html; ?>
		</div>
	</div>
</div>
