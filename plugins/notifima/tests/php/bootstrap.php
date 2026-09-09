<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Notifima
 */

define( 'NOTIFIMA_PLUGIN_DIR', dirname( __DIR__, 2 ) );

/**
 * Resolve the WP core test library (WP_UnitTestCase, factories, etc.). Checked in order:
 *   1. WP_TESTS_DIR env var - explicit override, works anywhere (local, CI).
 *   2. vendor/wp-phpunit/wp-phpunit - composer-managed (see composer.json), reproducible via
 *      `composer install`, no bin/install-wp-tests.sh / svn dependency needed.
 *   3. The classic /tmp/wordpress-tests-lib location used by WP-CLI's install-wp-tests.sh,
 *      for anyone who already has that script's output lying around.
 *
 * @return string
 */
function notifima_resolve_wp_tests_dir() {
	$env_override = getenv( 'WP_TESTS_DIR' );
	if ( $env_override && file_exists( $env_override . '/includes/functions.php' ) ) {
		return rtrim( $env_override, '/\\' );
	}

	$composer_managed = NOTIFIMA_PLUGIN_DIR . '/vendor/wp-phpunit/wp-phpunit';
	if ( file_exists( $composer_managed . '/includes/functions.php' ) ) {
		return $composer_managed;
	}

	return rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

/**
 * Resolve a WooCommerce checkout to test against without requiring one to be committed inside
 * the repo. Checked in order: WC_TEST_DIR env var, composer-managed
 * vendor/wpackagist-plugin/woocommerce, wp-env's own download cache, then the legacy
 * <repo-root>/woocommerce manual-placement path.
 *
 * @return string
 */
function notifima_resolve_test_wc_dir() {
	$env_override = getenv( 'WC_TEST_DIR' );
	if ( $env_override && file_exists( $env_override . '/woocommerce.php' ) ) {
		return $env_override;
	}

	$composer_managed = NOTIFIMA_PLUGIN_DIR . '/vendor/wpackagist-plugin/woocommerce';
	if ( file_exists( $composer_managed . '/woocommerce.php' ) ) {
		return $composer_managed;
	}

	$home_dir = getenv( 'HOME' );
	if ( $home_dir ) {
		$wp_env_matches = glob( $home_dir . '/.wp-env/wp-env-*/woocommerce*/woocommerce.php' );
		if ( ! empty( $wp_env_matches ) ) {
			return dirname( $wp_env_matches[0] );
		}
	}

	return dirname( NOTIFIMA_PLUGIN_DIR, 3 ) . '/woocommerce';
}

define( 'TEST_WC_DIR', notifima_resolve_test_wc_dir() );

$_tests_dir = notifima_resolve_wp_tests_dir();

echo 'Notifima plugin dir : ' . NOTIFIMA_PLUGIN_DIR . "\n";
echo 'WP tests dir        : ' . $_tests_dir . "\n";
echo 'test wc dir         : ' . TEST_WC_DIR . "\n";

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false === $_phpunit_polyfills_path ) {
	$_phpunit_polyfills_path = NOTIFIMA_PLUGIN_DIR . '/vendor/yoast/phpunit-polyfills';
}
if ( $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php - run 'composer install' in this plugin directory first." . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested (and WooCommerce, which it depends on).
 *
 * @return void
 */
function _manually_load_plugin() {
	require TEST_WC_DIR . '/woocommerce.php';
	require NOTIFIMA_PLUGIN_DIR . '/product_stock_alert.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Truncate this plugin's custom tables between test runs so no stale data leaks between suites.
 *
 * @return void
 */
function notifima_truncate_table_data() {
	$tables = array(
		'notifima_subscribers',
	);
	global $wpdb;
	foreach ( $tables as $table_name ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}{$table_name}" );
	}
}

/**
 * Install WooCommerce's schema/data into the test database.
 *
 * @return void
 */
function install_wc() {
	define( 'WP_UNINSTALL_PLUGIN', true );
	define( 'WC_REMOVE_ALL_DATA', true );

	include TEST_WC_DIR . '/uninstall.php';

	WC_Install::install();

	// Reload capabilities after install, see https://core.trac.wordpress.org/ticket/28374.
	if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
		$GLOBALS['wp_roles']->reinit();
	} else {
		$GLOBALS['wp_roles'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_roles();
	}

	echo esc_html( 'Installing WooCommerce...' . PHP_EOL );
}

/**
 * Install Notifima's own schema into the test database.
 *
 * @return void
 */
function install_notifima() {
	echo 'Installing Notifima...' . PHP_EOL;
	notifima_truncate_table_data();

	Notifima()->activate();
}

// install WC and Notifima
tests_add_filter( 'setup_theme', 'install_wc' );
tests_add_filter( 'setup_theme', 'install_notifima' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
