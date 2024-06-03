<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/vendor-followed-customer.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   	3.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $MVX;
$text_align = is_rtl() ? 'right' : 'left';
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<p style="text-align:<?php echo $text_align; ?>;" >
	<?php printf( esc_html__( "Dear %s,",  'multivendorx' ), $vendor->page_title ); ?><br>
	<?php printf( esc_html__( "We hope this message finds you well.",  'multivendorx' ) ); ?><br>
	<?php printf( esc_html__( "We wanted to inform you that you have a new follower %s, who has chosen to follow your profile to stay updated on your offerings.",  'multivendorx' ), $customer->user_login ); ?><br>
	<?php printf( esc_html__( "This reflects positively on the quality and appeal of your products/services, and we encourage you to engage with your followers to further enhance your presence on our platform.",  'multivendorx' )); ?><br>
</p>

<?php do_action( 'mvx_email_footer' ); ?>
