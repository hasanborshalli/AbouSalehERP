//controlling settings sections
(function () {
    const sections = document.querySelectorAll("[data-acc]");
    if (!sections.length) return;

    // set true if you want only one open at a time
    const OPEN_ONE_AT_A_TIME = true;

    sections.forEach((section) => {
        const btn = section.querySelector(".settings-section__toggle");
        const panel = section.querySelector(".settings-section__panel");
        if (!btn || !panel) return;

        // start closed
        panel.style.maxHeight = "0px";

        btn.addEventListener("click", () => {
            const isOpen = btn.getAttribute("aria-expanded") === "true";

            if (OPEN_ONE_AT_A_TIME) {
                sections.forEach((s) => {
                    const b = s.querySelector(".settings-section__toggle");
                    const p = s.querySelector(".settings-section__panel");
                    if (!b || !p) return;
                    b.setAttribute("aria-expanded", "false");
                    p.style.maxHeight = "0px";
                    s.classList.remove("is-open");
                });
            }

            if (!isOpen) {
                btn.setAttribute("aria-expanded", "true");
                section.classList.add("is-open");
                panel.style.maxHeight = panel.scrollHeight + "px";
            } else {
                btn.setAttribute("aria-expanded", "false");
                section.classList.remove("is-open");
                panel.style.maxHeight = "0px";
            }
        });

        // keep height correct if content changes or window resizes
        window.addEventListener("resize", () => {
            if (btn.getAttribute("aria-expanded") === "true") {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    });
})();
//controlling modal
(function () {
    const openBtns = document.querySelectorAll("[data-modal-open]");
    const closeBtns = document.querySelectorAll("[data-modal-close]");

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");

        // lock background scroll
        document.documentElement.style.overflow = "hidden";
        document.body.style.overflow = "hidden";

        // focus first input if exists
        const focusEl = modal.querySelector(
            "input, button, select, textarea, a[href]",
        );
        if (focusEl) focusEl.focus();
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");

        // unlock scroll
        document.documentElement.style.overflow = "";
        document.body.style.overflow = "";
    }

    openBtns.forEach((btn) => {
        btn.addEventListener("click", () => openModal(btn.dataset.modalOpen));
    });

    closeBtns.forEach((btn) => {
        btn.addEventListener("click", () => closeModal(btn.dataset.modalClose));
    });

    document.addEventListener("keydown", (e) => {
        if (e.key !== "Escape") return;
        const opened = document.querySelector(".settings-modal.is-open");
        if (opened) closeModal(opened.id);
    });

    // Employees search
    const employeesSearch = document.getElementById("employeesSearch");
    const employeesTbody = document.getElementById("employeesTbody");

    if (employeesSearch && employeesTbody) {
        employeesSearch.addEventListener("input", () => {
            const q = employeesSearch.value.trim().toLowerCase();
            const rows = employeesTbody.querySelectorAll("tr");
            rows.forEach((row) => {
                const hay = row.getAttribute("data-search") || "";
                row.style.display = hay.includes(q) ? "" : "none";
            });
        });
    }
})();
//toggle visibility of password
document.addEventListener("click", function (e) {
    const toggle = e.target.closest(".password-toggle");
    if (!toggle) return;

    const wrapper = toggle.closest(".password-field");
    const input = wrapper.querySelector("input");

    if (!input) return;

    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    toggle.textContent = isPassword ? "🙈" : "👁";
});
//seacrh audit log
(function () {
    const q = document.getElementById("auditSearch");
    const action = document.getElementById("auditFilterAction");
    const entity = document.getElementById("auditFilterEntity");
    const from = document.getElementById("auditFrom");
    const to = document.getElementById("auditTo");
    const tbody = document.getElementById("auditTbody");

    if (!tbody) return;

    function inRange(dateStr, fromStr, toStr) {
        // dateStr is YYYY-MM-DD
        if (!dateStr) return true;
        if (fromStr && dateStr < fromStr) return false;
        if (toStr && dateStr > toStr) return false;
        return true;
    }

    function apply() {
        const needle = (q?.value || "").trim().toLowerCase();
        const a = (action?.value || "").trim().toLowerCase();
        const e = (entity?.value || "").trim().toLowerCase();
        const f = (from?.value || "").trim();
        const t = (to?.value || "").trim();

        tbody.querySelectorAll("tr").forEach((tr) => {
            const hay = tr.getAttribute("data-search") || "";
            const trAction = (
                tr.getAttribute("data-action") || ""
            ).toLowerCase();
            const trEntity = (
                tr.getAttribute("data-entity") || ""
            ).toLowerCase();
            const trDate = tr.getAttribute("data-date") || "";

            const okSearch = !needle || hay.includes(needle);
            const okAction = !a || trAction === a;
            const okEntity = !e || trEntity === e;
            const okDate = inRange(trDate, f, t);

            tr.style.display =
                okSearch && okAction && okEntity && okDate ? "" : "none";
        });
    }

    [q, action, entity, from, to].forEach((el) => {
        if (!el) return;
        el.addEventListener("input", apply);
        el.addEventListener("change", apply);
    });
})();

//export as excel
(function () {
    const btn = document.getElementById("auditExportExcelBtn");
    const table = document.querySelector(".audit-log__table");
    if (!btn || !table) return;

    btn.addEventListener("click", () => {
        // Export only visible rows (after filtering)
        const clone = table.cloneNode(true);
        clone.querySelectorAll("tbody tr").forEach((tr) => {
            if (getComputedStyle(tr).display === "none") tr.remove();
        });

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(clone);

        XLSX.utils.book_append_sheet(wb, ws, "Audit Log");
        XLSX.writeFile(wb, "audit-log.xlsx");
    });
})();
//export as pdf
(function () {
    const btn = document.getElementById("auditPrintBtn");
    const wrap = document.querySelector(".audit-log__table-wrap");
    if (!btn || !wrap) return;

    btn.addEventListener("click", () => {
        const w = window.open("", "_blank");
        w.document.write(`
      <html>
        <head>
          <title>Audit Log</title>
          <style>
            body{font-family: Arial, sans-serif; padding:20px;}
            table{width:100%; border-collapse:collapse; font-size:12px;}
            th,td{border:1px solid #ccc; padding:8px; text-align:left; vertical-align:top;}
            th{background:#f2f2f2;}
          </style>
        </head>
        <body>
          <h2>Activity audit log</h2>
          ${wrap.innerHTML}
        </body>
      </html>
    `);
        w.document.close();
        w.focus();
        w.print();
    });
})();
//manage passwords
(function () {
    const search = document.getElementById("pwdSearch");
    const tbody = document.getElementById("pwdTbody");
    const panel = document.getElementById("pwdResetPanel");
    const empLabel = document.getElementById("pwdResetEmpLabel");
    const empIdInput = document.getElementById("ResetEmpId");
    const cancelBtn = document.getElementById("pwdResetCancelBtn");

    if (!tbody) return;

    // Search filter
    if (search) {
        search.addEventListener("input", () => {
            const q = search.value.trim().toLowerCase();
            tbody.querySelectorAll("tr").forEach((tr) => {
                const hay = tr.getAttribute("data-search") || "";
                tr.style.display = hay.includes(q) ? "" : "none";
            });
        });
    }

    function openReset(empId, empName) {
        if (!panel) return;
        panel.hidden = false;
        if (empLabel) empLabel.textContent = `${empName} (${empId})`;
        if (empIdInput) empIdInput.value = empId;

        // scroll to panel inside modal
        panel.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    function closeReset() {
        if (!panel) return;
        panel.hidden = true;
        if (empLabel) empLabel.textContent = "";
        if (empIdInput) empIdInput.value = "";

        // clear inputs
        const inputs = panel.querySelectorAll(
            'input[type="password"], input[type="text"]',
        );
        inputs.forEach((i) => {
            if (i.id !== "pwdResetEmpId") i.value = "";
        });
        const chk = document.getElementById("force_change");
        if (chk) chk.checked = false;
    }

    // Open reset from table button
    document.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-open-reset]");
        if (!btn) return;

        const empId = btn.getAttribute("data-open-reset");
        const empName = btn.getAttribute("data-emp-name") || "Employee";
        openReset(empId, empName);
    });

    // Cancel closes reset panel (not the whole modal)
    if (cancelBtn) cancelBtn.addEventListener("click", closeReset);
})();
// ===== Employees delete (confirm modal + fetch DELETE) =====
(function () {
    const employeesTbody = document.getElementById("employeesTbody");
    const confirmModal = document.getElementById("confirmEmployeeDeleteModal");
    const confirmDeleteBtn = document.getElementById(
        "confirmEmployeeDeleteBtn",
    );
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (!employeesTbody || !confirmModal || !confirmDeleteBtn) return;

    let pendingDelete = {
        empId: null,
        tr: null,
        btn: null,
    };

    function openConfirmModal({ empId, tr, btn }) {
        pendingDelete.empId = empId;
        pendingDelete.tr = tr;
        pendingDelete.btn = btn;
        confirmModal.classList.add("is-open");
    }

    window.closeEmployeeConfirmModal = function () {
        confirmModal.classList.remove("is-open");

        pendingDelete.empId = null;
        pendingDelete.tr = null;
        pendingDelete.btn = null;

        confirmDeleteBtn.disabled = false;
        confirmDeleteBtn.textContent = "Delete";
    };

    // open modal when clicking delete icon
    employeesTbody.addEventListener("click", function (e) {
        const delBtn = e.target.closest(
            ".settings-employees__icon-btn--danger",
        );
        if (!delBtn) return;

        const tr = delBtn.closest("tr");
        if (!tr) return;

        const empId = delBtn.dataset.delete;
        if (!empId) return;

        openConfirmModal({ empId, tr, btn: delBtn });
    });

    // close when clicking backdrop
    confirmModal.addEventListener("click", function (e) {
        if (e.target.classList.contains("confirm-modal__backdrop")) {
            window.closeEmployeeConfirmModal();
        }
    });

    // close on ESC
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && confirmModal.classList.contains("is-open")) {
            window.closeEmployeeConfirmModal();
        }
    });

    // confirm delete
    confirmDeleteBtn.addEventListener("click", async function () {
        if (!pendingDelete.empId) return;

        try {
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.textContent = "Deleting...";

            // ✅ Change this URL to match your route
            const res = await fetch(
                `/employees/delete/${pendingDelete.empId}`,
                {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": token,
                        Accept: "application/json",
                    },
                },
            );

            if (!res.ok) {
                let msg = "Failed to delete employee.";
                try {
                    const data = await res.json();
                    msg = data.message || msg;
                } catch (_) {}
                throw new Error(msg);
            }

            // remove row + close modal
            pendingDelete.tr?.remove();
            window.closeEmployeeConfirmModal();
        } catch (err) {
            alert(err.message || "Delete failed");
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = "Delete";
        }
    });
})();
(function () {
    const employeesTbody = document.getElementById("employeesTbody");
    if (!employeesTbody) return;

    // Edit modal inputs
    const editId = document.getElementById("edit_emp_id");
    const editName = document.getElementById("edit_emp_name");
    const editPhone = document.getElementById("edit_emp_phone");
    const editEmail = document.getElementById("edit_emp_email");

    if (!editId || !editName || !editPhone || !editEmail) return;

    // Use event delegation
    employeesTbody.addEventListener("click", (e) => {
        const editBtn = e.target.closest(".settings-employees__icon-btn--edit");
        if (!editBtn) return;

        const tr = editBtn.closest("tr");
        if (!tr) return;

        // Pull data from <tr data-...>
        editId.value = tr.dataset.id || "";
        editName.value = tr.dataset.name || "";
        editPhone.value = tr.dataset.phone || "";
        editEmail.value = tr.dataset.email || "";

        // Optional: focus first field
        setTimeout(() => editName.focus(), 0);
    });
})();
// ===== Invite employee: avatar preview =====
(function () {
    const input = document.getElementById("emp_avatar");
    const img = document.getElementById("emp_avatar_preview");
    if (!input || !img) return;

    input.addEventListener("change", () => {
        const file = input.files && input.files[0];
        if (!file) {
            img.src = "/img/avatar-placeholder.png";
            return;
        }

        // Basic guard: only images
        if (!file.type.startsWith("image/")) {
            input.value = "";
            img.src = "/img/avatar-placeholder.png";
            alert("Please select an image file.");
            return;
        }

        const url = URL.createObjectURL(file);
        img.src = url;

        // Cleanup the blob URL after it loads
        img.onload = () => URL.revokeObjectURL(url);
    });
})();
