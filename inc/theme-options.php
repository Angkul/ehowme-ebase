<?php
/**
 * Theme Options Page
 * รองรับ multi-language (Polylang / WPML)
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HEC_Theme_Options {

	private static $instance = null;

	/** Option group / page slug */
	const OPTION_GROUP = 'hec_theme_options';
	const PAGE_SLUG    = 'hec-theme-options';

	/** Option fields (base names) */
	private $header_fields = [];
	private $active_langs  = [];

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->active_langs = $this->get_active_languages_list();
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * ดึงรายการภาษา active (คืน array ของ code)
	 */
	private function get_active_languages_list() {
		$langs = [];
		if ( function_exists( 'hec_get_languages' ) ) {
			foreach ( hec_get_languages() as $lang ) {
				$langs[] = $lang['code'];
			}
		}
		return ! empty( $langs ) ? $langs : [ '' ]; // '' = ไม่มี multilang plugin
	}

	/**
	 * Tabs ที่ใช้ทั้งตอน register settings (จัดกลุ่ม field เข้า section)
	 * และตอน render หน้า (สร้างปุ่ม tab + panel ตามลำดับนี้)
	 */
	private function get_tabs() {
		return [
			'layout'    => __( 'General', 'ehowme-ebase' ),
			'logo'      => __( 'Logo', 'ehowme-ebase' ),
			'colors'    => __( 'Colors', 'ehowme-ebase' ),
			'langbtn'   => __( 'Language Button', 'ehowme-ebase' ),
			'cta'       => __( 'CTA Button', 'ehowme-ebase' ),
			'menu'      => __( 'Menus', 'ehowme-ebase' ),
			'multilang' => __( 'Multi-Language', 'ehowme-ebase' ),
		];
	}

	/** เพิ่ม submenu ใต้ Appearance */
	public function add_menu_page() {
		add_theme_page(
			__( 'Theme Options', 'ehowme-ebase' ),
			__( 'Theme Options', 'ehowme-ebase' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	/** Register all settings */
	public function register_settings() {

		// ---- General Header Options (ไม่แยกภาษา) ----
		// 'section' คือ tab ที่ field นี้จะไปอยู่ (ดู get_tabs())
		$general_options = [
			// ── Header ทั่วไป ──
			'hec_header_height'      => [ 'section' => 'layout', 'label' => __( 'Header Height (px)', 'ehowme-ebase' ), 'default' => '70' ],
			'hec_header_max_width'   => [ 'section' => 'layout', 'label' => __( 'Container Max Width (px)', 'ehowme-ebase' ), 'default' => '1200' ],
			'hec_header_sticky'      => [ 'section' => 'layout', 'label' => __( 'Sticky Header', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_header_transparent' => [ 'section' => 'layout', 'label' => __( 'Transparent Header at Top', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '0' ],

			// ── โลโก้ ──
			'hec_logo_url'           => [ 'section' => 'logo', 'label' => __( 'Logo Image', 'ehowme-ebase' ), 'type' => 'image', 'default' => '' ],
			'hec_logo_url_2x'        => [ 'section' => 'logo', 'label' => __( 'Logo Image (Retina 2x)', 'ehowme-ebase' ), 'type' => 'image', 'default' => '' ],
			'hec_header_logo_height' => [ 'section' => 'logo', 'label' => __( 'Logo Max Height (px)', 'ehowme-ebase' ), 'default' => '50' ],

			// ── สี ──
			'hec_header_bg_color'              => [ 'section' => 'colors', 'label' => __( 'Background Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_header_border_color'          => [ 'section' => 'colors', 'label' => __( 'Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e5e5e5' ],
			'hec_header_nav_color'             => [ 'section' => 'colors', 'label' => __( 'Nav Text Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#333333' ],
			'hec_header_nav_hover_color'       => [ 'section' => 'colors', 'label' => __( 'Nav Text Hover Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e67e22' ],
			'hec_header_active_color'          => [ 'section' => 'colors', 'label' => __( 'Nav Active / Accent Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e67e22' ],
			'hec_header_transparent_nav_color' => [ 'section' => 'colors', 'label' => __( 'Nav/Logo Text Color (Transparent State)', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_header_transparent_nav_hover_color' => [ 'section' => 'colors', 'label' => __( 'Nav Text Hover Color (Transparent State)', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],

			// ── ปุ่มภาษา ──
			'hec_show_lang_switcher'          => [ 'section' => 'langbtn', 'label' => __( 'Show Language Switcher', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_lang_btn_bg_color'           => [ 'section' => 'langbtn', 'label' => __( 'Lang Button BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_lang_btn_border_color'       => [ 'section' => 'langbtn', 'label' => __( 'Lang Button Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#E8E8E6' ],
			'hec_lang_btn_hover_bg_color'     => [ 'section' => 'langbtn', 'label' => __( 'Lang Button Hover BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#f7f7f5' ],
			'hec_lang_btn_hover_border_color' => [ 'section' => 'langbtn', 'label' => __( 'Lang Button Hover Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#E8E8E6' ],
			'hec_lang_btn_hover_color'        => [ 'section' => 'langbtn', 'label' => __( 'Lang Button Hover Text Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#0F0F0F' ],
			'hec_lang_btn_radius'             => [ 'section' => 'langbtn', 'label' => __( 'Lang Button Border Radius (e.g. 100px or 50%)', 'ehowme-ebase' ), 'default' => '100px' ],

			// ── ปุ่ม CTA ──
			'hec_show_cta_button' => [ 'section' => 'cta', 'label' => __( 'Show CTA Button', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_cta_bg_color'       => [ 'section' => 'cta', 'label' => __( 'CTA Button BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#222222' ],
			'hec_cta_hover_bg_color' => [ 'section' => 'cta', 'label' => __( 'CTA Button Hover BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e67e22' ],
			'hec_cta_btn_radius'     => [ 'section' => 'cta', 'label' => __( 'CTA Button Border Radius (e.g. 30px or 50%)', 'ehowme-ebase' ), 'default' => '30px' ],

			// ── เมนู ──
			'hec_header_menu'       => [ 'section' => 'menu', 'label' => __( 'Header Menu', 'ehowme-ebase' ), 'type' => 'menu_select', 'default' => '' ],
			'hec_mobile_menu_id'    => [
				'section' => 'menu',
				'label'   => __( 'Mobile Menu (leave blank to use Header Menu)', 'ehowme-ebase' ),
				'type'    => 'menu_select',
				'default' => '',
			],
			'hec_mobile_menu_style' => [
				'section' => 'menu',
				'label'   => __( 'Mobile Menu Style', 'ehowme-ebase' ),
				'type'    => 'select',
				'default' => 'dropdown',
				'options' => [
					'dropdown'      => __( 'Dropdown (below header)', 'ehowme-ebase' ),
					'sidebar-right' => __( 'Side Drawer — Right', 'ehowme-ebase' ),
					'sidebar-left'  => __( 'Side Drawer — Left', 'ehowme-ebase' ),
				],
			],

			// ── Mega Panel ──
			'hec_mega_panel_top_offset' => [
				'section' => 'menu',
				'label'   => __( 'Mega Panel Top Offset (e.g. 0px, 1px, -2px)', 'ehowme-ebase' ),
				'default' => '0px',
			],
			'hec_mega_panel_width' => [
				'section' => 'menu',
				'label'   => __( 'Mega Panel Width (e.g. 760px)', 'ehowme-ebase' ),
				'default' => '760px',
			],
		];

		foreach ( $this->get_tabs() as $tab_id => $tab_label ) {
			if ( 'multilang' === $tab_id ) {
				continue; // จัดการแยกด้านล่าง (ต้อง render คำอธิบาย plugin ที่ตรวจเจอ)
			}
			add_settings_section( 'hec_section_' . $tab_id, $tab_label, null, self::PAGE_SLUG );
		}

		foreach ( $general_options as $key => $args ) {
			register_setting( self::OPTION_GROUP, $key, [ 'sanitize_callback' => [ $this, 'sanitize_option' ] ] );
			add_settings_field( $key, $args['label'], [ $this, 'render_field' ], self::PAGE_SLUG, 'hec_section_' . $args['section'], array_merge( [ 'id' => $key ], $args ) );
		}

		// ---- Multi-language fields ----
		// CTA URL + Label แยกตามภาษา
		add_settings_section( 'hec_section_multilang', __( 'Multi-Language Content', 'ehowme-ebase' ), null, self::PAGE_SLUG );

		foreach ( $this->active_langs as $lang ) {
			$suffix      = $lang ? '_' . $lang : '';
			$lang_label  = strtoupper( $lang ?: 'default' );

			$ml_fields = [
				"hec_cta_label{$suffix}" => [ 'label' => sprintf( __( 'CTA Button Label [%s]', 'ehowme-ebase' ), $lang_label ), 'default' => __( 'Contact Us', 'ehowme-ebase' ) ],
				"hec_cta_url{$suffix}"   => [ 'label' => sprintf( __( 'CTA Button URL [%s]', 'ehowme-ebase' ), $lang_label ), 'type' => 'url', 'default' => home_url( '/contact' ) ],
			];

			foreach ( $ml_fields as $key => $args ) {
				register_setting( self::OPTION_GROUP, $key, [ 'sanitize_callback' => [ $this, 'sanitize_option' ] ] );
				add_settings_field( $key, $args['label'], [ $this, 'render_field' ], self::PAGE_SLUG, 'hec_section_multilang', array_merge( [ 'id' => $key ], $args ) );
			}
		}
	}

	public function render_multilang_section_desc() {
		$plugin = hec_get_multilang_plugin();
		if ( ! $plugin ) {
			echo '<p>' . esc_html__( 'No multi-language plugin detected. Install Polylang or WPML to enable per-language fields.', 'ehowme-ebase' ) . '</p>';
		} else {
			echo '<p>' . sprintf( esc_html__( 'Plugin detected: %s. Fields below are per-language.', 'ehowme-ebase' ), '<strong>' . esc_html( strtoupper( $plugin ) ) . '</strong>' ) . '</p>';
		}
	}

	/** Sanitize callback */
	public function sanitize_option( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_text_field( $value );
	}

	/** Render individual field */
	public function render_field( $args ) {
		$id      = $args['id'];
		$type    = isset( $args['type'] ) ? $args['type'] : 'text';
		$value   = get_option( $id, isset( $args['default'] ) ? $args['default'] : '' );

		switch ( $type ) {
			case 'checkbox':
				printf(
					'<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $id ),
					checked( $value, '1', false ),
					esc_html__( 'Enable', 'ehowme-ebase' )
				);
				break;

			case 'color':
				printf(
					'<input type="color" id="%1$s" name="%1$s" value="%2$s" class="hec-color-picker">',
					esc_attr( $id ),
					esc_attr( $value )
				);
				printf( '<span class="hec-color-hex">%s</span>', esc_html( $value ) );
				break;

			case 'url':
				printf(
					'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text">',
					esc_attr( $id ),
					esc_attr( $value )
				);
				break;

			case 'image':
				$preview = $value ? '<img src="' . esc_url( $value ) . '" style="max-height:60px;max-width:240px;display:block;margin-bottom:8px;border-radius:4px;border:1px solid #ddd;padding:4px;">' : '';
				echo $preview;
				printf(
					'<input type="hidden" id="%1$s" name="%1$s" value="%2$s">',
					esc_attr( $id ),
					esc_attr( $value )
				);
				printf(
					'<button type="button" class="button hec-upload-btn" data-target="%s">%s</button>',
					esc_attr( $id ),
					esc_html__( 'Upload / Select Image', 'ehowme-ebase' )
				);
				if ( $value ) {
					printf(
						' <button type="button" class="button hec-remove-btn" data-target="%s">%s</button>',
						esc_attr( $id ),
						esc_html__( 'Remove', 'ehowme-ebase' )
					);
				}
				break;

			case 'select':
				$options = isset( $args['options'] ) ? $args['options'] : [];
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
				foreach ( $options as $val => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $val ),
						selected( $value, $val, false ),
						esc_html( $label )
					);
				}
				echo '</select>';
				break;

			case 'menu_select':
				$menus = get_terms( 'nav_menu', [ 'hide_empty' => false ] );
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
				echo '<option value="">' . esc_html__( '— Select Menu —', 'ehowme-ebase' ) . '</option>';
				foreach ( $menus as $menu ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $menu->term_id ),
						selected( $value, $menu->term_id, false ),
						esc_html( $menu->name )
					);
				}
				echo '</select>';
				echo '<p class="description">' . esc_html__( 'Or assign via Appearance → Menus.', 'ehowme-ebase' ) . '</p>';
				break;

			default: // text / number
				printf(
					'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text">',
					esc_attr( $id ),
					esc_attr( $value )
				);
				break;
		}
	}

	/** Render the settings page */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs     = $this->get_tabs();
		$first_id = array_key_first( $tabs );
		$logo_url = get_option( 'hec_logo_url', '' );
		?>
		<div class="wrap hec-options-page hec-modern">

			<div class="hec-page-head">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<p class="hec-page-sub"><?php esc_html_e( 'Configure the header, colors, and buttons for this theme — with a live preview below.', 'ehowme-ebase' ); ?></p>
			</div>

			<?php settings_errors( self::OPTION_GROUP ); ?>

			<div class="hec-live-preview-card">
				<div class="hec-live-preview-toolbar">
					<strong><?php esc_html_e( 'Header Live Preview', 'ehowme-ebase' ); ?></strong>
					<span class="hec-live-preview-hint"><?php esc_html_e( 'Scroll inside the box below to preview the scrolled state.', 'ehowme-ebase' ); ?></span>
				</div>
				<div class="hec-live-preview" id="hec-live-preview" data-site-name="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<header class="site-header-custom is-sticky" id="hec-mock-header">
						<div class="header-inner">
							<div class="header-logo" id="hec-mock-logo-wrap">
								<?php if ( $logo_url ) : ?>
									<a href="#" tabindex="-1"><img src="<?php echo esc_url( $logo_url ); ?>" id="hec-mock-logo-img" style="max-height:var(--header-logo-height,50px);width:auto;" alt=""></a>
								<?php else : ?>
									<a href="#" tabindex="-1"><span class="site-title-text" id="hec-mock-sitename"><?php bloginfo( 'name' ); ?></span></a>
								<?php endif; ?>
							</div>
							<nav class="header-nav">
								<ul>
									<li class="nav-item current-menu-item"><a href="#" class="nav-link" tabindex="-1"><?php esc_html_e( 'Home', 'ehowme-ebase' ); ?></a></li>
									<li class="nav-item"><a href="#" class="nav-link" tabindex="-1"><?php esc_html_e( 'Products', 'ehowme-ebase' ); ?></a></li>
									<li class="nav-item"><a href="#" class="nav-link" tabindex="-1"><?php esc_html_e( 'Services', 'ehowme-ebase' ); ?></a></li>
									<li class="nav-item"><a href="#" class="nav-link" tabindex="-1"><?php esc_html_e( 'Contact', 'ehowme-ebase' ); ?></a></li>
								</ul>
							</nav>
							<div class="header-actions">
								<div class="lang-switch" id="hec-mock-lang-switch">
									<button type="button" class="lang-btn" tabindex="-1">TH <span class="caret">▾</span></button>
								</div>
								<a href="#" class="header-cta-btn" id="hec-mock-cta" tabindex="-1"><?php esc_html_e( 'Contact Us', 'ehowme-ebase' ); ?></a>
							</div>
							<button type="button" class="mobile-menu-toggle" id="hec-mock-toggle" tabindex="-1" aria-hidden="true"><span></span><span></span><span></span></button>
						</div>
					</header>
					<div class="hec-mock-body" id="hec-mock-body">
						<span><?php esc_html_e( 'Page content area (simulated)', 'ehowme-ebase' ); ?></span>
					</div>
				</div>
			</div>

			<form method="post" action="options.php" class="hec-tabs-form">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<div class="hec-tabs">
					<nav class="hec-tab-nav" role="tablist">
						<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
							<button type="button" class="hec-tab-btn<?php echo ( $tab_id === $first_id ) ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $tab_id ); ?>"><?php echo esc_html( $tab_label ); ?></button>
						<?php endforeach; ?>
					</nav>

					<div class="hec-tab-panels">
						<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
							<div class="hec-tab-panel<?php echo ( $tab_id === $first_id ) ? ' is-active' : ''; ?>" data-tab="<?php echo esc_attr( $tab_id ); ?>">
								<?php if ( 'multilang' === $tab_id ) : ?>
									<?php $this->render_multilang_section_desc(); ?>
								<?php endif; ?>
								<table class="form-table" role="presentation">
									<?php do_settings_fields( self::PAGE_SLUG, 'hec_section_' . $tab_id ); ?>
								</table>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="hec-save-bar">
					<?php submit_button( __( 'Save Settings', 'ehowme-ebase' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<div class="hec-preview-box">
				<h2><?php esc_html_e( 'Header CSS Variables', 'ehowme-ebase' ); ?></h2>
				<p><?php esc_html_e( 'Current CSS custom property values (updates live as you edit the form above, no need to Save first):', 'ehowme-ebase' ); ?></p>
				<pre id="hec-css-preview"></pre>
			</div>
		</div>
		<?php
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'appearance_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();

		// โหลด stylesheet จริงของธีม เพื่อให้ Live Preview ใช้ CSS class
		// เดียวกับหน้าเว็บจริง (site-header-custom, lang-btn, header-cta-btn ฯลฯ)
		// หน้าตาที่เห็นในหน้านี้จะตรงกับหน้าเว็บจริงเป๊ะๆ
		wp_enqueue_style(
			'ehowme-ebase',
			get_stylesheet_uri(),
			[ 'wp-color-picker' ],
			wp_get_theme()->get( 'Version' )
		);

		wp_enqueue_style(
			'hec-theme-options-admin',
			get_stylesheet_directory_uri() . '/assets/css/theme-options-admin.css',
			[ 'wp-color-picker', 'ehowme-ebase' ],
			wp_get_theme()->get( 'Version' )
		);

		wp_enqueue_script(
			'hec-theme-options-admin',
			get_stylesheet_directory_uri() . '/assets/js/theme-options-admin.js',
			[ 'jquery', 'wp-color-picker' ],
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}

// Init
HEC_Theme_Options::get_instance();


/* =============================================
   Helper functions สำหรับ template
   ============================================= */

/**
 * ดึง option header (รองรับ multilang สำหรับ field ที่มี suffix)
 */
function hec_option( $key, $default = '' ) {
	if ( function_exists( 'hec_get_multilang_option' ) ) {
		return hec_get_multilang_option( $key, $default );
	}
	return get_option( $key, $default );
}

/**
 * Normalize a CSS length value coming from a free-text theme option field.
 * Accepts "100", "100px", "50%", "1.5em", etc. Plain numbers (no unit)
 * are assumed to be px, since that's what most users will type.
 */
function hec_css_length( $value, $default ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		$value = $default;
	}
	if ( is_numeric( $value ) ) {
		$value .= 'px';
	}
	return $value;
}

/**
 * Output CSS variables จาก theme options เข้า <head>
 */
function hec_output_header_css_vars() {
	$height            = (int) get_option( 'hec_header_height', 70 );
	$max_width         = (int) get_option( 'hec_header_max_width', 1200 );
	$bg_color          = get_option( 'hec_header_bg_color', '#ffffff' );
	$border_color      = get_option( 'hec_header_border_color', '#e5e5e5' );
	$nav_color         = get_option( 'hec_header_nav_color', '#333333' );
	$nav_hover_color   = get_option( 'hec_header_nav_hover_color', '#e67e22' );
	$active_color      = get_option( 'hec_header_active_color', '#e67e22' );
	$logo_height       = (int) get_option( 'hec_header_logo_height', 50 );
	$cta_bg            = get_option( 'hec_cta_bg_color', '#222222' );
	$cta_hover_bg      = get_option( 'hec_cta_hover_bg_color', '#e67e22' );
	$lang_btn_bg             = get_option( 'hec_lang_btn_bg_color', '#ffffff' );
	$lang_btn_border         = get_option( 'hec_lang_btn_border_color', '#E8E8E6' );
	$lang_btn_hover_bg       = get_option( 'hec_lang_btn_hover_bg_color', '#f7f7f5' );
	$lang_btn_hover_border   = get_option( 'hec_lang_btn_hover_border_color', '#E8E8E6' );
	$lang_btn_hover_color    = get_option( 'hec_lang_btn_hover_color', '#0F0F0F' );
	$lang_btn_radius         = hec_css_length( get_option( 'hec_lang_btn_radius', '100px' ), '100px' );
	$cta_btn_radius          = hec_css_length( get_option( 'hec_cta_btn_radius', '30px' ), '30px' );
	$transparent_nav_color   = get_option( 'hec_header_transparent_nav_color', '#ffffff' );
	$transparent_nav_hover_color = get_option( 'hec_header_transparent_nav_hover_color', '#ffffff' );
	$mega_panel_top_offset = hec_css_length( get_option( 'hec_mega_panel_top_offset', '0px' ), '0px' );
	$mega_panel_width      = hec_css_length( get_option( 'hec_mega_panel_width', '760px' ), '760px' );

	echo '<style id="hec-header-css-vars">
	:root {
		--header-height: ' . esc_attr( $height ) . 'px;
		--header-max-width: ' . esc_attr( $max_width ) . 'px;
		--header-bg-color: ' . esc_attr( $bg_color ) . ';
		--header-border-color: ' . esc_attr( $border_color ) . ';
		--header-nav-color: ' . esc_attr( $nav_color ) . ';
		--header-nav-hover-color: ' . esc_attr( $nav_hover_color ) . ';
		--header-nav-active-color: ' . esc_attr( $active_color ) . ';
		--header-transparent-nav-color: ' . esc_attr( $transparent_nav_color ) . ';
		--header-transparent-nav-hover-color: ' . esc_attr( $transparent_nav_hover_color ) . ';
		--mega-panel-top-offset: ' . esc_attr( $mega_panel_top_offset ) . ';
		--mega-panel-width: ' . esc_attr( $mega_panel_width ) . ';
		--header-logo-height: ' . esc_attr( $logo_height ) . 'px;
		--header-cta-bg: ' . esc_attr( $cta_bg ) . ';
		--header-cta-hover-bg: ' . esc_attr( $cta_hover_bg ) . ';
		--header-cta-color: #ffffff;
		--header-cta-radius: ' . esc_attr( $cta_btn_radius ) . ';
		--lang-btn-bg-color: ' . esc_attr( $lang_btn_bg ) . ';
		--lang-btn-border-color: ' . esc_attr( $lang_btn_border ) . ';
		--lang-btn-hover-bg-color: ' . esc_attr( $lang_btn_hover_bg ) . ';
		--lang-btn-hover-border-color: ' . esc_attr( $lang_btn_hover_border ) . ';
		--lang-btn-hover-color: ' . esc_attr( $lang_btn_hover_color ) . ';
		--lang-btn-radius: ' . esc_attr( $lang_btn_radius ) . ';
	}
	</style>' . "\n";
}
add_action( 'wp_head', 'hec_output_header_css_vars', 5 );
