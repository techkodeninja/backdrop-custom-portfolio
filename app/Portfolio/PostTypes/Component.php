<?php
/**
 * Post types manager component
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace BCP\Portfolio\PostTypes;

use Backdrop\Contracts\Bootable;

class Component implements Bootable {

	/**
	 * Top-level menu hook suffix.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $menu_hook = '';

	/**
	 * Add New submenu hook suffix.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var    string
	 */
	public $add_hook = '';

	/**
	 * Sets up the Portfolio menu and the manager pages.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function admin_menu() {

		// The top-level page is the post type list.
		$this->menu_hook = add_menu_page(
			esc_html__( 'Post Types', 'backdrop-custom-portfolio' ),
			esc_html__( 'Portfolio',  'backdrop-custom-portfolio' ),
			'manage_options',
			'bcp-portfolio',
			array( $this, 'list_page' ),
			'dashicons-portfolio'
		);

		// The list again as the first submenu, so it is labelled
		// "Post Types" instead of "Portfolio".
		add_submenu_page(
			'bcp-portfolio',
			esc_html__( 'Post Types', 'backdrop-custom-portfolio' ),
			esc_html__( 'Post Types', 'backdrop-custom-portfolio' ),
			'manage_options',
			'bcp-portfolio',
			array( $this, 'list_page' )
		);

		$this->add_hook = add_submenu_page(
			'bcp-portfolio',
			esc_html__( 'Add New Post Type', 'backdrop-custom-portfolio' ),
			esc_html__( 'Add New',          'backdrop-custom-portfolio' ),
			'manage_options',
			'bcp-post-type-new',
			array( $this, 'form_page' )
		);
	}

	public function boot(): void {

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Loads the admin stylesheet on the manager pages.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  string  $hook  The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_styles( $hook ): void {

		if ( $hook !== $this->menu_hook && $hook !== $this->add_hook ) {
			return;
		}

		\BCP\bcp_enqueue_admin_style();
	}

	/**
	 * Menu placement labels for the list table.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return array
	 */
	public function menu_labels(): array {

		return [
			'portfolio' => esc_html__( 'Under Portfolio', 'backdrop-custom-portfolio' ),
			'own'       => esc_html__( 'Own menu',        'backdrop-custom-portfolio' ),
			'hidden'    => esc_html__( 'Hidden',          'backdrop-custom-portfolio' ),
		];
	}

	/**
	 * Renders a notice for the list page, from the `bcp_notice` query arg.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function list_notice(): void {

		$notice = isset( $_GET['bcp_notice'] ) ? sanitize_key( wp_unslash( $_GET['bcp_notice'] ) ) : '';

		if ( 'added' === $notice ) {
			$message = esc_html__( 'Post type added.', 'backdrop-custom-portfolio' );
			$class   = 'notice-success';
		} elseif ( 'updated' === $notice ) {
			$message = esc_html__( 'Post type updated.', 'backdrop-custom-portfolio' );
			$class   = 'notice-success';
		} elseif ( 'deleted' === $notice ) {
			$message = esc_html__( 'Post type deleted. Its posts are not deleted, but they are not shown anywhere until the post type is added again.', 'backdrop-custom-portfolio' );
			$class   = 'notice-success';
		} elseif ( 'not-found' === $notice ) {
			$message = esc_html__( 'That post type no longer exists.', 'backdrop-custom-portfolio' );
			$class   = 'notice-error';
		} else {
			return;
		}

		printf(
			'<div class="notice %s is-dismissible"><p>%s</p></div>',
			esc_attr( $class ),
			esc_html( $message )
		);
	}

	/**
	 * Renders the post type list.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function list_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage post types.', 'backdrop-custom-portfolio' ) );
		}

		// Delete a type. Its posts are left alone.
		if ( isset( $_GET['action'], $_GET['type'] ) && 'delete' === $_GET['action'] ) {

			$type = sanitize_key( wp_unslash( $_GET['type'] ) );

			check_admin_referer( 'bcp_delete_post_type_' . $type );

			$rows = bcp_get_post_type_rows();

			if ( isset( $rows[ $type ] ) ) {
				unset( $rows[ $type ] );
				update_option( 'bcp_post_types', $rows );
			}

			wp_safe_redirect( add_query_arg( 'bcp_notice', 'deleted', admin_url( 'admin.php?page=bcp-portfolio' ) ) );
			exit;
		}

		$rows   = bcp_get_post_type_rows();
		$base   = bcp_get_portfolio_rewrite_base();
		$menus  = $this->menu_labels();
		$labels = [
			'edit'   => esc_html__( 'Edit',   'backdrop-custom-portfolio' ),
			'delete' => esc_html__( 'Delete', 'backdrop-custom-portfolio' ),
		]; ?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Post Types', 'backdrop-custom-portfolio' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=bcp-post-type-new' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'backdrop-custom-portfolio' ); ?></a>

			<?php $this->list_notice(); ?>

			<p class="description">
				<?php esc_html_e( 'Every post type is a row. Portfolio is the first one; add more the same way, such as Photography or Themes. They all share the portfolio categories and tags.', 'backdrop-custom-portfolio' ); ?>
			</p>

			<?php if ( empty( $rows ) ) : ?>
				<p><?php esc_html_e( 'No post types yet.', 'backdrop-custom-portfolio' ); ?></p>
			<?php else : ?>
				<table class="widefat striped bcp-types">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Post Type', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Singular', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Plural', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Rewrite', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Menu', 'backdrop-custom-portfolio' ); ?></th>
							<th><?php esc_html_e( 'Posts', 'backdrop-custom-portfolio' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $name => $row ) :

							if ( ! is_array( $row ) ) {
								continue;
							}

							$slug     = sanitize_title( $row['slug'] ?? '' );
							$rewrite  = bcp_get_post_type_rewrite( $slug, (string) ( $row['rewrite'] ?? '' ), $base );
							$edit_url = add_query_arg( 'type', $name, admin_url( 'admin.php?page=bcp-post-type-new' ) );
							$delete_url = wp_nonce_url(
								add_query_arg( [ 'action' => 'delete', 'type' => $name ], admin_url( 'admin.php?page=bcp-portfolio' ) ),
								'bcp_delete_post_type_' . $name
							);

							$count   = wp_count_posts( $name );
							$object  = get_post_type_object( $name );
							$posts   = $object
								? sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=' . $name ) ), number_format_i18n( (int) $count->publish ) )
								: number_format_i18n( (int) $count->publish );
							?>
							<tr>
								<td>
									<strong><code><?php echo esc_html( $name ); ?></code></strong>
									<div class="row-actions">
										<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $labels['edit'] ); ?></a></span> |
										<span class="delete"><a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this post type? Its posts are not deleted.', 'backdrop-custom-portfolio' ) ); ?>');"><?php echo esc_html( $labels['delete'] ); ?></a></span>
									</div>
								</td>
								<td><code><?php echo esc_html( $slug ); ?></code></td>
								<td><?php echo esc_html( $row['singular'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['plural'] ?? '' ); ?></td>
								<td><code><?php echo esc_html( $rewrite ); ?></code></td>
								<td><?php echo esc_html( $menus[ $row['menu'] ?? 'portfolio' ] ?? $menus['portfolio'] ); ?></td>
								<td><?php echo $posts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	<?php }

	/**
	 * Renders the add/edit form, and saves it on submit.
	 *
	 * @since  2.0.0
	 * @access public
	 * @return void
	 */
	public function form_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage post types.', 'backdrop-custom-portfolio' ) );
		}

		$editing = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
		$rows    = bcp_get_post_type_rows();
		$row     = '' !== $editing ? ( $rows[ $editing ] ?? null ) : null;

		if ( '' !== $editing && null === $row ) {
			wp_safe_redirect( add_query_arg( 'bcp_notice', 'not-found', admin_url( 'admin.php?page=bcp-portfolio' ) ) );
			exit;
		}

		$errors = [];
		$values = is_array( $row ) ? $row : [
			'slug'     => '',
			'singular' => '',
			'plural'   => '',
			'rewrite'  => '',
			'menu'     => 'portfolio',
			'icon'     => 'dashicons-portfolio',
		];

		if ( isset( $_POST['bcp_save_post_type'] ) ) {

			check_admin_referer( 'bcp_save_post_type' );

			$posted = isset( $_POST['bcp_post_type'] ) && is_array( $_POST['bcp_post_type'] ) ? wp_unslash( $_POST['bcp_post_type'] ) : [];
			$values = array_merge( $values, $posted );

			$result = bcp_validate_post_type_row( $posted, $rows, $editing );

			if ( ! empty( $result['errors'] ) ) {
				$errors = $result['errors'];
			} else {
				$rows[ $result['name'] ] = $result['row'];
				update_option( 'bcp_post_types', $rows );

				wp_safe_redirect( add_query_arg( 'bcp_notice', '' === $editing ? 'added' : 'updated', admin_url( 'admin.php?page=bcp-portfolio' ) ) );
				exit;
			}
		}

		$base  = bcp_get_portfolio_rewrite_base();
		$menus = $this->menu_labels();
		$action_url = admin_url( 'admin.php?page=bcp-post-type-new' . ( '' !== $editing ? '&type=' . $editing : '' ) ); ?>

		<div class="wrap">
			<h1><?php echo '' !== $editing ? esc_html__( 'Edit Post Type', 'backdrop-custom-portfolio' ) : esc_html__( 'Add New Post Type', 'backdrop-custom-portfolio' ); ?></h1>

			<?php if ( '' !== $editing ) : ?>
				<p><code><?php echo esc_html( $editing ); ?></code></p>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="notice notice-error"><p><?php echo implode( '<br />', array_map( 'esc_html', $errors ) ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( $action_url ); ?>">
				<?php wp_nonce_field( 'bcp_save_post_type' ); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="bcp-slug"><?php esc_html_e( 'Slug', 'backdrop-custom-portfolio' ); ?></label></th>
							<td>
								<input type="text" id="bcp-slug" class="regular-text code" name="bcp_post_type[slug]" value="<?php echo esc_attr( $values['slug'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Used in the default URL. For a new type, the post type name is bcp-{slug}.', 'backdrop-custom-portfolio' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bcp-singular"><?php esc_html_e( 'Singular', 'backdrop-custom-portfolio' ); ?></label></th>
							<td><input type="text" id="bcp-singular" class="regular-text" name="bcp_post_type[singular]" value="<?php echo esc_attr( $values['singular'] ?? '' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="bcp-plural"><?php esc_html_e( 'Plural', 'backdrop-custom-portfolio' ); ?></label></th>
							<td><input type="text" id="bcp-plural" class="regular-text" name="bcp_post_type[plural]" value="<?php echo esc_attr( $values['plural'] ?? '' ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="bcp-rewrite"><?php esc_html_e( 'Rewrite slug', 'backdrop-custom-portfolio' ); ?></label></th>
							<td>
								<input type="text" id="bcp-rewrite" class="regular-text code" name="bcp_post_type[rewrite]" value="<?php echo esc_attr( $values['rewrite'] ?? '' ); ?>" />
								<p class="description">
									<?php
									printf(
										/* translators: 1: the portfolio base, 2: the type slug placeholder. */
										esc_html__( 'Leave empty to use the default URL: %1$s/%2$s (or just %1$s for the type whose slug is the base). Enter a plain slug, such as "themes", to put the archive at the top level, or any path.', 'backdrop-custom-portfolio' ),
										'<code>' . esc_html( $base ) . '</code>',
										'<code>' . esc_html( $values['slug'] ?? 'slug' ) . '</code>'
									);
									?>
								</p>
								<?php if ( '' !== $editing && '' !== ( $values['slug'] ?? '' ) ) : ?>
									<p class="description">
										<?php
										printf(
											/* translators: %s: the current archive URL. */
											esc_html__( 'Current archive: %s', 'backdrop-custom-portfolio' ),
											'<code>' . esc_url( home_url( '/' . bcp_get_post_type_rewrite( $values['slug'], (string) ( $values['rewrite'] ?? '' ), $base ) . '/' ) ) . '</code>'
										);
										?>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bcp-menu"><?php esc_html_e( 'Menu', 'backdrop-custom-portfolio' ); ?></label></th>
							<td>
								<select id="bcp-menu" name="bcp_post_type[menu]">
									<?php foreach ( $menus as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $values['menu'] ?? 'portfolio', $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bcp-icon"><?php esc_html_e( 'Menu icon', 'backdrop-custom-portfolio' ); ?></label></th>
							<td>
								<input type="text" id="bcp-icon" class="regular-text code" name="bcp_post_type[icon]" value="<?php echo esc_attr( $values['icon'] ?? '' ); ?>" />
								<p class="description">
									<?php esc_html_e( 'A Dashicon class, for example dashicons-portfolio. Used when the type has its own menu.', 'backdrop-custom-portfolio' ); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( '' !== $editing ? esc_attr__( 'Update Post Type', 'backdrop-custom-portfolio' ) : esc_attr__( 'Add Post Type', 'backdrop-custom-portfolio' ), 'primary', 'bcp_save_post_type' ); ?>
			</form>
		</div>
	<?php }
}
