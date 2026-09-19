#!/usr/bin/env python3
"""
Générateur statique du site Cours Chambertin.

Principe : chaque page est un fragment HTML "de contenu" dans content/,
assemblé avec un gabarit commun (en-tête, navigation, pied de page) défini
plus bas dans TEMPLATE. Le script réécrit les liens internes et les chemins
d'assets en chemins relatifs selon la profondeur de la page dans l'arborescence.

Cette séparation contenu / gabarit / présentation reproduit volontairement
la logique d'un thème WordPress (page-xxx.php + header.php + footer.php)
pour faciliter une conversion future.

Usage : python3 build.py
"""
import os
import re
import shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
CONTENT_DIR = os.path.join(ROOT, "content")

SITE_NAME = "Cours Chambertin"
SITE_TAGLINE = "École et Collège privés · Asnières-sur-Seine"
ORIGINAL_PREFIX = "https://www.courschambertin.fr/clone0726"

# --------------------------------------------------------------------------
# Manifest des pages : id -> métadonnées + chemin de sortie sur le site.
# path == "" pour la racine, sinon un chemin de dossier terminé par "/".
# --------------------------------------------------------------------------
PAGES = [
    {"id": "2", "file": "2_accueil.html", "path": "", "parent": None,
     "title": "Cours Chambertin",
     "excerpt": "Fondé en 1982, le Cours Chambertin prépare la réouverture de son École élémentaire en septembre 2027 aux côtés du Collège Chambertin, de la 6e à la 3e."},

    {"id": "145", "file": "145_le-cours-chambertin.html", "path": "le-cours-chambertin/", "parent": None,
     "title": "Le Cours Chambertin",
     "excerpt": "Découvrez le Cours Chambertin, institution privée laïque fondée en 1982 à Asnières-sur-Seine, composée de l’École Chambertin et du Collège Chambertin."},
    {"id": "146", "file": "146_notre-histoire.html", "path": "le-cours-chambertin/notre-histoire/", "parent": "145",
     "title": "Notre histoire",
     "excerpt": "De la fondation du Cours Chambertin en 1982 à la réouverture de l’École en septembre 2027 : découvrez les étapes d’une institution scolaire asniéroise."},
    {"id": "147", "file": "147_notre-projet-educatif.html", "path": "le-cours-chambertin/notre-projet-educatif/", "parent": "145",
     "title": "Notre projet éducatif",
     "excerpt": "Programmes nationaux, responsabilité de l’établissement, connaissances solides, laïcité, langues et cultures : les engagements éducatifs du Cours Chambertin."},
    {"id": "148", "file": "148_langues-et-cultures.html", "path": "le-cours-chambertin/langues-et-cultures/", "parent": "145",
     "title": "Langues et cultures",
     "excerpt": "Le Cours Chambertin place les langues et les cultures au cœur de l’ouverture au monde, de l’École élémentaire au Collège."},

    {"id": "149", "file": "149_ecole.html", "path": "ecole/", "parent": None,
     "title": "L’École Chambertin",
     "excerpt": "L’École Chambertin rouvrira en septembre 2027 à Asnières-sur-Seine avec trois classes : CP, CE1-CE2 et CM1-CM2."},
    {"id": "150", "file": "150_cp.html", "path": "ecole/cp/", "parent": "149",
     "title": "CP",
     "excerpt": "Le CP à l’École Chambertin : lecture, écriture, mathématiques, langage et premiers repères du travail scolaire à partir de septembre 2027."},
    {"id": "151", "file": "151_ce1-ce2.html", "path": "ecole/ce1-ce2/", "parent": "149",
     "title": "CE1-CE2",
     "excerpt": "Le CE1-CE2 à l’École Chambertin : lecture, écriture, calcul, résolution de problèmes, connaissances et habitudes de travail."},
    {"id": "152", "file": "152_cm1-cm2.html", "path": "ecole/cm1-cm2/", "parent": "149",
     "title": "CM1-CM2",
     "excerpt": "Le CM1-CM2 à l’École Chambertin : maîtrise du français et des mathématiques, culture, méthodes de travail et préparation progressive au collège."},
    {"id": "153", "file": "153_organisation.html", "path": "ecole/organisation/", "parent": "149",
     "title": "Organisation de l’École",
     "excerpt": "Adresse, classes, calendrier de préparation, horaires, restauration et périscolaire de l’École Chambertin pour la rentrée de septembre 2027."},
    {"id": "154", "file": "154_admissions.html", "path": "ecole/admissions/", "parent": "149",
     "title": "Admissions à l’École",
     "excerpt": "La première étape d’une demande d’admission à l’École Chambertin est un rendez-vous individuel avec la direction."},

    {"id": "155", "file": "155_college.html", "path": "college/", "parent": None,
     "title": "Le Collège Chambertin",
     "excerpt": "Le Collège Chambertin est un établissement privé laïque sous contrat d’association avec l’État, de la 6e à la 3e, à Asnières-sur-Seine."},
    {"id": "156", "file": "156_enseignements-et-parcours.html", "path": "college/enseignements-et-parcours/", "parent": "155",
     "title": "Enseignements et parcours",
     "excerpt": "Programmes nationaux, langues, méthodologie, numérique et préparation au brevet au Collège Chambertin."},
    {"id": "157", "file": "157_vie-scolaire-restauration-etudes.html", "path": "college/vie-scolaire-restauration-etudes/", "parent": "155",
     "title": "Vie scolaire, restauration et études",
     "excerpt": "Horaires, restauration, ateliers méridiens, études du soir et accompagnement des élèves au Collège Chambertin."},
    {"id": "158", "file": "158_resultats-brevet-orientation.html", "path": "college/resultats-brevet-orientation/", "parent": "155",
     "title": "Résultats, brevet et orientation",
     "excerpt": "Résultats au diplôme national du brevet, préparation de la troisième et orientation des élèves du Collège Chambertin."},
    {"id": "159", "file": "159_admissions.html", "path": "college/admissions/", "parent": "155",
     "title": "Admissions au Collège",
     "excerpt": "Préinscription, évaluation d’entrée et entretien : découvrez la procédure d’admission au Collège Chambertin de la 6e à la 3e."},

    {"id": "160", "file": "160_admissions.html", "path": "admissions/", "parent": None,
     "title": "Admissions",
     "excerpt": "Les admissions à l’École et au Collège Chambertin sont distinctes. Choisissez la procédure correspondant au niveau demandé."},
    {"id": "161", "file": "161_actualites.html", "path": "actualites/", "parent": None,
     "title": "Vie & actualités",
     "excerpt": "Suivez la vie du Collège Chambertin et les étapes de la réouverture de l’École Chambertin prévue en septembre 2027."},
    {"id": "162", "file": "162_informations-pratiques.html", "path": "informations-pratiques/", "parent": None,
     "title": "Informations pratiques",
     "excerpt": "Adresses, accès, horaires, restauration et contacts de l’École Chambertin et du Collège Chambertin à Asnières-sur-Seine."},
    {"id": "163", "file": "163_faq.html", "path": "faq/", "parent": None,
     "title": "FAQ",
     "excerpt": "Réponses aux questions sur la réouverture de l’École, le Collège, les admissions, les travaux, les horaires et les tarifs."},
    {"id": "164", "file": "164_contact.html", "path": "contact/", "parent": None,
     "title": "Contact",
     "excerpt": "Contactez l’École Chambertin ou le Collège Chambertin à Asnières-sur-Seine selon la nature de votre demande."},
    {"id": "165", "file": "165_mentions-legales.html", "path": "mentions-legales/", "parent": None,
     "title": "Mentions légales",
     "excerpt": "Mentions légales du site du Cours Chambertin."},
    {"id": "166", "file": "166_politique-confidentialite.html", "path": "politique-confidentialite/", "parent": None,
     "title": "Politique de confidentialité",
     "excerpt": "Politique de confidentialité et gestion des données personnelles du site du Cours Chambertin."},
    {"id": "298", "file": "298_plan-du-site.html", "path": "plan-du-site/", "parent": None,
     "title": "Plan du site",
     "excerpt": "L’ensemble des pages du site du Cours Chambertin, regroupées par rubrique."},

    {"id": "191", "file": "news_191_lecole-chambertin-rouvrira-en-septembre-2027.html",
     "path": "actualites/lecole-chambertin-rouvrira-en-septembre-2027/", "parent": "161", "is_news": True,
     "title": "L’École Chambertin rouvrira en septembre 2027", "date": "1 août 2026",
     "excerpt": "Quarante-cinq ans après la fondation du Cours Chambertin, l’École élémentaire retrouvera sa place au sein de l’institution avec trois classes : CP, CE1-CE2 et CM1-CM2."},
    {"id": "192", "file": "news_192_diplome-national-du-brevet-2026-100-de-reussite.html",
     "path": "actualites/diplome-national-du-brevet-2026-100-de-reussite/", "parent": "161", "is_news": True,
     "title": "Diplôme national du brevet 2026 : 100 % de réussite", "date": "1 août 2026",
     "excerpt": "Tous les candidats présentés par le Collège Chambertin ont obtenu le DNB en 2026 ; 97 % ont obtenu une mention."},
    {"id": "193", "file": "news_193_le-futur-site-du-cours-chambertin-reunit-lecole-et-le-college.html",
     "path": "actualites/le-futur-site-du-cours-chambertin-reunit-lecole-et-le-college/", "parent": "161", "is_news": True,
     "title": "Le futur site du Cours Chambertin réunit l’École et le Collège", "date": "1 août 2026",
     "excerpt": "La réouverture de l’École conduit l’institution à rassembler ses contenus sous une même identité, tout en maintenant des informations et des admissions distinctes."},
]

BY_ID = {p["id"]: p for p in PAGES}

# Pages appartenant à l'identité "École" (bleu) ou "Collège" (bordeaux), pour
# teinter localement les pages propres à chaque établissement. Les pages
# communes (accueil, Le Cours Chambertin, admissions générales, etc.)
# gardent l'accent bordeaux par défaut.
ECOLE_IDS = {"149", "150", "151", "152", "153", "154"}
COLLEGE_IDS = {"155", "156", "157", "158", "159"}


def section_of(page_id):
    if page_id in ECOLE_IDS:
        return "ecole"
    if page_id in COLLEGE_IDS:
        return "college"
    return ""

# Chemin (dans le site d'origine) -> chemin (sur ce site), pour la réécriture des liens.
ORIGINAL_PATH_TO_NEW = {}
for p in PAGES:
    if p.get("is_news"):
        # Permaliens d'origine du type /2026/08/01/<slug>/
        slug = p["file"].split("_", 1)[1].rsplit(".", 1)[0]
        # tel qu'observé dans le contenu source
        ORIGINAL_PATH_TO_NEW[f"/2026/08/01/{slug}/"] = p["path"]
    ORIGINAL_PATH_TO_NEW["/" + p["path"]] = p["path"]
ORIGINAL_PATH_TO_NEW["/"] = ""  # accueil

# Menu principal (correspond au menu WordPress "Menu Haut" d'origine)
NAV_MENU = [
    {"id": "145", "label": "Le Cours Chambertin", "children": ["146", "147", "148"]},
    {"id": "149", "label": "L’École", "children": ["150", "151", "152", "153", "154"]},
    {"id": "155", "label": "Le Collège", "children": ["156", "157", "158", "159"]},
    {"id": "160", "label": "Admissions", "children": []},
    {"id": "161", "label": "Vie & actualités", "children": []},
    {"id": "162", "label": "Infos pratiques", "children": []},
    {"id": "164", "label": "Contact", "children": []},
]

FOOTER_NAV = [
    ("145", "Le Cours Chambertin"), ("149", "L’École Chambertin"), ("155", "Le Collège Chambertin"),
    ("160", "Admissions"), ("161", "Vie & actualités"), ("162", "Informations pratiques"),
    ("163", "FAQ"), ("164", "Contact"), ("165", "Mentions légales"),
    ("166", "Politique de confidentialité"), ("298", "Plan du site"),
]


def depth_of(path):
    return 0 if path == "" else path.count("/")


def rel_prefix(depth):
    return "../" * depth


def to_relative(target_path, depth):
    """target_path: chemin site ('' ou 'ecole/cp/') -> chemin relatif depuis une page à `depth`."""
    prefix = rel_prefix(depth)
    if target_path == "":
        return prefix if prefix else "./"
    return prefix + target_path


IMG_RE = re.compile(re.escape(ORIGINAL_PREFIX) + r"/wp-content/uploads/[^\"'\s]*?/([A-Za-z0-9_.-]+\.(?:jpg|jpeg|png|svg|webp))")
LINK_RE = re.compile(re.escape(ORIGINAL_PREFIX) + r"(/[^\"'\s]*)?")


def rewrite_content(html, depth):
    # 1) images -> assets/images/<file>
    def img_sub(m):
        fname = m.group(1)
        return to_relative("assets/images/" + fname, depth)
    html = IMG_RE.sub(img_sub, html)

    # 2) liens internes -> chemins relatifs
    def link_sub(m):
        path = m.group(1) or "/"
        if not path.startswith("/"):
            path = "/" + path
        if path in ORIGINAL_PATH_TO_NEW:
            return to_relative(ORIGINAL_PATH_TO_NEW[path], depth)
        return to_relative("", depth)  # repli : accueil
    html = LINK_RE.sub(link_sub, html)
    return html


def nav_html(current_id, depth):
    items = []
    for entry in NAV_MENU:
        page = BY_ID[entry["id"]]
        href = to_relative(page["path"], depth)
        is_current = (current_id == entry["id"]) or (BY_ID.get(current_id, {}).get("parent") == entry["id"])
        cls = ' class="current"' if is_current else ""
        if entry["children"]:
            sub_items = "".join(
                f'<li><a href="{to_relative(BY_ID[c]["path"], depth)}">{BY_ID[c]["title"]}</a></li>'
                for c in entry["children"]
            )
            items.append(
                f'<li class="has-children"{cls}><a href="{href}">{entry["label"]}</a>'
                f'<ul class="sub-menu">{sub_items}</ul></li>'
            )
        else:
            items.append(f'<li{cls}><a href="{href}">{entry["label"]}</a></li>')
    return "\n".join(items)


def footer_nav_html(depth):
    mid = (len(FOOTER_NAV) + 1) // 2
    col1 = FOOTER_NAV[:mid]
    col2 = FOOTER_NAV[mid:]
    def col(items):
        return "".join(f'<li><a href="{to_relative(BY_ID[i]["path"], depth)}">{label}</a></li>' for i, label in items)
    return f'<ul>{col(col1)}</ul><ul>{col(col2)}</ul>'


def render_page(page):
    depth = depth_of(page["path"])
    prefix = rel_prefix(depth)

    content_file = os.path.join(CONTENT_DIR, page["file"])
    with open(content_file, encoding="utf-8") as f:
        raw = f.read()
    content = rewrite_content(raw, depth)

    if page.get("is_news"):
        content = (
            f'<div class="wp-block-group cc-section cc-section--craie">'
            f'<div class="wp-block-group cc-narrow">'
            f'<p class="surtitre">{page["date"]}</p>'
            f'<h1>{page["title"]}</h1>'
            f'</div></div>'
            f'<div class="wp-block-group cc-section cc-section--blanc">'
            f'<div class="wp-block-group cc-narrow cc-article">'
            f'{content}'
            f'</div></div>'
        )

    logo_file = {
        "ecole": "cc-logo-ecole-transparent.png",
        "college": "cc-logo-college-transparent.png",
    }.get(section_of(page["id"]), "cc-logo-master-transparent.png")
    logo_src = to_relative(f"assets/images/{logo_file}", depth)
    favicon_src = to_relative("assets/images/cc-logo-sans-point-transparent.png", depth)
    logo_footer_src = to_relative("assets/images/cc-logo-sans-point-blanc.png", depth)
    css_href = to_relative("assets/css/style.css", depth)
    js_src = to_relative("assets/js/main.js", depth)
    home_href = to_relative("", depth)

    canonical_path = page["path"]
    breadcrumb = breadcrumb_html(page, depth)

    html = TEMPLATE.format(
        lang="fr",
        section=section_of(page["id"]),
        title=f'{page["title"]} | {SITE_NAME}' if page["path"] else f'{SITE_NAME} | Enseignement privé École et Collège',
        description=page["excerpt"],
        css_href=css_href,
        js_src=js_src,
        home_href=home_href,
        logo_src=logo_src,
        favicon_src=favicon_src,
        logo_footer_src=logo_footer_src,
        nav_items=nav_html(page["id"], depth),
        breadcrumb=breadcrumb,
        content=content,
        footer_nav=footer_nav_html(depth),
        year="2026",
    )
    return html


def breadcrumb_html(page, depth):
    if page["path"] == "":
        return ""
    chain = []
    cur = page
    while cur:
        chain.append(cur)
        cur = BY_ID.get(cur.get("parent")) if cur.get("parent") else None
    chain.reverse()
    parts = [f'<a href="{to_relative("", depth)}">Accueil</a>']
    for i, c in enumerate(chain):
        if i == len(chain) - 1:
            parts.append(f'<span aria-current="page">{c["title"]}</span>')
        else:
            parts.append(f'<a href="{to_relative(c["path"], depth)}">{c["title"]}</a>')
    return '<nav class="cc-breadcrumb" aria-label="Fil d’Ariane">' + " <span class=\"sep\">/</span> ".join(parts) + "</nav>"


TEMPLATE = """<!DOCTYPE html>
<html lang="{lang}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{title}</title>
<meta name="description" content="{description}">
<link rel="icon" href="{favicon_src}">
<link rel="stylesheet" href="{css_href}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Spectral:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body data-section="{section}">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<header id="site-header">
  <div class="cc-header-inner">
    <a class="cc-logo" href="{home_href}">
      <img src="{logo_src}" alt="Cours Chambertin" width="220" height="80">
    </a>
    <button id="nav-toggle" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
      <span></span><span></span><span></span>
      <span class="sr-only">Menu</span>
    </button>
    <nav id="site-nav" class="site-nav">
      <ul>
        {nav_items}
      </ul>
    </nav>
  </div>
</header>

<main id="contenu">
{breadcrumb}
{content}
</main>

<footer class="cc-pied">
  <div class="cc-pied-inner">
    <div class="cc-pied-grille">
      <div class="cc-pied-marque">
        <img src="{logo_footer_src}" alt="Cours Chambertin" loading="lazy">
        <p>Ensemble scolaire fondé en 1982 à Asnières-sur-Seine, composé de l’École Chambertin et du Collège Chambertin.</p>
        <a class="cc-pied-itineraire" href="https://www.google.com/maps/search/?api=1&query=9+avenue+de+la+Marne+92600+Asni%C3%A8res-sur-Seine" target="_blank" rel="noopener noreferrer">Voir l’itinéraire &rarr;</a>
      </div>
      <nav class="cc-pied-nav" aria-label="Plan du site">
        {footer_nav}
      </nav>
      <div>
        <h2>Collège Chambertin</h2>
        <p>9 avenue de la Marne<br>92600 Asnières-sur-Seine<br>Tél. 01 47 93 97 92</p>
      </div>
      <div>
        <h2>École Chambertin</h2>
        <p>14 rue Steffen<br>92600 Asnières-sur-Seine<br>Réouverture en septembre 2027</p>
      </div>
    </div>
    <div class="cc-pied-bas">
      <p class="cc-pied-mention">&copy; {year} Cours Chambertin — Crédit image : Freepik.com, Pexels.com, Pixabay.com, Unsplash.com</p>
      <ul class="cc-pied-social">
        <li><a href="#" aria-label="Facebook">Facebook</a></li>
        <li><a href="#" aria-label="Instagram">Instagram</a></li>
      </ul>
    </div>
  </div>
</footer>

<script src="{js_src}"></script>
</body>
</html>
"""


def write_page(page):
    html = render_page(page)
    out_dir = os.path.join(ROOT, page["path"]) if page["path"] else ROOT
    os.makedirs(out_dir, exist_ok=True)
    out_file = os.path.join(out_dir, "index.html")
    with open(out_file, "w", encoding="utf-8") as f:
        f.write(html)
    print("built", out_file.replace(ROOT + "/", ""))


def main():
    for page in PAGES:
        write_page(page)
    print(f"\n{len(PAGES)} pages générées.")


if __name__ == "__main__":
    main()
