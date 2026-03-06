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

    // In-kind elements
    const typeCashBtn = document.getElementById("typeCashBtn");
    const typeInKindBtn = document.getElementById("typeInKindBtn");
    const cashSection = document.getElementById("cashSection");
    const inKindSection = document.getElementById("inKindSection");
    const inkindRows = document.getElementById("inkindRows");
    const addRowBtn = document.getElementById("addInKindRowBtn");
    const inkindInvTotal = document.getElementById("inkindInvoiceTotal");
    const inkindItemsTotal = document.getElementById("inkindItemsTotal");
    const inkindDiff = document.getElementById("inkindDiff");
    const inkindDiffWrap = document.getElementById("inkindDiffWrap");

    if (!tbody) return;

    let currentRow = null;
    let inventoryItems = []; // loaded once on first in-kind open
    let paymentMode = "cash"; // "cash" or "in_kind"
    let currentInvoiceAmount = 0;

    // ── Helpers ────────────────────────────────────────────────────────────

    function money(v) {
        const n = Number(v || 0);
        return (
            "$" +
            n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        );
    }

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
    }

    [editModal, paidModal].forEach((m) => {
        if (!m) return;
        m.addEventListener("click", (e) => {
            if (e.target.classList.contains("confirm-modal__backdrop"))
                closeModal(m);
        });
    });

    editCancel?.addEventListener("click", () => closeModal(editModal));
    paidCancel?.addEventListener("click", () => closeModal(paidModal));

    // ── Filtering ─────────────────────────────────────────────────────────

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
        [...tbody.querySelectorAll("tr")].forEach((tr) => {
            tr.style.display = rowMatches(tr) ? "" : "none";
        });
    }

    search?.addEventListener("input", applyFilters);
    statusFilter?.addEventListener("change", applyFilters);

    // ── Details panel ─────────────────────────────────────────────────────

    function renderDetails(tr) {
        const d = tr.dataset;
        const baseAmount = Number(d.amount || 0);
        const lateFee = Number(d.lateFee || 0);
        const totalDue = baseAmount + lateFee;
        const isLate = (d.status || "").toLowerCase() === "overdue";
        const receiptPdfUrl = d.receiptPdf ? "/storage/" + d.receiptPdf : null;
        const isPaid = (d.status || "").toLowerCase() === "paid";
        const isInKind = (d.paymentType || "cash") === "in_kind";

        detailsTitle.textContent = d.invoiceNumber
            ? d.invoiceNumber
            : "Invoice";

        const contractPdfUrl = d.contractPdf
            ? "/storage/" + d.contractPdf
            : null;
        const invoicePdfUrl = d.invoicePdf ? "/storage/" + d.invoicePdf : null;

        detailsBody.innerHTML = `
      <div class="invoices__block">
        <div class="invoices__block-title">Invoice</div>
        <div class="invoices__kv"><span>Invoice #</span><strong>${d.invoiceNumber || "-"}</strong></div>
        <div class="invoices__kv"><span>Status</span><strong>${(d.status || "-").toUpperCase()}</strong></div>
        ${isPaid ? `<div class="invoices__kv"><span>Payment type</span><strong>${isInKind ? "📦 In-Kind (Inventory)" : "💵 Cash"}</strong></div>` : ""}
        <div class="invoices__kv"><span>Issue date</span><strong>${d.issue || "-"}</strong></div>
        <div class="invoices__kv"><span>Due date</span><strong>${d.due || "-"}</strong></div>
        <div class="invoices__kv"><span>Base amount</span><strong>${money(baseAmount)}</strong></div>
${
    isLate && lateFee > 0
        ? `<div class="invoices__kv"><span>Late fee</span><strong>${money(lateFee)}</strong></div>
       <div class="invoices__kv"><span>Total due</span><strong>${money(totalDue)}</strong></div>`
        : `<div class="invoices__kv"><span>Total due</span><strong>${money(baseAmount)}</strong></div>`
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
        <div class="invoices__kv"><span>Contract date</span><strong>${d.contractDate || "-"}</strong></div>
        <div class="invoices__kv"><span>Payment start</span><strong>${d.paymentStart || "-"}</strong></div>
      </div>

      <div class="invoices__block">
        <div class="invoices__block-title">Downloads</div>
        <div class="invoices__kv">
          <span>Contract PDF</span>
          <strong>${contractPdfUrl ? `<a href="${contractPdfUrl}" target="_blank" rel="noopener">Download</a>` : "—"}</strong>
        </div>
        <div class="invoices__kv">
          <span>Invoice PDF</span>
          <strong>${invoicePdfUrl ? `<a href="${invoicePdfUrl}" target="_blank" rel="noopener">Download</a>` : "—"}</strong>
        </div>
        <div class="invoices__kv">
          <span>Receipt PDF</span>
          <strong>
            ${
                isPaid
                    ? receiptPdfUrl
                        ? `<a href="${receiptPdfUrl}" target="_blank" rel="noopener">Download</a>`
                        : "—"
                    : "Available after payment"
            }
          </strong>
        </div>
      </div>
    `;
    }

    // ── In-Kind logic ─────────────────────────────────────────────────────

    async function loadInventoryItems() {
        if (inventoryItems.length > 0) return; // cached
        try {
            const res = await fetch("/invoices/inventory-items", {
                headers: { Accept: "application/json", "X-CSRF-TOKEN": token },
            });
            inventoryItems = await res.json();
        } catch {
            inventoryItems = [];
        }
    }

    function buildItemSelect(selectedId = null) {
        const sel = document.createElement("select");
        sel.className = "inv-modal__input inkind-item-select";
        sel.innerHTML = `<option value="">— select item —</option>`;
        inventoryItems.forEach((it) => {
            const opt = document.createElement("option");
            opt.value = it.id;
            opt.dataset.price = it.price;
            opt.dataset.stock = it.quantity;
            opt.dataset.unit = it.unit || "";
            opt.textContent = `${it.name} (${money(it.price)}/${it.unit || "unit"}, stock: ${it.quantity})`;
            if (selectedId && it.id == selectedId) opt.selected = true;
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

        const rowValue = document.createElement("span");
        rowValue.className = "inkind-row-value";
        rowValue.textContent = "$0.00";

        const stockInfo = document.createElement("span");
        stockInfo.className = "inkind-stock-info";

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
            const val = price * qty;
            rowValue.textContent = money(val);
            recalcTotal();
        }

        select.addEventListener("change", () => {
            const opt = select.options[select.selectedIndex];
            const stock = opt?.dataset.stock;
            const unit = opt?.dataset.unit || "";
            stockInfo.textContent = opt?.value
                ? `Available: ${stock} ${unit}`
                : "";
            qtyInput.max = stock || "";
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

        inkindItemsTotal.textContent = money(total);
        const diff = currentInvoiceAmount - total;
        inkindDiff.textContent = money(Math.abs(diff));

        if (Math.abs(diff) <= 1) {
            inkindDiff.style.color = "#22c55e";
            inkindDiffWrap.querySelector("strong").previousSibling?.remove?.();
            inkindDiffWrap.firstChild.textContent =
                diff > 0 ? "Remaining: " : diff < 0 ? "Over by: " : "✓ Exact: ";
        } else {
            inkindDiff.style.color = diff > 0 ? "#ef4444" : "#f97316";
            inkindDiffWrap.firstChild.textContent =
                diff > 0 ? "Remaining: " : "Over by: ";
        }
    }

    function switchPaymentMode(mode) {
        paymentMode = mode;
        if (mode === "cash") {
            cashSection.style.display = "";
            inKindSection.style.display = "none";
            typeCashBtn.classList.add("inv-modal__type-btn--active");
            typeInKindBtn.classList.remove("inv-modal__type-btn--active");
        } else {
            cashSection.style.display = "none";
            inKindSection.style.display = "";
            typeCashBtn.classList.remove("inv-modal__type-btn--active");
            typeInKindBtn.classList.add("inv-modal__type-btn--active");
        }
    }

    typeCashBtn?.addEventListener("click", () => switchPaymentMode("cash"));
    typeInKindBtn?.addEventListener("click", async () => {
        await loadInventoryItems();
        switchPaymentMode("in_kind");
        // Add a first row automatically if none exist
        if (inkindRows.querySelectorAll(".inkind-row").length === 0)
            addInKindRow();
    });

    addRowBtn?.addEventListener("click", addInKindRow);

    // ── API helpers ────────────────────────────────────────────────────────

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
                const data = await res.json();
                msg =
                    data.message || data.errors
                        ? data.message || Object.values(data.errors)[0]?.[0]
                        : msg;
            } catch (_) {}
            throw new Error(msg);
        }
        return res.json().catch(() => ({}));
    }

    // ── Row state updates ─────────────────────────────────────────────────

    function setRowStatus(tr, status, paymentType) {
        tr.dataset.status = status;
        tr.dataset.paymentType = paymentType || "cash";

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

    // ── Table click handler ────────────────────────────────────────────────

    tbody.addEventListener("click", (e) => {
        const viewBtn = e.target.closest(".invoices__btn--view");
        const editBtn = e.target.closest(".invoices__icon-btn--edit");
        const paidBtn = e.target.closest(".invoices__icon-btn--paid");

        const tr = e.target.closest("tr");
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
            // Reset modal state
            switchPaymentMode("cash");
            paidDate.value = "";
            inkindRows.innerHTML = "";
            // Set invoice total display
            const amt = parseFloat(tr.dataset.amount || 0);
            const late = parseFloat(tr.dataset.lateFee || 0);
            currentInvoiceAmount = amt + late;
            if (inkindInvTotal)
                inkindInvTotal.textContent = money(currentInvoiceAmount);
            if (inkindItemsTotal) inkindItemsTotal.textContent = "$0.00";
            if (inkindDiff)
                inkindDiff.textContent = money(currentInvoiceAmount);
            openModal(paidModal);
            return;
        }
    });

    // ── Edit dates save ────────────────────────────────────────────────────

    editSave?.addEventListener("click", async () => {
        if (!currentRow) return;
        const invoiceId = currentRow.dataset.id;
        const issue_date = editIssue.value || null;
        const due_date = editDue.value || null;

        try {
            editSave.disabled = true;
            await apiPatch(`/invoices/${invoiceId}/dates`, {
                issue_date,
                due_date,
            });
            setRowDates(currentRow, issue_date, due_date);
            renderDetails(currentRow);
            closeModal(editModal);
        } catch (err) {
            alert(err.message || "Failed to update dates.");
        } finally {
            editSave.disabled = false;
        }
    });

    // ── Mark paid confirm ─────────────────────────────────────────────────

    paidConfirm?.addEventListener("click", async () => {
        if (!currentRow) return;
        const invoiceId = currentRow.dataset.id;

        try {
            paidConfirm.disabled = true;

            if (paymentMode === "in_kind") {
                // Collect items
                const itemRows = inkindRows.querySelectorAll(".inkind-row");
                const items = [];

                for (const row of itemRows) {
                    const sel = row.querySelector(".inkind-item-select");
                    const qty = row.querySelector(".inkind-qty");
                    const itemId = sel?.value;
                    const qtyVal = parseFloat(qty?.value || 0);

                    if (!itemId) {
                        alert(
                            "Please select an item for every row, or remove empty rows.",
                        );
                        paidConfirm.disabled = false;
                        return;
                    }
                    if (!qtyVal || qtyVal <= 0) {
                        alert("Please enter a valid quantity for every item.");
                        paidConfirm.disabled = false;
                        return;
                    }

                    items.push({
                        inventory_item_id: parseInt(itemId),
                        quantity_used: qtyVal,
                    });
                }

                if (items.length === 0) {
                    alert("Please add at least one inventory item.");
                    paidConfirm.disabled = false;
                    return;
                }

                await apiPatch(`/invoices/${invoiceId}/mark-paid`, {
                    payment_type: "in_kind",
                    items,
                });

                setRowStatus(currentRow, "paid", "in_kind");
            } else {
                const paid_at = paidDate.value || null;
                const resp = await apiPatch(
                    `/invoices/${invoiceId}/mark-paid`,
                    { paid_at, payment_type: "cash" },
                );
                setRowStatus(currentRow, "paid", "cash");
                if (resp?.receipt_path)
                    currentRow.dataset.receiptPdf = resp.receipt_path;
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
