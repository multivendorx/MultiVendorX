#!/usr/bin/env bash
#
# Idempotent test-environment setup for notifima.
#
# Ensures whatever `composer test` (PHPUnit) and/or `pnpm test:e2e` (Playwright) need before they
# can run, without redoing work that's already done:
#   - a real WordPress core checkout at WP_CORE_DIR (default: ~/.cache/mvx-test-vendor/wordpress -
#     see tests/php/phpunit-wp-config.php, which reads the same variable/default)
#   - the PHPUnit MySQL container (notifima-test-mysql, matching tests/php/phpunit-wp-config.php's
#     default WP_DB_HOST/WP_DB_PORT)
#   - wp-env, for Playwright
#
# Usage: bin/setup-test-env.sh [phpunit|e2e|all]   (default: all)
#
# Nothing this script downloads/creates is written inside the repo - WordPress core goes outside
# it (WP_CORE_DIR), and the MySQL/wp-env containers are Docker state, not repo files.

set -euo pipefail

TARGET="${1:-all}"
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_ROOT="$(cd "$PLUGIN_DIR/../../.." && pwd)"

WP_CORE_DIR="${WP_CORE_DIR:-$HOME/.cache/mvx-test-vendor/wordpress}"
DB_CONTAINER="notifima-test-mysql"
DB_PORT="${WP_DB_PORT:-33061}"

ensure_wp_core() {
	if [ -f "$WP_CORE_DIR/wp-load.php" ]; then
		echo "WordPress core already present at $WP_CORE_DIR"
		return
	fi

	echo "Downloading WordPress core to $WP_CORE_DIR ..."
	mkdir -p "$WP_CORE_DIR"
	local tmp_zip
	tmp_zip="$(mktemp -d)/wordpress.zip"
	curl -sL -o "$tmp_zip" https://wordpress.org/latest.zip
	unzip -q "$tmp_zip" -d "$(dirname "$WP_CORE_DIR")"
	rm -f "$tmp_zip"
}

ensure_phpunit_mysql() {
	if docker exec "$DB_CONTAINER" mariadb -uroot -e "SELECT 1" >/dev/null 2>&1; then
		echo "PHPUnit MySQL container ($DB_CONTAINER) already running"
		return
	fi

	if docker ps -a --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
		echo "Starting existing $DB_CONTAINER container..."
		docker start "$DB_CONTAINER" >/dev/null
	else
		echo "Creating $DB_CONTAINER container..."
		docker run -d --name "$DB_CONTAINER" \
			-e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=yes \
			-e MARIADB_DATABASE=wordpress_test \
			-p "$DB_PORT:3306" \
			mariadb:lts >/dev/null
	fi

	echo -n "Waiting for $DB_CONTAINER to accept connections..."
	for _ in $(seq 1 30); do
		if docker exec "$DB_CONTAINER" mariadb -uroot -e "SELECT 1" >/dev/null 2>&1; then
			echo " ready"
			return
		fi
		echo -n "."
		sleep 1
	done
	echo
	echo "$DB_CONTAINER did not become ready in time" >&2
	exit 1
}

ensure_wp_env() {
	echo "Starting wp-env (no-op if already running)..."
	(
		cd "$PLUGIN_DIR"
		NODE_OPTIONS='--no-network-family-autoselection' \
			bash "$REPO_ROOT/node_modules/.bin/wp-env" start
	)

	# `wp-env start` returning doesn't guarantee the site answers requests promptly yet (a cold
	# container can be slow on its first few real HTTP hits) - Playwright's own 30s navigation
	# timeout has been observed to trip on this. Wait for a real response before handing off.
	local base_url="${WP_BASE_URL:-http://localhost:8888}"
	echo -n "Waiting for $base_url to respond..."
	for _ in $(seq 1 30); do
		if curl -s -o /dev/null "$base_url/wp-login.php"; then
			echo " ready"
			return
		fi
		echo -n "."
		sleep 1
	done
	echo
	echo "$base_url did not respond in time" >&2
	exit 1
}

case "$TARGET" in
	phpunit)
		ensure_wp_core
		ensure_phpunit_mysql
		;;
	e2e)
		ensure_wp_env
		;;
	all)
		ensure_wp_core
		ensure_phpunit_mysql
		ensure_wp_env
		;;
	*)
		echo "Usage: $0 [phpunit|e2e|all]" >&2
		exit 1
		;;
esac
