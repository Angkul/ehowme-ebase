<?php
/**
 * Hello Elementor Child Theme — functions.php
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =============================================
   1. ENQUEUE PARENT & CHILD STYLES
   ============================================= */

add_action( 'wp_enqueue_scripts', 'hec_enqueue_styles' );

function hec_enqueue_styles() {
	// Parent theme stylesheet
	wp_enqueue_style(
		'hello-elementor-parent',
		get_template_directory_uri() . '/style.css',
		[],
		wp_get_theme( 'hello-elementor' )->get( 'Version' )
	);

	// Child theme stylesheet
	wp_enqueue_style(
		'ehowme-ebase',
		get_stylesheet_uri(),
		[ 'hello-elementor-parent' ],
		wp_get_theme()->get( 'Version' )
	);
}

/* =============================================
   2. THEME SETUP
   ============================================= */

add_action( 'after_setup_theme', 'hec_theme_setup' );

function hec_theme_setup() {
	// Text domain
	load_child_theme_textdomain( 'ehowme-ebase', get_stylesheet_directory() . '/languages' );

	// รองรับ custom logo
	add_theme_support( 'custom-logo', [
		'height'      => 100,
		'width'       => 300,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	// Title tag
	add_theme_support( 'title-tag' );

	// Post thumbnails
	add_theme_support( 'post-thumbnails' );

	// HTML5
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	] );
}

/* =============================================
   3. LOAD INCLUDES
   ============================================= */

// Language helper (ต้องโหลดก่อน theme options)
require_once get_stylesheet_directory() . '/ebase-config.php';

// Language helper (ต้องโหลดก่อน theme options)
require_once get_stylesheet_directory() . '/inc/language-helper.php';

// Theme Options page
require_once get_stylesheet_directory() . '/inc/theme-options.php';

// Header functions & Walker
require_once get_stylesheet_directory() . '/inc/header-functions.php';

// GitHub Auto-Updater via Plugin Update Checker (admin only)
if ( is_admin() && EBASE_GITHUB_USER && EBASE_GITHUB_REPO ) {
	$_puc_dir  = get_stylesheet_directory() . '/inc/plugin-update-checker';
	$_puc_path = $_puc_dir . '/plugin-update-checker.php';
	if ( file_exists( $_puc_path ) ) {
		// โหลด Parsedown ก่อน (PUC 5.7 ใส่ไว้ใน vendor/ ตรงๆ ไม่ใช่ Composer)
		$_parsedown = $_puc_dir . '/vendor/Parsedown.php';
		if ( file_exists( $_parsedown ) ) {
			require_once $_parsedown;
		}
		require_once $_puc_path;
		$_ebase_updater = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			'https://github.com/' . EBASE_GITHUB_USER . '/' . EBASE_GITHUB_REPO . '/',
			get_stylesheet_directory() . '/style.css',
			EBASE_GITHUB_REPO
		);
		$_ebase_updater->setBranch( 'main' );
		if ( EBASE_GITHUB_PAT ) {
			$_ebase_updater->setAuthentication( EBASE_GITHUB_PAT );
		}
	}
}

/* =============================================
   4. SYNC THEME OPTIONS LOGO → WP CUSTOM LOGO
   เพื่อป้องกัน Elementor Pro warning เรื่อง custom_logo
   ============================================= */

/**
 * Filter theme_mod custom_logo แบบ dynamic
 * ป้องกัน Elementor Pro warning เมื่อยังไม่ได้ set logo ใน Customizer
 */
add_filter( 'theme_mod_custom_logo', 'hec_provide_custom_logo_id' );

function hec_provide_custom_logo_id( $logo_id ) {
	// ถ้ามี logo_id อยู่แล้วให้ใช้ของเดิม
	if ( $logo_id ) {
		return $logo_id;
	}

	// ดึงจาก Theme Options
	$logo_url = get_option( 'hec_logo_url', '' );
	if ( ! $logo_url ) {
		return $logo_id;
	}

	$attachment_id = attachment_url_to_postid( $logo_url );
	return $attachment_id ? $attachment_id : $logo_id;
}

/**
 * Sync เมื่อ save Theme Options — อัปเดต WP custom_logo theme mod ด้วย
 */
add_action( 'updated_option', 'hec_sync_logo_to_wp_custom_logo', 10, 3 );

function hec_sync_logo_to_wp_custom_logo( $option, $old_value, $new_value ) {
	if ( 'hec_logo_url' !== $option ) {
		return;
	}

	if ( empty( $new_value ) ) {
		remove_theme_mod( 'custom_logo' );
		return;
	}

	$attachment_id = attachment_url_to_postid( $new_value );
	if ( $attachment_id ) {
		set_theme_mod( 'custom_logo', $attachment_id );
	}
}

/* =============================================
   5. DISABLE HELLO ELEMENTOR DEFAULT HEADER
   (เมื่อใช้ custom header ของ child theme)
   ============================================= */

/**
 * ปิด Hello Elementor header/footer display function เฉพาะส่วน header
 * โดย override ด้วย filter
 */
add_filter( 'hello_elementor_header_footer', '__return_false' );

/* =============================================
   5. POLYLANG — ลงทะเบียน strings ที่แปลได้
   ============================================= */

add_action( 'init', 'hec_register_polylang_strings' );

function hec_register_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$strings = [
		'hec_cta_label' => __( 'CTA Button Label', 'ehowme-ebase' ),
		'hec_cta_url'   => __( 'CTA Button URL', 'ehowme-ebase' ),
	];

	foreach ( $strings as $name => $desc ) {
		pll_register_string( $name, get_option( $name, '' ), 'Hello Elementor Child' );
	}
}

/* =============================================
   6. WPML — string translation filter
   ============================================= */

add_filter( 'hec_cta_label', 'hec_wpml_translate_string', 10, 2 );
add_filter( 'hec_cta_url',   'hec_wpml_translate_string', 10, 2 );

function hec_wpml_translate_string( $value, $name ) {
	if ( function_exists( 'icl_t' ) ) {
		return icl_t( 'Hello Elementor Child', $name, $value );
	}
	return $value;
}

/* =============================================
   7. BODY CLASS — เพิ่ม language class
   ============================================= */

add_filter( 'body_class', 'hec_body_lang_class' );

function hec_body_lang_class( $classes ) {
	if ( function_exists( 'hec_get_current_language' ) ) {
		$lang = hec_get_current_language();
		if ( $lang ) {
			$classes[] = 'lang-' . sanitize_html_class( $lang );
		}
	}
	// Mobile menu style class
	$mobile_style = get_option( 'hec_mobile_menu_style', 'dropdown' );
	$classes[]    = 'mobile-menu-' . sanitize_html_class( $mobile_style );
	return $classes;
}

/* =============================================
   8. REMOVE HELLO ELEMENTOR PAGE TITLE
   (optional — uncomment ถ้าต้องการ)
   ============================================= */

// add_filter( 'hello_elementor_page_title', '__return_false' );

/* =============================================
   9. ADD HREFLANG TAGS (SEO)
   ============================================= */

add_action( 'wp_head', 'hec_output_hreflang_tags' );

function hec_output_hreflang_tags() {
	if ( ! function_exists( 'hec_get_languages' ) ) {
		return;
	}

	$languages = hec_get_languages();
	if ( count( $languages ) <= 1 ) {
		return;
	}

	foreach ( $languages as $lang ) {
		$locale = isset( $lang['locale'] ) ? str_replace( '_', '-', $lang['locale'] ) : $lang['code'];
		printf(
			'<link rel="alternate" hreflang="%s" href="%s">' . "\n",
			esc_attr( $locale ),
			esc_url( $lang['url'] )
		);
	}

	// x-default = ภาษาแรก
	if ( ! empty( $languages ) ) {
		printf(
			'<link rel="alternate" hreflang="x-default" href="%s">' . "\n",
			esc_url( $languages[0]['url'] )
		);
	}
}
