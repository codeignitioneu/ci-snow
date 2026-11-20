<?php
/**
 * Removes the plugin settings when the plugin is deleted.
 *
 * @package ci-snow
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ci_snow_settings' );
delete_site_transient( 'ci_snow_latest_release' );
