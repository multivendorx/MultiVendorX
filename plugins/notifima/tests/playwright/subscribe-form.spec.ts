import { test, expect } from '@playwright/test';
import { loginAsWordPressUser } from './utils/login';

/**
 * Covers the plugin's single most important user-facing flow: a shopper subscribing to be
 * notified when an out-of-stock product is back in stock (FrontEnd::display_product_subscription_form()
 * + the /notifima/v1/subscribers REST endpoint + the SubscribeForm React block).
 *
 * By default (Install.php's is_guest_subscriptions_enable = 'logged_in') the form only renders
 * for a logged-in visitor, so every test here logs in first - this is deliberately the real,
 * out-of-the-box default, not a setting this suite changes to make testing easier.
 */
const PRODUCT_URL = '/product/notifima-e2e-out-of-stock-product/';

test.describe( 'Stock subscription form', () => {
	test.beforeEach( async ( { page } ) => {
		await loginAsWordPressUser( page );
	} );

	test( 'renders on an out-of-stock product page for a logged-in visitor', async ( {
		page,
	} ) => {
		await page.goto( PRODUCT_URL );

		await expect( page.locator( 'p.stock.out-of-stock' ) ).toHaveText(
			'Out of stock'
		);

		// SubscribeForm.tsx's own root element also carries the "notifima-subscribe-form" class,
		// nested one level inside the PHP-rendered container that mounts it (which already has
		// the same class) - so the plain class selector matches two elements. The outer one is
		// uniquely identifiable by data-product-id (only the PHP-rendered container has it).
		const form = page.locator( '.notifima-subscribe-form[data-product-id]' );
		await expect( form ).toBeVisible();
		await expect(
			form.getByPlaceholder( 'Enter your email' )
		).toBeVisible();
		await expect(
			form.getByRole( 'button', { name: 'Notify Me' } )
		).toBeVisible();
	} );

	test( 'subscribing shows a success message', async ( { page } ) => {
		await page.goto( PRODUCT_URL );

		const form = page.locator( '.notifima-subscribe-form[data-product-id]' );
		await form.getByPlaceholder( 'Enter your email' ).fill(
			'e2e-shopper@example.com'
		);
		await form.getByRole( 'button', { name: 'Notify Me' } ).click();

		await expect( form.locator( '.woocommerce-message' ) ).toContainText(
			'Thank you for expressing interest',
			{ timeout: 10_000 }
		);
	} );

	test( 'subscribing twice with the same email reports "already subscribed" and offers to unsubscribe', async ( {
		page,
	} ) => {
		await page.goto( PRODUCT_URL );

		const form = page.locator( '.notifima-subscribe-form[data-product-id]' );
		const emailInput = form.getByPlaceholder( 'Enter your email' );
		const submitButton = form.getByRole( 'button', {
			name: 'Notify Me',
		} );

		await emailInput.fill( 'e2e-repeat-shopper@example.com' );
		await submitButton.click();
		await expect( form.locator( '.woocommerce-message' ) ).toBeVisible( {
			timeout: 10_000,
		} );

		// Re-visit fresh (the form only re-renders on submit within the same page load, and a
		// full reload is the more realistic repeat-visit scenario anyway).
		await page.goto( PRODUCT_URL );
		await emailInput.fill( 'e2e-repeat-shopper@example.com' );
		await submitButton.click();

		await expect( form.locator( '.woocommerce-error' ) ).toContainText(
			'already registered',
			{ timeout: 10_000 }
		);
		await expect(
			form.getByRole( 'button', { name: 'Unsubscribe' } )
		).toBeVisible();
	} );
} );
