<?php
/*
Plugin Name: Manage Inactive Subsites
Plugin URI: https://bitbucket.org/ritzpatel91/manage-inactive-subsites
Description: Manage inactive subsites in admin network settings
Version: 0.1
Author: Ritesh Patel
Author URI: http://riteshpatel.me/
Network: true
Text Domain: manage-inactive-subsites
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'MIS_VERSION' ) ) {
	define( 'MIS_VERSION', '0.1' );
}

if ( ! defined( 'MIS_TEXT_DOMAIN' ) ) {
	define( 'MIS_TEXT_DOMAIN', 'manage-inactive-subsites' );
}

if ( ! defined( 'MIS_PLUGIN_FILE' ) ) {
	define( 'MIS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MIS_BASE_NAME' ) ) {
	define( 'MIS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'MIS_PATH' ) ) {
	define( 'MIS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MIS_URL' ) ) {
	define( 'MIS_URL', plugin_dir_url( __FILE__ ) );
}

function manage_inactive_sites_init() {

	//let's get started
	require_once MIS_PATH . 'includes/class-mis-init.php';
	new MIS_Init();
}

add_action( 'plugins_loaded', 'manage_inactive_sites_init' );
