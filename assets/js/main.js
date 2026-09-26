// Cours Chambertin — comportements front minimes (menu mobile + sous-menus tactiles)
(function () {
  "use strict";

  // Bandeau d'annonce : fermeture mémorisée par annonce (la clé change si le
  // message change, pour que la fermeture d'une ancienne annonce ne masque
  // pas la suivante).
  var announce = document.getElementById("cc-announce");
  if (announce) {
    var key = "cc-announce-dismissed-" + announce.dataset.announceId;
    var dismissed = false;
    try { dismissed = window.localStorage.getItem(key) === "1"; } catch (e) {}
    if (dismissed) {
      announce.remove();
    } else {
      var closeBtn = announce.querySelector(".cc-announce-close");
      if (closeBtn) {
        closeBtn.addEventListener("click", function () {
          announce.remove();
          try { window.localStorage.setItem(key, "1"); } catch (e) {}
        });
      }
    }
  }

  var toggle = document.getElementById("nav-toggle");
  var nav = document.getElementById("site-nav");

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var isOpen = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  }

  // Sur mobile/tablette, un premier tap sur un lien avec sous-menu déplie le sous-menu
  // au lieu de suivre le lien (le second tap, ou un tap sur un sous-lien, navigue).
  var mq = window.matchMedia("(max-width: 880px)");
  document.querySelectorAll(".site-nav li.has-children > a").forEach(function (link) {
    link.addEventListener("click", function (e) {
      if (!mq.matches) return;
      var li = link.parentElement;
      if (!li.classList.contains("open")) {
        e.preventDefault();
        document.querySelectorAll(".site-nav li.has-children.open").forEach(function (openLi) {
          if (openLi !== li) openLi.classList.remove("open");
        });
        li.classList.add("open");
      }
    });
  });
})();
