(function () {
    const input = document.getElementById("navSearchInput");
    const results = document.getElementById("navSearchResults");
    if (!input || !results) return;

    // ✅ Add your app routes here
    const pages = [
        // Core
        {
            label: "Dashboard",
            keywords: "home main dashboard",
            url: "/dashboard",
        },
        { label: "Logout", keywords: "logout sign out", url: "/logout" },

        // Inventory (owner/admin)
        {
            label: "Inventory Overview",
            keywords: "inventory items overview",
            url: "/inventory",
        },
        {
            label: "Stock Control",
            keywords: "inventory stock control",
            url: "/inventory/stock-control",
        },
        {
            label: "Add Item",
            keywords: "inventory add item create",
            url: "/inventory/add-item",
        },
        {
            label: "Stock Info",
            keywords: "inventory stock info",
            url: "/inventory/stock-info",
        },

        // Clients (owner/admin)
        {
            label: "Clients Overview",
            keywords: "clients overview",
            url: "/clients",
        },
        {
            label: "Add Client",
            keywords: "clients add create new",
            url: "/clients/add-client",
        },
        {
            label: "Existing Clients",
            keywords: "clients existing list",
            url: "/clients/existing-clients",
        },

        // Invoices (owner/admin)
        {
            label: "Invoices",
            keywords: "invoices overview payments",
            url: "/invoices",
        },

        // Apartments / Projects (owner/admin)
        {
            label: "Apartments Overview",
            keywords: "apartments overview units",
            url: "/apartments",
        },
        {
            label: "Existing Projects",
            keywords: "apartments projects existing",
            url: "/apartments/existing-projects",
        },
        {
            label: "Create Project",
            keywords: "apartments projects create new",
            url: "/apartments/create-project",
        },
        {
            label: "Accounting Overview",
            keywords:
                "accounting overview cash basis finance profit net revenue expenses",
            url: "/accounting",
        },
        {
            label: "Record Purchase",
            keywords:
                "accounting purchase record restock inventory buy supplier vendor",
            url: "/accounting/purchases",
        },
        {
            label: "Record Expense",
            keywords:
                "accounting expense record operating costs utilities rent salary",
            url: "/accounting/expenses",
        },
        // Settings
        { label: "Settings", keywords: "settings overview", url: "/settings" },
        {
            label: "Export Data (Owner)",
            keywords: "settings export backup zip",
            url: "/settings/export",
        },

        // Employees (mostly modals, but keep as “actions” so user can find them)
        // Note: there is no GET page for these routes; they submit forms. We'll link to Settings as the entry point.
        {
            label: "Invite Employee (Owner)",
            keywords: "employees invite add create user team",
            url: "/settings",
        },
        {
            label: "Edit Employee (Owner)",
            keywords: "employees edit update team",
            url: "/settings",
        },
        {
            label: "Reset / Edit Password (Owner/Admin)",
            keywords: "password reset change edit manage",
            url: "/settings",
        },
        {
            label: "Edit Avatar",
            keywords: "avatar profile photo change",
            url: "/settings",
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
        // basic token match
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
        // keep active in view
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

    // close dropdown when clicking outside
    document.addEventListener("click", (e) => {
        const inside = e.target.closest(".app-navbar__search");
        if (!inside) closeResults();
    });

    // Optional: Ctrl+K / Cmd+K focuses search
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
        e.stopPropagation(); // keep it open when clicking inside
    });
});
