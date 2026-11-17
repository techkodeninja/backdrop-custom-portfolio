<?php
/**
 * Manage component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace BCP\Portfolio\Manage;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	public function boot(): void {

		// Custom columns on the edit screen of every portfolio post type.
		foreach ( array_keys( bcp_get_post_types() ) as $type ) {
			add_filter( "manage_edit-{$type}_columns",        [ $this, 'columns' ]             );
			add_action( "manage_{$type}_posts_custom_column", [ $this, 'custom_column' ], 10, 2 );
		}
	}

	public function columns( $columns ) {

		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			return $columns;
		}

		// Put the thumbnail right after the title.
		$new_columns = [];

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['thumbnail'] = esc_html__( 'Thumbnail', 'backdrop-custom-portfolio' );
			}
		}

		return $new_columns;
	}

	public function custom_column( $column, $post_id ) {

		if ( 'thumbnail' === $column ) {
			echo get_the_post_thumbnail( $post_id, [ 60, 60 ] ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}
