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
 * 2.0 - Plugin functions
 *
 * These only use WordPress core, so they are safe to load right away. They are
 * loaded here instead of through Composer's "files" autoload so the framework
 * bootstrap below can decide whether the bundled framework is needed at all.
 */
require_once plugin_dir_path( __FILE__ ) . 'app/functions-options.php';
require_once plugin_dir_path( __FILE__ ) . 'app/functions-rewrites.php';
require_once plugin_dir_path( __FILE__ ) . 'app/functions-scripts.php';

/**
 * 3.0 - Translations
 */
function bcp_load_textdomain(): void {
	load_plugin_textdomain( 'backdrop-custom-portfolio', false, dirname( plugin_basename( __FILE__ ) ) . '/public/lang' );
}

add_action( 'init', 'bcp_load_textdomain' );

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

/**
 * 5.0 - Bootstrap the Backdrop framework
 *
 * The plugin must work with Backdrop framework 1.x and 2.0 themes, and the two
 * lines cannot share one request: framework 1.x declares the `Backdrop\Attr`
 * helpers without guards, while 2.0 guards the same names in its separate
 * `backdrop-dev/attr` package, so loading both copies fatals with "Cannot
 * redeclare function Backdrop\Attr\attr()".
 *
 * So the bundled framework (1.x, in `vendor/`) is only loaded when no framework
 * is already present. A Backdrop theme loads its own framework in functions.php,
 * before `after_setup_theme` runs, so by the time this runs the theme's copy —
 * 1.x or 2.0 — is already there and this plugin reuses it. The plugin's own
 * code only uses the framework surface both lines share (`Application`,
 * `ServiceProvider`, `Bootable`, `booted()` / `app()`), so it runs unchanged
 * against either one.
 *
 * Runs late (priority 20) so a Backdrop theme has already booted its
 * application when there is one. If `after_setup_theme` has already fired
 * (for example, while the plugin is being activated), bootstrap right away.
 */
if ( did_action( 'after_setup_theme' ) ) {
	bcp_bootstrap();
} else {
	add_action( 'after_setup_theme', 'bcp_bootstrap', 20 );
}

function bcp_bootstrap(): void {

	// A framework is already loaded (the active theme's, 1.x or 2.0). Use it
	// and never touch the bundled copy.
	if ( class_exists( 'Backdrop\Core\Application' ) ) {
		bcp_register_autoloader();
	} else {
		// No theme framework: fall back to the bundled copy (1.x).
		$bcp_autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

		if ( ! file_exists( $bcp_autoload ) ) {
			add_action( 'admin_notices', function() {
				// Make sure the string is translated even if this runs
				// before the text domain loads on init.
				bcp_load_textdomain();

				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html__( 'Backdrop Custom Portfolio is missing its dependencies. Run "composer install" in the plugin folder, or install a release ZIP.', 'backdrop-custom-portfolio' )
				);
			} );

			return;
		}

		require_once $bcp_autoload;
	}

	$bcp = function_exists( 'Backdrop\booted' ) && Backdrop\booted() ? Backdrop\app() : new Backdrop\Core\Application();

	$bcp->provider( BCP\Portfolio\Provider::class );
	$bcp->provider( BCP\Portfolio\Taxonomies\Provider::class );

	if ( is_admin() ) {
		$bcp->provider( BCP\Portfolio\Manage\Provider::class );
		$bcp->provider( BCP\Portfolio\Settings\Provider::class );
	}

	do_action( 'bcp/bootstrap', $bcp );

	$bcp->boot();
}

/**
 * Registers a PSR-4 autoloader for the plugin's own classes (`BCP\`).
 *
 * Used when the theme's framework is reused, so the bundled autoloader — which
 * would also load the bundled framework — is never required.
 */
function bcp_register_autoloader(): void {

	spl_autoload_register( function( $class ) {

		if ( 0 !== strpos( $class, 'BCP\\' ) ) {
			return;
		}

		$file = plugin_dir_path( __FILE__ ) . 'app/' . str_replace( '\\', '/', substr( $class, 4 ) ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );
}
