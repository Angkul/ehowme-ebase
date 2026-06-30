<?php
/**
 * Header Template — Hello Elementor Child
 *
 * Override parent header.php
 * ถ้า Elementor Pro มี Header Location จะใช้ของ Elementor แทน
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url    = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) : ?>
	<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>">
		<?php esc_html_e( 'Skip to content', 'ehowme-ebase' ); ?>
	</a>
<?php endif; ?>

<?php
/**
 * ลำดับความสำคัญ:
 * 1. ถ้า Elementor Pro มี Header Location → ให้ Elementor จัดการ
 * 2. ถ้าไม่มี → ใช้ custom header ของ child theme
 */
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	get_template_part( 'template-parts/custom-header' );
}
?>
