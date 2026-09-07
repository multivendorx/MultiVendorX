import type { Page } from '@playwright/test';

/**
 * Log in through the real wp-login.php form.
 *
 * wp-env's default site admin credentials (see @wordpress/env's documented defaults) - this
 * suite doesn't create a separate customer account, since the plugin's "Notify me" form only
 * checks is_user_logged_in() (see FrontEnd::display_product_subscription_form()), not any
 * specific role.
 *
 * @param page     Playwright page.
 * @param username WP username. Defaults to wp-env's default admin user.
 * @param password WP password. Defaults to wp-env's default admin password.
 */
export async function loginAsWordPressUser(
	page: Page,
	username = 'admin',
	password = 'password'
): Promise< void > {
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', username );
	await page.fill( '#user_pass', password );
	await page.click( '#wp-submit' );
	await page.waitForURL( /wp-admin/ );
}
