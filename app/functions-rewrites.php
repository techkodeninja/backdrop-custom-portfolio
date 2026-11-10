<?php
/**
 * Default Rewrites
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

/**
 * Flushes the rewrite rules after the plugin is activated, once the portfolio
 * post type and taxonomies have been registered.
 *
 * @since  1.0.4
 * @access public
 * @return void
 */
add_action( 'init', function() {

	if ( get_option( 'bcp_flush_rewrite_rules' ) ) {
		flush_rewrite_rules();
		delete_option( 'bcp_flush_rewrite_rules' );
	}
}, 20 );

/**
 * Returns the project rewrite slug used for single projects.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function bcp_get_portfolio_rewrite_slug(): string {

	$slug = bcp_get_portfolio_rewrite_base();

	return apply_filters( 'bcp_get_project_rewrite_slug', $slug );
}
