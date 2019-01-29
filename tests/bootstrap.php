<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Sample_Theme
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';


global $phpunit_theme_root, $phpunit_current_theme;
$phpunit_theme_root    = dirname( __FILE__ );
$phpunit_current_theme = 'sample-theme';

function _get_current_theme() {
	global $phpunit_current_theme;

	return $phpunit_current_theme;
}

function _get_theme_root() {
	global $phpunit_theme_root;

	return $phpunit_theme_root;
}

/**
 * Registers theme
 */
function _register_theme() {
	global $phpunit_theme_root;
	add_filter( 'theme_root', '_get_theme_root' );

	register_theme_directory( $phpunit_theme_root );

	add_filter( 'pre_option_template', '_get_current_theme' );
	add_filter( 'pre_option_stylesheet', '_get_current_theme' );
}

tests_add_filter( 'muplugins_loaded', '_register_theme' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
