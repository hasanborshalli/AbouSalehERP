let deleteFormRef = null;

function deleteProject(elOrEvent) {
    // accept: form OR button OR event
    let el = elOrEvent;

    // if it's an event
    if (elOrEvent && elOrEvent.preventDefault) {
        elOrEvent.preventDefault();
        el = elOrEvent.target;
    }

    // resolve the form safely
    deleteFormRef = el && el.tagName === "FORM" ? el : el?.closest?.("form");

    if (!deleteFormRef) {
        console.error("Delete confirm: could not find form element.");
        return;
    }

    document.getElementById("confirmModal")?.classList.add("is-open");
}

function closeConfirmModal() {
    deleteFormRef = null;
    document.getElementById("confirmModal")?.classList.remove("is-open");
}

document
    .getElementById("confirmDeleteBtn")
    ?.addEventListener("click", function () {
        if (!deleteFormRef) return;

        // IMPORTANT: native submit bypasses onsubmit handlers to avoid loop
        if (typeof deleteFormRef.submit === "function") {
            deleteFormRef.submit();
        } else {
            // fallback (shouldn't happen, but safe)
            deleteFormRef
                .querySelector('button[type="submit"], input[type="submit"]')
                ?.click();
        }
    });

// Optional: close modal if click outside box
document
    .getElementById("confirmModal")
    ?.addEventListener("click", function (e) {
        if (e.target.classList.contains("confirm-modal__backdrop"))
            closeConfirmModal();
    });

// Optional: ESC to close
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeConfirmModal();
});
(function () {
    const input = document.getElementById("projectsSearch");
    const tbody = document.getElementById("projectsTbody");
    if (!input || !tbody) return;

    function norm(s) {
        return (s || "").toString().trim().toLowerCase();
    }

    input.addEventListener("input", function () {
        const q = norm(this.value);
        const rows = tbody.querySelectorAll(".projects-index__row");

        rows.forEach((row) => {
            const hay = [
                row.dataset.code,
                row.dataset.name,
                row.dataset.city,
                row.dataset.area,
            ]
                .map(norm)
                .join(" ");

            row.style.display = hay.includes(q) ? "" : "none";
        });
    });
})();
