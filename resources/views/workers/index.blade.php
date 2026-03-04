<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Workers</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/workers.css">
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">
            <div class="wrk">
                @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif

                <div class="wrk-header">
                    <h2>Workers & Contractors</h2>
                    <a class="btn-add" href="{{ route('workers.create') }}">＋ Add Worker</a>
                </div>

                <div class="wrk-table-wrap">
                    @if($workers->isEmpty())
                    <p class="empty">No workers yet. Add your first contractor above.</p>
                    @else
                    <table class="wrk-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Contracts</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workers as $w)
                            <tr>
                                <td style="font-weight:600;">{{ $w->name }}</td>
                                <td>{{ $w->email ?? '—' }}</td>
                                <td>{{ $w->phone ?? '—' }}</td>
                                <td>
                                    <span
                                        style="background:rgba(42,127,176,.1);color:rgba(42,127,176,.9);padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700;">
                                        {{ $w->worker_contracts_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($w->is_active)
                                    <span class="badge badge--active">Active</span>
                                    @else
                                    <span class="badge badge--inactive">Inactive</span>
                                    @endif
                                </td>
                                <td style="opacity:.6; font-size:12px;">{{ $w->created_at->format('d M Y') }}</td>
                                <td>
                                    <a class="link-btn" href="{{ route('workers.show', $w) }}">View →</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
</body>

</html>