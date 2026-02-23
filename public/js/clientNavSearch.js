(function () {
    const input = document.getElementById("navSearchInput");
    const results = document.getElementById("navSearchResults");
    if (!input || !results) return;

    // ✅ Add your app routes here
    const pages = [
        // Client Portal landing
        {
            label: "Contracts",
            keywords: "contracts contract agreement overview home",
            url: "/client/contracts",
        },
        {
            label: "Invoices",
            keywords: "invoices invoice payments receipts download center",
            url: "/client/invoices",
        },
        {
            label: "Settings",
            keywords: "settings profile password avatar",
            url: "/client/settings",
        },

        // Contracts section
        {
            label: "Contract Overview",
            keywords: "contracts overview project start date completion status",
            url: "/client/contracts/overview",
        },
        {
            label: "Project Manager",
            keywords: "contracts manager project manager contact",
            url: "/client/contracts/manager",
        },
        {
            label: "Contract Documents",
            keywords: "contracts documents pdf contract download view",
            url: "/client/contracts/documents",
        },
        {
            label: "Progress Tracker",
            keywords: "contracts progress tracker milestones steps",
            url: "/client/contracts/progress",
        },

        // Invoices section
        {
            label: "Invoice List",
            keywords: "invoices list all invoice table",
            url: "/client/invoices/list",
        },
        {
            label: "Receipts",
            keywords: "invoices receipts paid download receipt",
            url: "/client/invoices/receipts",
        },
        {
            label: "Download Center",
            keywords: "invoices download center unpaid official tax",
            url: "/client/invoices/download-center",
        },
        {
            label: "Payments Graph",
            keywords: "invoices payments graph chart history",
            url: "/client/invoices/payments",
        },

        // Logout (IMPORTANT: should be POST in Laravel, so navSearch should NOT link to /logout GET)
        // Keep it pointing to settings and user can logout from the sidebar button/form.
        {
            label: "Logout",
            keywords: "logout sign out exit",
            url: "/client/settings",
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
