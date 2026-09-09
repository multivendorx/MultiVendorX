<?php
/**
 * WP core test config consumed by wp-phpunit's bootstrap (via WP_PHPUNIT__TESTS_CONFIG).
 *
 * Every value below can be overridden with an environment variable so the same test setup
 * works unmodified on any machine/CI runner - nothing here is specific to one developer's box.
 */

$wordpress_dir = getenv( 'WP_CORE_DIR' );
if ( ! $wordpress_dir ) {
	// Default: a WordPress core checkout kept outside the repo (see tests/php/bootstrap.php
	// for why - the repo itself must not carry a full WP install).
	$wordpress_dir = rtrim( getenv( 'HOME' ), '/\\' ) . '/.cache/mvx-test-vendor/wordpress';
}

/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define( 'ABSPATH', rtrim( $wordpress_dir, '/\\' ) . '/' );

define( 'WP_DEFAULT_THEME', 'default' );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// ** MySQL settings ** //
//
// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.
//
// DB_HOST defaults to 127.0.0.1 (forces TCP) rather than 'localhost' - PHP's mysqli/pdo_mysql
// drivers silently switch to a local Unix socket for the literal string 'localhost', which
// fails on a machine with no local MySQL socket (e.g. a DB reachable only via a Docker port).
$db_host = getenv( 'WP_DB_HOST' ) ?: '127.0.0.1';
$db_port = getenv( 'WP_DB_PORT' ) ?: '33061';

define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wordpress_test' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASS' ) ?: '' );
define( 'DB_HOST', $db_port ? "{$db_host}:{$db_port}" : $db_host );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'unit_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
