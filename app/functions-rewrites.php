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
 * @since  1.1.0
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
 * Flags the rewrite rules for a flush when the settings change. The flush runs
 * on the next request, after the post types have been registered with the new
 * slugs.
 *
 * @since  1.1.0
 * @access public
 * @return void
 */
add_action( 'add_option_bcp_settings',    function() { update_option( 'bcp_flush_rewrite_rules', true ); } );
add_action( 'update_option_bcp_settings', function() { update_option( 'bcp_flush_rewrite_rules', true ); } );

/**
 * Flags the rewrite rules for a flush when the post type registry changes.
 * The flush runs on the next request, after the post types have been
 * registered with the new slugs.
 *
 * @since  2.0.0
 * @access public
 * @return void
 */
add_action( 'add_option_bcp_post_types',    function() { update_option( 'bcp_flush_rewrite_rules', true ); } );
add_action( 'update_option_bcp_post_types', function() { update_option( 'bcp_flush_rewrite_rules', true ); } );

/**
 * Returns the project rewrite slug used for single projects.
 *
 * @since       1.0.0
 * @deprecated  Use bcp_get_portfolio_rewrite_base() instead. The
 *              `bcp_get_project_rewrite_slug` filter still works.
 * @access public
 * @return string
 */
function bcp_get_portfolio_rewrite_slug(): string {

	return apply_filters( 'bcp_get_project_rewrite_slug', bcp_get_portfolio_rewrite_base() );
}

/**
 * Moves the rules of post types whose rewrite slug has more than one segment
 * (nested under another type's slug, such as `portfolio/themes`) to the top
 * of the rewrite rules.
 *
 * Without this, a parent type's attachment rule (`{parent}/[^/]+/([^/]+)`)
 * matches a nested type's single URL first — `portfolio/themes/mei-theme/`
 * reads as an attachment called "mei-theme" — and the single 404s even
 * though its own rule exists further down. The nested type's rules keep
 * their order relative to each other, so its feed and archive rules still
 * come before its single rule.
 *
 * @since  2.0.0
 * @access public
 * @param  array  $rules
 * @return array
 */
function bcp_reorder_nested_type_rules( array $rules ): array {

	$types = bcp_get_post_types();

	// Most specific (most segments) first, so a nested slug that is itself
	// a prefix of another nested slug still wins.
	uasort( $types, function( $a, $b ) {
		return substr_count( $b['rewrite'], '/' ) <=> substr_count( $a['rewrite'], '/' );
	} );

	$nested = [];

	foreach ( $types as $config ) {

		if ( substr_count( $config['rewrite'], '/' ) < 1 ) {
			continue;
		}

		$prefix = $config['rewrite'] . '/';

		foreach ( $rules as $regex => $query ) {

			if ( 0 === strpos( $regex, $prefix ) ) {
				$nested[ $regex ] = $query;
				unset( $rules[ $regex ] );
			}
		}
	}

	return $nested + $rules;
}

add_filter( 'rewrite_rules_array', 'bcp_reorder_nested_type_rules' );
