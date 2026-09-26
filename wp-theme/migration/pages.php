<?php
/**
 * Manifest de migration — port direct du tableau PAGES (et NAV_MENU /
 * FOOTER_NAV) de build.py. Seule source de vérité pour la hiérarchie des
 * pages, les articles et les menus : si le manifest de build.py change,
 * répercuter le changement ici.
 *
 * `date_iso` (absent de build.py) fixe la date de publication réelle des
 * articles d'actualité, pour que leur permalien WordPress par défaut
 * (/%year%/%monthnum%/%day%/%postname%/) reproduise exactement le chemin
 * déjà utilisé dans content/*.html (/2026/08/01/<slug>/).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'pages' => array(
		array( 'id' => '2', 'file' => '2_accueil.html', 'path' => '', 'parent' => null,
			'title' => 'Cours Chambertin',
			'excerpt' => 'Fondé en 1982, le Cours Chambertin prépare la réouverture de son École élémentaire en septembre 2027 aux côtés du Collège Chambertin, de la 6e à la 3e.' ),

		array( 'id' => '145', 'file' => '145_le-cours-chambertin.html', 'path' => 'le-cours-chambertin', 'parent' => null,
			'title' => 'Le Cours Chambertin',
			'excerpt' => 'Découvrez le Cours Chambertin, institution privée laïque fondée en 1982 à Asnières-sur-Seine, composée de l’École Chambertin et du Collège Chambertin.' ),
		array( 'id' => '146', 'file' => '146_notre-histoire.html', 'path' => 'le-cours-chambertin/notre-histoire', 'parent' => '145',
			'title' => 'Notre histoire',
			'excerpt' => 'De la fondation du Cours Chambertin en 1982 à la réouverture de l’École en septembre 2027 : découvrez les étapes d’une institution scolaire asniéroise.' ),
		array( 'id' => '147', 'file' => '147_notre-projet-educatif.html', 'path' => 'le-cours-chambertin/notre-projet-educatif', 'parent' => '145',
			'title' => 'Notre projet éducatif',
			'excerpt' => 'Programmes nationaux, responsabilité de l’établissement, connaissances solides, laïcité, langues et cultures : les engagements éducatifs du Cours Chambertin.' ),
		array( 'id' => '148', 'file' => '148_langues-et-cultures.html', 'path' => 'le-cours-chambertin/langues-et-cultures', 'parent' => '145',
			'title' => 'Langues et cultures',
			'excerpt' => 'Le Cours Chambertin place les langues et les cultures au cœur de l’ouverture au monde, de l’École élémentaire au Collège.' ),

		array( 'id' => '149', 'file' => '149_ecole.html', 'path' => 'ecole', 'parent' => null,
			'title' => 'L’École Chambertin',
			'excerpt' => 'L’École Chambertin rouvrira en septembre 2027 à Asnières-sur-Seine, du CP au CM2.' ),
		array( 'id' => '150', 'file' => '150_cp.html', 'path' => 'ecole/cp', 'parent' => '149',
			'title' => 'CP',
			'excerpt' => 'Le CP à l’École Chambertin : lecture, écriture, mathématiques, langage et premiers repères du travail scolaire à partir de septembre 2027.' ),
		array( 'id' => '151', 'file' => '151_ce1-ce2.html', 'path' => 'ecole/ce1-ce2', 'parent' => '149',
			'title' => 'CE1-CE2',
			'excerpt' => 'Le CE1-CE2 à l’École Chambertin : lecture, écriture, calcul, résolution de problèmes, connaissances et habitudes de travail.' ),
		array( 'id' => '152', 'file' => '152_cm1-cm2.html', 'path' => 'ecole/cm1-cm2', 'parent' => '149',
			'title' => 'CM1-CM2',
			'excerpt' => 'Le CM1-CM2 à l’École Chambertin : maîtrise du français et des mathématiques, culture, méthodes de travail et préparation progressive au collège.' ),
		array( 'id' => '153', 'file' => '153_organisation.html', 'path' => 'ecole/organisation', 'parent' => '149',
			'title' => 'Organisation de l’École',
			'excerpt' => 'Adresse, classes, calendrier de préparation, horaires, restauration et périscolaire de l’École Chambertin pour la rentrée de septembre 2027.' ),
		array( 'id' => '154', 'file' => '154_admissions.html', 'path' => 'ecole/admissions', 'parent' => '149',
			'title' => 'Admissions à l’École',
			'excerpt' => 'La première étape d’une demande d’admission à l’École Chambertin est un rendez-vous individuel avec la direction.' ),

		array( 'id' => '155', 'file' => '155_college.html', 'path' => 'college', 'parent' => null,
			'title' => 'Le Collège Chambertin',
			'excerpt' => 'Le Collège Chambertin est un établissement privé laïque sous contrat d’association avec l’État, de la 6e à la 3e, à Asnières-sur-Seine.' ),
		array( 'id' => '156', 'file' => '156_enseignements-et-parcours.html', 'path' => 'college/enseignements-et-parcours', 'parent' => '155',
			'title' => 'Enseignements et parcours',
			'excerpt' => 'Programmes nationaux, langues, méthodologie, numérique et préparation au brevet au Collège Chambertin.' ),
		array( 'id' => '157', 'file' => '157_vie-scolaire-restauration-etudes.html', 'path' => 'college/vie-scolaire-restauration-etudes', 'parent' => '155',
			'title' => 'Vie scolaire, restauration et études',
			'excerpt' => 'Horaires, restauration, ateliers méridiens, études du soir et accompagnement des élèves au Collège Chambertin.' ),
		array( 'id' => '158', 'file' => '158_resultats-brevet-orientation.html', 'path' => 'college/resultats-brevet-orientation', 'parent' => '155',
			'title' => 'Résultats, brevet et orientation',
			'excerpt' => 'Résultats au diplôme national du brevet, préparation de la troisième et orientation des élèves du Collège Chambertin.' ),
		array( 'id' => '159', 'file' => '159_admissions.html', 'path' => 'college/admissions', 'parent' => '155',
			'title' => 'Admissions au Collège',
			'excerpt' => 'Préinscription, évaluation d’entrée et entretien : découvrez la procédure d’admission au Collège Chambertin de la 6e à la 3e.' ),

		array( 'id' => '160', 'file' => '160_admissions.html', 'path' => 'admissions', 'parent' => null,
			'title' => 'Admissions',
			'excerpt' => 'Les admissions à l’École et au Collège Chambertin sont distinctes. Choisissez la procédure correspondant au niveau demandé.' ),
		array( 'id' => '161', 'file' => '161_actualites.html', 'path' => 'actualites', 'parent' => null,
			'title' => 'Vie & actualités',
			'excerpt' => 'Suivez la vie du Collège Chambertin et les étapes de la réouverture de l’École Chambertin prévue en septembre 2027.' ),
		array( 'id' => '162', 'file' => '162_informations-pratiques.html', 'path' => 'informations-pratiques', 'parent' => null,
			'title' => 'Informations pratiques',
			'excerpt' => 'Adresses, accès, horaires, restauration et contacts de l’École Chambertin et du Collège Chambertin à Asnières-sur-Seine.' ),
		array( 'id' => '163', 'file' => '163_faq.html', 'path' => 'faq', 'parent' => null,
			'title' => 'FAQ',
			'excerpt' => 'Réponses aux questions sur la réouverture de l’École, le Collège, les admissions, les horaires et les tarifs.' ),
		array( 'id' => '164', 'file' => '164_contact.html', 'path' => 'contact', 'parent' => null,
			'title' => 'Contact',
			'excerpt' => 'Contactez l’École Chambertin ou le Collège Chambertin à Asnières-sur-Seine selon la nature de votre demande.' ),
		array( 'id' => '165', 'file' => '165_mentions-legales.html', 'path' => 'mentions-legales', 'parent' => null,
			'title' => 'Mentions légales',
			'excerpt' => 'Mentions légales du site du Cours Chambertin.' ),
		array( 'id' => '166', 'file' => '166_politique-confidentialite.html', 'path' => 'politique-confidentialite', 'parent' => null,
			'title' => 'Politique de confidentialité',
			'excerpt' => 'Politique de confidentialité et gestion des données personnelles du site du Cours Chambertin.' ),
		array( 'id' => '298', 'file' => '298_plan-du-site.html', 'path' => 'plan-du-site', 'parent' => null,
			'title' => 'Plan du site',
			'excerpt' => 'L’ensemble des pages du site du Cours Chambertin, regroupées par rubrique.' ),
	),

	// Articles d'actualité (type "post", pas "page" : leurs permaliens
	// d'origine /2026/08/01/<slug>/ sont le format WordPress par défaut).
	'posts' => array(
		array( 'id' => '191', 'file' => 'news_191_lecole-chambertin-rouvrira-en-septembre-2027.html',
			'slug' => 'lecole-chambertin-rouvrira-en-septembre-2027', 'date_iso' => '2026-08-01 09:00:00',
			'title' => 'L’École Chambertin rouvrira en septembre 2027',
			'excerpt' => 'Quarante-cinq ans après la fondation du Cours Chambertin, l’École élémentaire retrouvera sa place au sein de l’institution, du CP au CM2.' ),
		array( 'id' => '192', 'file' => 'news_192_diplome-national-du-brevet-2026-100-de-reussite.html',
			'slug' => 'diplome-national-du-brevet-2026-100-de-reussite', 'date_iso' => '2026-08-01 09:00:00',
			'title' => 'Diplôme national du brevet 2026 : 100 % de réussite',
			'excerpt' => 'Tous les candidats présentés par le Collège Chambertin ont obtenu le DNB en 2026 ; 97 % ont obtenu une mention.' ),
		array( 'id' => '193', 'file' => 'news_193_le-futur-site-du-cours-chambertin-reunit-lecole-et-le-college.html',
			'slug' => 'le-futur-site-du-cours-chambertin-reunit-lecole-et-le-college', 'date_iso' => '2026-08-01 09:00:00',
			'title' => 'Le futur site du Cours Chambertin réunit l’École et le Collège',
			'excerpt' => 'La réouverture de l’École conduit l’institution à rassembler ses contenus sous une même identité, tout en maintenant des informations et des admissions distinctes.' ),
	),

	// Menu principal (emplacement de thème "primary").
	'nav_menu' => array(
		array( 'id' => '145', 'label' => 'Le Cours Chambertin', 'children' => array( '146', '147', '148' ) ),
		array( 'id' => '149', 'label' => 'L’École', 'children' => array( '150', '151', '152', '153', '154' ) ),
		array( 'id' => '155', 'label' => 'Le Collège', 'children' => array( '156', '157', '158', '159' ) ),
		array( 'id' => '160', 'label' => 'Admissions', 'children' => array() ),
		array( 'id' => '161', 'label' => 'Vie & actualités', 'children' => array() ),
		array( 'id' => '162', 'label' => 'Infos pratiques', 'children' => array() ),
		array( 'id' => '164', 'label' => 'Contact', 'children' => array() ),
	),

	// Menu de pied de page (emplacement de thème "footer").
	'footer_menu' => array(
		array( 'id' => '145', 'label' => 'Le Cours Chambertin' ),
		array( 'id' => '149', 'label' => 'L’École Chambertin' ),
		array( 'id' => '155', 'label' => 'Le Collège Chambertin' ),
		array( 'id' => '160', 'label' => 'Admissions' ),
		array( 'id' => '161', 'label' => 'Vie & actualités' ),
		array( 'id' => '162', 'label' => 'Informations pratiques' ),
		array( 'id' => '163', 'label' => 'FAQ' ),
		array( 'id' => '164', 'label' => 'Contact' ),
		array( 'id' => '165', 'label' => 'Mentions légales' ),
		array( 'id' => '166', 'label' => 'Politique de confidentialité' ),
		array( 'id' => '298', 'label' => 'Plan du site' ),
	),
);
