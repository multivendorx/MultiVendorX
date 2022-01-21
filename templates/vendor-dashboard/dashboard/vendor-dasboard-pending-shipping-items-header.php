<?php
/**
 * The template for displaying vendor dashboard
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/dashboard/vendor-dasboard-pending-shipping-items-header.php
 *
 * @author 		Multivendor X
 * @package 	MVX/Templates
 * @version   2.2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $woocommerce, $MVX;
?>
<tr>
	<td align="center" ><?php echo __('Product Name','dc-woocommerce-multi-vendor'); ?></td>
	<td  align="center" class="no_display" ><?php echo __('Order Date','dc-woocommerce-multi-vendor'); ?><br>
		<span style="font-size:12px;"><?php echo __('dd/mm','dc-woocommerce-multi-vendor'); ?></span></td>
	<td  align="center" class="no_display" ><?php echo __('L/B/H/W','dc-woocommerce-multi-vendor'); ?></td>
	<td align="left" ><?php echo __('Address','dc-woocommerce-multi-vendor'); ?></td>
	<td align="center" class="no_display" ><?php echo __('Charges','dc-woocommerce-multi-vendor'); ?></td>
</tr>
