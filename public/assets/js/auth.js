(() => {
    
    const levelInputs = document.querySelectorAll('[name="studyLevel"]');
    const yearField = document.getElementById("yearField");
    levelInputs.forEach((input) =>
        input.addEventListener("change", () => {
            const student = input.value === "student" && input.checked;
            if (yearField) {
                yearField.hidden = !student;
                yearField.querySelector("select").disabled = !student;
            }
            document
                .querySelectorAll(
                    '[data-choice-group="studyLevel"] .choice-label',
                )
                .forEach((label) =>
                    label.classList.toggle(
                        "is-active",
                        label.querySelector("input").checked,
                    ),
                );
        }),
    );
    document
        .querySelectorAll('[data-choice-group="gender"] input')
        .forEach((input) =>
            input.addEventListener("change", () =>
                document
                    .querySelectorAll(
                        '[data-choice-group="gender"] .choice-label',
                    )
                    .forEach((label) =>
                        label.classList.toggle(
                            "is-active",
                            label.querySelector("input").checked,
                        ),
                    ),
            ),
        );
    const fileInput = document.getElementById("profileImage");
    const preview = document.getElementById("profilePreview");
    const fileName = document.getElementById("fileName");
    const remove = document.getElementById("removeImage");
    fileInput?.addEventListener("change", () => {
        const file = fileInput.files[0];
        if (!file) return;
        preview.innerHTML = "";
        const image = document.createElement("img");
        image.src = URL.createObjectURL(file);
        preview.append(image);
        fileName.textContent = file.name;
        remove.hidden = false;
    });
    remove?.addEventListener("click", () => {
        fileInput.value = "";
        preview.textContent = "Y";
        fileName.textContent = "No image selected";
        remove.hidden = true;
    });
})();
