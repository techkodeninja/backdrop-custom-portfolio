<?php
/**
 * Removes the plugin's options when it is deleted from the Plugins screen.
 * Portfolio posts, categories and tags are left alone.
 *
 * @package Backdrop Custom Portfolio
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bcp_settings' );
delete_option( 'bcp_flush_rewrite_rules' );
