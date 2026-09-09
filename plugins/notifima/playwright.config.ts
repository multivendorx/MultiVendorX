import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright config for notifima's browser E2E suite.
 *
 * Targets the plugin's own wp-env instance (see .wp-env.json - WooCommerce + this plugin,
 * `testsEnvironment: false` so this runs against the regular dev site, not a separate wp-env
 * "tests" environment). `wp-env start` prints the actual URL/port on every run
 * ("WordPress development site started at http://..."); override WP_BASE_URL if it differs
 * from the default below.
 *
 * `pnpm run test:e2e` ensures wp-env is running before invoking Playwright (see
 * bin/setup-test-env.sh) - running `playwright test` directly still expects it already up, same
 * as before manually testing the plugin in a browser.
 */
export default defineConfig( {
	testDir: './tests/playwright',
	globalSetup: require.resolve( './tests/playwright/global-setup.ts' ),
	fullyParallel: true,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: 'list',
	timeout: 30_000,

	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},

	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
} );
