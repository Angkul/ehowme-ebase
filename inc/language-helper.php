<?php
/**
 * Multi-Language Helper
 * รองรับทั้ง Polylang และ WPML
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ตรวจสอบว่า plugin multi-language ตัวไหนใช้งานอยู่
 */
function hec_get_multilang_plugin() {
	if ( defined( 'WPML_PLUGIN_FILE' ) ) {
		return 'wpml';
	}
	if ( function_exists( 'pll_current_language' ) ) {
		return 'polylang';
	}
	return false;
}

/**
 * ดึงภาษาปัจจุบัน (language code)
 */
function hec_get_current_language() {
	$plugin = hec_get_multilang_plugin();

	if ( 'wpml' === $plugin ) {
		return apply_filters( 'wpml_current_language', null );
	}

	if ( 'polylang' === $plugin ) {
		return pll_current_language( 'slug' );
	}

	return get_locale();
}

/**
 * ดึง locale ของภาษาปัจจุบัน
 */
function hec_get_current_locale() {
	$plugin = hec_get_multilang_plugin();

	if ( 'wpml' === $plugin ) {
		return apply_filters( 'wpml_current_language', null );
	}

	if ( 'polylang' === $plugin ) {
		return pll_current_language( 'locale' );
	}

	return get_locale();
}

/**
 * ดึงรายการภาษาทั้งหมดที่ active
 *
 * @return array [ ['code' => 'th', 'name' => 'ไทย', 'url' => '...', 'flag' => '...', 'active' => true], ... ]
 */
function hec_get_languages() {
	$plugin = hec_get_multilang_plugin();
	$languages = [];

	if ( 'wpml' === $plugin ) {
		$wpml_languages = apply_filters( 'wpml_active_languages', null, [
			'skip_missing' => 0,
			'orderby'      => 'custom',
			'order'        => 'asc',
		] );

		if ( is_array( $wpml_languages ) ) {
			foreach ( $wpml_languages as $lang ) {
				$languages[] = [
					'code'   => $lang['language_code'],
					'name'   => $lang['translated_name'],
					'url'    => $lang['url'],
					'flag'   => isset( $lang['country_flag_url'] ) ? $lang['country_flag_url'] : '',
					'active' => (bool) $lang['active'],
				];
			}
		}
	} elseif ( 'polylang' === $plugin ) {
		$pll_languages = function_exists( 'pll_the_languages' ) ? pll_the_languages( [
			'raw'            => 1,
			'show_names'     => 1,
			'display_names_as' => 'name',
			'hide_if_empty'  => 0,
		] ) : '';

		if ( is_array( $pll_languages ) && ! empty( $pll_languages ) ) {
			foreach ( $pll_languages as $lang ) {
				$languages[] = [
					'code'   => $lang['slug'],
					'name'   => $lang['name'],
					'url'    => $lang['url'],
					'flag'   => isset( $lang['flag'] ) ? $lang['flag'] : '',
					'active' => (bool) $lang['current_lang'],
				];
			}
		} elseif ( function_exists( 'pll_languages_list' ) ) {
			// pll_the_languages() ทำงานเฉพาะ frontend (PLL_Frontend) — ใน
			// wp-admin จะคืน '' เสมอ ทำให้ field ต่อภาษาในหน้า Theme Options
			// (hec_header_menu_{lang}, hec_cta_label_{lang}, ...) ไม่ถูก
			// register/แสดงเลย ใช้ pll_languages_list() ซึ่งทำงานทุก context
			// เป็น fallback (url ใช้ home url ของภาษา — พอสำหรับ admin,
			// ส่วน frontend switcher ยังได้ per-page url จาก branch บน)
			$slugs   = pll_languages_list( [ 'fields' => 'slug' ] );
			$names   = pll_languages_list( [ 'fields' => 'name' ] );
			$current = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';

			if ( is_array( $slugs ) ) {
				foreach ( $slugs as $i => $slug ) {
					$languages[] = [
						'code'   => $slug,
						'name'   => isset( $names[ $i ] ) ? $names[ $i ] : strtoupper( $slug ),
						'url'    => function_exists( 'pll_home_url' ) ? pll_home_url( $slug ) : home_url( '/' ),
						'flag'   => '',
						'active' => ( $slug === $current ),
					];
				}
			}
		}
	} else {
		// ไม่มี plugin — ใส่ภาษา default ให้
		$languages[] = [
			'code'   => 'en',
			'name'   => 'English',
			'url'    => home_url( '/' ),
			'flag'   => '',
			'active' => true,
		];
	}

	return $languages;
}

/**
 * ดึง option ที่รองรับ multi-language
 * ถ้ามี plugin จะดึง option ตาม suffix ของภาษา เช่น my_option_th / my_option_en
 * ถ้าไม่มี plugin ดึง option ปกติ
 *
 * @param string $option_name  ชื่อ option base (ไม่ต้องใส่ suffix ภาษา)
 * @param mixed  $default
 * @return mixed
 */
function hec_get_multilang_option( $option_name, $default = '' ) {
	$lang = hec_get_current_language();

	if ( $lang ) {
		$lang_option = get_option( $option_name . '_' . $lang, null );
		if ( null !== $lang_option && '' !== $lang_option ) {
			return $lang_option;
		}
	}

	// Fallback ไปที่ option ไม่มี suffix
	return get_option( $option_name, $default );
}

/**
 * ดึง custom flag emoji ตาม language code
 */
function hec_get_lang_flag_emoji( $code ) {
	$flags = [
		'th'    => '🇹🇭',
		'en'    => '🇬🇧',
		'en_US' => '🇺🇸',
		'zh'    => '🇨🇳',
		'ja'    => '🇯🇵',
		'ko'    => '🇰🇷',
		'de'    => '🇩🇪',
		'fr'    => '🇫🇷',
		'es'    => '🇪🇸',
		'ar'    => '🇸🇦',
		'vi'    => '🇻🇳',
		'ms'    => '🇲🇾',
		'id'    => '🇮🇩',
	];

	return isset( $flags[ $code ] ) ? $flags[ $code ] : '🌐';
}
