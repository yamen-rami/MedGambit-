const root = document.documentElement,
        toggle = document.getElementById("themeToggle");
      const saved = localStorage.getItem("medgambit-theme");
      if (saved) root.dataset.theme = saved;
      toggle.addEventListener("click", () => {
        root.dataset.theme = root.dataset.theme === "light" ? "dark" : "light";
        localStorage.setItem("medgambit-theme", root.dataset.theme);
      });
      const topLeft = document.querySelector(".top-left"),
        navToggle = document.getElementById("navToggle");
      navToggle.addEventListener("click", () => {
        const open = topLeft.classList.toggle("nav-open");
        navToggle.setAttribute("aria-expanded", open);
        navToggle.setAttribute(
          "aria-label",
          open ? "Close navigation" : "Open navigation",
        );
      });
      document.querySelectorAll(".mg-choice").forEach((button) =>
        button.addEventListener("click", () => {
          document
            .querySelectorAll('[data-group="' + button.dataset.group + '"]')
            .forEach((item) => {
              item.classList.remove("mg-choice-active");
              item.setAttribute("aria-pressed", "false");
            });
          button.classList.add("mg-choice-active");
          button.setAttribute("aria-pressed", "true");
        }),
      );
      document
        .querySelectorAll(".select2-tag button")
        .forEach((button) =>
          button.addEventListener("click", () => button.parentElement.remove()),
        );
      document.getElementById("arenaForm").addEventListener("submit", (e) => {
        e.preventDefault();
        const submit = e.currentTarget.querySelector('button[type="submit"]');
        submit.querySelector("span").textContent = "Opponent search queued";
        setTimeout(
          () => (submit.querySelector("span").textContent = "Find opponent"),
          1400,
        );
      });

