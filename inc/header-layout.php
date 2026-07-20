<?php
/**
 * Header Layout — Slot-based Header Builder (per device: Desktop / Tablet / Mobile)
 *
 * ให้กำหนดได้ว่า element ไหน (Logo, Menu, Language Switcher, ปุ่ม ฯลฯ) อยู่โซนไหน
 * ของ header (Left / Center / Right) และเรียงลำดับภายในโซนได้ — แยกกันได้อิสระ
 * สำหรับ Desktop / Tablet / Mobile (ไม่จำเป็นต้องเหมือนกัน) ผ่านหน้า
 * Appearance → Theme Options → Header Layout (ดู inc/theme-options.php,
 * method render_header_layout_builder()).
 *
 * เพิ่ม element ใหม่ในอนาคต: เติมเข้า hec_get_header_elements() รายการเดียวพอ
 * ระบบ zone/renderer ที่เหลือใช้งานได้ทันทีโดยไม่ต้องแก้ที่อื่น
 *
 * วิธีทำงาน (สำคัญ): แต่ละ element จะถูก render ลง DOM แค่ครั้งเดียวเท่านั้น
 * (กัน id ซ้ำ เช่น เมนู, off-canvas, language switcher) โดยฝังไว้ในโซนตาม
 * ผังของ "Desktop" เป็นตำแหน่งจริงทาง DOM (หรือใน pool ที่ซ่อนไว้ ถ้า element
 * นั้นไม่ได้ใช้ใน Desktop เลย) แล้วฝัง data-desktop-zone / data-tablet-zone /
 * data-mobile-zone (+ -order) ไว้ที่ wrapper ของแต่ละตัว ให้ JS
 * (assets/js/header.js) ย้าย element ไปโซนที่ถูกต้องตาม breakpoint ปัจจุบัน
 * ตอนโหลดหน้า/resize อีกที — ดู hecApplyHeaderZones() ใน header.js
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * รายการ element ทั้งหมดที่วางลงใน header ได้ พร้อม render callback
 */
function hec_get_header_elements() {
	return [
		'logo'          => [
			'label'  => __( 'Logo', 'ehowme-ebase' ),
			'render' => 'hec_render_header_logo',
		],
		'main_menu'     => [
			'label'  => __( 'Main Menu', 'ehowme-ebase' ),
			'render' => 'hec_header_navigation',
		],
		'lang_switcher' => [
			'label'  => __( 'Language Switcher', 'ehowme-ebase' ),
			'render' => 'hec_header_language_switcher',
		],
		'cta_button'    => [
			'label'  => __( 'Button 1 (Primary)', 'ehowme-ebase' ),
			'render' => 'hec_header_cta_button',
		],
		'cta_button_2'  => [
			'label'  => __( 'Button 2 (Secondary)', 'ehowme-ebase' ),
			'render' => 'hec_header_cta_button_2',
		],
		'mobile_toggle' => [
			'label'  => __( 'Mobile Menu Toggle', 'ehowme-ebase' ),
			'render' => 'hec_render_mobile_toggle',
		],
	];
}

/** รายชื่อ device / zone ที่ระบบรองรับ — ใช้ร่วมกันหลายจุดด้านล่าง */
function hec_get_header_devices() {
	return [ 'desktop', 'tablet', 'mobile' ];
}
function hec_get_header_zones() {
	return [ 'left', 'center', 'right' ];
}

/**
 * ค่า default — ตรงกับ layout เดิมของ header ก่อนมีระบบ per-device นี้ (ใช้ตอน
 * ยังไม่เคย save หรือกรณี option เสียหาย เพื่อไม่ให้ header หายไปทั้งแถบ)
 * Tablet/Mobile ตั้งค่าเริ่มต้นให้ center ว่าง (เมนูถูกซ่อนเป็น hamburger อยู่แล้ว
 * ที่ breakpoint เหล่านี้) และตัด main_menu ออกเพื่อไม่ให้ไปค้างใน "Not Used"
 */
function hec_get_default_header_layout() {
	return [
		// mobile_toggle is included in every device's default 'right' zone —
		// harmless on Desktop since #site-header .mobile-menu-toggle stays
		// display:none there regardless of zone (see style.css), and this
		// way it always has a real DOM home instead of needing the hidden
		// pool. Position/zone is still fully draggable per device.
		'desktop' => [
			'left'   => [ 'logo' ],
			'center' => [ 'main_menu' ],
			'right'  => [ 'cta_button_2', 'lang_switcher', 'cta_button', 'mobile_toggle' ],
		],
		'tablet'  => [
			'left'   => [ 'logo' ],
			'center' => [],
			'right'  => [ 'lang_switcher', 'cta_button', 'mobile_toggle' ],
		],
		'mobile'  => [
			'left'   => [ 'logo' ],
			'center' => [],
			'right'  => [ 'lang_switcher', 'mobile_toggle' ],
		],
	];
}

/**
 * ดึง layout ที่บันทึกไว้ (ทั้ง 3 device) กรองให้เหลือเฉพาะ element ที่ยังมีอยู่จริง
 * ใน registry — รองรับข้อมูลรูปแบบเก่า (ก่อนมี per-device, เก็บ left/center/right
 * แบบแบนไว้ระดับบนสุด) โดย migrate เป็น desktop ของโครงสร้างใหม่ให้อัตโนมัติ
 * (ไม่เขียนกลับ DB ตรงนี้ — จะถูกบันทึกเป็นรูปแบบใหม่ตอน save ครั้งถัดไป)
 */
function hec_get_header_layout() {
	$saved     = get_option( 'hec_header_layout', '' );
	$devices   = hec_get_header_devices();
	$zones     = hec_get_header_zones();
	$valid_ids = array_keys( hec_get_header_elements() );
	$defaults  = hec_get_default_header_layout();

	$decoded = $saved ? json_decode( $saved, true ) : null;
	if ( ! is_array( $decoded ) ) {
		$decoded = [];
	}

	// รูปแบบเก่า: { left: [...], center: [...], right: [...] } อยู่ระดับบนสุด
	// (ไม่มี key desktop/tablet/mobile) — ถือเป็นผัง desktop เดิม
	$is_legacy_flat = isset( $decoded['left'] ) || isset( $decoded['center'] ) || isset( $decoded['right'] );
	if ( $is_legacy_flat && ! isset( $decoded['desktop'] ) ) {
		$decoded = [ 'desktop' => $decoded ];
	}

	$clean = [];
	foreach ( $devices as $device ) {
		$clean[ $device ] = [];
		foreach ( $zones as $zone ) {
			$ids = ( isset( $decoded[ $device ][ $zone ] ) && is_array( $decoded[ $device ][ $zone ] ) )
				? $decoded[ $device ][ $zone ]
				: null;
			if ( null === $ids ) {
				// ไม่มีข้อมูลของ device นี้เลย (เช่น migrate จาก legacy ที่มีแค่ desktop)
				// ใช้ default ของ device นั้นแทน
				$ids = $defaults[ $device ][ $zone ] ?? [];
			}
			$clean[ $device ][ $zone ] = array_values( array_intersect( $ids, $valid_ids ) );
		}
	}

	// Migration safety net: sites that saved a layout BEFORE "Mobile Menu
	// Toggle" became a draggable element (see hec_get_header_elements())
	// have no zone containing 'mobile_toggle' anywhere, for any device —
	// without this, the element would render into no zone at all (see
	// hec_render_header_zones_responsive()) and the hamburger button
	// would simply vanish from every real page. Append it to 'right' on
	// any device where it's missing so existing sites keep a working
	// toggle after upgrading; users can still drag it anywhere they like
	// afterwards. Only kicks in per-device, so once a user's own save
	// includes it (anywhere, even "Not Used" — i.e. genuinely absent from
	// every zone), that device is left alone.
	$toggle_used_anywhere = false;
	foreach ( $devices as $device ) {
		foreach ( $zones as $zone ) {
			if ( in_array( 'mobile_toggle', $clean[ $device ][ $zone ], true ) ) {
				$toggle_used_anywhere = true;
				break 2;
			}
		}
	}
	if ( ! $toggle_used_anywhere && isset( $decoded['desktop'] ) ) {
		// Only backfill when there WAS a pre-existing save to migrate —
		// a brand-new site with no saved option at all already gets the
		// toggle from hec_get_default_header_layout() above.
		foreach ( $devices as $device ) {
			$clean[ $device ]['right'][] = 'mobile_toggle';
		}
	}

	return $clean;
}

/** เช่นเดียวกับ hec_get_header_layout() แต่คืนแค่ device เดียว */
function hec_get_header_layout_for_device( $device ) {
	$all = hec_get_header_layout();
	return $all[ $device ] ?? hec_get_default_header_layout()[ $device ];
}

/**
 * Render header ทั้งแถบ (ทุกโซน ทุก device) — เรียกจาก
 * template-parts/custom-header.php แทนที่ hec_render_header_zone() เดิม
 *
 * แต่ละ element render ลง DOM ครั้งเดียว: ฝังไว้ในโซนตามผัง "Desktop" จริง ๆ
 * (หรือใน pool ที่ซ่อนไว้ท้าย .header-inner ถ้า desktop ไม่ได้ใช้ element นั้นเลย
 * แต่ tablet/mobile ใช้) แล้วฝัง data attribute บอกโซน/ลำดับของแต่ละ device ไว้
 * ให้ JS ย้ายเข้าโซนจริงตาม breakpoint ที่ใช้งานอยู่อีกที (ดู header.js)
 */
function hec_render_header_zones_responsive() {
	$layout   = hec_get_header_layout();
	$elements = hec_get_header_elements();
	$devices  = hec_get_header_devices();
	$zones    = hec_get_header_zones();

	// รวบรวม zone/order ของแต่ละ device ต่อ element หนึ่งตัว
	$meta = [];
	foreach ( $devices as $device ) {
		foreach ( $zones as $zone ) {
			foreach ( array_values( $layout[ $device ][ $zone ] ?? [] ) as $i => $id ) {
				if ( ! isset( $elements[ $id ] ) ) {
					continue;
				}
				$meta[ $id ][ $device . '_zone' ]  = $zone;
				$meta[ $id ][ $device . '_order' ] = $i;
			}
		}
	}

	// ตำแหน่งจริงทาง DOM: ใช้ผัง desktop เป็นหลัก — element ที่ desktop ไม่ได้ใช้
	// เลย (ใช้แค่ tablet/mobile) จะถูก render ลง pool ที่ซ่อนไว้แทน
	$in_zone = [ 'left' => [], 'center' => [], 'right' => [] ];
	$pool    = [];
	foreach ( $meta as $id => $m ) {
		if ( isset( $m['desktop_zone'] ) ) {
			$in_zone[ $m['desktop_zone'] ][] = $id;
		} else {
			$pool[] = $id;
		}
	}
	foreach ( $in_zone as $zone => $ids ) {
		usort(
			$ids,
			function ( $a, $b ) use ( $meta ) {
				return ( $meta[ $a ]['desktop_order'] ?? 0 ) <=> ( $meta[ $b ]['desktop_order'] ?? 0 );
			}
		);
		$in_zone[ $zone ] = $ids;
	}

	foreach ( $zones as $zone ) {
		echo '<div class="header-zone header-zone--' . esc_attr( $zone ) . '">';
		foreach ( $in_zone[ $zone ] as $id ) {
			hec_render_zone_item( $id, $elements[ $id ], $meta[ $id ] );
		}
		echo '</div>';
	}

	if ( $pool ) {
		// display:none เป็น fallback เผื่อ JS ปิด/ยังไม่ทำงาน — element ในนี้จะ
		// แสดงก็ต่อเมื่อ JS ย้ายออกจาก pool ไปโซนจริงสำเร็จเท่านั้น
		echo '<div class="hec-zone-pool" aria-hidden="true">';
		foreach ( $pool as $id ) {
			hec_render_zone_item( $id, $elements[ $id ], $meta[ $id ] );
		}
		echo '</div>';
	}
}

/**
 * Render element เดียว ห่อด้วย wrapper ที่ฝัง data-{device}-zone/order ไว้ให้ JS
 * ใช้ย้ายตำแหน่งตอน resize/โหลดหน้า — display:contents ทำให้ wrapper ไม่กระทบ
 * flex/grid layout ของโซน (ลูกข้างในทำตัวเหมือนเป็น child ตรงของโซนเลย)
 */
function hec_render_zone_item( $id, $element, $meta ) {
	$attrs = [ 'data-hec-el="' . esc_attr( $id ) . '"' ];
	foreach ( hec_get_header_devices() as $device ) {
		$zone  = $meta[ $device . '_zone' ] ?? 'none';
		$order = $meta[ $device . '_order' ] ?? 0;
		$attrs[] = 'data-' . $device . '-zone="' . esc_attr( $zone ) . '"';
		$attrs[] = 'data-' . $device . '-order="' . esc_attr( $order ) . '"';
	}
	echo '<div class="hec-zone-item" style="display:contents" ' . implode( ' ', $attrs ) . '>';
	if ( isset( $element['render'] ) && is_callable( $element['render'] ) ) {
		call_user_func( $element['render'] );
	}
	echo '</div>';
}

/**
 * Element "Logo" — แยกเป็น callback เพื่อให้ระบบ zone เรียกได้เหมือน element อื่น
 * (ย้าย markup เดิมจาก template-parts/custom-header.php มาไว้ที่นี่ ห่อด้วย
 * div.header-logo เหมือนเดิมทุกประการ เพื่อให้ CSS เดิม .header-logo img /
 * .header-logo .site-title-text ยังทำงานได้ไม่ว่าจะย้ายไปโซนไหน)
 */
function hec_render_header_logo() {
	$hec_logo    = get_option( 'hec_logo_url', '' );
	$hec_logo_2x = get_option( 'hec_logo_url_2x', '' );
	$logo_height = (int) get_option( 'hec_header_logo_height', 50 );
	?>
	<div class="header-logo">
		<?php if ( $hec_logo ) :
			// Logo จาก Theme Options
			$srcset = $hec_logo_2x ? esc_url( $hec_logo_2x ) . ' 2x' : '';
			?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo esc_url( $hec_logo ); ?>"
				     <?php if ( $srcset ) echo 'srcset="' . esc_attr( $srcset ) . '"'; ?>
				     alt="<?php bloginfo( 'name' ); ?>"
				     height="<?php echo esc_attr( $logo_height ); ?>"
				     style="max-height:<?php echo esc_attr( $logo_height ); ?>px;width:auto;">
			</a>
		<?php elseif ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="site-title-text"><?php bloginfo( 'name' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Element "Mobile Menu Toggle" — the hamburger button that opens the mobile
 * nav (dropdown or off-canvas — see assets/js/header.js). Markup moved here
 * from template-parts/custom-header.php so it flows through the same
 * per-device zone system as every other element (draggable in Theme
 * Options → Header Layout). It stays a SINGLE DOM node either way — the
 * zone system always renders each element exactly once and only moves it
 * between zone containers, never duplicates it — so #hec-mobile-toggle
 * stays a valid unique id no matter which zone/device it's assigned to.
 * Visibility itself is still controlled purely by CSS (display:none above
 * 991px, display:flex below — see style.css), independent of zone
 * placement, so dragging it onto a Desktop zone has no visible effect.
 */
function hec_render_mobile_toggle() {
	?>
	<button class="mobile-menu-toggle" id="hec-mobile-toggle" aria-expanded="false" aria-controls="site-navigation" aria-label="<?php esc_attr_e( 'Toggle Menu', 'ehowme-ebase' ); ?>">
		<span></span>
		<span></span>
		<span></span>
	</button>
	<?php
}

/**
 * Sanitize + save layout (ทั้ง 3 device) — ผูกกับ register_setting(
 * 'hec_header_layout', ... ) ใน inc/theme-options.php รับค่าดิบเป็น JSON
 * string จาก hidden input #hec_header_layout ที่ JS (theme-options-admin.js)
 * serialize มาให้ตอน submit ในรูปแบบ { desktop:{left,center,right},
 * tablet:{...}, mobile:{...} }
 */
function hec_sanitize_header_layout( $raw_json ) {
	$decoded = json_decode( wp_unslash( (string) $raw_json ), true );
	if ( ! is_array( $decoded ) ) {
		return wp_json_encode( hec_get_default_header_layout() );
	}

	// รองรับกรณี JS เก่ายังส่งรูปแบบแบน (left/center/right ตรง ๆ) มาเป็น desktop
	if ( ( isset( $decoded['left'] ) || isset( $decoded['center'] ) || isset( $decoded['right'] ) ) && ! isset( $decoded['desktop'] ) ) {
		$decoded = [ 'desktop' => $decoded ];
	}

	$valid_ids = array_keys( hec_get_header_elements() );
	$devices   = hec_get_header_devices();
	$zones     = hec_get_header_zones();
	$defaults  = hec_get_default_header_layout();
	$clean     = [];

	foreach ( $devices as $device ) {
		$clean[ $device ] = [];
		foreach ( $zones as $zone ) {
			$ids = ( isset( $decoded[ $device ][ $zone ] ) && is_array( $decoded[ $device ][ $zone ] ) )
				? $decoded[ $device ][ $zone ]
				: $defaults[ $device ][ $zone ];
			$ids = array_map( 'sanitize_key', $ids );
			$clean[ $device ][ $zone ] = array_values( array_intersect( $ids, $valid_ids ) );
		}
	}

	return wp_json_encode( $clean );
}
