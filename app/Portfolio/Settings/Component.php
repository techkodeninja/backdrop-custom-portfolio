<?php
/**
 * Settings component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace BCP\Portfolio\Settings;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	/**
	 * Settings page hook suffix.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	public $page_hook = '';

	/**
	 * Sets up custom admin menus.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function admin_menu() {

		// Create the settings page.
		$this->page_hook = add_submenu_page(
			'edit.php?post_type=' . bcp_get_setting( 'portfolio_type' ),
			esc_html__( 'Portfolio Settings', 'backdrop-custom-portfolio' ),
			esc_html__( 'Settings',           'backdrop-custom-portfolio' ),
			'manage_options',
			'bcp-settings',
			array( $this, 'settings_page' )
		);

		if ( $this->page_hook ) {

			// Register settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	/**
	 * Registers the plugin settings.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	function register_settings() {

		// Register the setting.
		register_setting( 'bcp_settings', 'bcp_settings', array( $this, 'validate_settings' ) );

		/* === Settings Sections === */

		add_settings_section( 'general',    esc_html__( 'General Settings', 'backdrop-custom-portfolio' ), array( $this, 'section_general'    ), $this->page_hook );
		add_settings_section( 'permalinks', esc_html__( 'Permalinks',       'backdrop-custom-portfolio' ), array( $this, 'section_permalinks' ), $this->page_hook );

		/* === Settings Fields === */

		// General section fields
		add_settings_field( 'portfolio_title',       esc_html__( 'Title',       'backdrop-custom-portfolio' ), array( $this, 'field_portfolio_title'       ), $this->page_hook, 'general' );
		add_settings_field( 'portfolio_description', esc_html__( 'Description', 'backdrop-custom-portfolio' ), array( $this, 'field_portfolio_description' ), $this->page_hook, 'general' );

		// Permalinks section fields.
		add_settings_field( 'portfolio_rewrite_base', esc_html__( 'Portfolio Base', 'backdrop-custom-portfolio' ), array( $this, 'field_portfolio_rewrite_base' ), $this->page_hook, 'permalinks' );

		// Extra post types section.
		add_settings_section( 'extra_types', esc_html__( 'Extra Post Types', 'backdrop-custom-portfolio' ), array( $this, 'section_extra_types' ), $this->page_hook );
		add_settings_field( 'extra_types', esc_html__( 'Post Types', 'backdrop-custom-portfolio' ), array( $this, 'field_extra_types' ), $this->page_hook, 'extra_types' );
	}

	/**
	 * Validates the plugin settings.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  mixed  $settings
	 * @return array
	 */
	function validate_settings( $settings ): array {

		$settings = is_array( $settings ) ? $settings : [];
		$current  = bcp_get_settings();
		$reserved = bcp_get_reserved_slugs();

		// Anything not on the form (post type and taxonomy names) is kept as is.
		$clean = $current;

		// Title.
		$title                    = isset( $settings['portfolio_title'] ) ? sanitize_text_field( $settings['portfolio_title'] ) : '';
		$clean['portfolio_title'] = '' !== $title ? $title : esc_html__( 'Portfolio', 'backdrop-custom-portfolio' );

		// Rewrite base: lowercase slug segments, no leading or trailing slashes.
		$base = isset( $settings['portfolio_rewrite_base'] ) ? sanitize_text_field( $settings['portfolio_rewrite_base'] ) : '';
		$base = implode( '/', array_filter( array_map( 'sanitize_title', explode( '/', trim( $base, '/' ) ) ) ) );
		$base = '' !== $base ? $base : 'portfolio';

		// Every segment of the base must be checked, not just the first one:
		// a base like "work/page" would otherwise slip a reserved slug in.
		$base_is_reserved = false;

		foreach ( explode( '/', $base ) as $segment ) {
			if ( in_array( $segment, $reserved, true ) ) {
				$base_is_reserved = true;
				break;
			}
		}

		if ( $base_is_reserved ) {
			add_settings_error( 'bcp_settings', 'bcp_reserved_base', sprintf(
				/* translators: %s: the slug that was rejected. */
				esc_html__( '"%s" is a reserved slug, so the portfolio base was not changed.', 'backdrop-custom-portfolio' ),
				esc_html( $base )
			) );
			$base = $current['portfolio_rewrite_base'];
		}

		$clean['portfolio_rewrite_base'] = $base;

		// Kill evil scripts.
		$description                    = isset( $settings['portfolio_description'] ) ? $settings['portfolio_description'] : '';
		$clean['portfolio_description'] = stripslashes( wp_filter_post_kses( addslashes( $description ) ) );

		// Extra post types. Rows without a slug are dropped, which is how one is removed.
		$extra = [];
		$names = [];
		$rows  = isset( $settings['extra_types'] ) && is_array( $settings['extra_types'] ) ? $settings['extra_types'] : [];

		foreach ( $rows as $row ) {

			$slug = sanitize_title( $row['slug'] ?? '' );

			if ( '' === $slug ) {
				continue;
			}

			if ( $slug === $base || in_array( $slug, $reserved, true ) || isset( $extra[ $slug ] ) ) {
				add_settings_error( 'bcp_settings', 'bcp_bad_type_' . $slug, sprintf(
					/* translators: %s: the slug that was rejected. */
					esc_html__( 'The post type "%s" was skipped because that slug is already in use.', 'backdrop-custom-portfolio' ),
					esc_html( $slug )
				) );
				continue;
			}

			// Post type names are limited to 20 characters, so two different
			// slugs can shorten to the same name. Tell the user instead of
			// silently dropping the second one.
			$name = bcp_get_extra_type_name( $slug );

			if ( isset( $names[ $name ] ) ) {
				add_settings_error( 'bcp_settings', 'bcp_collision_' . $slug, sprintf(
					/* translators: 1: the slug that was rejected, 2: the slug it collides with. */
					esc_html__( 'The post type "%1$s" was skipped because its name collides with "%2$s" after shortening.', 'backdrop-custom-portfolio' ),
					esc_html( $slug ),
					esc_html( $names[ $name ] )
				) );
				continue;
			}

			$names[ $name ] = $slug;

			$singular = sanitize_text_field( $row['singular'] ?? '' );
			$plural   = sanitize_text_field( $row['plural'] ?? '' );

			$menu = $row['menu'] ?? 'portfolio';

			$extra[ $slug ] = [
				'slug'     => $slug,
				'menu'     => in_array( $menu, bcp_get_menu_choices(), true ) ? $menu : 'portfolio',
				'singular' => '' !== $singular ? $singular : ucfirst( $slug ),
				'plural'   => '' !== $plural ? $plural : ( '' !== $singular ? $singular : ucfirst( $slug ) ),
			];
		}

		if ( count( $extra ) > 10 ) {
			add_settings_error( 'bcp_settings', 'bcp_too_many_types',
				esc_html__( 'Only the first 10 extra post types were kept; the rest were dropped.', 'backdrop-custom-portfolio' )
			);
		}

		$clean['extra_types'] = array_values( array_slice( $extra, 0, 10 ) );

		return $clean;
	}

	/**
	 * General section callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function section_general() { ?>

		<p class="description">
			<?php esc_html_e( 'General portfolio settings for your site.', 'backdrop-custom-portfolio' ); ?>
		</p>
	<?php }

	/**
	 * Portfolio title field callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function field_portfolio_title() { ?>

		<label>
			<input type="text" class="regular-text" name="bcp_settings[portfolio_title]" value="<?php echo esc_attr( bcp_get_portfolio_title() ); ?>" />
			<br />
			<span class="description"><?php esc_html_e( 'The name of your portfolio. May be used for the portfolio page title and other places, depending on your theme.', 'backdrop-custom-portfolio' ); ?></span>
		</label>
	<?php }

	/**
	 * Portfolio description field callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function field_portfolio_description() {

		wp_editor(
			bcp_get_portfolio_description(),
			'bcp_portfolio_description',
			array(
				'textarea_name'    => 'bcp_settings[portfolio_description]',
				'drag_drop_upload' => true,
				'editor_height'    => 150
			)
		); ?>

		<p>
			<span class="description"><?php esc_html_e( 'Your portfolio description. This may be shown by your theme on the portfolio page.', 'backdrop-custom-portfolio' ); ?></span>
		</p>
	<?php }

	/**
	 * Permalinks section callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function section_permalinks() { ?>

		<p class="description">
			<?php esc_html_e( 'Set up custom permalinks for the portfolio section on your site.', 'backdrop-custom-portfolio' ); ?>
		</p>
	<?php }

	/**
	 * Portfolio rewrite base field callback.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function field_portfolio_rewrite_base() { ?>

		<label>
			<code><?php echo esc_url( home_url( '/' ) ); ?></code>
			<input type="text" class="regular-text code" name="bcp_settings[portfolio_rewrite_base]" value="<?php echo esc_attr( bcp_get_portfolio_rewrite_base() ); ?>" />
		</label>
	<?php }

	/**
	 * Extra post types section callback.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return void
	 */
	public function section_extra_types() { ?>

		<p class="description">
			<?php esc_html_e( 'Add more portfolio-style post types, such as Photography or Web Design. They share the portfolio categories and tags. Clear the slug to remove one.', 'backdrop-custom-portfolio' ); ?>
		</p>
	<?php }

	/**
	 * Extra post types field callback.
	 *
	 * @since  1.1.0
	 * @access public
	 * @return void
	 */
	public function field_extra_types() {

		$rows   = array_values( bcp_get_settings()['extra_types'] );
		$rows[] = [];
		$rows[] = [];

		$menus = [
			'portfolio' => esc_html__( 'Under Portfolio', 'backdrop-custom-portfolio' ),
			'own'       => esc_html__( 'Own menu', 'backdrop-custom-portfolio' ),
			'hidden'    => esc_html__( 'Hidden', 'backdrop-custom-portfolio' ),
		]; ?>

		<table class="widefat striped bcp-types">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Slug', 'backdrop-custom-portfolio' ); ?></th>
					<th><?php esc_html_e( 'Singular', 'backdrop-custom-portfolio' ); ?></th>
					<th><?php esc_html_e( 'Plural', 'backdrop-custom-portfolio' ); ?></th>
					<th><?php esc_html_e( 'Menu', 'backdrop-custom-portfolio' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $i => $row ) : ?>
					<tr>
						<td><input type="text" class="regular-text code" name="bcp_settings[extra_types][<?php echo (int) $i; ?>][slug]" value="<?php echo esc_attr( $row['slug'] ?? '' ); ?>" /></td>
						<td><input type="text" class="regular-text" name="bcp_settings[extra_types][<?php echo (int) $i; ?>][singular]" value="<?php echo esc_attr( $row['singular'] ?? '' ); ?>" /></td>
						<td><input type="text" class="regular-text" name="bcp_settings[extra_types][<?php echo (int) $i; ?>][plural]" value="<?php echo esc_attr( $row['plural'] ?? '' ); ?>" /></td>
						<td>
							<select name="bcp_settings[extra_types][<?php echo (int) $i; ?>][menu]">
								<?php foreach ( $menus as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $row['menu'] ?? 'portfolio', $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="description">
			<?php esc_html_e( 'The slug is used in the URL (for example, photography). Menu sets where the type appears in the admin menu. Save again to get two more empty rows.', 'backdrop-custom-portfolio' ); ?>
		</p>
	<?php }

	/**
	 * Renders the settings page.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function settings_page() {

		?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Portfolio Settings', 'backdrop-custom-portfolio' ); ?></h1>

				<?php settings_errors(); ?>

				<form method="post" action="options.php">
					<?php settings_fields( 'bcp_settings' ); ?>
					<?php do_settings_sections( $this->page_hook ); ?>
					<?php submit_button( esc_attr__( 'Update Settings', 'backdrop-custom-portfolio' ), 'primary' ); ?>
				</form>

			</div><!-- wrap -->
	<?php }

	public function boot(): void {

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Loads the admin stylesheet on the settings page.
	 *
	 * @since  1.1.0
	 * @access public
	 * @param  string  $hook  The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_styles( $hook ): void {

		if ( $hook !== $this->page_hook ) {
			return;
		}

		\BCP\bcp_enqueue_admin_style();
	}
}