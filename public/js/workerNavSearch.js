(function () {
    const input = document.getElementById("navSearchInput");
    const results = document.getElementById("navSearchResults");
    if (!input || !results) return;

    const pages = [
        {
            label: "Dashboard",
            keywords: "dashboard home overview summary kpi",
            url: "/worker/home",
        },
        {
            label: "Contracts",
            keywords:
                "contracts contract work scope payment schedule agreement",
            url: "/worker/contracts",
        },
        {
            label: "Payments",
            keywords:
                "payments payment history receipts paid pending installment",
            url: "/worker/payments",
        },
        {
            label: "Settings",
            keywords: "settings profile password avatar account",
            url: "/worker/settings",
        },
        {
            label: "Logout",
            keywords: "logout sign out exit",
            url: "/worker/settings",
        },
    ];

    let activeIndex = -1;
    let visibleItems = [];

    function openResults() {
        results.hidden = false;
    }
    function closeResults() {
        results.hidden = true;
        results.innerHTML = "";
        activeIndex = -1;
        visibleItems = [];
    }

    function scoreMatch(q, p) {
        const hay = (p.label + " " + p.keywords).toLowerCase();
        if (hay.includes(q)) return 2;
        const tokens = q.split(/\s+/).filter(Boolean);
        let hits = 0;
        tokens.forEach((t) => {
            if (hay.includes(t)) hits++;
        });
        return hits ? 1 : 0;
    }

    function render(list, q) {
        results.innerHTML = "";
        if (!list.length) {
            results.innerHTML = `<div class="nav-search-item" style="cursor:default;opacity:.6;">No results</div>`;
            openResults();
            return;
        }

        list.forEach((p, idx) => {
            const row = document.createElement("div");
            row.className =
                "nav-search-item" + (idx === activeIndex ? " is-active" : "");
            row.innerHTML = `
              <span>${p.label}</span>
              <span class="nav-search-kbd">↵</span>
            `;
            row.addEventListener("click", () => (window.location.href = p.url));
            results.appendChild(row);
        });

        openResults();
    }

    function updateActive() {
        const rows = results.querySelectorAll(".nav-search-item");
        rows.forEach((r, i) =>
            r.classList.toggle("is-active", i === activeIndex),
        );
        const active = rows[activeIndex];
        if (active) active.scrollIntoView({ block: "nearest" });
    }

    input.addEventListener("input", () => {
        const q = input.value.trim().toLowerCase();
        if (!q) return closeResults();

        visibleItems = pages
            .map((p) => ({ p, s: scoreMatch(q, p) }))
            .filter((x) => x.s > 0)
            .sort((a, b) => b.s - a.s || a.p.label.localeCompare(b.p.label))
            .slice(0, 10)
            .map((x) => x.p);

        activeIndex = 0;
        render(visibleItems, q);
        updateActive();
    });

    input.addEventListener("keydown", (e) => {
        if (results.hidden) return;

        if (e.key === "ArrowDown") {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, visibleItems.length - 1);
            updateActive();
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            updateActive();
        } else if (e.key === "Enter") {
            e.preventDefault();
            const item = visibleItems[activeIndex];
            if (item) window.location.href = item.url;
        } else if (e.key === "Escape") {
            closeResults();
            input.blur();
        }
    });

    document.addEventListener("click", (e) => {
        const inside = e.target.closest(".app-navbar__search");
        if (!inside) closeResults();
    });

    document.addEventListener("keydown", (e) => {
        const isK = e.key.toLowerCase() === "k";
        if ((e.ctrlKey || e.metaKey) && isK) {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });
})();

document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("notifBellBtn");
    const menu = document.getElementById("notifBellMenu");
    if (!btn || !menu) return;

    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        menu.style.display = menu.style.display === "none" ? "block" : "none";
    });

    document.addEventListener("click", () => {
        menu.style.display = "none";
    });

    menu.addEventListener("click", (e) => {
        e.stopPropagation();
    });
});
