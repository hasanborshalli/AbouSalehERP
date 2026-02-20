(function () {
    const floorCountInput = document.getElementById("floor_count");
    const floorsWrap = document.getElementById("cpFloorsWrap");
    const floorTpl = document.getElementById("cpFloorTemplate");

    if (!floorCountInput || !floorsWrap || !floorTpl) return;

    function buildInputName(floorNumber, unitIndex, key) {
        // floors[1][units][0][unit_code]
        return `floors[${floorNumber}][units][${unitIndex}][${key}]`;
    }

    function createUnitRow(floorNumber, unitIndex, unitTemplate) {
        const frag = unitTemplate.content.cloneNode(true);
        const row = frag.querySelector("[data-unit-row]");
        const inputs = row.querySelectorAll("[data-name]");

        inputs.forEach((el) => {
            const key = el.getAttribute("data-name");
            el.setAttribute(
                "name",
                buildInputName(floorNumber, unitIndex, key),
            );
        });

        return row;
    }

    function renderUnitsForFloor(floorCard, floorNumber, count) {
        const unitsWrap = floorCard.querySelector("[data-units-wrap]");
        const unitTemplate = floorCard.querySelector(
            "template[data-unit-template]",
        );
        if (!unitsWrap || !unitTemplate) return;

        const target = Math.max(1, Number(count || 1));
        const rows = Array.from(unitsWrap.querySelectorAll("[data-unit-row]"));

        // 1) If target is bigger → append new rows
        if (rows.length < target) {
            for (let i = rows.length; i < target; i++) {
                unitsWrap.appendChild(
                    createUnitRow(floorNumber, i, unitTemplate),
                );
            }
        }

        // 2) If target is smaller → remove extra rows (from the end)
        if (rows.length > target) {
            for (let i = rows.length - 1; i >= target; i--) {
                rows[i].remove();
            }
        }

        // 3) Re-index names (important after removals)
        const updatedRows = unitsWrap.querySelectorAll("[data-unit-row]");
        updatedRows.forEach((r, idx) => {
            r.querySelectorAll("[data-name]").forEach((el) => {
                const key = el.getAttribute("data-name");
                el.setAttribute("name", buildInputName(floorNumber, idx, key));
            });
        });

        updateRemoveButtons(unitsWrap);
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

    function wireFloorEvents(floorCard, floorNumber) {
        const unitsCountInput = floorCard.querySelector("[data-units-count]");
        const unitsWrap = floorCard.querySelector("[data-units-wrap]");
        const unitTemplate = floorCard.querySelector(
            "template[data-unit-template]",
        );
        const addUnitBtn = floorCard.querySelector("[data-add-unit]");

        if (!unitsCountInput || !unitsWrap || !unitTemplate || !addUnitBtn)
            return;

        // Change units count -> rebuild units
        unitsCountInput.addEventListener("input", () => {
            renderUnitsForFloor(floorCard, floorNumber, unitsCountInput.value);
        });

        // Add unit button -> append one new row
        addUnitBtn.addEventListener("click", () => {
            const current =
                unitsWrap.querySelectorAll("[data-unit-row]").length;
            unitsWrap.appendChild(
                createUnitRow(floorNumber, current, unitTemplate),
            );
            unitsCountInput.value = current + 1;
            updateRemoveButtons(unitsWrap);
        });

        // Remove unit button (event delegation)
        unitsWrap.addEventListener("click", (e) => {
            const btn = e.target.closest("[data-remove-unit]");
            if (!btn) return;
            const row = btn.closest("[data-unit-row]");
            if (!row) return;

            row.remove();

            // Re-index names to keep backend clean
            const rows = unitsWrap.querySelectorAll("[data-unit-row]");
            rows.forEach((r, idx) => {
                r.querySelectorAll("[data-name]").forEach((el) => {
                    const key = el.getAttribute("data-name");
                    el.setAttribute(
                        "name",
                        buildInputName(floorNumber, idx, key),
                    );
                });
            });

            unitsCountInput.value = rows.length;
            updateRemoveButtons(unitsWrap);
        });
    }

    function renderFloors(count) {
        floorsWrap.innerHTML = "";

        const safeCount = Math.max(1, Math.min(200, Number(count || 1))); // guard
        for (let floorNumber = 1; floorNumber <= safeCount; floorNumber++) {
            const node = floorTpl.content.cloneNode(true);
            const card = node.querySelector("[data-floor-card]");
            const floorNumSpan = node.querySelector("[data-floor-number]");

            floorNumSpan.textContent = floorNumber;

            // initial units: 1
            floorsWrap.appendChild(node);

            // must query again after append because node is inserted
            const inserted =
                floorsWrap.querySelectorAll("[data-floor-card]")[
                    floorNumber - 1
                ];
            renderUnitsForFloor(inserted, floorNumber, 1);
            wireFloorEvents(inserted, floorNumber);
        }
    }

    // Debounce typing so it doesn't rebuild on every keypress too aggressively
    let t = null;
    floorCountInput.addEventListener("input", () => {
        clearTimeout(t);
        t = setTimeout(() => renderFloors(floorCountInput.value), 250);
    });

    // initial state (optional)
    renderFloors(1);
})();
