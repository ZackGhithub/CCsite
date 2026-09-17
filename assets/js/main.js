// Cours Chambertin — comportements front minimes (menu mobile + sous-menus tactiles)
(function () {
  "use strict";

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
