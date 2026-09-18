(() => {

    const names = [
        "Chest pain assessment",
        "ECG interpretation",
        "Acute asthma attack",
        "Heart failure staging",
        "Diabetes screening",
        "Hypertension workup",
        "Delirium in older adults",
        "Stroke warning signs",
        "Pneumonia treatment",
        "Anemia evaluation",
        "Rheumatoid arthritis therapy",
        "COPD acute exacerbation",
        "Hyperkalemia emergency",
        "Gastrointestinal hemorrhage",
        "Delirium in older adults",
        "Thyroid function & myxedema",
        "Acute kidney injury criteria",
        "Sepsis 3-hour bundle protocol",
        "Cirrhosis & ascites evaluation",
        "Ischemic stroke thrombolysis",
    ];
    const list = document.getElementById("questionList");
    document
        .getElementById("mobileNavTrigger")
        ?.addEventListener("click", () =>
            list.closest(".question-sidebar").classList.toggle("open"),
        );
    let current = 15;
    names.forEach((name, index) => {
        const number = index + 1;
        const button = document.createElement("button");
        button.type = "button";
        button.className = `question-item ${number < 15 ? "answered " : ""}${number === current ? "current" : ""}`;
        button.setAttribute("role", "listitem");
        button.innerHTML = `<span class="q-label"><span class="q-num">${String(number).padStart(2, "0")}</span><span class="q-name">${name}</span></span><span class="q-status">${number < 15 ? "✓" : number === current ? "●" : ""}</span>`;
        button.addEventListener("click", () => {
            current = number;
            list.closest(".question-sidebar").classList.remove("open");
            document
                .querySelectorAll(".question-item")
                .forEach((item, i) =>
                    item.classList.toggle("current", i + 1 === current),
                );
            document.getElementById("questionNumber").textContent = current;
        });
        list.appendChild(button);
    });
    list.querySelector(".current")?.scrollIntoView({ block: "center" });

    const options = [...document.querySelectorAll(".quiz-option")];
    let selected = options.findIndex((option) =>
        option.classList.contains("is-selected"),
    );
    const selectOption = (index) => {
        if (index < 0 || index >= options.length) return;
        options.forEach((option, optionIndex) => {
            const active = optionIndex === index;
            option.classList.toggle("is-selected", active);
            option.setAttribute("aria-checked", String(active));
        });
        selected = index;
    };
    options.forEach((option, index) =>
        option.addEventListener("click", () => selectOption(index)),
    );
    const next = () => {
        if (current < names.length) list.children[current]?.click();
        else document.getElementById("explanationBox").hidden = false;
    };
    document.getElementById("nextBtn")?.addEventListener("click", next);
    document.getElementById("previousBtn")?.addEventListener("click", () => {
        if (current > 1) list.children[current - 2]?.click();
    });
    document.getElementById("flagBtn")?.addEventListener("click", (event) => {
        event.currentTarget.classList.toggle("is-flagged");
        event.currentTarget.innerHTML = event.currentTarget.classList.contains(
            "is-flagged",
        )
            ? '<i class="bi bi-flag-fill"></i> Flagged'
            : '<i class="bi bi-flag"></i> Flag';
    });
    document.addEventListener("keydown", (event) => {
        const index = ["a", "b", "c", "d"].indexOf(event.key.toLowerCase());
        if (index > -1) selectOption(index);
        if (event.key === "Enter") {
            event.preventDefault();
            next();
        }
    });
})();
