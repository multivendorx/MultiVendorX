<?php
/**
 * Admin View: Notice - Untested extensions.
 *
 * @package WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


?>
<div id="wc_untested_extensions_modal">
	<div class="wc_untested_extensions_modal--content">
		<h1><?php esc_html_e( "Are you sure you're ready?", 'woocommerce' ); ?></h1>
		<div class="wc_plugin_upgrade_notice extensions_warning">
			
			<p><?php esc_html_e( 'We strongly recommend creating a backup of your site before updating.', 'woocommerce' ); ?> <a href="https://woocommerce.com/2017/05/create-use-backups-woocommerce/" target="_blank"><?php esc_html_e( 'Learn more', 'woocommerce' ); ?></a></p>

			<?php if ( current_user_can( 'update_plugins' ) ) : ?>
				<div class="actions">
					<a href="#" class="button button-secondary cancel"><?php esc_html_e( 'Cancel', 'woocommerce' ); ?></a>
					<a class="button button-primary accept" href="#"><?php esc_html_e( 'Update now', 'woocommerce' ); ?></a>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
