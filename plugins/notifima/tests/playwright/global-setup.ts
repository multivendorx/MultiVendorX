import { execFileSync } from 'node:child_process';
import path from 'node:path';

/**
 * Ensure the fixed-slug, out-of-stock test product exists before the suite runs (idempotent -
 * see tests/playwright/fixtures/ensure-test-product.php). Requires wp-env to already be running -
 * `pnpm run test:e2e` ensures that via bin/setup-test-env.sh before Playwright ever gets here;
 * running `playwright test` directly still needs it started manually first (`pnpm run env:start`).
 */
export default function globalSetup(): void {
	const wpEnvBin = path.resolve(
		__dirname,
		'../../../../../node_modules/.bin/wp-env'
	);

	execFileSync(
		'bash',
		[
			wpEnvBin,
			'run',
			'cli',
			'wp',
			'eval-file',
			'wp-content/plugins/notifima/tests/playwright/fixtures/ensure-test-product.php',
		],
		{
			cwd: path.resolve( __dirname, '../..' ),
			stdio: 'inherit',
			env: { ...process.env, NODE_OPTIONS: '--no-network-family-autoselection' },
		}
	);
}
