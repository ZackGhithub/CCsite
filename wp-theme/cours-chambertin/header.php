<?php
/**
 * En-tête du site : <head>, bandeau d'annonce, en-tête et navigation
 * principale. Équivalent de la partie <head>/<header> de TEMPLATE dans
 * build.py.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-section="<?php echo esc_attr( cc_current_section() ); ?>">
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'cours-chambertin' ); ?></a>

<?php cc_announcement_bar(); ?>

<header id="site-header">
	<div class="cc-header-inner">
		<?php cc_site_logo(); ?>
		<button id="nav-toggle" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
			<span></span><span></span><span></span>
			<span class="sr-only"><?php esc_html_e( 'Menu', 'cours-chambertin' ); ?></span>
		</button>
		<nav id="site-nav" class="site-nav">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '<ul>%3$s</ul>',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="contenu">
<?php cc_breadcrumb(); ?>
