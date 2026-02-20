(function () {
    const floorCountInput = document.getElementById("floor_count");
    const floorsWrap = document.getElementById("cpFloorsWrap");
    const floorTpl = document.getElementById("cpFloorTemplate");
    if (!floorCountInput || !floorsWrap || !floorTpl) return;

    const existingFloors = Array.isArray(window.__existingFloors)
        ? window.__existingFloors
        : [];

    function buildInputName(floorIndex, unitIndex, key) {
        return `floors[${floorIndex}][units][${unitIndex}][${key}]`;
    }

    function createUnitRow(floorIndex, unitIndex, unitTemplate, data) {
        const frag = unitTemplate.content.cloneNode(true);
        const row = frag.querySelector("[data-unit-row]");

        // hidden unit id
        const hiddenId = document.createElement("input");
        hiddenId.type = "hidden";
        hiddenId.setAttribute("data-name", "id");
        row.prepend(hiddenId);

        row.querySelectorAll("[data-name]").forEach((el) => {
            const key = el.getAttribute("data-name");
            el.name = buildInputName(floorIndex, unitIndex, key);

            if (data && key in data) {
                if (el.tagName === "SELECT")
                    el.value = data[key] ?? "available";
                else el.value = data[key] ?? "";
            }
        });

        hiddenId.name = buildInputName(floorIndex, unitIndex, "id");
        hiddenId.value = data?.id ?? "";

        return row;
    }

    function updateRemoveButtons(unitsWrap) {
        const rows = unitsWrap.querySelectorAll("[data-unit-row]");
        rows.forEach((row) => {
            const btn = row.querySelector("[data-remove-unit]");
            if (!btn) return;
            btn.disabled = rows.length === 1;
            btn.style.opacity = btn.disabled ? "0.35" : "1";
            btn.style.pointerEvents = btn.disabled ? "none" : "auto";
        });
    }

    function wireFloorEvents(floorCard, floorIndex) {
        const unitsCountInput = floorCard.querySelector("[data-units-count]");
        const unitsWrap = floorCard.querySelector("[data-units-wrap]");
        const unitTemplate = floorCard.querySelector(
            "template[data-unit-template]",
        );
        const addUnitBtn = floorCard.querySelector("[data-add-unit]");
        if (!unitsCountInput || !unitsWrap || !unitTemplate || !addUnitBtn)
            return;

        function reindexUnitNames() {
            const rows = unitsWrap.querySelectorAll("[data-unit-row]");
            rows.forEach((r, idx) => {
                r.querySelectorAll("[data-name]").forEach((el) => {
                    const key = el.getAttribute("data-name");
                    el.name = buildInputName(floorIndex, idx, key);
                });
            });
        }

        unitsCountInput.addEventListener("input", () => {
            // IMPORTANT: do NOT clear existing values on edit.
            // Only allow increasing by adding new blank units.
            const target = Math.max(1, Number(unitsCountInput.value || 1));
            const current =
                unitsWrap.querySelectorAll("[data-unit-row]").length;

            if (target > current) {
                for (let i = current; i < target; i++) {
                    unitsWrap.appendChild(
                        createUnitRow(floorIndex, i, unitTemplate, null),
                    );
                }
            } else if (target < current) {
                if (
                    !confirm(
                        "Reducing units will remove the last units from the form. Continue?",
                    )
                ) {
                    unitsCountInput.value = current;
                    return;
                }
                for (let i = current - 1; i >= target; i--) {
                    unitsWrap.querySelectorAll("[data-unit-row]")[i]?.remove();
                }
                reindexUnitNames();
            }

            updateRemoveButtons(unitsWrap);
        });

        addUnitBtn.addEventListener("click", () => {
            const current =
                unitsWrap.querySelectorAll("[data-unit-row]").length;
            unitsWrap.appendChild(
                createUnitRow(floorIndex, current, unitTemplate, null),
            );
            unitsCountInput.value = current + 1;
            updateRemoveButtons(unitsWrap);
        });

        unitsWrap.addEventListener("click", (e) => {
            const btn = e.target.closest("[data-remove-unit]");
            if (!btn) return;
            const row = btn.closest("[data-unit-row]");
            if (!row) return;

            row.remove();
            reindexUnitNames();
            unitsCountInput.value =
                unitsWrap.querySelectorAll("[data-unit-row]").length;
            updateRemoveButtons(unitsWrap);
        });

        updateRemoveButtons(unitsWrap);
    }

    function renderFloorCard(floorIndex, floorNumberLabel, floorId, units) {
        const node = floorTpl.content.cloneNode(true);
        floorsWrap.appendChild(node);

        const card =
            floorsWrap.querySelectorAll("[data-floor-card]")[floorIndex];
        card.querySelector("[data-floor-number]").textContent =
            floorNumberLabel;

        // hidden floor fields
        const floorIdInput = document.createElement("input");
        floorIdInput.type = "hidden";
        floorIdInput.name = `floors[${floorIndex}][id]`;
        floorIdInput.value = floorId || "";
        card.prepend(floorIdInput);

        const floorNumberInput = document.createElement("input");
        floorNumberInput.type = "hidden";
        floorNumberInput.name = `floors[${floorIndex}][floor_number]`;
        floorNumberInput.value = floorNumberLabel;
        card.prepend(floorNumberInput);

        // units
        const unitsWrap = card.querySelector("[data-units-wrap]");
        const unitTemplate = card.querySelector("template[data-unit-template]");
        const unitsCountInput = card.querySelector("[data-units-count]");

        unitsWrap.innerHTML = "";
        const safeUnits = Array.isArray(units) ? units : [];
        const count = Math.max(1, safeUnits.length || 1);

        for (let i = 0; i < count; i++) {
            unitsWrap.appendChild(
                createUnitRow(
                    floorIndex,
                    i,
                    unitTemplate,
                    safeUnits[i] || null,
                ),
            );
        }
        unitsCountInput.value = count;

        wireFloorEvents(card, floorIndex);
    }

    function renderFromDB() {
        floorsWrap.innerHTML = "";
        existingFloors.forEach((f, idx) => {
            renderFloorCard(idx, f.floor_number ?? idx + 1, f.id, f.units);
        });
        floorCountInput.value = existingFloors.length || 1;
    }

    function appendNewFloors(targetCount) {
        const safe = Math.max(1, Math.min(200, Number(targetCount || 1)));
        const current = floorsWrap.querySelectorAll("[data-floor-card]").length;

        if (safe > current) {
            for (let i = current; i < safe; i++) {
                renderFloorCard(i, i + 1, "", []); // new floor no id
            }
            return;
        }

        if (safe < current) {
            if (
                !confirm(
                    "Reducing floors will remove the last floors from the form. Continue?",
                )
            ) {
                floorCountInput.value = current;
                return;
            }
            for (let i = current - 1; i >= safe; i--) {
                floorsWrap.querySelectorAll("[data-floor-card]")[i]?.remove();
            }
            // NOTE: we don't reindex floor indexes here to avoid breaking DB references mid-edit.
            // If you want strict reindexing, do it, but you must also shift hidden ids accordingly.
        }
    }

    // init
    renderFromDB();

    let t = null;
    floorCountInput.addEventListener("input", () => {
        clearTimeout(t);
        t = setTimeout(() => appendNewFloors(floorCountInput.value), 250);
    });
})();
