(function () {
    const tbody = document.getElementById("invTbody");
    const search = document.getElementById("invSearch");
    const statusFilter = document.getElementById("invStatus");
    const detailsTitle = document.getElementById("invDetailsTitle");
    const detailsBody = document.getElementById("invDetailsBody");
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    // ── Modals ───────────────────────────────────────────────────────────
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

    // ── Amount-paid elements (cash section) ──────────────────────────────
    const amountPaidInput = document.getElementById("amountPaidInput");
    const amountPaidHint = document.getElementById("amountPaidHint");
    const amountPaidDiff = document.getElementById("amountPaidDiff");

    // ── In-kind elements ─────────────────────────────────────────────────
    const typeCashBtn = document.getElementById("typeCashBtn");
    const typeInKindBtn = document.getElementById("typeInKindBtn");
    const cashSection = document.getElementById("cashSection");
    const inKindSection = document.getElementById("inKindSection");
    const inkindRows = document.getElementById("inkindRows");
    const addRowBtn = document.getElementById("addInKindRowBtn");
    const inkindItemsTotal = document.getElementById("inkindItemsTotal");
    const inkindPaymentNotes = document.getElementById("inkindPaymentNotes");

    // ── Confirmation step elements ───────────────────────────────────────
    const inkindConfirmStep = document.getElementById("inkindConfirmStep");
    const inkindConfirmBody = document.getElementById("inkindConfirmBody");
    const inkindConfirmTotal = document.getElementById("inkindConfirmTotal");

    if (!tbody) return;

    let currentRow = null;
    let inventoryItems = [];
    let paymentMode = "cash";
    let confirmStep = false;

    // ── Helpers ──────────────────────────────────────────────────────────
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

    // ── Search & filter — server-side via URL params ─────────────────────
    // Typing debounces 450ms then navigates; status change navigates immediately.
    // withQueryString() on the paginator preserves these params in page links.
    let searchTimer = null;

    function navigateWithParams() {
        const url = new URL(window.location.href);
        const q = (search?.value || "").trim();
        const st = statusFilter?.value || "all";
        if (q) url.searchParams.set("search", q);
        else url.searchParams.delete("search");
        if (st !== "all") url.searchParams.set("status", st);
        else url.searchParams.delete("status");
        url.searchParams.delete("page"); // reset to page 1 on new search
        window.location.href = url.toString();
    }

    search?.addEventListener("input", () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(navigateWithParams, 450);
    });

    statusFilter?.addEventListener("change", navigateWithParams);

    function applyFilters() {} // kept as no-op so existing call below doesn't break

    // ── Details panel ────────────────────────────────────────────────────
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

    // ── Inventory items loader ───────────────────────────────────────────
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

    // ── Build one item row in the in-kind form ───────────────────────────
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

    // ── Amount-paid: live diff feedback ─────────────────────────────────
    // Runs every time the user types in the "Amount received" field.
    // Shows whether the payment is exact, over, or under, and explains
    // what will happen to upcoming invoices.
    function updateAmountPaidDiff() {
        if (!amountPaidInput || !amountPaidDiff || !currentRow) return;

        const totalDue =
            Number(currentRow.dataset.amount || 0) +
            Number(currentRow.dataset.lateFee || 0);
        const paid = parseFloat(amountPaidInput.value);

        if (isNaN(paid) || amountPaidInput.value === "") {
            amountPaidDiff.textContent = "";
            return;
        }

        const diff = parseFloat((paid - totalDue).toFixed(2));

        if (Math.abs(diff) < 0.01) {
            amountPaidDiff.style.color = "#059669";
            amountPaidDiff.textContent =
                "✓ Exact payment — no adjustment needed.";
        } else if (diff > 0) {
            amountPaidDiff.style.color = "#2563eb";
            amountPaidDiff.textContent = `Overpayment of ${money(diff)} — upcoming invoices will be auto-paid from this credit.`;
        } else {
            amountPaidDiff.style.color = "#d97706";
            amountPaidDiff.textContent = `Underpayment of ${money(Math.abs(diff))} — will be added to the next invoice.`;
        }
    }

    amountPaidInput?.addEventListener("input", updateAmountPaidDiff);

    // ── Payment mode switch ──────────────────────────────────────────────
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

    inkindBack?.addEventListener("click", () => {
        confirmStep = false;
        inKindSection.style.display = "";
        inkindConfirmStep.style.display = "none";
        inkindBack.style.display = "none";
        if (paidConfirm) paidConfirm.textContent = "Review & Confirm →";
    });

    // ── API patch ────────────────────────────────────────────────────────
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

    // ── Row state updates ────────────────────────────────────────────────
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

    function setRowAmount(tr, amount) {
        tr.dataset.amount = amount;
        // Amount column is index 5 (0-based: #, client, unit, issue, due, amount, status, actions)
        const amountTd = tr.querySelectorAll("td")[5];
        if (amountTd) amountTd.textContent = money(amount);
    }

    function setRowDates(tr, issue, due) {
        tr.dataset.issue = issue;
        tr.dataset.due = due;
        const tds = tr.querySelectorAll("td");
        if (tds[3]) tds[3].textContent = issue || "-";
        if (tds[4]) tds[4].textContent = due || "-";
    }

    // ── Table click ──────────────────────────────────────────────────────
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

            // Pre-fill "Amount received" with the exact amount due
            // so the user can just confirm without typing if nothing changed.
            const totalDue =
                Number(tr.dataset.amount || 0) +
                Number(tr.dataset.lateFee || 0);
            if (amountPaidInput) {
                amountPaidInput.value = totalDue.toFixed(2);
            }
            if (amountPaidHint) {
                amountPaidHint.textContent = `(due: ${money(totalDue)})`;
            }
            if (amountPaidDiff) amountPaidDiff.textContent = "";
            updateAmountPaidDiff();

            openModal(paidModal);
        }
    });

    // ── Edit save ────────────────────────────────────────────────────────
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

    // ── Confirm button (handles review step + final submit) ──────────────
    paidConfirm?.addEventListener("click", async () => {
        if (!currentRow) return;

        // ── IN-KIND: STEP 1 → show confirmation table ────────────────────
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

            inKindSection.style.display = "none";
            inkindConfirmStep.style.display = "";
            inkindBack.style.display = "";
            paidConfirm.textContent = "✓ Yes, confirm & add to stock";
            confirmStep = true;
            return;
        }

        // ── IN-KIND: STEP 2 → actually submit ───────────────────────────
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

        // ── CASH ─────────────────────────────────────────────────────────
        try {
            paidConfirm.disabled = true;

            const resp = await apiPatch(
                `/invoices/${currentRow.dataset.id}/mark-paid`,
                {
                    payment_type: "cash",
                    paid_at: paidDate?.value || null,
                    amount_paid:
                        amountPaidInput?.value !== ""
                            ? parseFloat(amountPaidInput.value)
                            : null,
                },
            );

            // Mark the current invoice as paid in the UI
            setRowStatus(currentRow, "paid");
            if (resp?.receipt_path)
                currentRow.dataset.receiptPdf = resp.receipt_path;

            // Update all invoices that were adjusted by the server:
            // - status "paid"    → mark row as paid (removes action buttons)
            // - status "pending" → update the displayed amount only
            if (resp?.adjusted_invoices?.length) {
                resp.adjusted_invoices.forEach((adj) => {
                    const adjTr = tbody.querySelector(
                        `tr[data-id="${adj.id}"]`,
                    );
                    if (!adjTr) return;

                    if (adj.status === "paid") {
                        setRowStatus(adjTr, "paid");
                        setRowAmount(adjTr, adj.amount);
                    } else {
                        setRowAmount(adjTr, adj.amount);
                    }
                });
            }

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
