<?php
/**
 * Settings page: Settings → Seasonal Snow.
 *
 * @package ci-snow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CI_Snow_Settings {

	const OPTION = 'ci_snow_settings';

	public static function defaults() {
		return array(
			'enabled'        => 1,
			'where'          => 'front', // all | front | pages.
			'page_ids'       => '',
			'start_date'     => '',
			'end_date'       => '',
			'shapes'         => array( 'flake', 'bauble', 'gift' ),
			'flake_color'    => '#ffffff',
			'outline_color'  => '#7fc4d6',
			'bauble_colors'  => '#e53935,#f9a825,#0191b1,#ff6000,#43a047,#8e24aa',
			'gift_colors'    => '#e53935,#0191b1,#ff6000,#43a047',
			'image_ids'      => '',
			'count_desktop'  => 50,
			'count_mobile'   => 22,
			'size_min'       => 8,
			'size_max'       => 20,
			'speed'          => 1,
			'opacity'        => 0.9,
			'z_index'        => 9990,
			'mobile_enabled' => 1,
		);
	}

	public static function get() {
		$saved = get_option( self::OPTION, array() );
		$s     = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
		if ( ! is_array( $s['shapes'] ) ) {
			$s['shapes'] = array();
		}
		return $s;
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_options_page(
			__( 'Seasonal Snow', 'ci-snow' ),
			__( 'Seasonal Snow', 'ci-snow' ),
			'manage_options',
			'ci-snow',
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			'ci_snow',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	private static function hex_list( $value ) {
		$out = array();
		foreach ( explode( ',', (string) $value ) as $c ) {
			$c = sanitize_hex_color( trim( $c ) );
			if ( $c ) {
				$out[] = $c;
			}
		}
		return implode( ',', $out );
	}

	private static function int_list( $value ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
		return implode( ',', array_unique( $ids ) );
	}

	private static function date_or_empty( $value ) {
		$value = sanitize_text_field( (string) $value );
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
	}

	public static function sanitize( $in ) {
		$d   = self::defaults();
		$in  = is_array( $in ) ? $in : array();
		$out = array();

		$out['enabled']        = empty( $in['enabled'] ) ? 0 : 1;
		$out['mobile_enabled'] = empty( $in['mobile_enabled'] ) ? 0 : 1;
		$out['where']          = in_array( $in['where'] ?? '', array( 'all', 'front', 'pages' ), true ) ? $in['where'] : $d['where'];
		$out['page_ids']       = self::int_list( $in['page_ids'] ?? '' );
		$out['start_date']     = self::date_or_empty( $in['start_date'] ?? '' );
		$out['end_date']       = self::date_or_empty( $in['end_date'] ?? '' );

		$allowed       = array( 'flake', 'bauble', 'gift', 'dot' );
		$shapes        = isset( $in['shapes'] ) && is_array( $in['shapes'] ) ? $in['shapes'] : array();
		$out['shapes'] = array_values( array_intersect( $allowed, array_map( 'sanitize_key', $shapes ) ) );

		$out['flake_color']   = sanitize_hex_color( $in['flake_color'] ?? '' ) ?: $d['flake_color'];
		$out['outline_color'] = sanitize_hex_color( $in['outline_color'] ?? '' ) ?: $d['outline_color'];
		$out['bauble_colors'] = self::hex_list( $in['bauble_colors'] ?? '' ) ?: $d['bauble_colors'];
		$out['gift_colors']   = self::hex_list( $in['gift_colors'] ?? '' ) ?: $d['gift_colors'];
		$out['image_ids']     = self::int_list( $in['image_ids'] ?? '' );

		$out['count_desktop'] = max( 0, min( 200, absint( $in['count_desktop'] ?? $d['count_desktop'] ) ) );
		$out['count_mobile']  = max( 0, min( 100, absint( $in['count_mobile'] ?? $d['count_mobile'] ) ) );
		$out['size_min']      = max( 3, min( 80, absint( $in['size_min'] ?? $d['size_min'] ) ) );
		$out['size_max']      = max( $out['size_min'], min( 120, absint( $in['size_max'] ?? $d['size_max'] ) ) );
		$out['speed']         = max( 0.2, min( 4, (float) ( $in['speed'] ?? $d['speed'] ) ) );
		$out['opacity']       = max( 0.2, min( 1, (float) ( $in['opacity'] ?? $d['opacity'] ) ) );
		$out['z_index']       = max( 0, min( 999999, absint( $in['z_index'] ?? $d['z_index'] ) ) );

		return $out;
	}

	public static function assets( $hook ) {
		if ( 'settings_page_ci-snow' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'ci-snow-admin', CI_SNOW_URL . 'assets/admin.js', array( 'jquery', 'wp-color-picker' ), CI_SNOW_VERSION, true );
		wp_enqueue_script( 'ci-snow', CI_SNOW_URL . 'assets/snow.js', array(), CI_SNOW_VERSION, true );
	}

	private static function field_name( $key ) {
		return self::OPTION . '[' . $key . ']';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s      = self::get();
		$images = array_filter( array_map( 'absint', explode( ',', $s['image_ids'] ) ) );
		?>
		<div class="wrap ci-snow-wrap">
			<h1><?php esc_html_e( 'Seasonal Snow', 'ci-snow' ); ?></h1>
			<p><?php esc_html_e( 'Falling snowflakes, Christmas baubles, tiny gifts and your own images. Changes apply as soon as you save.', 'ci-snow' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ci_snow' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable', 'ci-snow' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::field_name( 'enabled' ) ); ?>" value="1" <?php checked( $s['enabled'], 1 ); ?>> <?php esc_html_e( 'Show the effect', 'ci-snow' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( self::field_name( 'mobile_enabled' ) ); ?>" value="1" <?php checked( $s['mobile_enabled'], 1 ); ?>> <?php esc_html_e( 'Also on phones', 'ci-snow' ); ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Where', 'ci-snow' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( self::field_name( 'where' ) ); ?>">
								<option value="front" <?php selected( $s['where'], 'front' ); ?>><?php esc_html_e( 'Homepage only', 'ci-snow' ); ?></option>
								<option value="all" <?php selected( $s['where'], 'all' ); ?>><?php esc_html_e( 'Whole site', 'ci-snow' ); ?></option>
								<option value="pages" <?php selected( $s['where'], 'pages' ); ?>><?php esc_html_e( 'Only these page IDs', 'ci-snow' ); ?></option>
							</select>
							<input type="text" class="regular-text" placeholder="12, 45, 47" name="<?php echo esc_attr( self::field_name( 'page_ids' ) ); ?>" value="<?php echo esc_attr( $s['page_ids'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Period (optional)', 'ci-snow' ); ?></th>
						<td>
							<input type="date" name="<?php echo esc_attr( self::field_name( 'start_date' ) ); ?>" value="<?php echo esc_attr( $s['start_date'] ); ?>"> —
							<input type="date" name="<?php echo esc_attr( self::field_name( 'end_date' ) ); ?>" value="<?php echo esc_attr( $s['end_date'] ); ?>">
							<p class="description"><?php esc_html_e( 'Leave empty to show it all the time.', 'ci-snow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Shapes', 'ci-snow' ); ?></th>
						<td>
							<?php
							$labels = array(
								'flake'  => __( 'Snowflakes', 'ci-snow' ),
								'bauble' => __( 'Christmas baubles', 'ci-snow' ),
								'gift'   => __( 'Tiny gifts', 'ci-snow' ),
								'dot'    => __( 'Soft snow dots', 'ci-snow' ),
							);
							foreach ( $labels as $key => $label ) :
								?>
								<label style="margin-right:14px"><input type="checkbox" name="<?php echo esc_attr( self::field_name( 'shapes' ) ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $s['shapes'], true ) ); ?>> <?php echo esc_html( $label ); ?></label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Your own images', 'ci-snow' ); ?></th>
						<td>
							<input type="hidden" id="ci-snow-image-ids" name="<?php echo esc_attr( self::field_name( 'image_ids' ) ); ?>" value="<?php echo esc_attr( $s['image_ids'] ); ?>">
							<div id="ci-snow-images" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
								<?php foreach ( $images as $id ) : ?>
									<span class="ci-snow-thumb" data-id="<?php echo esc_attr( $id ); ?>" style="position:relative;display:inline-block"><?php echo wp_get_attachment_image( $id, array( 48, 48 ) ); ?></span>
								<?php endforeach; ?>
							</div>
							<button type="button" class="button" id="ci-snow-add-images"><?php esc_html_e( 'Choose images', 'ci-snow' ); ?></button>
							<button type="button" class="button-link-delete" id="ci-snow-clear-images"><?php esc_html_e( 'Remove all', 'ci-snow' ); ?></button>
							<p class="description"><?php esc_html_e( 'Transparent PNG or SVG works best. They fall together with the shapes above.', 'ci-snow' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Colors', 'ci-snow' ); ?></th>
						<td>
							<p><?php esc_html_e( 'Snowflake', 'ci-snow' ); ?> <input type="text" class="ci-snow-color" name="<?php echo esc_attr( self::field_name( 'flake_color' ) ); ?>" value="<?php echo esc_attr( $s['flake_color'] ); ?>">
							<?php esc_html_e( 'Outline (visible on white backgrounds)', 'ci-snow' ); ?> <input type="text" class="ci-snow-color" name="<?php echo esc_attr( self::field_name( 'outline_color' ) ); ?>" value="<?php echo esc_attr( $s['outline_color'] ); ?>"></p>
							<p><label><?php esc_html_e( 'Bauble colors (comma separated)', 'ci-snow' ); ?><br><input type="text" class="large-text" name="<?php echo esc_attr( self::field_name( 'bauble_colors' ) ); ?>" value="<?php echo esc_attr( $s['bauble_colors'] ); ?>"></label></p>
							<p><label><?php esc_html_e( 'Gift colors (comma separated)', 'ci-snow' ); ?><br><input type="text" class="large-text" name="<?php echo esc_attr( self::field_name( 'gift_colors' ) ); ?>" value="<?php echo esc_attr( $s['gift_colors'] ); ?>"></label></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Amount and size', 'ci-snow' ); ?></th>
						<td>
							<label><?php esc_html_e( 'Desktop', 'ci-snow' ); ?> <input type="number" min="0" max="200" class="small-text" name="<?php echo esc_attr( self::field_name( 'count_desktop' ) ); ?>" value="<?php echo esc_attr( $s['count_desktop'] ); ?>"></label>
							<label style="margin-left:12px"><?php esc_html_e( 'Phone', 'ci-snow' ); ?> <input type="number" min="0" max="100" class="small-text" name="<?php echo esc_attr( self::field_name( 'count_mobile' ) ); ?>" value="<?php echo esc_attr( $s['count_mobile'] ); ?>"></label>
							<label style="margin-left:12px"><?php esc_html_e( 'Size (px)', 'ci-snow' ); ?> <input type="number" min="3" max="80" class="small-text" name="<?php echo esc_attr( self::field_name( 'size_min' ) ); ?>" value="<?php echo esc_attr( $s['size_min'] ); ?>"> – <input type="number" min="3" max="120" class="small-text" name="<?php echo esc_attr( self::field_name( 'size_max' ) ); ?>" value="<?php echo esc_attr( $s['size_max'] ); ?>"></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Motion', 'ci-snow' ); ?></th>
						<td>
							<label><?php esc_html_e( 'Speed', 'ci-snow' ); ?> <input type="number" step="0.1" min="0.2" max="4" class="small-text" name="<?php echo esc_attr( self::field_name( 'speed' ) ); ?>" value="<?php echo esc_attr( $s['speed'] ); ?>"></label>
							<label style="margin-left:12px"><?php esc_html_e( 'Opacity', 'ci-snow' ); ?> <input type="number" step="0.05" min="0.2" max="1" class="small-text" name="<?php echo esc_attr( self::field_name( 'opacity' ) ); ?>" value="<?php echo esc_attr( $s['opacity'] ); ?>"></label>
							<label style="margin-left:12px"><?php esc_html_e( 'Layer (z-index)', 'ci-snow' ); ?> <input type="number" min="0" class="small-text" name="<?php echo esc_attr( self::field_name( 'z_index' ) ); ?>" value="<?php echo esc_attr( $s['z_index'] ); ?>"></label>
							<p class="description"><?php esc_html_e( 'Visitors who turned off animations in their system settings never see the effect. It never blocks clicks.', 'ci-snow' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<h2><?php esc_html_e( 'Preview', 'ci-snow' ); ?></h2>
			<div id="ci-snow-preview" style="position:relative;height:220px;border-radius:10px;overflow:hidden;background:linear-gradient(90deg,#f6f7f8 50%,#123c46 50%)"></div>
			<script>window.ciSnowPreview = <?php echo wp_json_encode( CI_Snow_Front::config( $s ) ); ?>;</script>
		</div>
		<?php
	}
}
