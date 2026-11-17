<?php
/**
 * Plugin Name: Backdrop Custom Portfolio
 * Plugin URI:  https://luthemes.com/portfolio/backdrop-custom-portfolio
 * Description: A simple way to create custom portfolio.
 * Author:      Benjamin Lu
 * Author URI:  https://luthemes.com
 * Text Domain: backdrop-custom-portfolio
 * Domain Path: /public/lang
 * Version:     1.1.0
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
 *
 * The framework is installed with Composer. If `vendor/` is missing (for example
 * when the plugin is cloned from GitHub and `composer install` hasn't been run),
 * show a notice instead of a fatal error.
 */
$bcp_autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( ! file_exists( $bcp_autoload ) ) {
	add_action( 'admin_notices', function() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Backdrop Custom Portfolio is missing its dependencies. Run "composer install" in the plugin folder, or install a release ZIP.', 'backdrop-custom-portfolio' )
		);
	} );

	return;
}

require_once $bcp_autoload;

/**
 * 3.0 - Translations
 */
add_action( 'init', function() {
	load_plugin_textdomain( 'backdrop-custom-portfolio', false, dirname( plugin_basename( __FILE__ ) ) . '/public/lang' );
} );

/**
 * 4.0 - Activation and Deactivation
 *
 * The portfolio post types are registered on `init`, which has already run when a
 * plugin is activated. So activation only sets a flag, and the rewrite rules are
 * flushed on the next request, after the post types and taxonomies exist.
 */
register_activation_hook( __FILE__, function() {
	update_option( 'bcp_flush_rewrite_rules', true );
} );

register_deactivation_hook( __FILE__, function() {
	if ( function_exists( 'bcp_get_post_types' ) ) {
		foreach ( array_keys( bcp_get_post_types() ) as $type ) {
			unregister_post_type( $type );
		}
	}

	delete_option( 'bcp_flush_rewrite_rules' );
	flush_rewrite_rules();
} );
