<?php
/**
 *
 * Plugin Name:       Review Plugin
 * Plugin URI:       
 * Description:       This Plugin is used to review the products.
 * Version:           1.0
 * Author:            Yeshwanth Reddy Goli
 * Author URI:        
 * Text Domain:       Product-review
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_rp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rp-activator.php';
	rp_Activator::activate();
}

function deactivate_rp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rp-deactivator.php';
	rp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rp' );
register_deactivation_hook( __FILE__, 'deactivate_rp' );

function run_rp() {

	
	define( 'RP_PATH', dirname( __FILE__ ) );
	define( 'RP_SLUG', 'rp' );
	define( 'RP_CACHE_DISABLED', false );
	define( 'RP_BASENAME', plugin_basename( __FILE__ ) );

	$plugin = new RP();
	$plugin->run();

	require_once RP_PATH . '/includes/legacy.php';
	require_once RP_PATH . '/includes/functions.php';

	$vendor_file = RP_PATH . '/vendor/autoload_52.php';
	if ( is_readable( $vendor_file ) ) {
		require_once $vendor_file;
	}
	add_filter( 'pirate_parrot_log', 'rp_lite_register_parrot', 10, 1 );
	add_filter( 'themeisle_sdk_products', 'rp_lite_register_sdk' );
}


function rp_lite_register_parrot( $plugins ) {
	$plugins[] = RP_SLUG;
	return $plugins;
}


function rp_lite_register_sdk( $products ) {
	$products[] = __FILE__;

	return $products;
}

require( 'class-rp-autoloader.php' );
RP_Autoloader::define_namespaces( array( 'RP' ) );

spl_autoload_register( array( 'RP_Autoloader', 'loader' ) );

run_rp();

