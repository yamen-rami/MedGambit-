const root = document.documentElement,
        toggle = document.getElementById("themeToggle"),
        name = document.getElementById("themeName");
      const saved = localStorage.getItem("medgambit-theme");
      if (saved) root.dataset.theme = saved;
      function sync() {
        if (name) {
          name.textContent =
            root.dataset.theme === "light" ? "LIGHT ENGINE" : "DARK ENGINE";
        }
        if (toggle) {
          toggle.setAttribute(
            "aria-label",
            "Switch to " +
              (root.dataset.theme === "light" ? "dark" : "light") +
              " mode",
          );
        }
      }
      if (toggle) {
        toggle.addEventListener("click", () => {
          root.dataset.theme = root.dataset.theme === "light" ? "dark" : "light";
          localStorage.setItem("medgambit-theme", root.dataset.theme);
          sync();
        });
      }
      const navToggle = document.getElementById("navToggle");
      const topLeft = document.querySelector(".top-left");
      if (navToggle && topLeft) {
        navToggle.addEventListener("click", () => {
          const isOpen = topLeft.classList.toggle("nav-open");
          navToggle.setAttribute("aria-expanded", String(isOpen));
          navToggle.setAttribute("aria-label", isOpen ? "Close navigation" : "Open navigation");
        });
      }
      sync();
