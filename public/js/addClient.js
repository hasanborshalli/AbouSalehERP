(function () {
    const totalPrice = document.getElementById("total_price");
    const discount = document.getElementById("discount");
    const downPayment = document.getElementById("down_payment");
    const months = document.getElementById("installment_months");
    const monthlyPayment = document.getElementById("installment_amount");
    const autoCalcBtn = document.getElementById("autoCalcBtn");

    const netPriceText = document.getElementById("netPriceText");
    const remainingText = document.getElementById("remainingText");
    const totalPaidText = document.getElementById("totalPaidText");

    function num(el) {
        const v = el ? parseFloat(el.value) : 0;
        return Number.isFinite(v) ? v : 0;
    }

    function money(v) {
        return "$" + (Math.round(v * 100) / 100).toFixed(2);
    }

    function calc() {
        const t = num(totalPrice);
        const d = num(discount);
        const net = Math.max(t - d, 0);

        const dp = num(downPayment);
        const rem = Math.max(net - dp, 0);

        const m = Math.max(parseInt(months?.value || "0", 10) || 0, 0);
        const mp = num(monthlyPayment);
        const totalPaid = dp + m * mp;

        if (netPriceText) netPriceText.textContent = money(net);
        if (remainingText) remainingText.textContent = money(rem);
        if (totalPaidText) totalPaidText.textContent = money(totalPaid);
    }

    function autoCalcMonthly() {
        const t = num(totalPrice);
        const d = num(discount);
        const net = Math.max(t - d, 0);

        const dp = num(downPayment);
        const rem = Math.max(net - dp, 0);

        const m = Math.max(parseInt(months?.value || "0", 10) || 0, 0);
        if (!m) return;

        const mp = rem / m;
        monthlyPayment.value = (Math.round(mp * 100) / 100).toFixed(2);
        calc();
    }

    [totalPrice, discount, downPayment, months, monthlyPayment].forEach(
        (el) => {
            if (!el) return;
            el.addEventListener("input", calc);
        },
    );

    if (autoCalcBtn) autoCalcBtn.addEventListener("click", autoCalcMonthly);

    calc();
})();
(function () {
    const sel = document.getElementById("apartment_id");
    if (!sel) return;

    const projectName = document.getElementById("project_name");
    const unitNumber = document.getElementById("unit_number");
    const location = document.getElementById("location");
    const totalPrice = document.getElementById("total_price");
    const aptFloor = document.getElementById("apt_floor");
    const aptArea = document.getElementById("apt_area");
    const aptBedrooms = document.getElementById("apt_bedrooms");
    const aptBathrooms = document.getElementById("apt_bathrooms");
    const aptNotes = document.getElementById("apt_notes");
    function setVal(el, v) {
        if (!el) return;
        el.value = (v ?? "").toString();
    }

    function fillFromSelected() {
        const opt = sel.selectedOptions && sel.selectedOptions[0];
        if (!opt) return;

        setVal(projectName, opt.dataset.projectName);
        setVal(unitNumber, opt.dataset.unitNumber);
        setVal(location, opt.dataset.location);

        // Optional: auto-fill total price if your apartment has price
        if (opt.dataset.price !== undefined && opt.dataset.price !== "") {
            setVal(totalPrice, opt.dataset.price);
        }
        setVal(aptFloor, opt.dataset.floor);
        setVal(aptArea, opt.dataset.area);
        setVal(aptBedrooms, opt.dataset.bedrooms);
        setVal(aptBathrooms, opt.dataset.bathrooms);
        if (aptNotes) aptNotes.value = (opt.dataset.notes ?? "").toString();
    }

    sel.addEventListener("change", fillFromSelected);

    // If page reloads with old selected value (validation error), refill
    fillFromSelected();
})();

// ── In-kind payment type toggle ──────────────────────────────────────────
(function () {
    const typeCash = document.getElementById("typeCash");
    const typeInKind = document.getElementById("typeInKind");
    const cashSection = document.getElementById("cashPaymentSection");
    const inKindSection = document.getElementById("inKindSection");
    const container = document.getElementById("ikItemsContainer");
    const addBtn = document.getElementById("addIkItemBtn");
    const ikTotal = document.getElementById("ikTotalDisplay");

    if (!typeCash || !typeInKind) return;

    let inventoryItems = [];

    async function loadItems() {
        if (inventoryItems.length > 0) return;
        try {
            const res = await fetch("/invoices/inventory-items");
            inventoryItems = await res.json();
        } catch {
            inventoryItems = [];
        }
    }

    function money(v) {
        return (
            "$" +
            Number(v || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        );
    }

    let rowIndex = 0;

    function buildSelect(index) {
        const sel = document.createElement("select");
        sel.name = `items[${index}][inventory_item_id]`;
        sel.className = "add-client__select ik-item-select";
        sel.required = true;
        sel.innerHTML = `<option value="">— select item —</option>`;
        inventoryItems.forEach((it) => {
            const opt = document.createElement("option");
            opt.value = it.id;
            opt.dataset.price = it.price;
            opt.dataset.unit = it.unit || "";
            opt.dataset.stock = it.quantity;
            opt.textContent = `${it.name} — ${money(it.price)}/${it.unit || "unit"} (stock: ${it.quantity})`;
            sel.appendChild(opt);
        });
        return sel;
    }

    function recalcGrandTotal() {
        let total = 0;
        container.querySelectorAll(".ik-row").forEach((row) => {
            const sel = row.querySelector(".ik-item-select");
            const qty = row.querySelector(".ik-qty");
            const opt = sel?.options[sel?.selectedIndex];
            const price = parseFloat(opt?.dataset.price || 0);
            const q = parseFloat(qty?.value || 0);
            total += price * q;
        });
        if (ikTotal) ikTotal.textContent = money(total);
    }

    function addRow() {
        const index = rowIndex++;
        const row = document.createElement("div");
        row.className = "ik-row";

        const select = buildSelect(index);
        const qty = document.createElement("input");
        qty.type = "number";
        qty.name = `items[${index}][quantity]`;
        qty.className = "add-client__input ik-qty";
        qty.placeholder = "Quantity";
        qty.min = "0.001";
        qty.step = "0.001";
        qty.required = true;

        const stockInfo = document.createElement("span");
        stockInfo.className = "ik-stock-info";

        const rowVal = document.createElement("span");
        rowVal.className = "ik-row-value";
        rowVal.textContent = "$0.00";

        const rmBtn = document.createElement("button");
        rmBtn.type = "button";
        rmBtn.className = "ik-remove-btn";
        rmBtn.textContent = "✕";
        rmBtn.addEventListener("click", () => {
            row.remove();
            recalcGrandTotal();
        });

        select.addEventListener("change", () => {
            const opt = select.options[select.selectedIndex];
            stockInfo.textContent = opt?.value
                ? `Stock: ${opt.dataset.stock} ${opt.dataset.unit}`
                : "";
            rowVal.textContent = money(
                parseFloat(opt?.dataset.price || 0) *
                    parseFloat(qty.value || 0),
            );
            recalcGrandTotal();
        });
        qty.addEventListener("input", () => {
            const opt = select.options[select.selectedIndex];
            rowVal.textContent = money(
                parseFloat(opt?.dataset.price || 0) *
                    parseFloat(qty.value || 0),
            );
            recalcGrandTotal();
        });

        row.appendChild(select);
        row.appendChild(qty);
        row.appendChild(stockInfo);
        row.appendChild(rowVal);
        row.appendChild(rmBtn);
        container.appendChild(row);
    }

    async function showInKind() {
        await loadItems();
        cashSection.style.display = "none";
        inKindSection.style.display = "";
        if (container.querySelectorAll(".ik-row").length === 0) addRow();
        // Disable required cash-only fields so browser validation skips them
        [
            "down_payment",
            "installment_months",
            "installment_amount",
            "payment_start_date",
        ].forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = true;
                el.removeAttribute("required");
            }
        });
    }

    function showCash() {
        cashSection.style.display = "";
        inKindSection.style.display = "none";
        // Re-enable and restore required on cash fields
        const requiredIds = [
            "down_payment",
            "installment_months",
            "installment_amount",
        ];
        [
            "down_payment",
            "installment_months",
            "installment_amount",
            "payment_start_date",
        ].forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = false;
                if (requiredIds.includes(id)) el.setAttribute("required", "");
            }
        });
    }

    typeCash.addEventListener("change", () => showCash());
    typeInKind.addEventListener("change", () => showInKind());
    addBtn?.addEventListener("click", addRow);

    // Restore state on page reload (validation error)
    if (typeInKind.checked) showInKind();
})();
