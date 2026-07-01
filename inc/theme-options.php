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
		$general_options = [
			'hec_logo_url'            => [ 'label' => __( 'Logo Image', 'ehowme-ebase' ), 'type' => 'image', 'default' => '' ],
			'hec_logo_url_2x'         => [ 'label' => __( 'Logo Image (Retina 2x)', 'ehowme-ebase' ), 'type' => 'image', 'default' => '' ],
			'hec_header_height'       => [ 'label' => __( 'Header Height (px)', 'ehowme-ebase' ), 'default' => '70' ],
			'hec_header_max_width'    => [ 'label' => __( 'Container Max Width (px)', 'ehowme-ebase' ), 'default' => '1200' ],
			'hec_header_sticky'       => [ 'label' => __( 'Sticky Header', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_header_transparent'  => [ 'label' => __( 'Transparent Header at Top', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '0' ],
			'hec_header_transparent_nav_color' => [ 'label' => __( 'Nav/Logo Text Color (Transparent State)', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_header_bg_color'     => [ 'label' => __( 'Background Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_header_border_color' => [ 'label' => __( 'Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e5e5e5' ],
			'hec_header_nav_color'    => [ 'label' => __( 'Nav Text Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#333333' ],
			'hec_header_nav_hover_color' => [ 'label' => __( 'Nav Text Hover Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e67e22' ],
			'hec_header_active_color' => [ 'label' => __( 'Nav Active / Accent Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#e67e22' ],
			'hec_header_logo_height'  => [ 'label' => __( 'Logo Max Height (px)', 'ehowme-ebase' ), 'default' => '50' ],
			'hec_show_lang_switcher'      => [ 'label' => __( 'Show Language Switcher', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_lang_btn_bg_color'          => [ 'label' => __( 'Lang Button BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#ffffff' ],
			'hec_lang_btn_border_color'      => [ 'label' => __( 'Lang Button Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#E8E8E6' ],
			'hec_lang_btn_hover_bg_color'    => [ 'label' => __( 'Lang Button Hover BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#f7f7f5' ],
			'hec_lang_btn_hover_border_color'=> [ 'label' => __( 'Lang Button Hover Border Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#E8E8E6' ],
			'hec_lang_btn_hover_color'       => [ 'label' => __( 'Lang Button Hover Text Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#0F0F0F' ],
			'hec_show_cta_button'         => [ 'label' => __( 'Show CTA Button', 'ehowme-ebase' ), 'type' => 'checkbox', 'default' => '1' ],
			'hec_cta_bg_color'            => [ 'label' => __( 'CTA Button BG Color', 'ehowme-ebase' ), 'type' => 'color', 'default' => '#222222' ],
			'hec_header_menu'          => [ 'label' => __( 'Header Menu', 'ehowme-ebase' ), 'type' => 'menu_select', 'default' => '' ],
			'hec_mobile_menu_id' => [
				'label'   => __( 'Mobile Menu (leave blank to use Header Menu)', 'ehowme-ebase' ),
				'type'    => 'menu_select',
				'default' => '',
			],
			'hec_mobile_menu_style'    => [
				'label'   => __( 'Mobile Menu Style', 'ehowme-ebase' ),
				'type'    => 'select',
				'default' => 'dropdown',
				'options' => [
					'dropdown'      => __( 'Dropdown (below header)', 'ehowme-ebase' ),
					'sidebar-right' => __( 'Side Drawer — Right', 'ehowme-ebase' ),
					'sidebar-left'  => __( 'Side Drawer — Left', 'ehowme-ebase' ),
				],
			],
		];

		add_settings_section( 'hec_section_header', __( 'Header Settings', 'ehowme-ebase' ), null, self::PAGE_SLUG );

		foreach ( $general_options as $key => $args ) {
			register_setting( self::OPTION_GROUP, $key, [ 'sanitize_callback' => [ $this, 'sanitize_option' ] ] );
			add_settings_field( $key, $args['label'], [ $this, 'render_field' ], self::PAGE_SLUG, 'hec_section_header', array_merge( [ 'id' => $key ], $args ) );
		}

		// ---- Multi-language fields ----
		// CTA URL + Label แยกตามภาษา
		add_settings_section( 'hec_section_multilang', __( 'Multi-Language Content', 'ehowme-ebase' ), [ $this, 'render_multilang_section_desc' ], self::PAGE_SLUG );

		foreach ( $this->active_langs as $lang ) {
			$suffix      = $lang ? '_' . $lang : '';
			$lang_label  = strtoupper( $lang ?: 'default' );

			$ml_fields = [
				"hec_cta_label{$suffix}" => [ 'label' => sprintf( __( 'CTA Button Label [%s]', 'ehowme-ebase' ), $lang_label ), 'default' => __( 'Contact Us', 'ehowme-ebase' ) ],
				"hec_cta_url{$suffix}"   => [ 'label' => sprintf( __( 'CTA Button URL [%s]', 'ehowme-ebase' ), $lang_label ), 'type' => 'url', 'default' => home_url( '/contact' ) ],
				"hec_site_tagline{$suffix}" => [ 'label' => sprintf( __( 'Site Tagline [%s]', 'ehowme-ebase' ), $lang_label ), 'default' => '' ],
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
		?>
		<div class="wrap hec-options-page">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( self::OPTION_GROUP ); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'ehowme-ebase' ) );
				?>
			</form>

			<div class="hec-preview-box">
				<h2><?php esc_html_e( 'Header CSS Variables Preview', 'ehowme-ebase' ); ?></h2>
				<p><?php esc_html_e( 'These CSS custom properties are injected automatically based on your settings:', 'ehowme-ebase' ); ?></p>
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
		wp_add_inline_style( 'wp-color-picker', '
			.hec-options-page .hec-color-hex { margin-left: 8px; font-size: 12px; color: #666; }
			.hec-preview-box { background: #fff; border: 1px solid #ddd; padding: 16px 24px; margin-top: 24px; border-radius: 4px; }
			.hec-preview-box pre { background: #1e1e1e; color: #9cdcfe; padding: 16px; border-radius: 4px; overflow-x: auto; font-size: 13px; }
			.hec-upload-btn { margin-right: 6px !important; }
		' );
		wp_add_inline_script( 'wp-color-picker', '
			jQuery(function($){

				// Color picker
				$(".hec-color-picker").wpColorPicker({
					change: function(e, ui){
						$(e.target).next(".hec-color-hex").text(ui.color.toString());
					}
				});

				// Media uploader
				var mediaFrame;
				$(document).on("click", ".hec-upload-btn", function(e){
					e.preventDefault();
					var targetId = $(this).data("target");
					var $wrap    = $(this).closest("td");

					mediaFrame = wp.media({
						title: "Select Logo Image",
						button: { text: "Use this image" },
						multiple: false,
						library: { type: "image" }
					});

					mediaFrame.on("select", function(){
						var attachment = mediaFrame.state().get("selection").first().toJSON();
						$("#" + targetId).val(attachment.url);
						var $preview = $wrap.find("img");
						if ($preview.length) {
							$preview.attr("src", attachment.url);
						} else {
							$wrap.prepend("<img src=\"" + attachment.url + "\" style=\"max-height:60px;max-width:240px;display:block;margin-bottom:8px;border-radius:4px;border:1px solid #ddd;padding:4px;\">");
						}
						// แสดงปุ่ม Remove
						if (!$wrap.find(".hec-remove-btn").length) {
							$wrap.find(".hec-upload-btn").after(" <button type=\"button\" class=\"button hec-remove-btn\" data-target=\"" + targetId + "\">Remove</button>");
						}
					});

					mediaFrame.open();
				});

				// Remove logo
				$(document).on("click", ".hec-remove-btn", function(e){
					e.preventDefault();
					var targetId = $(this).data("target");
					var $wrap    = $(this).closest("td");
					$("#" + targetId).val("");
					$wrap.find("img").remove();
					$(this).remove();
				});

			});
		' );
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
	$lang_btn_bg             = get_option( 'hec_lang_btn_bg_color', '#ffffff' );
	$lang_btn_border         = get_option( 'hec_lang_btn_border_color', '#E8E8E6' );
	$lang_btn_hover_bg       = get_option( 'hec_lang_btn_hover_bg_color', '#f7f7f5' );
	$lang_btn_hover_border   = get_option( 'hec_lang_btn_hover_border_color', '#E8E8E6' );
	$lang_btn_hover_color    = get_option( 'hec_lang_btn_hover_color', '#0F0F0F' );
	$transparent_nav_color   = get_option( 'hec_header_transparent_nav_color', '#ffffff' );

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
		--header-logo-height: ' . esc_attr( $logo_height ) . 'px;
		--header-cta-bg: ' . esc_attr( $cta_bg ) . ';
		--header-cta-color: #ffffff;
		--lang-btn-bg-color: ' . esc_attr( $lang_btn_bg ) . ';
		--lang-btn-border-color: ' . esc_attr( $lang_btn_border ) . ';
		--lang-btn-hover-bg-color: ' . esc_attr( $lang_btn_hover_bg ) . ';
		--lang-btn-hover-border-color: ' . esc_attr( $lang_btn_hover_border ) . ';
		--lang-btn-hover-color: ' . esc_attr( $lang_btn_hover_color ) . ';
	}
	</style>' . "\n";
}
add_action( 'wp_head', 'hec_output_header_css_vars', 5 );
