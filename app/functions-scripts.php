<?php
/**
 * Default scripts
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2023. Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/benlumia007/backdrop-custom-portfolio
 */

namespace BCP;

add_action( 'admin_enqueue_scripts', function() {

    $file = plugin_dir_path( __DIR__ ) . 'public/assets/css/screen.css';

    wp_enqueue_style(
        'creativity-screen',
        plugin_dir_url( __DIR__ ) . 'public/assets/css/screen.css',
        [],
        file_exists( $file ) ? filemtime( $file ) : null
    );
} );

