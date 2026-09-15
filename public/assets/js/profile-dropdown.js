document.querySelectorAll(".profile").forEach((profile) => {
  const button = profile.querySelector(".profile-menu");
  const dropdown = profile.querySelector(".profile-dropdown");
  if (!button || !dropdown) return;

  button.setAttribute("aria-expanded", "false");
  button.setAttribute("aria-controls", dropdown.id || "profile-dropdown");

  const close = () => {
    profile.classList.remove("is-open");
    button.setAttribute("aria-expanded", "false");
  };

  button.addEventListener("click", (event) => {
    event.stopPropagation();
    const open = profile.classList.toggle("is-open");
    button.setAttribute("aria-expanded", String(open));
  });

  dropdown.addEventListener("click", (event) => event.stopPropagation());
  document.addEventListener("click", close);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") close();
  });
});
