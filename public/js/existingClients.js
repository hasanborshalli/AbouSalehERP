(function () {
    const search = document.getElementById("clientsSearch");
    const statusFilter = document.getElementById("clientsStatus");
    const tbody = document.getElementById("clientsTbody");

    const detailsCode = document.getElementById("detailsCode");
    const detailsBody = document.getElementById("detailsBody");

    if (!tbody) return;

    function money(v) {
        const n = Number(v || 0);
        return "$" + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
    }

    function rowMatches(tr) {
        const q = (search?.value || "").trim().toLowerCase();
        const st = (statusFilter?.value || "all").toLowerCase();

        const hay = [
            tr.dataset.code,
            tr.dataset.name,
            tr.dataset.phone,
            tr.dataset.apt,
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

    // View -> details panel
    tbody.addEventListener("click", function (e) {
        const btn = e.target.closest(".clients-index__btn--view");
        if (!btn) return;

        const tr = btn.closest("tr");
        if (!tr) return;

        const data = {
            code: tr.dataset.code,
            name: tr.dataset.name,
            phone: tr.dataset.phone,
            email: tr.dataset.email,
            unit: tr.dataset.apt,
            total_price: Number(tr.dataset.total || 0),
            discount: Number(tr.dataset.discount || 0),
            final_price: Number(tr.dataset.finalPrice || 0),
            remaining: Number(tr.dataset.remaining || 0),
            status: tr.dataset.status,
            project: tr.dataset.project,
            location: tr.dataset.location,
            contractdate: tr.dataset.contractdate,
            down: tr.dataset.down,
            months: tr.dataset.months,
            monthly: tr.dataset.monthly,
            paid: tr.dataset.paid,
            paidmonths: tr.dataset.paidmonths,
            lateMonths: Number(tr.dataset.lateMonths || 0),
            lateFeesApplied: Number(tr.dataset.lateFeesApplied || 0),
            lateFeesPaid: Number(tr.dataset.lateFeesPaid || 0),
            remainingmonths: tr.dataset.remainingmonths,
            nextdue: tr.dataset.nextdue,
            notes: tr.dataset.notes,
            contractPdf: tr.dataset.contractPdf,
        };

        detailsCode.textContent = data.code ? data.code : "Client";

        detailsBody.innerHTML = `
                <div class="clients-index__block">
                    <div class="clients-index__block-title">Personal</div>
                    <div class="clients-index__kv"><span>Name</span><strong>${data.name || "-"}</strong></div>
                    <div class="clients-index__kv"><span>Phone</span><strong>${data.phone || "-"}</strong></div>
                    <div class="clients-index__kv"><span>Email</span><strong>${data.email || "-"}</strong></div>
                    <div class="clients-index__kv"><span>ID</span><strong>${data.code || "-"}</strong></div>
                </div>

                <div class="clients-index__block">
                    <div class="clients-index__block-title">Apartment</div>
                    <div class="clients-index__kv"><span>Project</span><strong>${data.project || "-"}</strong></div>
                    <div class="clients-index__kv"><span>Unit</span><strong>${data.unit || "-"}</strong></div>
                    <div class="clients-index__kv"><span>Location</span><strong>${data.location || "-"}</strong></div>
                    <div class="clients-index__kv"><span>Contract date</span><strong>${data.contractdate || "-"}</strong></div>
                </div>

                <div class="clients-index__block">
                    <div class="clients-index__block-title">Payments</div>
                    <div class="clients-index__kv"><span>Total</span><strong>${money(data.total_price)}</strong></div>
                    <div class="clients-index__kv"><span>Discount</span><strong>${money(data.discount)}</strong></div>
                    <div class="clients-index__kv"><span>Down payment</span><strong>${money(data.down)}</strong></div>
                    <div class="clients-index__kv"><span>Months</span><strong>${data.months || 0}</strong></div>
                    <div class="clients-index__kv"><span>Monthly</span><strong>${money(data.monthly)}</strong></div>
                    <div class="clients-index__kv"><span>Paid months</span><strong>${data.paidmonths || 0}</strong></div>
                    <div class="clients-index__kv"><span>Late months</span><strong>${data.lateMonths || 0}</strong></div>
                    <div class="clients-index__kv"><span>Late fees applied</span><strong>${money(data.lateFeesApplied)}</strong></div>
                    <div class="clients-index__kv"><span>Late fees paid</span><strong>${money(data.lateFeesPaid)}</strong></div>
                    <div class="clients-index__kv"><span>Remaining months</span><strong>${data.remainingmonths || 0}</strong></div>
                    <div class="clients-index__kv"><span>Total Paid</span><strong>${money(data.paid)} + ${money(data.lateFeesPaid)} Late fees</strong></div>
                    <div class="clients-index__kv"><span>Remaining</span><strong>${money(data.remaining)}</strong></div>
                    <div class="clients-index__kv"><span>Next due</span><strong>${data.nextdue || "-"}</strong></div>
                </div>

                <div class="clients-index__block">
                    <div class="clients-index__block-title">Notes</div>
                    <div class="clients-index__note">${data.notes || "—"}</div>
                    <div style="margin-top:12px;">
                        ${
                            data.contractPdf
                                ? `<a class="clients-index__btn clients-index__btn--download"
                                href="${data.contractPdf}"
                                target="_blank"
                                rel="noopener"
                                download>
                                Download contract PDF
                            </a>`
                                : `<div class="muted" style="font-size:12px;">No contract PDF available yet.</div>`
                        }
                    </div>
                </div>

            `;
    });

    // ===== Modal delete (no browser confirm) =====
    const confirmModal = document.getElementById("confirmModal");
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    let pendingDelete = {
        userId: null,
        tr: null,
        btn: null,
    };

    function openConfirmModal({ userId, tr, btn }) {
        pendingDelete.userId = userId;
        pendingDelete.tr = tr;
        pendingDelete.btn = btn;

        confirmModal.classList.add("is-open");
    }

    window.closeConfirmModal = function () {
        confirmModal.classList.remove("is-open");

        pendingDelete.userId = null;
        pendingDelete.tr = null;
        pendingDelete.btn = null;

        if (confirmDeleteBtn) {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = "Delete";
        }
    };

    // open modal when clicking delete icon
    tbody.addEventListener("click", function (e) {
        const delBtn = e.target.closest(".clients-index__icon-btn--delete");
        if (!delBtn) return;

        const tr = delBtn.closest("tr");
        if (!tr) return;

        const userId = delBtn.dataset.delete;
        openConfirmModal({ userId, tr, btn: delBtn });
    });

    // close when clicking backdrop
    confirmModal.addEventListener("click", function (e) {
        if (e.target.classList.contains("confirm-modal__backdrop")) {
            window.closeConfirmModal();
        }
    });

    // close on ESC
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && confirmModal.classList.contains("is-open")) {
            window.closeConfirmModal();
        }
    });

    // confirm delete
    confirmDeleteBtn.addEventListener("click", async function () {
        if (!pendingDelete.userId) return;

        try {
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.textContent = "Deleting...";

            const res = await fetch(`/clients/delete/${pendingDelete.userId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
            });

            if (!res.ok) {
                let msg = "Failed to delete client.";
                try {
                    const data = await res.json();
                    msg = data.message || msg;
                } catch (_) {}
                throw new Error(msg);
            }

            // remove row + close modal
            pendingDelete.tr?.remove();
            window.closeConfirmModal();
        } catch (err) {
            alert(err.message || "Delete failed");
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = "Delete";
        }
    });

    applyFilters();
})();
