(function () {
    const wrap = document.getElementById("cpMaterials");
    const tpl = document.getElementById("cpMaterialTemplate");
    const addBtn = document.getElementById("cpAddMaterialBtn");

    if (!wrap || !tpl || !addBtn) return;

    function setUnitForRow(row) {
        const itemSelect = row.querySelector(
            'select[name="materials[item_id][]"]',
        );
        const unitInput = row.querySelector('input[name="materials[unit][]"]');
        if (!itemSelect || !unitInput) return;

        const opt = itemSelect.selectedOptions?.[0];
        unitInput.value = opt?.dataset?.unit ?? "";
    }

    function updateRemoveButtons() {
        const rows = wrap.querySelectorAll("[data-row]");
        rows.forEach((row) => {
            const btn = row.querySelector("[data-remove]");
            if (!btn) return;
            btn.disabled = rows.length === 1;
            btn.style.opacity = btn.disabled ? "0.35" : "1";
            btn.style.pointerEvents = btn.disabled ? "none" : "auto";
        });
    }

    // change item -> fill unit (works for all rows)
    wrap.addEventListener("change", (e) => {
        const itemSelect = e.target.closest(
            'select[name="materials[item_id][]"]',
        );
        if (!itemSelect) return;
        const row = itemSelect.closest("[data-row]");
        if (row) setUnitForRow(row);
    });

    // add row
    addBtn.addEventListener("click", () => {
        const frag = tpl.content.cloneNode(true);
        wrap.appendChild(frag);

        // init last row (optional)
        const lastRow = wrap.querySelector("[data-row]:last-child");
        if (lastRow) setUnitForRow(lastRow);

        updateRemoveButtons();
    });

    // remove row
    wrap.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-remove]");
        if (!btn) return;
        const row = btn.closest("[data-row]");
        if (!row) return;
        row.remove();
        updateRemoveButtons();
    });

    // init
    wrap.querySelectorAll("[data-row]").forEach(setUnitForRow);
    updateRemoveButtons();
})();
