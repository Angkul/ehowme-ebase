<?php
/**
 * Custom Header Template Part
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_sticky      = get_option( 'hec_header_sticky', '1' );
$is_transparent = get_option( 'hec_header_transparent', '0' );
$header_class   = 'site-header-custom';
if ( $is_sticky ) {
	$header_class .= ' is-sticky';
}
if ( $is_transparent ) {
	$header_class .= ' is-transparent';
}
?>
<header id="site-header" class="<?php echo esc_attr( $header_class ); ?>" role="banner">
	<div class="header-inner">

		<!-- Header Layout — Left / Center / Right zones, independently configurable
		     per Desktop / Tablet / Mobile (Theme Options → Header Layout),
		     including the Mobile Menu Toggle button itself. Each element
		     renders once; assets/js/header.js moves it into the right zone
		     for the current breakpoint via the data-*-zone/-order attrs
		     hec_render_zone_item() outputs. -->
		<?php hec_render_header_zones_responsive(); ?>

	</div><!-- .header-inner -->
</header><!-- #site-header -->

<!-- Overlay + Off-canvas (sidebar styles only) -->
<?php
$_hec_mobile_style = get_option( 'hec_mobile_menu_style', 'dropdown' );
if ( 'dropdown' !== $_hec_mobile_style ) :
?>
<div id="hec-drawer-overlay" aria-hidden="true"></div>
<?php hec_render_offcanvas(); ?>
<?php endif; ?>
