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
    <link rel="stylesheet" href="/css/alert.css">

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
            <section class="reports-index">
                <div class="reports-index__hero">
                    <h2>Reports</h2>
                    <p>Full financial breakdown by project or by individual apartment.</p>
                </div>

                <div class="reports-grid">

                    {{-- By Project --}}
                    <div class="reports-card">
                        <p class="reports-card__title">📊 Report by Project</p>
                        <p class="reports-card__sub">Total costs, revenues, materials and profit per project.</p>
                        @if($projects->isEmpty())
                        <p class="reports-empty">No projects yet.</p>
                        @else
                        <div class="reports-list">
                            @foreach($projects as $proj)
                            <a class="reports-list__item" href="{{ route('reports.project', $proj) }}">
                                <div>
                                    <div class="reports-list__item-name">{{ $proj->name }}</div>
                                    <div class="reports-list__item-meta">{{ $proj->city }} · {{ $proj->code ?? 'No code'
                                        }}</div>
                                </div>
                                <span class="reports-list__arrow">→</span>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- By Apartment --}}
                    <div class="reports-card">
                        <p class="reports-card__title">🏠 Report by Apartment</p>
                        <p class="reports-card__sub">Individual apartment cost breakdown, invoices paid, and profit.</p>
                        <div id="aptPicker">
                            <select id="projPicker" class="reports-apt-select" onchange="loadApts(this.value)">
                                <option value="">— Select project first —</option>
                                @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                @endforeach
                            </select>
                            <select id="aptPicker" class="reports-apt-select" style="display:none">
                                <option value="">— Select apartment —</option>
                            </select>
                            <a id="aptGoBtn" class="reports-apt-go" href="#" style="display:none">
                                View apartment report →
                            </a>
                        </div>
                    </div>

                </div>
            </section>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
    <script>
        const APT_ROUTES = {!! $aptRoutesJson !!};

    function loadApts(projId) {
        const sel  = document.getElementById('aptPicker');
        const btn  = document.getElementById('aptGoBtn');
        sel.innerHTML = '<option value="">— Select apartment —</option>';
        sel.style.display = 'block';
        btn.style.display = 'none';
        const apts = APT_ROUTES[projId] || [];
        apts.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.url;
            opt.textContent = a.label + ' · ' + a.status;
            sel.appendChild(opt);
        });
        sel.onchange = () => {
            btn.href = sel.value || '#';
            btn.style.display = sel.value ? 'flex' : 'none';
        };
    }
    </script>
</body>

</html>