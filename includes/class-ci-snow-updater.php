<?php
/**
 * Updates from GitHub Releases (public repo codeignitioneu/ci-snow).
 * A release tagged vX.Y.Z with an attached ci-snow.zip shows up as an update on the Plugins page.
 *
 * @package ci-snow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CI_Snow_Updater {

	const REPO      = 'codeignitioneu/ci-snow';
	const CACHE_KEY = 'ci_snow_latest_release';

	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'check' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'info' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'flush' ), 10, 0 );
	}

	public static function flush() {
		delete_site_transient( self::CACHE_KEY );
	}

	private static function latest() {
		$cached = get_site_transient( self::CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}
		$res = wp_remote_get(
			'https://api.github.com/repos/' . self::REPO . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);
		$data = array();
		if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
			$json = json_decode( wp_remote_retrieve_body( $res ), true );
			if ( is_array( $json ) && ! empty( $json['tag_name'] ) ) {
				$zip = '';
				foreach ( (array) ( $json['assets'] ?? array() ) as $asset ) {
					if ( isset( $asset['name'], $asset['browser_download_url'] ) && 'ci-snow.zip' === $asset['name'] ) {
						$zip = $asset['browser_download_url'];
					}
				}
				$data = array(
					'version' => ltrim( $json['tag_name'], 'vV' ),
					'zip'     => $zip,
					'notes'   => (string) ( $json['body'] ?? '' ),
					'url'     => (string) ( $json['html_url'] ?? '' ),
				);
			}
		}
		set_site_transient( self::CACHE_KEY, $data, 6 * HOUR_IN_SECONDS );
		return $data;
	}

	public static function check( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}
		$rel    = self::latest();
		$plugin = plugin_basename( CI_SNOW_FILE );
		if ( ! empty( $rel['version'] ) && ! empty( $rel['zip'] ) && version_compare( $rel['version'], CI_SNOW_VERSION, '>' ) ) {
			$transient->response[ $plugin ] = (object) array(
				'slug'        => 'ci-snow',
				'plugin'      => $plugin,
				'new_version' => $rel['version'],
				'package'     => $rel['zip'],
				'url'         => 'https://github.com/' . self::REPO,
			);
		}
		return $transient;
	}

	public static function info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || 'ci-snow' !== $args->slug ) {
			return $result;
		}
		$rel = self::latest();
		return (object) array(
			'name'          => 'Seasonal Snow by Code Ignition',
			'slug'          => 'ci-snow',
			'version'       => $rel['version'] ?? CI_SNOW_VERSION,
			'author'        => '<a href="https://codeignition.eu">Code Ignition SRL</a>',
			'homepage'      => 'https://github.com/' . self::REPO,
			'download_link' => $rel['zip'] ?? '',
			'sections'      => array(
				'description' => 'Falling snowflakes, Christmas baubles, tiny gifts and custom images. Configure it from Settings → Seasonal Snow.',
				'changelog'   => nl2br( esc_html( $rel['notes'] ?? '' ) ),
			),
		);
	}
}
