(function () {
    const table = document.getElementById("stockInfoTable");
    const tbody = document.getElementById("stockInfoTbody");
    const headers = table
        ? table.querySelectorAll(".stock-info__th--sortable")
        : [];
    const typeFilter = document.getElementById("typeFilter");
    const searchInput = document.getElementById("stockInfoSearch");

    if (!table || !tbody || !headers.length) return;

    let sortState = { key: null, dir: "asc" };

    function getRows() {
        return Array.from(tbody.querySelectorAll(".stock-info__row"));
    }

    function normalize(v) {
        return (v || "").toString().trim().toLowerCase();
    }

    function toNumber(v) {
        const n = parseFloat((v || "").toString().replace(/,/g, ""));
        return Number.isFinite(n) ? n : 0;
    }

    function applySearchAndFilter() {
        const typeVal = normalize(typeFilter?.value || "all");
        const searchVal = normalize(searchInput?.value || "");

        getRows().forEach((row) => {
            const rowType = normalize(row.dataset.type);
            const rowName = normalize(row.dataset.name);

            const matchType = typeVal === "all" || rowType === typeVal;
            const matchSearch = !searchVal || rowName.includes(searchVal);

            row.style.display = matchType && matchSearch ? "" : "none";
        });
    }

    function clearIndicators() {
        headers.forEach((h) => {
            h.classList.remove("is-sorted");
            const ind = h.querySelector(".stock-info__sort-indicator");
            if (ind) ind.textContent = "";
        });
    }

    function setIndicator(header, dir) {
        clearIndicators();
        header.classList.add("is-sorted");
        const ind = header.querySelector(".stock-info__sort-indicator");
        if (ind) ind.textContent = dir === "asc" ? "▲" : "▼";
    }

    function sortRows(key, type, dir) {
        const rows = getRows();

        rows.sort((a, b) => {
            const av = a.dataset[key];
            const bv = b.dataset[key];

            if (type === "number") {
                return (toNumber(av) - toNumber(bv)) * (dir === "asc" ? 1 : -1);
            }

            return (
                normalize(av).localeCompare(normalize(bv)) *
                (dir === "asc" ? 1 : -1)
            );
        });

        rows.forEach((r) => tbody.appendChild(r));
    }

    headers.forEach((header) => {
        header.addEventListener("click", () => {
            const key = header.dataset.key;
            const type = header.dataset.type || "text";
            if (!key) return;

            if (sortState.key === key) {
                sortState.dir = sortState.dir === "asc" ? "desc" : "asc";
            } else {
                sortState.key = key;
                sortState.dir = "asc";
            }

            setIndicator(header, sortState.dir);
            sortRows(key, type, sortState.dir);
            applySearchAndFilter();
        });
    });

    typeFilter?.addEventListener("change", applySearchAndFilter);
    searchInput?.addEventListener("input", applySearchAndFilter);

    applySearchAndFilter();
})();
