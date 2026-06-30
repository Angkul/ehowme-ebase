<?php
/**
 * GitHub Theme Updater
 * ตรวจสอบและอัปเดต theme จาก GitHub Releases โดยอัตโนมัติ
 *
 * การใช้งาน: เรียกใน functions.php
 *   new HEC_GitHub_Updater( 'your-github-username', 'ehowme-ebase' );
 *
 * วิธี release:
 *   1. git tag v1.2.1
 *   2. git push origin v1.2.1
 *   3. สร้าง Release บน GitHub พร้อม zip ของ theme
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HEC_GitHub_Updater {

	private string $theme_slug;
	private string $github_user;
	private string $github_repo;
	private string $version;
	private string $cache_key;
	private int    $cache_ttl;

	public function __construct( string $github_user, string $github_repo ) {
		$this->theme_slug  = get_stylesheet(); // 'ehowme-ebase'
		$this->github_user = $github_user;
		$this->github_repo = $github_repo;
		$this->cache_key   = 'hec_gh_update_' . md5( $github_user . $github_repo );
		$this->cache_ttl   = 12 * HOUR_IN_SECONDS;

		$theme         = wp_get_theme( $this->theme_slug );
		$this->version = $theme->get( 'Version' );

		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_update' ] );
		add_filter( 'themes_api', [ $this, 'theme_popup_info' ], 10, 3 );

		// ล้าง cache เมื่อกด "Check Again" ในหน้า Updates
		add_action( 'admin_init', [ $this, 'maybe_clear_cache' ] );
	}

	/* ─────────────────────────────────────────
	   ดึงข้อมูล Release ล่าสุดจาก GitHub API
	───────────────────────────────────────── */

	private function get_release_info(): array|false {
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$api_url  = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";
		$response = wp_remote_get( $api_url, [
			'headers' => [
				'Accept'     => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
			],
			'timeout' => 10,
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $code ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! $data || empty( $data->tag_name ) ) {
			return false;
		}

		// Package URL — ถ้ามี .zip asset ให้ใช้ก่อน (ชื่อ folder ถูกต้อง)
		$zip_url = $data->zipball_url; // default: GitHub auto-zip
		if ( ! empty( $data->assets ) ) {
			foreach ( $data->assets as $asset ) {
				if ( str_ends_with( strtolower( $asset->name ), '.zip' ) ) {
					$zip_url = $asset->browser_download_url;
					break;
				}
			}
		}

		$release = [
			'version'   => ltrim( $data->tag_name, 'v' ),
			'zip_url'   => $zip_url,
			'changelog' => isset( $data->body ) ? $data->body : '',
			'url'       => $data->html_url,
			'published' => isset( $data->published_at ) ? $data->published_at : '',
		];

		set_transient( $this->cache_key, $release, $this->cache_ttl );

		return $release;
	}

	/* ─────────────────────────────────────────
	   Inject update data เข้า WordPress transient
	───────────────────────────────────────── */

	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_release_info();
		if ( ! $release ) {
			return $transient;
		}

		if ( version_compare( $release['version'], $this->version, '>' ) ) {
			$transient->response[ $this->theme_slug ] = [
				'theme'       => $this->theme_slug,
				'new_version' => $release['version'],
				'url'         => "https://github.com/{$this->github_user}/{$this->github_repo}",
				'package'     => $release['zip_url'],
			];
		} else {
			// แจ้ง WP ว่า theme นี้ up-to-date
			$transient->no_update[ $this->theme_slug ] = [
				'theme'       => $this->theme_slug,
				'new_version' => $this->version,
				'url'         => "https://github.com/{$this->github_user}/{$this->github_repo}",
			];
		}

		return $transient;
	}

	/* ─────────────────────────────────────────
	   Popup ข้อมูล theme ใน WordPress Updates
	───────────────────────────────────────── */

	public function theme_popup_info( $result, $action, $args ) {
		if ( 'theme_information' !== $action || $args->slug !== $this->theme_slug ) {
			return $result;
		}

		$release = $this->get_release_info();
		if ( ! $release ) {
			return $result;
		}

		$changelog_html = nl2br( esc_html( $release['changelog'] ) );

		return (object) [
			'name'          => 'eBase',
			'slug'          => $this->theme_slug,
			'version'       => $release['version'],
			'author'        => '<a href="https://ehowme.com">eHowMe</a>',
			'homepage'      => "https://github.com/{$this->github_user}/{$this->github_repo}",
			'last_updated'  => $release['published'],
			'sections'      => [
				'changelog' => $changelog_html ?: __( 'See GitHub releases for changelog.', 'ehowme-ebase' ),
			],
			'download_link' => $release['zip_url'],
		];
	}

	/* ─────────────────────────────────────────
	   ล้าง cache เมื่อกด "Check Again"
	───────────────────────────────────────── */

	public function maybe_clear_cache(): void {
		if ( isset( $_GET['force-check'] ) && current_user_can( 'update_themes' ) ) {
			delete_transient( $this->cache_key );
		}
	}
}
