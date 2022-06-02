<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/admin-new-question.php
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
$question = isset( $question ) ? $question : '';
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<p style="text-align:<?php echo $text_align; ?>;" >
	<?php printf( esc_html__( "Greetings Admin,",  'multivendorx' ) ); ?><br><br>
	<?php printf( esc_html__( "A new query has added by your buyer - %s",  'multivendorx' ), $customer_name ); ?><br>
	<?php printf( esc_html__( "Query for : %s",  'multivendorx' ), $vendor->page_title ); ?><br>
	<?php printf( esc_html__( "Query : %s",  'multivendorx' ), $question ); ?><br>
    <?php 
    	$question_link = apply_filters( 'admin_question_redirect_link', admin_url( 'admin.php?page=mvx-to-do' ) ); 
        printf( esc_html__( "You can approve or reject query from here : %s",  'multivendorx' ), $question_link ); ?><br><br>

        <?php printf( esc_html__( 'Note: Quick replies help to maintain a friendly customer-buyer relationship', 'multivendorx' )); ?>
</p>

<?php do_action( 'mvx_email_footer' ); ?>


