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
			// ── DROPDOWN LINK ─────────────────────────────
			$url    = ! empty( $item->url ) ? $item->url : '#';
			$cur    = $item->current ? ' aria-current="page"' : '';
			$output .= '<li>';
			$output .= '<a href="' . esc_url( $url ) . '" class="nav-dropdown-link"' . $cur . '>' . esc_html( $item->title ) . '</a>';
			$output .= '</li>';
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

		if ( $has_children ) {
			echo '<div class="ofc-item-row">';
			$url = ! empty( $item->url ) && '#' !== $item->url ? $item->url : false;
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" class="ofc-link">' . esc_html( $item->title ) . '</a>';
			} else {
				echo '<span class="ofc-link">' . esc_html( $item->title ) . '</span>';
			}
			echo '<button class="ofc-trigger" data-target="item-' . esc_attr( $item->ID ) . '" aria-label="' . esc_attr( $item->title ) . ' submenu">';
			echo '<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M5 3l8 6-8 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			echo '</button>';
			echo '</div>';
		} else {
			$target = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			echo '<a href="' . esc_url( $item->url ?: '#' ) . '" class="ofc-link"' . $target . '>' . esc_html( $item->title ) . '</a>';
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
