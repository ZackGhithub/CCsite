<?php
/**
 * Pied de page. Équivalent de la partie <footer> de TEMPLATE dans build.py.
 * Les deux blocs d'adresse (École / Collège) sont des zones de widgets
 * (Apparence > Widgets) avec un repli statique si elles n'ont pas encore
 * été remplies, plutôt que du texte codé en dur.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="cc-pied">
	<div class="cc-pied-inner">
		<div class="cc-pied-grille">
			<div class="cc-pied-marque">
				<?php cc_footer_logo(); ?>
				<p><?php esc_html_e( 'Ensemble scolaire fondé en 1982 à Asnières-sur-Seine, composé de l’École Chambertin et du Collège Chambertin.', 'cours-chambertin' ); ?></p>
				<a class="cc-pied-itineraire" href="https://www.google.com/maps/search/?api=1&query=9+avenue+de+la+Marne+92600+Asni%C3%A8res-sur-Seine" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Voir l’itinéraire →', 'cours-chambertin' ); ?></a>
			</div>

			<nav class="cc-pied-nav" aria-label="<?php esc_attr_e( 'Plan du site', 'cours-chambertin' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>

			<div>
				<?php if ( is_active_sidebar( 'footer-college' ) ) : ?>
					<?php dynamic_sidebar( 'footer-college' ); ?>
				<?php else : ?>
					<h2><?php esc_html_e( 'Collège Chambertin', 'cours-chambertin' ); ?></h2>
					<p>9 avenue de la Marne<br>92600 Asnières-sur-Seine<br>Tél. 01 47 93 97 92</p>
				<?php endif; ?>
			</div>
			<div>
				<?php if ( is_active_sidebar( 'footer-ecole' ) ) : ?>
					<?php dynamic_sidebar( 'footer-ecole' ); ?>
				<?php else : ?>
					<h2><?php esc_html_e( 'École Chambertin', 'cours-chambertin' ); ?></h2>
					<p>9 avenue de la Marne<br>92600 Asnières-sur-Seine<br>Réouverture en septembre 2027</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="cc-pied-bas">
			<p class="cc-pied-mention">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Cours Chambertin</p>
			<ul class="cc-pied-social">
				<li><a href="#" aria-label="Facebook">Facebook</a></li>
				<li><a href="#" aria-label="Instagram">Instagram</a></li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
