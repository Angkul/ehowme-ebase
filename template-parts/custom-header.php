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

		<!-- Logo -->
		<div class="header-logo">
			<?php
			$hec_logo    = get_option( 'hec_logo_url', '' );
			$hec_logo_2x = get_option( 'hec_logo_url_2x', '' );
			$logo_height = (int) get_option( 'hec_header_logo_height', 50 );

			if ( $hec_logo ) :
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

		<!-- Navigation -->
		<?php hec_header_navigation(); ?>

		<!-- Actions: Language Switcher + CTA -->
		<div class="header-actions">
			<?php hec_header_language_switcher(); ?>
			<?php hec_header_cta_button(); ?>
		</div>

		<!-- Mobile Toggle -->
		<button class="mobile-menu-toggle" id="hec-mobile-toggle" aria-expanded="false" aria-controls="site-navigation" aria-label="<?php esc_attr_e( 'Toggle Menu', 'ehowme-ebase' ); ?>">
			<span></span>
			<span></span>
			<span></span>
		</button>

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
