<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/customer-answer.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $MVX;
$text_align = is_rtl() ? 'right' : 'left';
$answer = isset( $answer ) ? $answer : '';
$product_id = isset( $product_id ) ? $product_id : '';
$product = wc_get_product($product_id);
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<p style="text-align:<?php echo $text_align; ?>;" >
<?php printf(esc_html__('Your question had been noted and answered by the vendor : %s', 'dc-woocommerce-multi-vendor'), $answer ); ?><br><br>
    <?php  printf(esc_html__('Kindly check if the reply is up to your satisfaction : %s', 'dc-woocommerce-multi-vendor'), esc_url($product->get_permalink()) );?>
</p>


<?php do_action( 'mvx_email_footer' ); ?>


