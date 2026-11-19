<?php
/**
 * Portfolio component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */
namespace BCP\Portfolio;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	public function boot(): void {

		// Register custom post type on the 'init' hook.
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	/**
	 * Returns the labels for a portfolio post type.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string  $singular
	 * @param  string  $plural
	 * @param  string  $type     Post type name, passed on to the filter.
	 * @return array
	 */
	public function portfolio_labels( string $singular = '', string $plural = '', string $type = '' ): array {

		$singular = $singular ?: esc_html__( 'Portfolio',  'backdrop-custom-portfolio' );
		$plural   = $plural   ?: esc_html__( 'Portfolios', 'backdrop-custom-portfolio' );
		$lower    = function_exists( 'mb_strtolower' ) ? mb_strtolower( $plural ) : strtolower( $plural );

		/* translators: %s: post type name, e.g. Portfolio. */
		$new = esc_html__( 'New %s', 'backdrop-custom-portfolio' );

		$labels = [
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'add_new'               => sprintf( $new, $singular ),
			/* translators: %s: post type name. */
			'add_new_item'          => sprintf( esc_html__( 'Add New %s', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'edit_item'             => sprintf( esc_html__( 'Edit %s', 'backdrop-custom-portfolio' ), $singular ),
			'new_item'              => sprintf( $new, $singular ),
			/* translators: %s: post type name. */
			'view_item'             => sprintf( esc_html__( 'View %s', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name, plural. */
			'view_items'            => sprintf( esc_html__( 'View %s', 'backdrop-custom-portfolio' ), $plural ),
			/* translators: %s: post type name, plural. */
			'search_items'          => sprintf( esc_html__( 'Search %s', 'backdrop-custom-portfolio' ), $plural ),
			/* translators: %s: post type name, plural and lowercase. */
			'not_found'             => sprintf( esc_html__( 'No %s found', 'backdrop-custom-portfolio' ), $lower ),
			/* translators: %s: post type name, plural and lowercase. */
			'not_found_in_trash'    => sprintf( esc_html__( 'No %s found in trash', 'backdrop-custom-portfolio' ), $lower ),
			'all_items'             => $plural,
			/* translators: %s: post type name. */
			'archives'              => sprintf( esc_html__( '%s Archives', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'attributes'            => sprintf( esc_html__( '%s Attributes', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'featured_image'        => sprintf( esc_html__( '%s Image', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'set_featured_image'    => sprintf( esc_html__( 'Set %s image', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'remove_featured_image' => sprintf( esc_html__( 'Remove %s image', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'use_featured_image'    => sprintf( esc_html__( 'Use as %s image', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'insert_into_item'      => sprintf( esc_html__( 'Insert into %s', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'uploaded_to_this_item' => sprintf( esc_html__( 'Uploaded to this %s', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'filter_items_list'     => sprintf( esc_html__( 'Filter %s list', 'backdrop-custom-portfolio' ), $lower ),
			/* translators: %s: post type name. */
			'items_list_navigation' => sprintf( esc_html__( '%s list navigation', 'backdrop-custom-portfolio' ), $plural ),
			/* translators: %s: post type name. */
			'items_list'            => sprintf( esc_html__( '%s list', 'backdrop-custom-portfolio' ), $plural ),
			/* translators: %s: post type name. */
			'item_published'        => sprintf( esc_html__( '%s published.', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'item_published_privately' => sprintf( esc_html__( '%s published privately.', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'item_reverted_to_draft' => sprintf( esc_html__( '%s reverted to draft.', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'item_scheduled'        => sprintf( esc_html__( '%s scheduled.', 'backdrop-custom-portfolio' ), $singular ),
			/* translators: %s: post type name. */
			'item_updated'          => sprintf( esc_html__( '%s updated.', 'backdrop-custom-portfolio' ), $singular ),
		];

		return apply_filters( 'bcp/portfolio/labels', $labels, $type );
	}

	/**
	 * Registers every post type in the registry.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function register_post_type() {

		foreach ( bcp_get_post_types() as $type => $config ) {

			$args = [
				'labels'       => $this->portfolio_labels( $config['singular'], $config['plural'], $type ),
				'public'       => true,
				'has_archive'  => true,
				'menu_icon'    => $config['icon'] ?? 'dashicons-portfolio',
				'show_ui'      => true,
				'show_in_rest' => true,
				'supports'     => [ 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'page-attributes' ],
				'rewrite'      => [ 'with_front' => false, 'slug' => $config['rewrite'] ]
			];

			// Where the type appears in the admin menu: under the Portfolio
			// menu, on its own, or nowhere.
			$menu = $config['menu'] ?? 'portfolio';

			if ( 'own' === $menu ) {
				$args['show_in_menu'] = true;
			} elseif ( 'hidden' === $menu ) {
				$args['show_in_menu'] = false;
			} else {
				$args['show_in_menu'] = 'bcp-portfolio';
			}

			register_post_type( $type, apply_filters( 'bcp/portfolio/args', $args, $type ) );
		}
	}
}
