import { test, expect } from '@playwright/test';
import { loginAsWordPressUser } from './utils/login';

/**
 * Smoke-tests the admin side: the Notifima top-level menu registers (Admin::register_admin_menus())
 * and its React app root actually mounts content (create_setting_page()'s
 * <div id="admin-main-wrapper">, hydrated by the free plugin's src/index.tsx - see
 * react-frontend.md). This is deliberately a boot check, not a feature test - it exists to
 * catch a broken build/enqueue/localize chain that PHPUnit (which never loads real JS in a
 * browser) cannot.
 */
test.describe( 'Admin dashboard', () => {
	test.beforeEach( async ( { page } ) => {
		await loginAsWordPressUser( page );
	} );

	test( 'Notifima menu is registered in wp-admin', async ( { page } ) => {
		await page.goto( '/wp-admin/' );

		await expect(
			page.locator( '#adminmenu' ).getByRole( 'link', { name: 'Notifima', exact: true } )
		).toBeVisible();
	} );

	test( 'the admin app mounts real content, not an empty root', async ( {
		page,
	} ) => {
		await page.goto( '/wp-admin/admin.php?page=notifima' );

		const root = page.locator( '#admin-main-wrapper' );
		await expect( root ).toBeAttached();

		// The root div is rendered empty by PHP (Admin::create_setting_page()) and only gains
		// content once React hydrates it - wait for that to actually happen rather than
		// asserting on any specific text, which would break on the first UI copy change.
		await expect
			.poll(
				async () => ( await root.innerHTML() ).trim().length,
				{ timeout: 10_000 }
			)
			.toBeGreaterThan( 0 );
	} );
} );
