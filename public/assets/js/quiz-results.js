
document
    .querySelectorAll(".review-summary")
    .forEach((summary) => {
        const toggle = () => {
            const card = summary.closest(".review-card");
            const open = card.classList.toggle("open");
            summary.setAttribute("aria-expanded", open ? "true" : "false");
            const icon = summary.querySelector(".material-symbols-outlined");
            if (icon) icon.textContent = open ? "expand_less" : "expand_more";
        };
        summary.addEventListener("click", toggle);
        summary.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                toggle();
            }
        });
    });
document.querySelectorAll(".review-filters .btn").forEach((btn) =>
    btn.addEventListener("click", () => {
        document
            .querySelectorAll(".review-filters .btn")
            .forEach((x) => x.classList.remove("active"));
        btn.classList.add("active");
        const filter = btn.dataset.filter;
        document.querySelectorAll(".review-card").forEach((card) => {
            card.hidden = filter !== "all" && card.dataset.status !== filter;
        });
    }),
);
document.getElementById("reviewDiagnostics")?.addEventListener("click", () =>
    document.getElementById("questionReview")?.scrollIntoView({ behavior: "smooth" }),
);
