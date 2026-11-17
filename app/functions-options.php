<?php
/**
 * Default options
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

/**
 * Returns the portfolio title.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function bcp_get_portfolio_title(): string {
	return apply_filters( 'bcp_get_portfolio_title', bcp_get_setting( 'portfolio_title' ) );
}

/**
 * Returns the portfolio description.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function bcp_get_portfolio_description(): string {
	return apply_filters( 'bcp_get_portfolio_description', bcp_get_setting( 'portfolio_description' ) );
}

/**
 * Returns the portfolio rewrite base. Used for the project archive and as a prefix for taxonomy,
 * author, and any other slugs.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function bcp_get_portfolio_rewrite_base(): string {

	return apply_filters( 'bcp_get_portfolio_rewrite_base', bcp_get_setting( 'portfolio_rewrite_base' ) );
}

/**
 * Returns all plugin settings merged with the defaults.
 *
 * @since  1.1.0
 * @access public
 * @return array
 */
function bcp_get_settings(): array {

	$defaults = bcp_get_default_settings();
	$settings = get_option( 'bcp_settings', $defaults );

	return wp_parse_args( is_array( $settings ) ? $settings : [], $defaults );
}

/**
 * Returns a plugin setting.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $setting
 * @return string
 */
function bcp_get_setting( string $setting ): string {

	$settings = bcp_get_settings();

	return isset( $settings[ $setting ] ) && is_scalar( $settings[ $setting ] ) ? (string) $settings[ $setting ] : '';
}

/**
 * Returns the default settings for the plugin.
 *
 * @since  0.1.0
 * @access public
 * @return array
 */
function bcp_get_default_settings(): array {

	return [
		'portfolio_title'			=> esc_html__( 'Portfolio', 'backdrop-custom-portfolio' ),
		'portfolio_description'		=> '',
		'portfolio_rewrite_base'	=> 'portfolio',
		'portfolio_type' 			=> 'backdrop-portfolio',
		'portfolio_category'		=> 'portfolio-category',
		'portfolio_tag'				=> 'portfolio-tag',
		'extra_types'				=> []
	];
}

/**
 * Slugs that an extra post type can't use because ClassicPress already uses them.
 *
 * @since  1.1.0
 * @access public
 * @return array
 */
function bcp_get_reserved_slugs(): array {

	return apply_filters( 'bcp_get_reserved_slugs', [
		'post', 'posts', 'page', 'pages', 'category', 'tag', 'author', 'attachment', 'feed',
		'search', 'comments', 'embed', 'wp-admin', 'wp-content', 'wp-includes', 'wp-json'
	] );
}

/**
 * Returns the post type name for an extra post type slug. Post type names are
 * limited to 20 characters, so the slug is trimmed to fit.
 *
 * @since  1.1.0
 * @access public
 * @param  string  $slug
 * @return string
 */
function bcp_get_extra_type_name( string $slug ): string {

	return 'bcp-' . rtrim( substr( $slug, 0, 16 ), '-' );
}

/**
 * Where an extra post type shows up in the admin menu: `portfolio` (under the main
 * Portfolio menu), `own` (its own top-level menu) or `hidden` (no menu entry).
 *
 * @since  1.1.0
 * @access public
 * @return array
 */
function bcp_get_menu_choices(): array {

	return [ 'portfolio', 'own', 'hidden' ];
}

/**
 * Returns every portfolio post type, keyed by post type name. The first one is
 * always the main portfolio; the rest are the extra types from the settings.
 * Use the `bcp/post_types` filter to add more from code.
 *
 * Each entry has `singular`, `plural` and `rewrite` keys, and extra types also have a
 * `menu` key (see `bcp_get_menu_choices()`).
 *
 * @since  1.1.0
 * @access public
 * @return array
 */
function bcp_get_post_types(): array {

	$settings = bcp_get_settings();
	$main     = bcp_get_setting( 'portfolio_type' );
	$base     = bcp_get_portfolio_rewrite_slug();

	$types = [
		$main => [
			'singular' => esc_html__( 'Portfolio',  'backdrop-custom-portfolio' ),
			'plural'   => esc_html__( 'Portfolios', 'backdrop-custom-portfolio' ),
			'rewrite'  => $base,
			'main'     => true,
		],
	];

	foreach ( (array) $settings['extra_types'] as $row ) {

		$slug = sanitize_title( $row['slug'] ?? '' );

		if ( '' === $slug || $slug === $base || in_array( $slug, bcp_get_reserved_slugs(), true ) ) {
			continue;
		}

		$name = bcp_get_extra_type_name( $slug );

		if ( isset( $types[ $name ] ) ) {
			continue;
		}

		$singular = ! empty( $row['singular'] ) ? $row['singular'] : ucfirst( $slug );
		$plural   = ! empty( $row['plural'] )   ? $row['plural']   : $singular;

		$menu = $row['menu'] ?? 'portfolio';

		$types[ $name ] = [
			'singular' => $singular,
			'plural'   => $plural,
			'rewrite'  => $slug,
			'menu'     => in_array( $menu, bcp_get_menu_choices(), true ) ? $menu : 'portfolio',
		];
	}

	return apply_filters( 'bcp/post_types', $types );
}
