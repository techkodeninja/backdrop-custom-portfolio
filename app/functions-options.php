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
 * Returns the portfolio rewrite base. Used as the URL prefix for the portfolio
 * categories and tags, and as the default prefix for new post types.
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
 * @since  2.0.0
 * @access public
 * @return array
 */
function bcp_get_default_settings(): array {

	return [
		'portfolio_title'			=> esc_html__( 'Portfolio', 'backdrop-custom-portfolio' ),
		'portfolio_description'		=> '',
		'portfolio_rewrite_base'	=> 'portfolio',
		'portfolio_category'		=> 'portfolio-category',
		'portfolio_tag'				=> 'portfolio-tag',
	];
}

/**
 * Slugs that a post type can't use because ClassicPress already uses them.
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
 * Returns the post type name for a user-created post type slug. Post type
 * names are limited to 20 characters, so the slug is trimmed to fit.
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
 * Where a post type shows up in the admin menu: `portfolio` (under the
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
 * Returns the default post type registry: Portfolio on its own.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function bcp_get_default_post_types(): array {

	return [
		'backdrop-portfolio' => [
			'slug'     => 'portfolio',
			'singular' => esc_html__( 'Portfolio',  'backdrop-custom-portfolio' ),
			'plural'   => esc_html__( 'Portfolios', 'backdrop-custom-portfolio' ),
			'rewrite'  => '',
			'menu'     => 'portfolio',
			'icon'     => 'dashicons-portfolio',
		],
	];
}

/**
 * Moves the 1.x settings into the post type registry the first time the new
 * code runs. The main type and the extra types become registry rows with the
 * same post type names; the extra types keep the root-level URL they had, as
 * a rewrite override. The shared settings keep their option, in the new shape
 * (the old `portfolio_type` and `extra_types` keys are gone), and the rewrite
 * rules are flagged for a flush.
 *
 * Runs once, guarded by the registry option existing. A fresh install just
 * seeds the default registry.
 *
 * @since  2.0.0
 * @access public
 * @return void
 */
function bcp_maybe_migrate_post_types(): void {

	if ( false !== get_option( 'bcp_post_types', false ) ) {
		return;
	}

	$old = get_option( 'bcp_settings', [] );

	if ( is_array( $old ) && ( ! empty( $old['portfolio_type'] ) || ! empty( $old['extra_types'] ) ) ) {

		$base  = isset( $old['portfolio_rewrite_base'] ) && is_scalar( $old['portfolio_rewrite_base'] ) ? (string) $old['portfolio_rewrite_base'] : 'portfolio';
		$base  = '' !== $base ? $base : 'portfolio';
		$types = [];

		// The main type: no rewrite override, so its archive is the base.
		$main = ! empty( $old['portfolio_type'] ) && is_scalar( $old['portfolio_type'] ) ? (string) $old['portfolio_type'] : 'backdrop-portfolio';

		$types[ $main ] = [
			'slug'     => $base,
			'singular' => esc_html__( 'Portfolio',  'backdrop-custom-portfolio' ),
			'plural'   => esc_html__( 'Portfolios', 'backdrop-custom-portfolio' ),
			'rewrite'  => '',
			'menu'     => 'portfolio',
			'icon'     => 'dashicons-portfolio',
		];

		// Extra types keep the root-level URL they had in 1.x: their rewrite
		// was the bare slug, so it is stored as an override.
		foreach ( (array) ( $old['extra_types'] ?? [] ) as $row ) {

			if ( ! is_array( $row ) ) {
				continue;
			}

			$slug = sanitize_title( $row['slug'] ?? '' );

			if ( '' === $slug ) {
				continue;
			}

			$name = bcp_get_extra_type_name( $slug );

			if ( isset( $types[ $name ] ) ) {
				continue;
			}

			$singular = ! empty( $row['singular'] ) ? $row['singular'] : ucfirst( $slug );
			$plural   = ! empty( $row['plural'] )   ? $row['plural']   : $singular;
			$menu     = $row['menu'] ?? 'portfolio';

			$types[ $name ] = [
				'slug'     => $slug,
				'singular' => $singular,
				'plural'   => $plural,
				'rewrite'  => $slug,
				'menu'     => in_array( $menu, bcp_get_menu_choices(), true ) ? $menu : 'portfolio',
				'icon'     => 'dashicons-portfolio',
			];
		}
	} else {
		$types = bcp_get_default_post_types();
	}

	update_option( 'bcp_post_types', $types );

	// The shared settings keep their option, in the new shape.
	$settings = bcp_get_settings();

	update_option( 'bcp_settings', [
		'portfolio_title'        => $settings['portfolio_title'],
		'portfolio_description'  => $settings['portfolio_description'],
		'portfolio_rewrite_base' => $settings['portfolio_rewrite_base'],
		'portfolio_category'     => $settings['portfolio_category'],
		'portfolio_tag'          => $settings['portfolio_tag'],
	] );

	update_option( 'bcp_flush_rewrite_rules', true );
}

/**
 * Returns the post type registry as stored, keyed by post type name. Each row
 * has `slug`, `singular`, `plural`, `rewrite`, `menu` and `icon` keys.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function bcp_get_post_type_rows(): array {

	bcp_maybe_migrate_post_types();

	$rows = get_option( 'bcp_post_types', [] );

	return is_array( $rows ) ? $rows : [];
}

/**
 * Returns the URL slug for a post type row. An explicit rewrite is used as
 * is; otherwise the type lives under the portfolio base — unless its slug is
 * the base itself, in which case it IS the base archive.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $slug     The type's URL slug.
 * @param  string  $rewrite  The stored rewrite override, '' for the default.
 * @param  string  $base     The portfolio rewrite base.
 * @return string
 */
function bcp_get_post_type_rewrite( string $slug, string $rewrite, string $base ): string {

	$rewrite = implode( '/', array_filter( array_map( 'sanitize_title', explode( '/', trim( $rewrite, '/' ) ) ) ) );

	if ( '' !== $rewrite ) {
		return $rewrite;
	}

	return ( '' === $base || $slug === $base ) ? $slug : $base . '/' . $slug;
}

/**
 * Validates one post type row from the manager form.
 *
 * Returns the cleaned row (or null when it cannot be saved), any error
 * messages, and the post type name. `$editing` is the post type name being
 * edited, '' when adding a new type. The name of an existing type never
 * changes; a new type's name is derived from its slug.
 *
 * @since  2.0.0
 * @access public
 * @param  array   $input    The submitted row.
 * @param  array   $rows     The current registry, keyed by post type name.
 * @param  string  $editing  The post type name being edited, '' to add one.
 * @return array
 */
function bcp_validate_post_type_row( array $input, array $rows, string $editing = '' ): array {

	$errors   = [];
	$base     = bcp_get_portfolio_rewrite_base();
	$reserved = bcp_get_reserved_slugs();

	// Slug.
	$slug = sanitize_title( $input['slug'] ?? '' );

	if ( '' === $slug ) {
		$errors[] = esc_html__( 'A slug is required.', 'backdrop-custom-portfolio' );
	} elseif ( in_array( $slug, $reserved, true ) ) {
		$errors[] = sprintf(
			/* translators: %s: the slug that was rejected. */
			esc_html__( '"%s" is a reserved slug.', 'backdrop-custom-portfolio' ),
			esc_html( $slug )
		);
	}

	// Name: derived from the slug for a new type, fixed when editing.
	if ( '' === $editing ) {
		$name = bcp_get_extra_type_name( $slug );

		if ( '' !== $slug && isset( $rows[ $name ] ) ) {
			$errors[] = sprintf(
				/* translators: 1: the slug that was rejected, 2: the post type name it shortens to. */
				esc_html__( 'The slug "%1$s" shortens to the post type name "%2$s", which is already in use.', 'backdrop-custom-portfolio' ),
				esc_html( $slug ),
				esc_html( $name )
			);
		}
	} else {
		$name = $editing;

		if ( ! isset( $rows[ $name ] ) ) {
			$errors[] = esc_html__( 'That post type no longer exists.', 'backdrop-custom-portfolio' );
		}
	}

	// Labels.
	$singular = sanitize_text_field( $input['singular'] ?? '' );
	$plural   = sanitize_text_field( $input['plural'] ?? '' );

	if ( '' === $singular ) {
		$singular = ucfirst( $slug );
	}

	if ( '' === $plural ) {
		$plural = $singular;
	}

	// Rewrite override: slug segments, so a path like "work/themes" works.
	$rewrite = implode( '/', array_filter( array_map( 'sanitize_title', explode( '/', trim( (string) ( $input['rewrite'] ?? '' ), '/' ) ) ) ) );

	// Menu and icon.
	$menu = $input['menu'] ?? 'portfolio';

	if ( ! in_array( $menu, bcp_get_menu_choices(), true ) ) {
		$menu = 'portfolio';
	}

	$icon = sanitize_text_field( $input['icon'] ?? '' );
	$icon = '' !== $icon ? $icon : 'dashicons-portfolio';

	// The effective rewrite must be unique and must not start with a
	// reserved slug.
	if ( empty( $errors ) ) {
		$effective = bcp_get_post_type_rewrite( $slug, $rewrite, $base );
		$first     = strtok( $effective, '/' );

		if ( in_array( $first, $reserved, true ) ) {
			$errors[] = sprintf(
				/* translators: %s: the rewrite slug that was rejected. */
				esc_html__( 'The rewrite "%s" starts with a reserved slug.', 'backdrop-custom-portfolio' ),
				esc_html( $effective )
			);
		}

		foreach ( $rows as $other_name => $other_row ) {

			if ( $other_name === $editing || ! is_array( $other_row ) ) {
				continue;
			}

			$other = bcp_get_post_type_rewrite(
				sanitize_title( $other_row['slug'] ?? '' ),
				(string) ( $other_row['rewrite'] ?? '' ),
				$base
			);

			if ( $other === $effective ) {
				$errors[] = sprintf(
					/* translators: 1: the rewrite slug, 2: the post type name already using it. */
					esc_html__( 'The rewrite "%1$s" is already used by "%2$s".', 'backdrop-custom-portfolio' ),
					esc_html( $effective ),
					esc_html( $other_name )
				);
				break;
			}
		}
	}

	if ( ! empty( $errors ) ) {
		return [ 'row' => null, 'errors' => $errors, 'name' => $name ?? '' ];
	}

	return [
		'row'    => [
			'slug'     => $slug,
			'singular' => $singular,
			'plural'   => $plural,
			'rewrite'  => $rewrite,
			'menu'     => $menu,
			'icon'     => $icon,
		],
		'errors' => [],
		'name'   => $name,
	];
}

/**
 * Returns every portfolio post type, keyed by post type name. Each entry has
 * `singular`, `plural`, `rewrite`, `menu` and `icon` keys. Use the
 * `bcp/post_types` filter to add more from code.
 *
 * @since  2.0.0
 * @access public
 * @return array
 */
function bcp_get_post_types(): array {

	$base  = bcp_get_portfolio_rewrite_base();
	$types = [];

	foreach ( bcp_get_post_type_rows() as $name => $row ) {

		$name = sanitize_key( $name );

		if ( '' === $name || isset( $types[ $name ] ) || ! is_array( $row ) ) {
			continue;
		}

		$slug = sanitize_title( $row['slug'] ?? '' );

		if ( '' === $slug ) {
			continue;
		}

		$singular = ! empty( $row['singular'] ) ? (string) $row['singular'] : ucfirst( $slug );
		$plural   = ! empty( $row['plural'] )   ? (string) $row['plural']   : $singular;
		$menu     = $row['menu'] ?? 'portfolio';
		$icon     = ! empty( $row['icon'] ) ? (string) $row['icon'] : 'dashicons-portfolio';

		$types[ $name ] = [
			'singular' => $singular,
			'plural'   => $plural,
			'rewrite'  => bcp_get_post_type_rewrite( $slug, (string) ( $row['rewrite'] ?? '' ), $base ),
			'menu'     => in_array( $menu, bcp_get_menu_choices(), true ) ? $menu : 'portfolio',
			'icon'     => $icon,
		];
	}

	return apply_filters( 'bcp/post_types', $types );
}
