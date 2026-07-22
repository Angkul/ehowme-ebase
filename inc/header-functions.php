<?php
/**
 * Header Helper Functions
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register nav menus
 */
function hec_register_menus() {
	register_nav_menus( [
		'hec-header-menu' => __( 'Header Menu', 'ehowme-ebase' ),
		'hec-footer-menu' => __( 'Footer Menu', 'ehowme-ebase' ),
	] );
}
add_action( 'after_setup_theme', 'hec_register_menus' );

/**
 * ดึง header menu ID จาก theme options
 * ถ้าไม่ได้ตั้งค่าใน options จะใช้ location 'hec-header-menu'
 */
function hec_get_header_menu_id() {
	// รองรับ multi-language: ถ้ามี option เฉพาะภาษาปัจจุบัน (เช่น hec_header_menu_en)
	// จะใช้ค่านั้นก่อน ไม่งั้น fallback ไปที่ hec_header_menu (default)
	$menu_id = function_exists( 'hec_get_multilang_option' )
		? hec_get_multilang_option( 'hec_header_menu', '' )
		: get_option( 'hec_header_menu', '' );
	if ( $menu_id ) {
		return (int) $menu_id;
	}
	// Fallback: ดึงจาก nav location
	$locations = get_nav_menu_locations();
	return isset( $locations['hec-header-menu'] ) ? $locations['hec-header-menu'] : 0;
}

/**
 * Output header navigation
 */
function hec_header_navigation() {
	$menu_id = hec_get_header_menu_id();

	if ( ! $menu_id ) {
		// ไม่มีเมนู — แสดง placeholder ให้ admin เห็น
		if ( current_user_can( 'manage_options' ) ) {
			echo '<nav class="header-nav" aria-label="' . esc_attr__( 'Primary Navigation', 'ehowme-ebase' ) . '">';
			echo '<p style="color:#999;font-size:12px;padding:0 16px;">' . esc_html__( 'No menu assigned. Go to Appearance → Menus.', 'ehowme-ebase' ) . '</p>';
			echo '</nav>';
		}
		return;
	}

	$args = [
		'menu'            => $menu_id,
		'container'       => 'nav',
		'container_class' => 'header-nav',
		'container_id'    => 'site-navigation',
		'container_aria_label' => __( 'Primary Navigation', 'ehowme-ebase' ),
		'menu_class'      => '',
		'depth'           => 3,
		'fallback_cb'     => false,
		'walker'          => new HEC_Nav_Walker(),
	];

	wp_nav_menu( $args );
}

/**
 * Output language switcher (Polylang, hover-based dropdown)
 * ไม่แสดงถ้ามีภาษาเดียวหรือไม่มี Polylang
 */
function hec_header_language_switcher() {
	if ( ! get_option( 'hec_show_lang_switcher', '1' ) ) {
		return;
	}

	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}

	$languages = pll_the_languages( [ 'raw' => 1 ] );

	// pll_the_languages() คืน '' นอก frontend (REST/preview) — กัน
	// count('') ซึ่ง fatal บน PHP 8 / ไม่แสดงถ้ามีภาษาเดียวหรือน้อยกว่า
	if ( ! is_array( $languages ) || count( $languages ) <= 1 ) {
		return;
	}

	$current_slug = '';
	foreach ( $languages as $lang ) {
		if ( $lang['current_lang'] ) {
			$current_slug = strtoupper( $lang['slug'] );
			break;
		}
	}
	?>
	<div class="lang-switch">
		<button class="lang-btn" aria-label="<?php esc_attr_e( 'เลือกภาษา', 'ehowme-ebase' ); ?>" aria-haspopup="true">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8">
				<circle cx="12" cy="12" r="10"/>
				<path d="M2 12h20"/>
				<path d="M12 2c3 3 5 6 5 10s-2 7-5 10c-3-3-5-6-5-10s2-7 5-10z"/>
			</svg>
			<?php echo esc_html( $current_slug ); ?>
			<svg class="caret" viewBox="0 0 12 12" width="10" height="10" fill="none" stroke="currentColor" stroke-width="1.8">
				<path d="M2.5 4.5L6 8l3.5-3.5"/>
			</svg>
		</button>
		<div class="lang-menu" role="menu">
			<?php foreach ( $languages as $lang ) : ?>
			<a href="<?php echo esc_url( $lang['url'] ); ?>"
			   role="menuitem"
			   <?php if ( $lang['current_lang'] ) echo 'class="lang-current"'; ?>
			>
				<?php if ( ! empty( $lang['flag'] ) ) : ?>
				<img src="<?php echo esc_url( $lang['flag'] ); ?>" alt="<?php echo esc_attr( $lang['slug'] ); ?>" width="18" height="auto">
			<?php endif; ?>
				<?php echo esc_html( $lang['name'] ); ?>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Icon library ใช้ร่วมกันสำหรับปุ่ม CTA ทั้งสอง — เพิ่ม icon ใหม่ในอนาคต
 * แค่เติมเข้า array นี้ (slug => [ label, svg ]) ตัวเลือกใน Theme Options
 * (select field 'icon') จะมีให้เลือกอัตโนมัติ
 */
function hec_get_cta_icons() {
	return [
		'none' => [
			'label' => __( 'None', 'ehowme-ebase' ),
			'svg'   => '',
		],
		'arrow-right' => [
			'label' => __( 'Arrow Right', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M15 16l4 -4" /><path d="M15 8l4 4" /></svg>',
		],
		'arrow-up-right' => [
			'label' => __( 'Arrow Up-Right', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M8 7l9 0l0 9" /></svg>',
		],
		'chevron-right' => [
			'label' => __( 'Chevron Right', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg>',
		],
		'external-link' => [
			'label' => __( 'External Link', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>',
		],
		'phone' => [
			'label' => __( 'Phone', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>',
		],
		'mail' => [
			'label' => __( 'Mail', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>',
		],
		'download' => [
			'label' => __( 'Download', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>',
		],
		'user' => [
			'label' => __( 'User', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>',
		],
		'users' => [
			'label' => __( 'Users', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
		],
		'home' => [
			'label' => __( 'Home', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>',
		],
		'star' => [
			'label' => __( 'Star', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1.002l3.086 -6.253l3.086 6.253l6.9 1.002l-5 4.867l1.179 6.873z" /></svg>',
		],
		'star-filled' => [
			'label' => __( 'Star (Filled)', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1.002l3.086 -6.253l3.086 6.253l6.9 1.002l-5 4.867l1.179 6.873z" /></svg>',
		],
		'heart' => [
			'label' => __( 'Heart', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>',
		],
		'heart-filled' => [
			'label' => __( 'Heart (Filled)', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>',
		],
		'shopping-cart' => [
			'label' => __( 'Shopping Cart', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1" /><circle cx="17" cy="20" r="1" /><path d="M4 4h2l2.5 12.5a2 2 0 0 0 2 1.6h7a2 2 0 0 0 2 -1.6l1.5 -7.4h-15" /></svg>',
		],
		'shopping-bag' => [
			'label' => __( 'Shopping Bag', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /></svg>',
		],
		'trash' => [
			'label' => __( 'Trash', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>',
		],
		'search' => [
			'label' => __( 'Search', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7" /><path d="M21 21l-6 -6" /></svg>',
		],
		'zoom-in' => [
			'label' => __( 'Zoom In', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7" /><path d="M21 21l-6 -6" /><path d="M7 10l6 0" /><path d="M10 7l0 6" /></svg>',
		],
		'zoom-out' => [
			'label' => __( 'Zoom Out', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7" /><path d="M21 21l-6 -6" /><path d="M7 10l6 0" /></svg>',
		],
		'thumb-up' => [
			'label' => __( 'Thumbs Up', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a3 3 0 0 1 -3 -3" /></svg>',
		],
		'thumb-down' => [
			'label' => __( 'Thumbs Down', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13v-8a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v7a1 1 0 0 0 1 1h3a4 4 0 0 1 4 4v1a2 2 0 0 0 4 0v-5h3a2 2 0 0 0 2 -2l-1 -5a2 3 0 0 0 -2 -2h-7a3 3 0 0 0 -3 3" /></svg>',
		],
		'refresh' => [
			'label' => __( 'Refresh', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>',
		],
		'check' => [
			'label' => __( 'Check', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10" /></svg>',
		],
		'check-circle' => [
			'label' => __( 'Check Circle', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M9 12l2 2l4 -4" /></svg>',
		],
		'info-circle' => [
			'label' => __( 'Info', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>',
		],
		'alert-circle' => [
			'label' => __( 'Alert', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>',
		],
		'bell' => [
			'label' => __( 'Bell', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>',
		],
		'bookmark' => [
			'label' => __( 'Bookmark', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M18 7v14l-6 -4l-6 4v-14a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4z" /></svg>',
		],
		'tag' => [
			'label' => __( 'Tag', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h.01" /><path d="M3 7v4a1 1 0 0 0 .3 .7l9 9a1 1 0 0 0 1.4 0l6.6 -6.6a1 1 0 0 0 0 -1.4l-9 -9a1 1 0 0 0 -.7 -.3h-4a2 2 0 0 0 -2 2z" /></svg>',
		],
		'map-pin' => [
			'label' => __( 'Map Pin', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>',
		],
		'clock' => [
			'label' => __( 'Clock', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 3" /></svg>',
		],
		'calendar' => [
			'label' => __( 'Calendar', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="5" width="16" height="16" rx="2" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>',
		],
		'flag' => [
			'label' => __( 'Flag', 'ehowme-ebase' ),
			'svg'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 5a5 5 0 0 1 7 0a5 5 0 0 0 7 0v9a5 5 0 0 1 -7 0a5 5 0 0 0 -7 0v-9z" /><path d="M5 21v-7" /></svg>',
		],
	];
}

/**
 * คืน markup ของ icon (raw SVG, มาจาก array ที่กำหนดไว้ในธีมเท่านั้น
 * ไม่ใช่ค่าที่ user พิมพ์เอง จึงไม่ต้อง esc) ห่อด้วย span.header-cta-icon
 * เพื่อคุม vertical-align/size ผ่าน CSS ได้ — คืนค่าว่างถ้าไม่มี/ไม่เจอ icon
 */
function hec_get_cta_icon_markup( $icon_slug ) {
	$icons = hec_get_cta_icons();
	if ( empty( $icon_slug ) || 'none' === $icon_slug || empty( $icons[ $icon_slug ]['svg'] ) ) {
		return '';
	}
	return '<span class="header-cta-icon">' . $icons[ $icon_slug ]['svg'] . '</span>';
}

/**
 * เหมือน hec_get_cta_icon_markup() แต่ห่อด้วย span.nav-dropdown-icon แทน
 * (ขนาด/ตำแหน่งคุมแยกจาก CTA icon ผ่าน CSS) ใช้กับ icon ต่อรายการใน
 * dropdown เมนู header — icon slug มาจาก library เดียวกัน (hec_get_cta_icons())
 * เพื่อให้ธีมมี icon set เดียว ไม่ต้องดูแลสอง registry
 */
function hec_get_menu_item_icon_markup( $icon_slug ) {
	$icons = hec_get_cta_icons();
	if ( empty( $icon_slug ) || 'none' === $icon_slug || empty( $icons[ $icon_slug ]['svg'] ) ) {
		return '';
	}
	return '<span class="nav-dropdown-icon">' . $icons[ $icon_slug ]['svg'] . '</span>';
}

/**
 * คืน markup ของ icon ที่จะโชว์จริงสำหรับ menu item หนึ่งตัว — เช็ครูป
 * custom (_menu_item_hec_icon_image) ก่อนเสมอ ถ้ามีและยังเป็นรูปจริง
 * (กันกรณีถูกลบออกจาก Media Library ไปแล้วแต่ meta ค้าง) ใช้รูปนั้น,
 * ไม่งั้น fallback ไป preset slug (_menu_item_hec_icon) ตามเดิม — ทั้งสอง
 * กรณีห่อด้วย span.nav-dropdown-icon เดียวกัน CSS จึงคุมขนาด/ตำแหน่งแบบ
 * เดียวกันไม่ว่าจะเป็น SVG หรือรูป
 */
function hec_get_menu_item_icon_markup_for_item( $item_id ) {
	$image_id = (int) get_post_meta( $item_id, '_menu_item_hec_icon_image', true );
	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		$img = wp_get_attachment_image( $image_id, 'hec_menu_icon', false, [
			'class'   => 'nav-dropdown-icon-img',
			'alt'     => '',
			'loading' => 'lazy',
		] );
		if ( $img ) {
			return '<span class="nav-dropdown-icon nav-dropdown-icon--image">' . $img . '</span>';
		}
	}
	return hec_get_menu_item_icon_markup( get_post_meta( $item_id, '_menu_item_hec_icon', true ) );
}

/**
 * Output CTA Button
 */
function hec_header_cta_button() {
	if ( ! get_option( 'hec_show_cta_button', '1' ) ) {
		return;
	}

	$lang   = function_exists( 'hec_get_current_language' ) ? hec_get_current_language() : '';
	$suffix = $lang ? '_' . $lang : '';

	$label = get_option( 'hec_cta_label' . $suffix, '' );
	$url   = get_option( 'hec_cta_url' . $suffix, '' );

	// Fallback
	if ( ! $label ) $label = get_option( 'hec_cta_label', __( 'Contact Us', 'ehowme-ebase' ) );
	if ( ! $url )   $url   = get_option( 'hec_cta_url', home_url( '/contact' ) );

	if ( ! $label && ! $url ) {
		return;
	}

	$icon_markup = hec_get_cta_icon_markup( get_option( 'hec_cta_icon', 'arrow-right' ) );
	$icon_before = 'before' === get_option( 'hec_cta_icon_position', 'after' );
	?>
	<a href="<?php echo esc_url( $url ); ?>" class="header-cta-btn">
		<?php if ( $icon_before ) echo $icon_markup; ?>
		<?php echo esc_html( $label ); ?>
		<?php if ( ! $icon_before ) echo $icon_markup; ?>
	</a>
	<?php
}

/**
 * Output CTA Button 2 (secondary — ปิดโดย default, เปิดได้ที่ Theme Options → CTA Button)
 */
function hec_header_cta_button_2() {
	if ( ! get_option( 'hec_show_cta_button_2', '0' ) ) {
		return;
	}

	$lang   = function_exists( 'hec_get_current_language' ) ? hec_get_current_language() : '';
	$suffix = $lang ? '_' . $lang : '';

	$label = get_option( 'hec_cta_label_2' . $suffix, '' );
	$url   = get_option( 'hec_cta_url_2' . $suffix, '' );

	// Fallback
	if ( ! $label ) $label = get_option( 'hec_cta_label_2', __( 'Learn More', 'ehowme-ebase' ) );
	if ( ! $url )   $url   = get_option( 'hec_cta_url_2', home_url( '/' ) );

	if ( ! $label && ! $url ) {
		return;
	}

	$icon_markup = hec_get_cta_icon_markup( get_option( 'hec_cta_icon_2', 'none' ) );
	$icon_before = 'before' === get_option( 'hec_cta_icon_position_2', 'after' );
	?>
	<a href="<?php echo esc_url( $url ); ?>" class="header-cta-btn header-cta-btn--secondary">
		<?php if ( $icon_before ) echo $icon_markup; ?>
		<?php echo esc_html( $label ); ?>
		<?php if ( ! $icon_before ) echo $icon_markup; ?>
	</a>
	<?php
}

/**
 * Custom Nav Walker — รองรับ Mega Menu + Dropdown
 *
 * การใช้งาน Mega Menu:
 * 1. ไปที่ Appearance → Menus
 * 2. Top-level item ที่ต้องการ mega menu → ติ๊ก "Enable Mega Menu" ในกล่อง
 *    แก้ไข item เลย (เพิ่มโดย hec_mega_menu_checkbox_field() ด้านล่าง) —
 *    หรือจะใส่ CSS class "has-mega" เองผ่าน Screen Options → CSS Classes
 *    แบบเดิมก็ยังใช้ได้ ทั้งสองทางควบคุม class ตัวเดียวกัน ผสมกันได้
 * 3. Sub-items level 2 = Column Header (ใส่ Description เป็น subtitle ได้)
 * 4. Sub-items level 3 = Links ใต้ column
 * 5. หรือใส่ Description ของ top-level item เป็น "elementor:ID" เพื่อใช้
 *    Elementor template แทนทั้ง panel
 */

/**
 * เพิ่ม checkbox "Enable Mega Menu" ในกล่องแก้ไข menu item ที่
 * Appearance → Menus (เฉพาะ top-level item — mega menu ใช้ได้แค่ depth 0)
 */
add_action( 'wp_nav_menu_item_custom_fields', 'hec_mega_menu_checkbox_field', 10, 4 );
function hec_mega_menu_checkbox_field( $item_id, $item, $depth, $args ) {
	if ( 0 !== $depth ) {
		return;
	}

	$checked = in_array( 'has-mega', (array) $item->classes, true );
	?>
	<p class="field-hec-mega description description-wide">
		<label for="edit-menu-item-hec-mega-<?php echo esc_attr( $item_id ); ?>">
			<input type="checkbox"
			       id="edit-menu-item-hec-mega-<?php echo esc_attr( $item_id ); ?>"
			       name="menu-item-hec-mega[<?php echo esc_attr( $item_id ); ?>]"
			       value="1"
			       <?php checked( $checked ); ?> />
			<?php esc_html_e( 'Enable Mega Menu', 'ehowme-ebase' ); ?>
		</label>
		<br>
		<span class="description">
			<?php esc_html_e( 'Level-2 sub-items become column headers, level-3 become links under each column. Or set this item\'s Description to "elementor:ID" to render an Elementor template as the whole panel instead.', 'ehowme-ebase' ); ?>
		</span>
	</p>
	<?php
}

/**
 * บันทึก checkbox ด้านบน — toggle class "has-mega" ใน _menu_item_classes
 * ที่ WP core save ไปแล้วตอนนี้ (จาก field CSS Classes แบบข้อความ) แทนที่
 * จะเขียนทับทั้ง array เอง เพื่อไม่ให้ class อื่นที่ผู้ใช้พิมพ์เองหายไป.
 *
 * เช็ก menu-item-title[$menu_item_db_id] แทนการเช็ก
 * menu-item-hec-mega ตรงๆ เพราะถ้าไม่ติ๊กอะไรเลยสักช่อง (ปิด mega ทุก
 * item) ทั้ง array menu-item-hec-mega จะไม่ถูกส่งมาใน $_POST เลย —
 * ถ้าเช็กจาก key นั้นตรงๆ จะพลาดไม่ลบ has-mega ออกในกรณีนี้.
 * menu-item-title ถูกส่งมาเสมอสำหรับทุก item ที่ผ่านฟอร์มนี้จริง
 * ไม่ว่าจะติ๊กอะไรไว้หรือไม่.
 */
add_action( 'wp_update_nav_menu_item', 'hec_save_mega_menu_checkbox', 10, 2 );
function hec_save_mega_menu_checkbox( $menu_id, $menu_item_db_id ) {
	if ( ! isset( $_POST['menu-item-title'][ $menu_item_db_id ] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$classes = get_post_meta( $menu_item_db_id, '_menu_item_classes', true );
	$classes = is_array( $classes ) ? $classes : [];
	$classes = array_values( array_filter( $classes, function ( $c ) {
		return '' !== $c && 'has-mega' !== $c;
	} ) );

	if ( ! empty( $_POST['menu-item-hec-mega'][ $menu_item_db_id ] ) ) {
		$classes[] = 'has-mega';
	}

	update_post_meta( $menu_item_db_id, '_menu_item_classes', $classes );
}

/**
 * โหลด JS/CSS ของ Menu Icon Picker เฉพาะหน้า Appearance → Menus
 * (nav-menus.php) เท่านั้น — ไม่ใช่ทุกหน้า admin — พร้อม wp_enqueue_media()
 * เพื่อให้ wp.media() ใน menu-icon-picker.js ใช้ได้ (เปิด Media Library
 * modal สำหรับปุ่ม "Upload Custom Icon") และ localize icon registry
 * (hec_get_cta_icons()) ให้ JS render กริดได้โดยไม่ต้อง duplicate ข้อมูล
 * icon เป็น HTML ซ้ำต่อ menu item (เมนูอาจมีหลายสิบ item)
 */
add_action( 'admin_enqueue_scripts', 'hec_enqueue_menu_icon_picker_assets' );
function hec_enqueue_menu_icon_picker_assets( $hook ) {
	if ( 'nav-menus.php' !== $hook ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'hec-menu-icon-picker',
		get_stylesheet_directory_uri() . '/assets/css/menu-icon-picker.css',
		[],
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'hec-menu-icon-picker',
		get_stylesheet_directory_uri() . '/assets/js/menu-icon-picker.js',
		[ 'jquery', 'media-editor' ],
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_localize_script( 'hec-menu-icon-picker', 'hecIconPickerData', [
		'icons' => hec_get_cta_icons(),
		'i18n'  => [
			'search'       => __( 'Search icons…', 'ehowme-ebase' ),
			'close'        => __( 'Close', 'ehowme-ebase' ),
			'none'         => __( 'None', 'ehowme-ebase' ),
			'upload'       => __( 'Upload Custom Icon', 'ehowme-ebase' ),
			'uploadTitle'  => __( 'Select Menu Icon', 'ehowme-ebase' ),
			'uploadButton' => __( 'Use this image', 'ehowme-ebase' ),
		],
	] );
}

/**
 * เพิ่มปุ่ม "Menu Icon" (เปิด picker แบบ search+grid ผ่าน JS, ดู
 * assets/js/menu-icon-picker.js) ในกล่องแก้ไข menu item ที่
 * Appearance → Menus (ทุก depth — แต่ตอนนี้ frontend render เฉพาะรายการใน
 * dropdown/submenu เท่านั้น ไม่ใช่แถบเมนูบนสุด, ดู HEC_Nav_Walker::start_el()
 * branch "DROPDOWN LINK") เลือกได้ทั้งจาก icon library ของธีม
 * (hec_get_cta_icons() — ไม่ใช่ icon ของ Anvogue หรือธีม/plugin อื่น เป็น
 * SVG ที่เขียนเองในสไตล์ Tabler Icons สัญญาอนุญาต MIT) หรือ upload รูป
 * custom (PNG/JPEG/GIF/WebP เท่านั้น — ไม่รับ SVG upload เพราะเสี่ยง XSS
 * ถ้ามีคน inject <script> เข้าไปใน SVG file) ผ่าน WP Media Library ปกติ
 * ไม่บังคับใส่ ค่าเริ่มต้นคือไม่มี icon เสมอ
 *
 * เก็บ 2 ฟิลด์แยกกัน: slug ของ preset icon กับ attachment ID ของรูป
 * custom — ฝั่ง JS จะเคลียร์อีกอันเวลาเลือกอย่างใดอย่างหนึ่ง, ฝั่ง save
 * (hec_save_menu_item_icon()) ก็ validate จากทั้งสอง input โดยรูป custom
 * (ถ้ามี) ชนะ preset เสมอ
 */
add_action( 'wp_nav_menu_item_custom_fields', 'hec_menu_item_icon_field', 10, 4 );
function hec_menu_item_icon_field( $item_id, $item, $depth, $args ) {
	$current_slug  = get_post_meta( $item_id, '_menu_item_hec_icon', true );
	$current_image = (int) get_post_meta( $item_id, '_menu_item_hec_icon_image', true );
	$preview       = '';

	if ( $current_image ) {
		$preview = wp_get_attachment_image( $current_image, [ 24, 24 ], false, [ 'alt' => '' ] );
	} elseif ( $current_slug ) {
		$preview = hec_get_menu_item_icon_markup( $current_slug );
	}
	?>
	<p class="field-hec-icon description description-wide">
		<span class="hec-icon-picker" data-item-id="<?php echo esc_attr( $item_id ); ?>">
			<button type="button" class="button hec-icon-picker-toggle">
				<span class="hec-icon-picker-preview"><?php echo $preview; // phpcs:ignore -- whitelisted SVG registry or wp_get_attachment_image(), both already safe ?></span>
				<?php esc_html_e( 'Menu Icon', 'ehowme-ebase' ); ?>
			</button>
			<input type="hidden"
			       class="hec-icon-picker-slug"
			       name="menu-item-hec-icon[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $current_slug ?: 'none' ); ?>">
			<input type="hidden"
			       class="hec-icon-picker-image"
			       name="menu-item-hec-icon-image[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $current_image ?: '' ); ?>">
		</span>
		<br>
		<span class="description">
			<?php esc_html_e( 'Shown to the left of the label in dropdown submenus only (not the top-level menu bar). Pick a preset icon or upload your own image (PNG/JPEG/GIF/WebP).', 'ehowme-ebase' ); ?>
		</span>
	</p>
	<?php
}

/**
 * บันทึก icon ที่เลือกด้านบน — รูป custom (ถ้ามี, validate ว่าเป็น
 * attachment จริงและ mime type อยู่ใน whitelist ราสเตอร์เท่านั้น — ไม่รับ
 * image/svg+xml แม้ media library จะมีไฟล์ svg อยู่ก็ตาม) ชนะ preset
 * icon เสมอ เก็บเป็น postmeta ของ menu item เอง (ต่างจาก has-mega ที่เก็บ
 * ใน _menu_item_classes เพราะ icon ไม่ใช่ CSS class ที่มีความหมายจะโชว์ใน
 * Screen Options ด้วย) validate slug ของ preset กับ whitelist ของ
 * hec_get_cta_icons() เสมอ ไม่เชื่อค่าที่ส่งมาตรงๆ
 */
add_action( 'wp_update_nav_menu_item', 'hec_save_menu_item_icon', 10, 2 );
function hec_save_menu_item_icon( $menu_id, $menu_item_db_id ) {
	if ( ! isset( $_POST['menu-item-title'][ $menu_item_db_id ] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$allowed_image_mimes = [ 'image/png', 'image/jpeg', 'image/gif', 'image/webp' ];
	$image_id            = isset( $_POST['menu-item-hec-icon-image'][ $menu_item_db_id ] )
		? absint( $_POST['menu-item-hec-icon-image'][ $menu_item_db_id ] )
		: 0;

	if ( $image_id && wp_attachment_is_image( $image_id ) && in_array( get_post_mime_type( $image_id ), $allowed_image_mimes, true ) ) {
		update_post_meta( $menu_item_db_id, '_menu_item_hec_icon_image', $image_id );
		// รูป custom ถูกเลือก — ล้าง preset slug ทิ้ง กัน markup เก่าตกค้าง
		// ไม่ให้ทั้งสอง source ขัดกันตอน render (ดู hec_get_menu_item_icon_markup_for_item()).
		delete_post_meta( $menu_item_db_id, '_menu_item_hec_icon' );
		return;
	}

	// ไม่มีรูป custom ที่ผ่าน validation — เคลียร์ค่าเก่า (ถ้ามี) แล้วตกไปใช้ preset ตามปกติ
	delete_post_meta( $menu_item_db_id, '_menu_item_hec_icon_image' );

	$posted = isset( $_POST['menu-item-hec-icon'][ $menu_item_db_id ] )
		? sanitize_key( wp_unslash( $_POST['menu-item-hec-icon'][ $menu_item_db_id ] ) )
		: 'none';

	$valid_icons = hec_get_cta_icons();
	if ( ! isset( $valid_icons[ $posted ] ) ) {
		$posted = 'none';
	}

	if ( 'none' === $posted ) {
		delete_post_meta( $menu_item_db_id, '_menu_item_hec_icon' );
	} else {
		update_post_meta( $menu_item_db_id, '_menu_item_hec_icon', $posted );
	}
}

class HEC_Nav_Walker extends Walker_Nav_Menu {

	/** ตรวจว่า item หรือ parent มี class has-mega ไหม */
	private function is_mega( $item ) {
		return in_array( 'has-mega', (array) $item->classes, true );
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			// ถ้า render Elementor panel ไปใน start_el แล้ว ข้ามได้เลย
			if ( ! empty( $this->_mega_rendered ) ) {
				$this->_mega_skip = true;
				return;
			}

			$output .= '<div class="mega-panel" role="region">';

			// ─── Standard mega-cols mode ────────────────────────────────
			$this->_mega_skip = false;
			$parent_title     = isset( $this->_mega_parent_title ) ? strtoupper( $this->_mega_parent_title ) : '';
			$parent_desc      = isset( $this->_mega_parent_desc ) ? $this->_mega_parent_desc : '';
			if ( $parent_title || $parent_desc ) {
				$header_text = $parent_title;
				if ( $parent_desc ) {
					$header_text .= ' — ' . $parent_desc;
				}
				$output .= '<div class="mega-hdr-bar"><span>' . esc_html( $header_text ) . '</span></div>';
			}
			$output .= '<div class="mega-cols">';

		} elseif ( 1 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			if ( ! empty( $this->_mega_skip ) ) return;
			$output .= '<ul class="mega-col-links">';
		} else {
			$output .= '<ul class="nav-dropdown">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			if ( ! empty( $this->_mega_skip ) ) {
				$output .= '</div>'; // .mega-panel
				$this->_mega_skip = false;
				return;
			}
			$output .= '</div>'; // .mega-cols
			$output .= '</div>'; // .mega-panel
		} elseif ( 1 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			if ( ! empty( $this->_mega_skip ) ) return;
			$output .= '</ul>'; // .mega-col-links
			$output .= '</div>'; // .mega-col
		} else {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$class_str = implode( ' ', array_filter( $classes ) );
		$has_mega  = $this->is_mega( $item );

		// Track mega state สำหรับ depth ลูก
		if ( 0 === $depth ) {
			$this->_current_mega      = $has_mega;
			$this->_mega_parent_title = $item->title;
			$this->_mega_elementor_id = 0;

			$desc = ! empty( $item->description ) ? trim( $item->description ) : '';

			// ตรวจว่า description เป็น "elementor:ID" หรือเปล่า
			if ( preg_match( '/^elementor:(\d+)$/i', $desc, $matches ) ) {
				$this->_mega_elementor_id = (int) $matches[1];
				$this->_mega_parent_desc  = ''; // ไม่แสดงเป็น header bar
			} else {
				$this->_mega_parent_desc = $desc;
			}
		}

		if ( 0 === $depth ) {
			// ── TOP LEVEL ────────────────────────────────
			$has_children = in_array( 'menu-item-has-children', $classes, true );
			$li_class     = 'nav-item' . ( $has_mega ? ' nav-item--mega' : '' );
			$output      .= '<li class="' . esc_attr( $li_class ) . '">';

			$url    = ! empty( $item->url ) ? $item->url : '#';
			$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$rel    = ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
			$cur    = $item->current ? ' aria-current="page"' : '';
			// แสดง chevron ถ้ามี children หรือเป็น mega+elementor (ไม่มี children แต่ต้องการ dropdown)
			$show_chevron = $has_children || ( $has_mega && ! empty( $this->_mega_elementor_id ) );
			$popup        = $show_chevron ? ' aria-haspopup="true" aria-expanded="false"' : '';

			$output .= '<a href="' . esc_url( $url ) . '" class="nav-link"' . $target . $rel . $cur . $popup . '>';
			$output .= esc_html( $item->title );
			if ( $show_chevron ) {
				$output .= '<svg class="nav-chevron" viewBox="0 0 10 10" fill="none" aria-hidden="true" width="10" height="10"><path d="M2 3.5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
			}
			$output .= '</a>';

			// ── Elementor template mode: render panel ตรงนี้เลย (ไม่ต้องมี children) ──
			if ( $has_mega && ! empty( $this->_mega_elementor_id ) && class_exists( '\Elementor\Plugin' ) ) {
				$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display(
					$this->_mega_elementor_id,
					true
				);
				$output .= '<div class="mega-panel" role="region">';
				$output .= '<div class="mega-elementor-wrap">' . $content . '</div>';
				$output .= '</div>';
				$this->_mega_rendered = true;
			} else {
				$this->_mega_rendered = false;
			}

		} elseif ( 1 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			// ── MEGA COLUMN HEADER (skip ถ้าใช้ Elementor mode) ─────
			if ( ! empty( $this->_mega_skip ) ) return;
			$subtitle = ! empty( $item->description ) ? $item->description : '';
			$output  .= '<div class="mega-col">';
			$output  .= '<div class="mega-col-hdr">';
			$output  .= '<a href="' . esc_url( $item->url ?: '#' ) . '" class="mega-col-title">' . esc_html( $item->title ) . '</a>';
			if ( $subtitle ) {
				$output .= '<span class="mega-col-sub">' . esc_html( $subtitle ) . '</span>';
			}
			$output .= '</div>';

		} elseif ( 2 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			// ── MEGA LINK (skip ถ้าใช้ Elementor mode) ──────────────
			if ( ! empty( $this->_mega_skip ) ) return;
			$url    = ! empty( $item->url ) ? $item->url : '#';
			$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$output .= '<li>';
			$output .= '<a href="' . esc_url( $url ) . '" class="mega-link"' . $target . '>' . esc_html( $item->title ) . '</a>';
			$output .= '</li>';

		} else {
			// ── DROPDOWN LINK (1st or 2nd level) ────────────
			// depth 1 = โผล่จาก .header-nav โดยตรง (dropdown แรก), depth 2 =
			// ซ้อนอีกชั้นในนั้น (dropdown ที่สอง, สูงสุดตาม depth=>3 ใน
			// hec_header_navigation()) ถ้า item มี children ต่อ (เกิดได้ที่
			// depth 1 เท่านั้น เพราะ depth 2 คือชั้นลึกสุดที่ render) ใส่
			// chevron + aria-haspopup ให้เหมือน top level เพื่อบอกว่ามี
			// submenu ซ้อนอยู่ (เปิดด้วย hover/focus — flyout ไปทางขวา,
			// ดู .nav-dropdown .nav-dropdown ใน style.css)
			//
			// สำคัญ: ห้ามปิด </li> ตรงนี้ (ใน start_el) ถ้า item มี children —
			// ต้องปล่อยให้ end_el ปิดหลังจาก start_lvl/children/end_lvl ของ
			// item นี้ทำงานเสร็จก่อน (เหมือน top-level ที่ depth 0) เดิมโค้ด
			// ปิด </li> ที่นี่ทันที ทำให้ <ul class="nav-dropdown"> ของชั้น
			// ที่ 2 ถูกต่อออกมานอก <li> ที่ปิดไปแล้ว (ul ซ้อนใน ul ตรงๆ ไม่ใช่
			// ใน li — HTML ไม่ยอมรับ) เบราว์เซอร์เลยตัดมันหลุดออกจากโครงสร้าง
			// dropdown ที่ 2 เลยไม่โผล่ตอน hover แม้ chevron จะโชว์ก็ตาม
			$has_children = in_array( 'menu-item-has-children', $classes, true );
			$url          = ! empty( $item->url ) ? $item->url : '#';
			$cur          = $item->current ? ' aria-current="page"' : '';
			$popup        = $has_children ? ' aria-haspopup="true" aria-expanded="false"' : '';
			// Optional per-item icon (Appearance → Menus → item → "Menu
			// Icon", added by hec_menu_item_icon_field() above) — checks
			// a custom-uploaded image first, falls back to the theme's
			// whitelisted SVG registry, falls back to nothing. See
			// hec_get_menu_item_icon_markup_for_item().
			$icon_markup  = hec_get_menu_item_icon_markup_for_item( $item->ID );
			$output      .= '<li>';
			$output      .= '<a href="' . esc_url( $url ) . '" class="nav-dropdown-link"' . $cur . $popup . '>';
			$output      .= '<span class="nav-dropdown-main">' . $icon_markup . '<span class="nav-dropdown-label">' . esc_html( $item->title ) . '</span></span>';
			if ( $has_children ) {
				$output .= '<svg class="nav-chevron" viewBox="0 0 10 10" fill="none" aria-hidden="true" width="10" height="10"><path d="M2 3.5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
			}
			$output .= '</a>';
			// </li> ปิดใน end_el() แทน — ดูคอมเมนต์ด้านบน
		}
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$classes = empty( $item->classes ) ? [] : (array) $item->classes;

		if ( 0 === $depth ) {
			$output .= '</li>';
		} elseif ( 1 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			// mega-col ปิดใน end_lvl
		} elseif ( 2 === $depth && isset( $this->_current_mega ) && $this->_current_mega ) {
			// li ปิดใน start_el แล้ว
		} else {
			$output .= '</li>';
		}
	}
}

/**
 * Get mobile menu ID — falls back to desktop menu if not set
 */
function hec_get_mobile_menu_id() {
	// รองรับ multi-language เช่นเดียวกับ hec_get_header_menu_id()
	$mobile_id = function_exists( 'hec_get_multilang_option' )
		? hec_get_multilang_option( 'hec_mobile_menu_id', '' )
		: get_option( 'hec_mobile_menu_id', '' );
	return $mobile_id ? (int) $mobile_id : hec_get_header_menu_id();
}

/**
 * Render off-canvas panel (recursive helper)
 */
function hec_render_ofc_panel( $panel_id, $parent_item, $items, $children_of, $is_root ) {
	$active = $is_root ? ' ofc-panel--active' : '';
	echo '<div class="ofc-panel' . $active . '" data-panel="' . esc_attr( $panel_id ) . '">';

	// ── Panel header ──
	echo '<div class="ofc-hdr">';
	if ( $is_root ) {
		echo '<span class="ofc-hdr-title">' . esc_html__( 'Menu', 'ehowme-ebase' ) . '</span>';
	} else {
		echo '<button class="ofc-back" aria-label="' . esc_attr__( 'Back', 'ehowme-ebase' ) . '">';
		echo '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M13 4l-6 6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		echo '</button>';
		if ( $parent_item && ! empty( $parent_item->url ) && '#' !== $parent_item->url ) {
			echo '<a href="' . esc_url( $parent_item->url ) . '" class="ofc-hdr-title">' . esc_html( $parent_item->title ) . '</a>';
		} else {
			echo '<span class="ofc-hdr-title">' . esc_html( $parent_item ? $parent_item->title : '' ) . '</span>';
		}
	}
	echo '<button class="ofc-close" aria-label="' . esc_attr__( 'Close menu', 'ehowme-ebase' ) . '">';
	echo '<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M2 2l14 14M16 2L2 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
	echo '</button>';
	echo '</div>'; // .ofc-hdr

	// ── Item list ──
	echo '<ul class="ofc-list">';
	foreach ( $items as $item ) {
		$has_children = ! empty( $children_of[ $item->ID ] );
		$classes      = (array) $item->classes;
		$is_current   = in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true );
		$li_class     = 'ofc-item';
		if ( $has_children ) $li_class .= ' ofc-item--has-children';
		if ( $is_current )   $li_class .= ' ofc-item--current';

		echo '<li class="' . esc_attr( $li_class ) . '">';

		// Same per-item icon as the desktop dropdown (Appearance → Menus
		// → item → "Menu Icon") — this off-canvas panel is its own
		// separate render function (not HEC_Nav_Walker), so it needs its
		// own call to pick the icon up; it was rendering plain text-only
		// links before, which is why icons "didn't work" on mobile even
		// though they were set and worked fine on desktop.
		$icon_markup = hec_get_menu_item_icon_markup_for_item( $item->ID );

		if ( $has_children ) {
			echo '<div class="ofc-item-row">';
			$url = ! empty( $item->url ) && '#' !== $item->url ? $item->url : false;
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" class="ofc-link">' . $icon_markup . '<span class="ofc-link-label">' . esc_html( $item->title ) . '</span></a>';
			} else {
				echo '<span class="ofc-link">' . $icon_markup . '<span class="ofc-link-label">' . esc_html( $item->title ) . '</span></span>';
			}
			echo '<button class="ofc-trigger" data-target="item-' . esc_attr( $item->ID ) . '" aria-label="' . esc_attr( $item->title ) . ' submenu">';
			echo '<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M5 3l8 6-8 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			echo '</button>';
			echo '</div>';
		} else {
			$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			echo '<a href="' . esc_url( $item->url ?: '#' ) . '" class="ofc-link"' . $target . '>' . $icon_markup . '<span class="ofc-link-label">' . esc_html( $item->title ) . '</span></a>';
		}

		echo '</li>';
	}
	echo '</ul>';
	echo '</div>'; // .ofc-panel
}

/**
 * Render full off-canvas menu HTML
 * Called from custom-header.php when mobile style is sidebar
 */
function hec_render_offcanvas() {
	$menu_id = hec_get_mobile_menu_id();
	if ( ! $menu_id ) return;

	$all_items = wp_get_nav_menu_items( $menu_id, [ 'update_post_term_cache' => false ] );
	if ( ! $all_items ) return;

	// Build children lookup
	$children_of = [];
	foreach ( $all_items as $item ) {
		$pid = (string) $item->menu_item_parent;
		if ( ! isset( $children_of[ $pid ] ) ) $children_of[ $pid ] = [];
		$children_of[ $pid ][] = $item;
	}

	$style = get_option( 'hec_mobile_menu_style', 'sidebar-right' );
	$side  = ( 'sidebar-left' === $style ) ? 'left' : 'right';

	echo '<div id="hec-offcanvas" class="hec-offcanvas hec-offcanvas--' . esc_attr( $side ) . '" aria-hidden="true" role="dialog" aria-modal="true">';
	echo '<div class="ofc-wrap">';

	// Root panel
	$root_items = isset( $children_of['0'] ) ? $children_of['0'] : [];
	hec_render_ofc_panel( 'root', null, $root_items, $children_of, true );

	// Sub-panels for every item that has children
	foreach ( $all_items as $item ) {
		$iid = (string) $item->ID;
		if ( empty( $children_of[ $iid ] ) ) continue;
		hec_render_ofc_panel( 'item-' . $item->ID, $item, $children_of[ $iid ], $children_of, false );
	}

	echo '</div>'; // .ofc-wrap

	// ── Footer: CTA only ─────────────────────────────────────
	// .ofc-footer is flex-direction:column, so DOM order = top-to-bottom
	// stacking order — Button 2 (secondary) renders first so Button 1
	// (primary) always ends up last, i.e. at the very bottom of the panel.
	echo '<div class="ofc-footer">';

	// Button 2 (secondary)
	if ( get_option( 'hec_show_cta_button_2', '0' ) ) {
		$lang   = function_exists( 'hec_get_current_language' ) ? hec_get_current_language() : '';
		$suffix = $lang ? '_' . $lang : '';
		$label  = get_option( 'hec_cta_label_2' . $suffix ) ?: get_option( 'hec_cta_label_2', __( 'Learn More', 'ehowme-ebase' ) );
		$url    = get_option( 'hec_cta_url_2' . $suffix ) ?: get_option( 'hec_cta_url_2', home_url( '/' ) );
		if ( $label && $url ) {
			$icon_markup = hec_get_cta_icon_markup( get_option( 'hec_cta_icon_2', 'none' ) );
			$icon_before = 'before' === get_option( 'hec_cta_icon_position_2', 'after' );
			echo '<a href="' . esc_url( $url ) . '" class="header-cta-btn header-cta-btn--secondary">';
			if ( $icon_before ) echo $icon_markup;
			echo esc_html( $label );
			if ( ! $icon_before ) echo $icon_markup;
			echo '</a>';
		}
	}

	// CTA button (primary) — always last, i.e. bottom-most
	if ( get_option( 'hec_show_cta_button', '1' ) ) {
		$lang   = function_exists( 'hec_get_current_language' ) ? hec_get_current_language() : '';
		$suffix = $lang ? '_' . $lang : '';
		$label  = get_option( 'hec_cta_label' . $suffix ) ?: get_option( 'hec_cta_label', __( 'Contact Us', 'ehowme-ebase' ) );
		$url    = get_option( 'hec_cta_url' . $suffix ) ?: get_option( 'hec_cta_url', home_url( '/contact' ) );
		if ( $label && $url ) {
			$icon_markup = hec_get_cta_icon_markup( get_option( 'hec_cta_icon', 'arrow-right' ) );
			$icon_before = 'before' === get_option( 'hec_cta_icon_position', 'after' );
			echo '<a href="' . esc_url( $url ) . '" class="header-cta-btn">';
			if ( $icon_before ) echo $icon_markup;
			echo esc_html( $label );
			if ( ! $icon_before ) echo $icon_markup;
			echo '</a>';
		}
	}

	echo '</div>'; // .ofc-footer
	echo '</div>'; // #hec-offcanvas
}

/**
 * Enqueue header JS (dropdown + sticky)
 */
function hec_enqueue_header_scripts() {
	wp_enqueue_script(
		'hec-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		[],
		wp_get_theme()->get( 'Version' ), // was hardcoded '1.0.0' — never busted cache on edits
		true
	);
}
add_action( 'wp_enqueue_scripts', 'hec_enqueue_header_scripts' );
