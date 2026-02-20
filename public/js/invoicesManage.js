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

    if (!tbody) return;

    let currentRow = null; // row being edited/paid

    function money(v) {
        const n = Number(v || 0);
        return "$" + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
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

    // close on backdrop click
    [editModal, paidModal].forEach((m) => {
        if (!m) return;
        m.addEventListener("click", (e) => {
            if (e.target.classList.contains("confirm-modal__backdrop"))
                closeModal(m);
        });
    });

    editCancel?.addEventListener("click", () => closeModal(editModal));
    paidCancel?.addEventListener("click", () => closeModal(paidModal));

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

        const okSearch = !q || hay.includes(q);
        const okStatus = st === "all" || tr.dataset.status === st;

        return okSearch && okStatus;
    }

    function applyFilters() {
        [...tbody.querySelectorAll("tr")].forEach((tr) => {
            tr.style.display = rowMatches(tr) ? "" : "none";
        });
    }

    search?.addEventListener("input", applyFilters);
    statusFilter?.addEventListener("change", applyFilters);

    function renderDetails(tr) {
        const d = tr.dataset;
        const baseAmount = Number(d.amount || 0);
        const lateFee = Number(d.lateFee || 0);
        const totalDue = baseAmount + lateFee;
        const isLate = (d.status || "").toLowerCase() === "overdue";

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
        <div class="invoices__kv"><span>Issue date</span><strong>${d.issue || "-"}</strong></div>
        <div class="invoices__kv"><span>Due date</span><strong>${d.due || "-"}</strong></div>
        <div class="invoices__kv"><span>Base amount</span><strong>${money(baseAmount)}</strong></div>
${
    isLate && lateFee > 0
        ? `
      <div class="invoices__kv"><span>Late fee</span><strong>${money(lateFee)}</strong></div>
      <div class="invoices__kv"><span>Total due</span><strong>${money(totalDue)}</strong></div>
    `
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
          <strong>
            ${contractPdfUrl ? `<a href="${contractPdfUrl}" target="_blank" rel="noopener">Download</a>` : "—"}
          </strong>
        </div>
        <div class="invoices__kv">
          <span>Invoice PDF</span>
          <strong>
            ${invoicePdfUrl ? `<a href="${invoicePdfUrl}" target="_blank" rel="noopener">Download</a>` : "—"}
          </strong>
        </div>
      </div>
    `;
    }

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
                msg = data.message || msg;
            } catch (_) {}
            throw new Error(msg);
        }
        return res.json().catch(() => ({}));
    }

    function setRowStatus(tr, status) {
        tr.dataset.status = status;

        const pill = tr.querySelector(".invoices__status");
        if (pill) {
            pill.className = `invoices__status invoices__status--${status}`;
            pill.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }

        // remove the "mark paid" button if now paid
        if (status === "paid") {
            const paidBtn = tr.querySelector(".invoices__icon-btn--paid");
            if (paidBtn) paidBtn.remove();
        }
    }

    function setRowDates(tr, issue, due) {
        tr.dataset.issue = issue;
        tr.dataset.due = due;

        const tds = tr.querySelectorAll("td");
        // columns: invoice#, client, unit, issue, due, amount, status, actions
        if (tds[3]) tds[3].textContent = issue || "-";
        if (tds[4]) tds[4].textContent = due || "-";
    }

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
            paidDate.value = ""; // optional
            openModal(paidModal);
            return;
        }
    });

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

    paidConfirm?.addEventListener("click", async () => {
        if (!currentRow) return;

        const invoiceId = currentRow.dataset.id;
        const paid_at = paidDate.value || null;

        try {
            paidConfirm.disabled = true;

            await apiPatch(`/invoices/${invoiceId}/mark-paid`, { paid_at });

            setRowStatus(currentRow, "paid");
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
