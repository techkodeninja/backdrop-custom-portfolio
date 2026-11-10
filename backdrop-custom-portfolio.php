<?php
/**
 * Plugin Name: Backdrop Custom Portfolio
 * Plugin URI:  https://luthemes.com/portfolio/backdrop-custom-portfolio
 * Description: A simple way to create custom portfolio.
 * Author:      Benjamin Lu
 * Author URI:  https://luthemes.com
 * Text Domain: backdrop-custom-portfolio
 * Domain Path: /languages
 * Version:     1.0.3
 * Requires CP: 1.0
 * Requires PHP: 7.4
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * 1.0 - Forbidden Access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 2.0 - Required Files
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * 3.0 - Activation and Deactivation
 *
 * The portfolio post type is registered on `init`, which has already run when a
 * plugin is activated. So activation only sets a flag, and the rewrite rules are
 * flushed on the next request, after the post type and taxonomies exist.
 */
register_activation_hook( __FILE__, function() {
	update_option( 'bcp_flush_rewrite_rules', true );
} );

register_deactivation_hook( __FILE__, function() {
	if ( function_exists( 'bcp_get_setting' ) ) {
		unregister_post_type( bcp_get_setting( 'portfolio_type' ) );
	}

	delete_option( 'bcp_flush_rewrite_rules' );
	flush_rewrite_rules();
} );