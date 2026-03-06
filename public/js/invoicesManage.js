(function () {
    const tbody = document.getElementById("invTbody");
    const search = document.getElementById("invSearch");
    const statusFilter = document.getElementById("invStatus");
    const detailsTitle = document.getElementById("invDetailsTitle");
    const detailsBody = document.getElementById("invDetailsBody");
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // Modals
    const editModal = document.getElementById("editDatesModal");
    const editIssue = document.getElementById("editIssueDate");
    const editDue = document.getElementById("editDueDate");
    const editCancel = document.getElementById("editCancelBtn");
    const editSave = document.getElementById("editSaveBtn");

    const paidModal = document.getElementById("markPaidModal");
    const paidDate = document.getElementById("paidDate");
    const paidCancel = document.getElementById("paidCancelBtn");
    const paidConfirm = document.getElementById("paidConfirmBtn");
    const inkindBack = document.getElementById("inkindBackBtn");

    // In-kind elements
    const typeCashBtn = document.getElementById("typeCashBtn");
    const typeInKindBtn = document.getElementById("typeInKindBtn");
    const cashSection = document.getElementById("cashSection");
    const inKindSection = document.getElementById("inKindSection");
    const inkindRows = document.getElementById("inkindRows");
    const addRowBtn = document.getElementById("addInKindRowBtn");
    const inkindItemsTotal = document.getElementById("inkindItemsTotal");
    const inkindPaymentNotes = document.getElementById("inkindPaymentNotes");

    // Confirmation step elements
    const inkindConfirmStep = document.getElementById("inkindConfirmStep");
    const inkindConfirmBody = document.getElementById("inkindConfirmBody");
    const inkindConfirmTotal = document.getElementById("inkindConfirmTotal");

    if (!tbody) return;

    let currentRow = null;
    let inventoryItems = [];
    let paymentMode = "cash";
    let confirmStep = false; // are we showing the confirmation step?

    // ── Helpers ─────────────────────────────────────────────────────────
    function money(v) {
        return (
            "$" +
            Number(v || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        );
    }

    function openModal(m) {
        m?.classList.add("is-open");
        m?.setAttribute("aria-hidden", "false");
    }
    function closeModal(m) {
        m?.classList.remove("is-open");
        m?.setAttribute("aria-hidden", "true");
    }

    [editModal, paidModal].forEach((m) => {
        m?.addEventListener("click", (e) => {
            if (e.target.classList.contains("confirm-modal__backdrop"))
                closeModal(m);
        });
    });

    editCancel?.addEventListener("click", () => closeModal(editModal));
    paidCancel?.addEventListener("click", () => closeModal(paidModal));

    // ── Filtering ─────────────────────────────────────────────────────
    function rowMatches(tr) {
        const q = (search?.value || "").trim().toLowerCase();
        const st = (statusFilter?.value || "all").toLowerCase();
        const hay = [
            tr.dataset.invoiceNumber,
            tr.dataset.client,
            tr.dataset.phone,
            tr.dataset.unit,
            tr.dataset.project,
        ]
            .join(" ")
            .toLowerCase();
        return (
            (!q || hay.includes(q)) &&
            (st === "all" || tr.dataset.status === st)
        );
    }
    function applyFilters() {
        [...tbody.querySelectorAll("tr")].forEach(
            (tr) => (tr.style.display = rowMatches(tr) ? "" : "none"),
        );
    }
    search?.addEventListener("input", applyFilters);
    statusFilter?.addEventListener("change", applyFilters);

    // ── Details panel ─────────────────────────────────────────────────
    function renderDetails(tr) {
        const d = tr.dataset;
        const base = Number(d.amount || 0),
            late = Number(d.lateFee || 0),
            total = base + late;
        const isLate = (d.status || "").toLowerCase() === "overdue";
        const isPaid = (d.status || "").toLowerCase() === "paid";
        const receiptUrl = d.receiptPdf ? "/storage/" + d.receiptPdf : null;
        const contractUrl = d.contractPdf ? "/storage/" + d.contractPdf : null;
        const invoiceUrl = d.invoicePdf ? "/storage/" + d.invoicePdf : null;

        detailsTitle.textContent = d.invoiceNumber || "Invoice";
        detailsBody.innerHTML = `
          <div class="invoices__block">
            <div class="invoices__block-title">Invoice</div>
            <div class="invoices__kv"><span>Invoice #</span><strong>${d.invoiceNumber || "-"}</strong></div>
            <div class="invoices__kv"><span>Status</span><strong>${(d.status || "-").toUpperCase()}</strong></div>
            <div class="invoices__kv"><span>Issue date</span><strong>${d.issue || "-"}</strong></div>
            <div class="invoices__kv"><span>Due date</span><strong>${d.due || "-"}</strong></div>
            <div class="invoices__kv"><span>Base amount</span><strong>${money(base)}</strong></div>
            ${
                isLate && late > 0
                    ? `<div class="invoices__kv"><span>Late fee</span><strong>${money(late)}</strong></div>
                 <div class="invoices__kv"><span>Total due</span><strong>${money(total)}</strong></div>`
                    : `<div class="invoices__kv"><span>Total due</span><strong>${money(base)}</strong></div>`
            }
          </div>
          <div class="invoices__block">
            <div class="invoices__block-title">Client</div>
            <div class="invoices__kv"><span>Name</span><strong>${d.client || "-"}</strong></div>
            <div class="invoices__kv"><span>Phone</span><strong>${d.phone || "-"}</strong></div>
            <div class="invoices__kv"><span>Email</span><strong>${d.email || "-"}</strong></div>
          </div>
          <div class="invoices__block">
            <div class="invoices__block-title">Contract</div>
            <div class="invoices__kv"><span>Contract ID</span><strong>#${d.contractId || "-"}</strong></div>
            <div class="invoices__kv"><span>Project</span><strong>${d.project || "-"}</strong></div>
            <div class="invoices__kv"><span>Unit</span><strong>${d.unit || "-"}</strong></div>
          </div>
          <div class="invoices__block">
            <div class="invoices__block-title">Downloads</div>
            <div class="invoices__kv"><span>Contract PDF</span><strong>${contractUrl ? `<a href="${contractUrl}" target="_blank">Download</a>` : "—"}</strong></div>
            <div class="invoices__kv"><span>Invoice PDF</span><strong>${invoiceUrl ? `<a href="${invoiceUrl}" target="_blank">Download</a>` : "—"}</strong></div>
            <div class="invoices__kv"><span>Receipt PDF</span><strong>${isPaid ? (receiptUrl ? `<a href="${receiptUrl}" target="_blank">Download</a>` : "—") : "Available after payment"}</strong></div>
          </div>`;
    }

    // ── Inventory items loader ────────────────────────────────────────
    async function loadInventoryItems() {
        if (inventoryItems.length > 0) return;
        try {
            const res = await fetch("/invoices/inventory-items", {
                headers: { Accept: "application/json", "X-CSRF-TOKEN": token },
            });
            inventoryItems = await res.json();
        } catch {
            inventoryItems = [];
        }
    }

    // ── Build one item row in the in-kind form ────────────────────────
    function buildItemSelect() {
        const sel = document.createElement("select");
        sel.className = "inv-modal__input inkind-item-select";
        sel.innerHTML = `<option value="">— select item —</option>`;
        inventoryItems.forEach((it) => {
            const opt = document.createElement("option");
            opt.value = it.id;
            opt.dataset.price = it.price;
            opt.dataset.stock = it.quantity;
            opt.dataset.unit = it.unit || "";
            opt.dataset.name = it.name;
            opt.textContent = `${it.name} (${money(it.price)}/${it.unit || "unit"}, stock: ${it.quantity})`;
            sel.appendChild(opt);
        });
        return sel;
    }

    function addInKindRow() {
        const row = document.createElement("div");
        row.className = "inkind-row";

        const select = buildItemSelect();
        const qtyInput = document.createElement("input");
        qtyInput.type = "number";
        qtyInput.min = "0.001";
        qtyInput.step = "0.001";
        qtyInput.placeholder = "Qty";
        qtyInput.className = "inv-modal__input inkind-qty";

        const stockInfo = document.createElement("span");
        stockInfo.className = "inkind-stock-info";

        const rowValue = document.createElement("span");
        rowValue.className = "inkind-row-value";
        rowValue.textContent = "$0.00";

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "inkind-remove-btn";
        removeBtn.textContent = "✕";
        removeBtn.addEventListener("click", () => {
            row.remove();
            recalcTotal();
        });

        function recalcRow() {
            const opt = select.options[select.selectedIndex];
            const price = parseFloat(opt?.dataset.price || 0);
            const qty = parseFloat(qtyInput.value || 0);
            rowValue.textContent = money(price * qty);
            recalcTotal();
        }

        select.addEventListener("change", () => {
            const opt = select.options[select.selectedIndex];
            stockInfo.textContent = opt?.value
                ? `Current stock: ${opt.dataset.stock} ${opt.dataset.unit}`
                : "";
            recalcRow();
        });
        qtyInput.addEventListener("input", recalcRow);

        row.appendChild(select);
        row.appendChild(qtyInput);
        row.appendChild(stockInfo);
        row.appendChild(rowValue);
        row.appendChild(removeBtn);
        inkindRows.appendChild(row);
    }

    function recalcTotal() {
        let total = 0;
        inkindRows.querySelectorAll(".inkind-row").forEach((row) => {
            const opt = row.querySelector(".inkind-item-select")?.options[
                row.querySelector(".inkind-item-select")?.selectedIndex
            ];
            const price = parseFloat(opt?.dataset.price || 0);
            const qty = parseFloat(
                row.querySelector(".inkind-qty")?.value || 0,
            );
            total += price * qty;
        });
        if (inkindItemsTotal) inkindItemsTotal.textContent = money(total);
    }

    function switchPaymentMode(mode) {
        paymentMode = mode;
        confirmStep = false;
        if (mode === "cash") {
            cashSection?.style && (cashSection.style.display = "");
            inKindSection?.style && (inKindSection.style.display = "none");
            inkindConfirmStep?.style &&
                (inkindConfirmStep.style.display = "none");
            typeCashBtn?.classList.add("inv-modal__type-btn--active");
            typeInKindBtn?.classList.remove("inv-modal__type-btn--active");
            if (inkindBack) inkindBack.style.display = "none";
            if (paidConfirm) paidConfirm.textContent = "Confirm";
        } else {
            cashSection?.style && (cashSection.style.display = "none");
            inKindSection?.style && (inKindSection.style.display = "");
            inkindConfirmStep?.style &&
                (inkindConfirmStep.style.display = "none");
            typeCashBtn?.classList.remove("inv-modal__type-btn--active");
            typeInKindBtn?.classList.add("inv-modal__type-btn--active");
            if (inkindBack) inkindBack.style.display = "none";
            if (paidConfirm) paidConfirm.textContent = "Review & Confirm →";
        }
    }

    typeCashBtn?.addEventListener("click", () => switchPaymentMode("cash"));
    typeInKindBtn?.addEventListener("click", async () => {
        await loadInventoryItems();
        switchPaymentMode("in_kind");
        if (inkindRows.querySelectorAll(".inkind-row").length === 0)
            addInKindRow();
    });
    addRowBtn?.addEventListener("click", addInKindRow);

    // Back button: go from confirmation step back to item entry
    inkindBack?.addEventListener("click", () => {
        confirmStep = false;
        inKindSection.style.display = "";
        inkindConfirmStep.style.display = "none";
        inkindBack.style.display = "none";
        if (paidConfirm) paidConfirm.textContent = "Review & Confirm →";
    });

    // ── API patch ──────────────────────────────────────────────────────
    async function apiPatch(url, payload) {
        const res = await fetch(url, {
            method: "PATCH",
            headers: {
                "X-CSRF-TOKEN": token,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload || {}),
        });
        if (!res.ok) {
            let msg = "Request failed.";
            try {
                const d = await res.json();
                msg = d.message || msg;
            } catch (_) {}
            throw new Error(msg);
        }
        return res.json().catch(() => ({}));
    }

    // ── Row state updates ──────────────────────────────────────────────
    function setRowStatus(tr, status) {
        tr.dataset.status = status;
        const pill = tr.querySelector(".invoices__status");
        if (pill) {
            pill.className = `invoices__status invoices__status--${status}`;
            pill.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }
        if (status === "paid") {
            tr.querySelector(".invoices__icon-btn--paid")?.remove();
            tr.querySelector(".invoices__icon-btn--edit")?.remove();
        }
    }

    function setRowDates(tr, issue, due) {
        tr.dataset.issue = issue;
        tr.dataset.due = due;
        const tds = tr.querySelectorAll("td");
        if (tds[3]) tds[3].textContent = issue || "-";
        if (tds[4]) tds[4].textContent = due || "-";
    }

    // ── Table click ────────────────────────────────────────────────────
    tbody.addEventListener("click", (e) => {
        const tr = e.target.closest("tr");
        const viewBtn = e.target.closest(".invoices__btn--view");
        const editBtn = e.target.closest(".invoices__icon-btn--edit");
        const paidBtn = e.target.closest(".invoices__icon-btn--paid");
        if (!tr) return;

        if (viewBtn) {
            renderDetails(tr);
            return;
        }

        if (editBtn) {
            currentRow = tr;
            editIssue.value = tr.dataset.issue || "";
            editDue.value = tr.dataset.due || "";
            openModal(editModal);
            return;
        }

        if (paidBtn) {
            currentRow = tr;
            confirmStep = false;
            switchPaymentMode("cash");
            if (paidDate) paidDate.value = "";
            if (inkindRows) inkindRows.innerHTML = "";
            if (inkindPaymentNotes) inkindPaymentNotes.value = "";
            openModal(paidModal);
        }
    });

    // ── Edit save ──────────────────────────────────────────────────────
    editSave?.addEventListener("click", async () => {
        if (!currentRow) return;
        try {
            editSave.disabled = true;
            await apiPatch(`/invoices/${currentRow.dataset.id}/dates`, {
                issue_date: editIssue.value || null,
                due_date: editDue.value || null,
            });
            setRowDates(currentRow, editIssue.value, editDue.value);
            renderDetails(currentRow);
            closeModal(editModal);
        } catch (err) {
            alert(err.message || "Failed.");
        } finally {
            editSave.disabled = false;
        }
    });

    // ── Confirm button (handles both review step and final submit) ────
    paidConfirm?.addEventListener("click", async () => {
        if (!currentRow) return;

        // ── IN-KIND: STEP 1 → show confirmation table ─────────────────
        if (paymentMode === "in_kind" && !confirmStep) {
            const rows = inkindRows.querySelectorAll(".inkind-row");
            const items = [];

            for (const row of rows) {
                const sel = row.querySelector(".inkind-item-select");
                const qty = row.querySelector(".inkind-qty");
                const itemId = sel?.value;
                const qtyVal = parseFloat(qty?.value || 0);
                if (!itemId) {
                    alert(
                        "Please select an item for every row, or remove empty rows.",
                    );
                    return;
                }
                if (!qtyVal || qtyVal <= 0) {
                    alert("Please enter a valid quantity for every item.");
                    return;
                }
                const opt = sel.options[sel.selectedIndex];
                items.push({
                    id: parseInt(itemId),
                    name: opt.dataset.name || "Item",
                    price: parseFloat(opt.dataset.price || 0),
                    qty: qtyVal,
                    unit: opt.dataset.unit || "",
                });
            }

            if (items.length === 0) {
                alert("Please add at least one item.");
                return;
            }

            // Build confirmation table
            let totalVal = 0;
            inkindConfirmBody.innerHTML = "";
            items.forEach((it) => {
                const val = it.price * it.qty;
                totalVal += val;
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${it.name}</td><td>${it.qty} ${it.unit}</td><td>${money(it.price)}</td><td>${money(val)}</td>`;
                inkindConfirmBody.appendChild(tr);
            });
            inkindConfirmTotal.textContent = money(totalVal);

            // Switch to confirmation view
            inKindSection.style.display = "none";
            inkindConfirmStep.style.display = "";
            inkindBack.style.display = "";
            paidConfirm.textContent = "✓ Yes, confirm & add to stock";
            confirmStep = true;
            return;
        }

        // ── IN-KIND: STEP 2 → actually submit ─────────────────────────
        if (paymentMode === "in_kind" && confirmStep) {
            const rows = inkindRows.querySelectorAll(".inkind-row");
            const items = [];
            for (const row of rows) {
                const sel = row.querySelector(".inkind-item-select");
                const qty = row.querySelector(".inkind-qty");
                items.push({
                    inventory_item_id: parseInt(sel.value),
                    quantity: parseFloat(qty.value),
                    notes: null,
                });
            }

            try {
                paidConfirm.disabled = true;
                await apiPatch(`/invoices/${currentRow.dataset.id}/mark-paid`, {
                    payment_type: "in_kind",
                    items,
                    payment_notes: inkindPaymentNotes?.value || null,
                });
                setRowStatus(currentRow, "paid");
                renderDetails(currentRow);
                closeModal(paidModal);
            } catch (err) {
                alert(err.message || "Failed to mark as paid.");
            } finally {
                paidConfirm.disabled = false;
            }
            return;
        }

        // ── CASH ───────────────────────────────────────────────────────
        try {
            paidConfirm.disabled = true;
            const resp = await apiPatch(
                `/invoices/${currentRow.dataset.id}/mark-paid`,
                {
                    payment_type: "cash",
                    paid_at: paidDate?.value || null,
                },
            );
            setRowStatus(currentRow, "paid");
            if (resp?.receipt_path)
                currentRow.dataset.receiptPdf = resp.receipt_path;
            renderDetails(currentRow);
            closeModal(paidModal);
        } catch (err) {
            alert(err.message || "Failed to mark as paid.");
        } finally {
            paidConfirm.disabled = false;
        }
    });

    applyFilters();
})();
