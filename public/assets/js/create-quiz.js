
document.querySelectorAll(".quiz-choice").forEach((b) =>
    b.addEventListener("click", () => {
        document
            .querySelectorAll('[data-group="' + b.dataset.group + '"]')
            .forEach((x) => {
                x.classList.remove("active");
                x.setAttribute("aria-pressed", "false");
            });
        b.classList.add("active");
        b.setAttribute("aria-pressed", "true");
    }),
);
document
    .querySelectorAll(".quiz-tag button")
    .forEach((b) =>
        b.addEventListener("click", () => b.parentElement.remove()),
    );
document.getElementById("quizForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const b = e.currentTarget.querySelector('button[type="submit"]');
    b.lastElementChild.textContent = "Quiz created";
    setTimeout(() => (b.lastElementChild.textContent = "Create quiz"), 1400);
});
