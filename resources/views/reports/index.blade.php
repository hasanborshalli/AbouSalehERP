<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reports</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/reportsIndex.css">
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">
            <div class="rpt-index-wrap">

                <div class="rpt-index-hero">
                    <h2>Reports</h2>
                    <p>Financial, operational and inventory reports — all exportable to Excel and PDF.</p>
                </div>

                <div class="rpt-group-label">Project &amp; Unit Reports</div>
                <div class="rpt-cards-grid" style="grid-template-columns:repeat(2,1fr);">
                    <a class="rpt-card" href="{{ route('reports.project') }}">
                        <div class="rpt-card__icon">📊</div>
                        <div class="rpt-card__name">Report by Project</div>
                        <div class="rpt-card__desc">Total costs, revenues, materials and profit per project. Select the
                            project inside.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.apartment') }}">
                        <div class="rpt-card__icon">🏠</div>
                        <div class="rpt-card__name">Report by Apartment</div>
                        <div class="rpt-card__desc">Individual unit cost breakdown, invoices paid and profit. Select the
                            apartment inside.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                </div>

                <div class="rpt-group-label">Financial Reports</div>
                <div class="rpt-cards-grid">
                    <a class="rpt-card" href="{{ route('reports.pl') }}">
                        <div class="rpt-card__icon">📈</div>
                        <div class="rpt-card__name">Profit &amp; Loss</div>
                        <div class="rpt-card__desc">Revenue vs expenses by source, with monthly trend breakdown.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.sales-pipeline') }}">
                        <div class="rpt-card__icon">🏗️</div>
                        <div class="rpt-card__name">Sales Pipeline</div>
                        <div class="rpt-card__desc">All units across projects: status, pricing, collected and
                            outstanding amounts.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.outstanding-invoices') }}">
                        <div class="rpt-card__icon">🧾</div>
                        <div class="rpt-card__name">Outstanding Invoices</div>
                        <div class="rpt-card__desc">Pending and overdue client invoices with days overdue per invoice.
                        </div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.worker-payments') }}">
                        <div class="rpt-card__icon">👷</div>
                        <div class="rpt-card__name">Worker Payments</div>
                        <div class="rpt-card__desc">All worker installments: paid, pending and overdue by worker and
                            project.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.operating-expenses') }}">
                        <div class="rpt-card__icon">💸</div>
                        <div class="rpt-card__name">Operating Expenses</div>
                        <div class="rpt-card__desc">All office and operational costs broken down by category with trend.
                        </div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                    <a class="rpt-card" href="{{ route('reports.inventory') }}">
                        <div class="rpt-card__icon">📦</div>
                        <div class="rpt-card__name">Inventory Report</div>
                        <div class="rpt-card__desc">Purchases, usage, cost paid and where each item was used across
                            projects and units.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                </div>

                <div class="rpt-group-label">Managed Properties</div>
                <div class="rpt-cards-grid" style="grid-template-columns:repeat(1,1fr);">
                    <a class="rpt-card" href="{{ route('reports.managed-properties') }}">
                        <div class="rpt-card__icon">🏠</div>
                        <div class="rpt-card__name">Managed Properties Report</div>
                        <div class="rpt-card__desc">Flip profit &amp; loss, rental commission income, renovation
                            expenses, pending payouts and overdue rent payments.</div>
                        <div class="rpt-card__arrow">View report →</div>
                    </a>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
</body>

</html>