<?php
/**
 * Default scripts
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace BCP;

/**
 * Loads the admin stylesheet.
 *
 * @since  1.1.0
 * @access public
 * @return void
 */
function bcp_enqueue_admin_style(): void {

	$file = plugin_dir_path( __DIR__ ) . 'public/assets/css/screen.css';

	wp_enqueue_style(
		'bcp-admin',
		plugin_dir_url( __DIR__ ) . 'public/assets/css/screen.css',
		[],
		file_exists( $file ) ? filemtime( $file ) : null
	);
}

/**
 * Loads the admin stylesheet, but only on the list screens of the portfolio post
 * types so it doesn't touch the rest of the admin.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {

	if ( 'edit.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ! array_key_exists( $screen->post_type, bcp_get_post_types() ) ) {
		return;
	}

	bcp_enqueue_admin_style();
} );
