<?php
/**
 * Plugin Name:       Seasonal Snow by Code Ignition
 * Plugin URI:        https://github.com/codeignitioneu/ci-snow
 * Description:       Lightweight falling-snow effect with snowflakes, colored Christmas baubles, tiny gifts and your own images from the Media Library. Everything is configured from Settings → Seasonal Snow, no code needed.
 * Version:           1.0.0
 * Author:            Code Ignition SRL
 * Author URI:        https://codeignition.eu
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       ci-snow
 * Update URI:        https://github.com/codeignitioneu/ci-snow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CI_SNOW_VERSION', '1.0.0' );
define( 'CI_SNOW_FILE', __FILE__ );
define( 'CI_SNOW_DIR', plugin_dir_path( __FILE__ ) );
define( 'CI_SNOW_URL', plugin_dir_url( __FILE__ ) );

require_once CI_SNOW_DIR . 'includes/class-ci-snow-settings.php';
require_once CI_SNOW_DIR . 'includes/class-ci-snow-front.php';
require_once CI_SNOW_DIR . 'includes/class-ci-snow-updater.php';

add_action( 'plugins_loaded', array( 'CI_Snow_Settings', 'init' ) );
add_action( 'plugins_loaded', array( 'CI_Snow_Front', 'init' ) );
add_action( 'plugins_loaded', array( 'CI_Snow_Updater', 'init' ) );

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=ci-snow' ) ) . '">' . esc_html__( 'Settings', 'ci-snow' ) . '</a>' );
		return $links;
	}
);
