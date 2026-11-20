<?php
/**
 * Front-end: decides where the effect runs and loads the script.
 *
 * @package ci-snow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CI_Snow_Front {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function config( $s ) {
		$images = array();
		foreach ( array_filter( array_map( 'absint', explode( ',', (string) $s['image_ids'] ) ) ) as $id ) {
			$src = wp_get_attachment_image_url( $id, 'thumbnail' );
			if ( $src ) {
				$images[] = $src;
			}
		}
		return array(
			'shapes'        => array_values( $s['shapes'] ),
			'images'        => $images,
			'flakeColor'    => $s['flake_color'],
			'outlineColor'  => $s['outline_color'],
			'baubleColors'  => array_values( array_filter( explode( ',', $s['bauble_colors'] ) ) ),
			'giftColors'    => array_values( array_filter( explode( ',', $s['gift_colors'] ) ) ),
			'countDesktop'  => (int) $s['count_desktop'],
			'countMobile'   => (int) $s['count_mobile'],
			'mobileEnabled' => (bool) $s['mobile_enabled'],
			'sizeMin'       => (int) $s['size_min'],
			'sizeMax'       => (int) $s['size_max'],
			'speed'         => (float) $s['speed'],
			'opacity'       => (float) $s['opacity'],
			'zIndex'        => (int) $s['z_index'],
		);
	}

	private static function should_run( $s ) {
		if ( empty( $s['enabled'] ) || is_admin() ) {
			return false;
		}
		$today = current_time( 'Y-m-d' );
		if ( $s['start_date'] && $today < $s['start_date'] ) {
			return false;
		}
		if ( $s['end_date'] && $today > $s['end_date'] ) {
			return false;
		}
		if ( 'front' === $s['where'] ) {
			return is_front_page();
		}
		if ( 'pages' === $s['where'] ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $s['page_ids'] ) ) );
			return $ids && is_page( $ids );
		}
		return true;
	}

	public static function enqueue() {
		$s = CI_Snow_Settings::get();
		if ( ! self::should_run( $s ) ) {
			return;
		}
		wp_enqueue_script( 'ci-snow', CI_SNOW_URL . 'assets/snow.js', array(), CI_SNOW_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_add_inline_script( 'ci-snow', 'window.ciSnowConfig = ' . wp_json_encode( self::config( $s ) ) . ';', 'before' );
	}
}
