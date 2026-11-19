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

		// Create the settings page under the Portfolio menu.
		$this->page_hook = add_submenu_page(
			'bcp-portfolio',
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
		add_settings_field( 'portfolio_category', esc_html__( 'Category Slug', 'backdrop-custom-portfolio' ), array( $this, 'field_portfolio_category' ), $this->page_hook, 'permalinks' );
		add_settings_field( 'portfolio_tag', esc_html__( 'Tag Slug', 'backdrop-custom-portfolio' ), array( $this, 'field_portfolio_tag' ), $this->page_hook, 'permalinks' );
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

		// Title.
		$title = isset( $settings['portfolio_title'] ) ? sanitize_text_field( $settings['portfolio_title'] ) : '';
		$title = '' !== $title ? $title : esc_html__( 'Portfolio', 'backdrop-custom-portfolio' );

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

		// Kill evil scripts.
		$description = isset( $settings['portfolio_description'] ) ? $settings['portfolio_description'] : '';
		$description = stripslashes( wp_filter_post_kses( addslashes( $description ) ) );

		// Taxonomy slugs: they sit under the base, so they must be plain slugs
		// that are not reserved and not the same as each other.
		$category = isset( $settings['portfolio_category'] ) ? sanitize_title( $settings['portfolio_category'] ) : '';

		if ( '' === $category || in_array( $category, $reserved, true ) ) {
			add_settings_error( 'bcp_settings', 'bcp_bad_category', sprintf(
				/* translators: %s: the slug that was rejected. */
				esc_html__( '"%s" is not a usable category slug, so it was not changed.', 'backdrop-custom-portfolio' ),
				esc_html( $category )
			) );
			$category = $current['portfolio_category'];
		}

		$tag = isset( $settings['portfolio_tag'] ) ? sanitize_title( $settings['portfolio_tag'] ) : '';

		if ( '' === $tag || in_array( $tag, $reserved, true ) || $tag === $category ) {
			add_settings_error( 'bcp_settings', 'bcp_bad_tag', sprintf(
				/* translators: %s: the slug that was rejected. */
				esc_html__( '"%s" is not a usable tag slug, so it was not changed.', 'backdrop-custom-portfolio' ),
				esc_html( $tag )
			) );
			$tag = $current['portfolio_tag'];
		}

		return [
			'portfolio_title'        => $title,
			'portfolio_description'  => $description,
			'portfolio_rewrite_base' => $base,
			'portfolio_category'     => $category,
			'portfolio_tag'          => $tag,
		];
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
			<?php esc_html_e( 'The base is the URL prefix for the portfolio categories and tags, and the default prefix for new post types. Post types already in the registry keep their own rewrite slug when the base changes — edit a type to move it.', 'backdrop-custom-portfolio' ); ?>
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
	 * Portfolio category slug field callback.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function field_portfolio_category() { ?>

		<label>
			<code><?php echo esc_url( home_url( '/' . bcp_get_portfolio_rewrite_base() . '/' ) ); ?></code>
			<input type="text" class="regular-text code" name="bcp_settings[portfolio_category]" value="<?php echo esc_attr( bcp_get_setting( 'portfolio_category' ) ); ?>" />
		</label>
	<?php }

	/**
	 * Portfolio tag slug field callback.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function field_portfolio_tag() { ?>

		<label>
			<code><?php echo esc_url( home_url( '/' . bcp_get_portfolio_rewrite_base() . '/' ) ); ?></code>
			<input type="text" class="regular-text code" name="bcp_settings[portfolio_tag]" value="<?php echo esc_attr( bcp_get_setting( 'portfolio_tag' ) ); ?>" />
		</label>
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
