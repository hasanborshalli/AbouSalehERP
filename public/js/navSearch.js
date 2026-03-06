/**
 * navSearch.js — Unified nav search for all portals
 * Styles injected inline — works regardless of external CSS state.
 * Role injected by navbar.blade: window.NAV_ROLE
 * Bell toggle also lives here.
 */
(function () {
    /* ── Inject styles once ──────────────────────────────────────────── */
    var STYLE_ID = "nav-search-styles";
    if (!document.getElementById(STYLE_ID)) {
        var style = document.createElement("style");
        style.id = STYLE_ID;
        style.textContent = [
            /* Results container */
            "#navSearchResults {",
            "  position: absolute;",
            "  top: calc(100% + 6px);",
            "  left: 0;",
            "  right: 0;",
            "  background: #fff;",
            "  border: 1px solid #e5e7eb;",
            "  border-radius: 10px;",
            "  box-shadow: 0 8px 24px rgba(0,0,0,.12);",
            "  z-index: 9999;",
            "  max-height: 360px;",
            "  overflow-y: auto;",
            "  padding: 6px;",
            "}",
            /* Each result row */
            "a.nav-search-item {",
            "  display: flex;",
            "  align-items: center;",
            "  justify-content: space-between;",
            "  gap: 12px;",
            "  padding: 10px 14px;",
            "  border-radius: 7px;",
            "  text-decoration: none;",
            "  color: inherit;",
            "  transition: background .1s;",
            "}",
            "a.nav-search-item:hover,",
            "a.nav-search-item.is-active {",
            "  background: #f3f4f6;",
            "}",
            ".nav-search-title {",
            "  font-size: 14px;",
            "  font-weight: 600;",
            "  color: #111827;",
            "  white-space: nowrap;",
            "}",
            ".nav-search-title mark {",
            "  background: #fef08a;",
            "  color: #111827;",
            "  border-radius: 2px;",
            "  padding: 0 1px;",
            "}",
            ".nav-search-desc {",
            "  font-size: 12px;",
            "  color: #9ca3af;",
            "  white-space: nowrap;",
            "  overflow: hidden;",
            "  text-overflow: ellipsis;",
            "}",
            ".nav-search-empty {",
            "  padding: 16px;",
            "  text-align: center;",
            "  font-size: 13px;",
            "  color: #9ca3af;",
            "}",
        ].join("\n");
        document.head.appendChild(style);
    }

    /* ── Page catalogue ──────────────────────────────────────────────── */
    var PAGES = {
        owner: [
            {
                title: "Dashboard",
                url: "/dashboard",
                desc: "Overview, stats, charts",
            },
            {
                title: "Inventory",
                url: "/inventory",
                desc: "Stock levels and items",
            },
            {
                title: "Stock Control",
                url: "/inventory/stock-control",
                desc: "Adjust stock levels",
            },
            {
                title: "Add Inventory Item",
                url: "/inventory/add-item",
                desc: "Create a new inventory item",
            },
            {
                title: "Stock Info",
                url: "/inventory/stock-info",
                desc: "Item details and quantities",
            },
            {
                title: "Clients",
                url: "/clients",
                desc: "Manage clients and contracts",
            },
            {
                title: "Add Client",
                url: "/clients/add-client",
                desc: "Create a new client",
            },
            {
                title: "Existing Clients",
                url: "/clients/existing-clients",
                desc: "All registered clients",
            },
            {
                title: "Invoices",
                url: "/invoices",
                desc: "Client invoices and payments",
            },
            {
                title: "Apartments",
                url: "/apartments",
                desc: "Manage apartments and units",
            },
            {
                title: "Existing Projects",
                url: "/apartments/existing-projects",
                desc: "All real estate projects",
            },
            {
                title: "Create Project",
                url: "/apartments/create-project",
                desc: "Add a new building project",
            },
            {
                title: "Accounting",
                url: "/accounting",
                desc: "Cash flow, expenses, revenue",
            },
            {
                title: "Record Purchase",
                url: "/accounting/purchases",
                desc: "Log an inventory purchase",
            },
            {
                title: "Record Expense",
                url: "/accounting/expenses",
                desc: "Log an operating expense",
            },
            {
                title: "Ledger",
                url: "/accounting/ledger",
                desc: "Full journal of all entries",
            },
            {
                title: "Reports",
                url: "/reports",
                desc: "Profit, cost and sales reports",
            },
            {
                title: "Project Report",
                url: "/reports/project",
                desc: "Cost and revenue per project",
            },
            {
                title: "Apartment Report",
                url: "/reports/apartment",
                desc: "Cost and profit per unit",
            },
            {
                title: "Profit & Loss",
                url: "/reports/pl",
                desc: "Revenue vs expenses by month",
            },
            {
                title: "Sales Pipeline",
                url: "/reports/sales-pipeline",
                desc: "Unit status and pricing",
            },
            {
                title: "Outstanding Invoices",
                url: "/reports/outstanding-invoices",
                desc: "Overdue and unpaid invoices",
            },
            {
                title: "Worker Payments Report",
                url: "/reports/worker-payments",
                desc: "Contractor payment history",
            },
            {
                title: "Operating Expenses Report",
                url: "/reports/operating-expenses",
                desc: "Office costs by category",
            },
            {
                title: "Inventory Report",
                url: "/reports/inventory",
                desc: "Stock usage and purchases",
            },
            {
                title: "Managed Properties Report",
                url: "/reports/managed-properties",
                desc: "Flip profit and rental commission",
            },
            {
                title: "Workers",
                url: "/workers",
                desc: "Contractors and payment schedules",
            },
            {
                title: "Add Worker",
                url: "/workers/create",
                desc: "Create a new worker account",
            },
            {
                title: "Managed Properties",
                url: "/managed",
                desc: "Flip and rental property management",
            },
            {
                title: "Settings",
                url: "/settings",
                desc: "Company settings and employees",
            },
        ],

        client: [
            {
                title: "My Contracts",
                url: "/client/contracts",
                desc: "View your contracts",
            },
            {
                title: "Contract Overview",
                url: "/client/contracts/overview",
                desc: "Contract summary and details",
            },
            {
                title: "Contract Manager",
                url: "/client/contracts/manager",
                desc: "Manage your contract",
            },
            {
                title: "Contract Documents",
                url: "/client/contracts/documents",
                desc: "Download contract PDFs",
            },
            {
                title: "Construction Progress",
                url: "/client/contracts/progress",
                desc: "Build stages and milestones",
            },
            {
                title: "My Invoices",
                url: "/client/invoices",
                desc: "View your invoices",
            },
            {
                title: "Invoice List",
                url: "/client/invoices/list",
                desc: "All your invoices",
            },
            {
                title: "Receipts",
                url: "/client/invoices/receipts",
                desc: "Paid invoice receipts",
            },
            {
                title: "Download Center",
                url: "/client/invoices/download-center",
                desc: "Download all invoices as PDF",
            },
            {
                title: "Payment History",
                url: "/client/invoices/payments",
                desc: "Paid amounts and dates",
            },
            {
                title: "Notifications",
                url: "/client/notifications",
                desc: "Your alerts and messages",
            },
            {
                title: "Settings",
                url: "/client/settings",
                desc: "Profile, password and avatar",
            },
        ],

        worker: [
            { title: "Home", url: "/worker/home", desc: "Your dashboard" },
            {
                title: "My Contracts",
                url: "/worker/contracts",
                desc: "Work scope and agreements",
            },
            {
                title: "My Payments",
                url: "/worker/payments",
                desc: "Salary and installments",
            },
            {
                title: "Settings",
                url: "/worker/settings",
                desc: "Profile, password and avatar",
            },
        ],
    };

    PAGES.admin = PAGES.owner;

    /* ── Pre-index ───────────────────────────────────────────────────── */
    var role = (window.NAV_ROLE || "admin").toLowerCase();
    var rawList = PAGES[role] || PAGES.admin;

    var index = rawList.map(function (p) {
        return {
            title: p.title,
            url: p.url,
            desc: p.desc,
            haystack: (p.title + " " + p.desc).toLowerCase(),
        };
    });

    /* ── DOM ─────────────────────────────────────────────────────────── */
    var input = document.getElementById("navSearchInput");
    var results = document.getElementById("navSearchResults");

    /* ── Bell toggle ─────────────────────────────────────────────────── */
    var bellBtn = document.getElementById("notifBellBtn");
    var bellMenu = document.getElementById("notifBellMenu");
    if (bellBtn && bellMenu) {
        bellBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            bellMenu.style.display =
                bellMenu.style.display === "block" ? "none" : "block";
        });
        document.addEventListener("click", function () {
            bellMenu.style.display = "none";
        });
    }

    if (!input || !results) return;

    /* ── Search ──────────────────────────────────────────────────────── */
    function search(raw) {
        var q = raw.trim().toLowerCase();
        if (!q) {
            hide();
            return;
        }

        var terms = q.split(/\s+/);
        var matched = [];

        for (var i = 0; i < index.length; i++) {
            var entry = index[i];
            var hit = true;
            for (var j = 0; j < terms.length; j++) {
                if (entry.haystack.indexOf(terms[j]) === -1) {
                    hit = false;
                    break;
                }
            }
            if (hit) {
                matched.push(entry);
                if (matched.length === 8) break;
            }
        }

        render(matched, terms[0]);
    }

    /* ── Render ──────────────────────────────────────────────────────── */
    function render(matched, firstTerm) {
        if (!matched.length) {
            results.innerHTML =
                '<div class="nav-search-empty">No pages found</div>';
            results.hidden = false;
            activeIdx = -1;
            return;
        }

        var html = "";
        for (var i = 0; i < matched.length; i++) {
            var m = matched[i];
            var lbl = esc(m.title);

            var lo = m.title.toLowerCase();
            var at = lo.indexOf(firstTerm);
            if (at !== -1) {
                lbl =
                    esc(m.title.slice(0, at)) +
                    "<mark>" +
                    esc(m.title.slice(at, at + firstTerm.length)) +
                    "</mark>" +
                    esc(m.title.slice(at + firstTerm.length));
            }

            html +=
                '<a class="nav-search-item" href="' +
                m.url +
                '">' +
                '<span class="nav-search-title">' +
                lbl +
                "</span>" +
                '<span class="nav-search-desc">' +
                esc(m.desc) +
                "</span>" +
                "</a>";
        }

        results.innerHTML = html;
        results.hidden = false;
        activeIdx = -1;
    }

    function hide() {
        results.hidden = true;
        results.innerHTML = "";
        activeIdx = -1;
    }

    function esc(s) {
        return s
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    /* ── Keyboard ────────────────────────────────────────────────────── */
    var activeIdx = -1;
    var debounceId = null;

    input.addEventListener("keydown", function (e) {
        var items = results.querySelectorAll(".nav-search-item");
        if (e.key === "ArrowDown") {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
            applyActive(items);
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, 0);
            applyActive(items);
        } else if (e.key === "Enter") {
            var target =
                activeIdx >= 0
                    ? items[activeIdx]
                    : results.querySelector(".nav-search-item");
            if (target) {
                e.preventDefault();
                window.location.href = target.href;
            }
        } else if (e.key === "Escape") {
            hide();
            input.blur();
        }
    });

    function applyActive(items) {
        for (var i = 0; i < items.length; i++) {
            items[i].classList.toggle("is-active", i === activeIdx);
        }
        if (items[activeIdx])
            items[activeIdx].scrollIntoView({ block: "nearest" });
    }

    input.addEventListener("input", function () {
        var val = this.value;
        clearTimeout(debounceId);
        debounceId = setTimeout(function () {
            search(val);
        }, 150);
    });

    input.addEventListener("focus", function () {
        if (this.value.trim()) search(this.value);
    });

    document.addEventListener("click", function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) hide();
    });
})();
