<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Unit {{ $apartment->unit_number }} — {{ $apartment->project->name }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/project.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/responsive.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">

            @if(session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">✕</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <section class="project-page" aria-label="Unit management">

                {{-- Header --}}
                <div class="project-page__header">
                    <h2 class="project-page__title">
                        Unit {{ $apartment->unit_number }}
                        <span class="project-page__muted">— {{ $apartment->project->name }}</span>
                    </h2>
                    <div style="display:flex;gap:8px;">
                        <a class="project-page__back" href="{{ route('apartments.project', $apartment->project_id) }}">
                            ← Back to project
                        </a>
                        <a href="{{ route('reports.apartment.show', $apartment->id) }}" class="project-page__edit">
                            📊 View Report
                        </a>
                    </div>
                </div>

                {{-- Unit info strip --}}
                <section class="dashboard-card" style="margin-bottom:16px;">
                    <div class="project-page__meta">
                        <div class="project-page__kv">
                            <div class="project-page__k">Status</div>
                            <div class="project-page__v"><span class="project-page__pill">{{ ucfirst($apartment->status)
                                    }}</span></div>
                        </div>
                        <div class="project-page__kv">
                            <div class="project-page__k">Floor</div>
                            <div class="project-page__v">{{ $apartment->floor->floor_number ?? '—' }}</div>
                        </div>
                        <div class="project-page__kv">
                            <div class="project-page__k">Bedrooms</div>
                            <div class="project-page__v">{{ $apartment->bedrooms ?? '—' }}</div>
                        </div>
                        <div class="project-page__kv">
                            <div class="project-page__k">Bathrooms</div>
                            <div class="project-page__v">{{ $apartment->bathrooms ?? '—' }}</div>
                        </div>
                        <div class="project-page__kv">
                            <div class="project-page__k">Area (sqm)</div>
                            <div class="project-page__v">{{ $apartment->area_sqm ?? '—' }}</div>
                        </div>
                        <div class="project-page__kv">
                            <div class="project-page__k">Price</div>
                            <div class="project-page__v">${{ number_format($apartment->price_total, 2) }}</div>
                        </div>
                    </div>
                </section>

                <div class="project-page__grid">

                    {{-- ── Materials ── --}}
                    <section class="dashboard-card" aria-label="Unit materials">
                        <h3 style="margin:0 0 14px;">🧱 Materials</h3>

                        @if($apartment->materials->isNotEmpty())
                        <div class="project-page__table-wrap" style="margin-bottom:16px;">
                            <table class="project-page__table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty Needed</th>
                                        <th>Unit</th>
                                        <th>Stock</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apartment->materials as $m)
                                    <tr>
                                        <td>{{ $m->inventoryItem->name ?? '—' }}</td>
                                        <td>{{ number_format($m->quantity_needed, 2) }}</td>
                                        <td>{{ $m->unit ?? '—' }}</td>
                                        <td>{{ $m->inventoryItem->quantity ?? '—' }}</td>
                                        <td>
                                            <form method="post"
                                                action="{{ route('apartments.materials.destroy', [$apartment, $m]) }}"
                                                onsubmit="return confirm('Remove this material and restore stock?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;">✕
                                                    Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="project-page__muted" style="margin-bottom:14px;">No materials recorded for this unit
                            yet.</p>
                        @endif

                        {{-- Add material form --}}
                        <div style="padding-top:14px;border-top:1px solid rgba(0,0,0,0.07);">
                            <p style="font-size:13px;font-weight:600;margin:0 0 10px;">＋ Add material</p>
                            <form method="post" action="{{ route('apartments.materials.store', $apartment) }}">
                                @csrf
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                                    <div>
                                        <label style="font-size:12px;display:block;margin-bottom:4px;">Inventory
                                            Item</label>
                                        <select name="inventory_item_id" required
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;min-width:180px;">
                                            <option value="">Select item</option>
                                            @foreach($inventoryItems as $it)
                                            <option value="{{ $it->id }}">{{ $it->name }} (Stock: {{ $it->quantity }} {{
                                                $it->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:12px;display:block;margin-bottom:4px;">Quantity</label>
                                        <input type="number" name="quantity_needed" step="0.01" min="0.01"
                                            placeholder="0.00" required
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;width:120px;">
                                    </div>
                                    <button type="submit"
                                        style="padding:7px 18px;border-radius:7px;background:#2563eb;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;">
                                        Add
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    {{-- ── Additional Costs ── --}}
                    <section class="dashboard-card" aria-label="Unit additional costs">
                        <h3 style="margin:0 0 14px;">📋 Additional Costs</h3>

                        @if($apartment->additionalCosts->isNotEmpty())
                        <div class="project-page__table-wrap" style="margin-bottom:16px;">
                            <table class="project-page__table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Expected</th>
                                        <th>Actual</th>
                                        <th>Settle / Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apartment->additionalCosts as $c)
                                    @php $settled = $c->isSettled(); @endphp
                                    <tr>
                                        <td>{{ $c->description }}</td>
                                        <td class="project-page__muted">{{ $c->category ?? '—' }}</td>
                                        <td>${{ number_format($c->expected_amount, 2) }}</td>
                                        <td>{{ $settled ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                                        <td>
                                            @if(!$settled)
                                            <form method="post" style="display:flex;gap:6px;align-items:center;"
                                                action="{{ route('apartments.costs.settle', [$apartment, $c]) }}">
                                                @csrf @method('PATCH')
                                                <input type="number" name="actual_amount" step="0.01" min="0"
                                                    placeholder="Actual $" required
                                                    style="padding:5px 8px;border-radius:6px;border:1px solid #d1d5db;font-size:12px;width:100px;">
                                                <button type="submit"
                                                    style="padding:5px 10px;border-radius:6px;background:#059669;color:#fff;border:none;font-size:12px;cursor:pointer;">
                                                    ✔ Settle
                                                </button>
                                            </form>
                                            @else
                                            <span style="font-size:12px;color:#059669;font-weight:600;">Settled {{
                                                $c->actual_entered_at?->format('Y-m-d') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$settled)
                                            <form method="post"
                                                action="{{ route('apartments.costs.destroy', [$apartment, $c]) }}"
                                                onsubmit="return confirm('Delete this cost?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;">✕</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <p class="project-page__muted" style="margin-bottom:14px;">No additional costs recorded yet.</p>
                        @endif

                        {{-- Add cost form --}}
                        <div style="padding-top:14px;border-top:1px solid rgba(0,0,0,0.07);">
                            <p style="font-size:13px;font-weight:600;margin:0 0 10px;">＋ Add expected cost</p>
                            <form method="post" action="{{ route('apartments.costs.store', $apartment) }}">
                                @csrf
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                                    <div>
                                        <label
                                            style="font-size:12px;display:block;margin-bottom:4px;">Description</label>
                                        <input type="text" name="description" placeholder="e.g. Painting" required
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;min-width:160px;">
                                    </div>
                                    <div>
                                        <label style="font-size:12px;display:block;margin-bottom:4px;">Category
                                            (optional)</label>
                                        <input type="text" name="category" placeholder="e.g. finishing"
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;width:140px;">
                                    </div>
                                    <div>
                                        <label style="font-size:12px;display:block;margin-bottom:4px;">Expected Amount
                                            ($)</label>
                                        <input type="number" name="expected_amount" step="0.01" min="0"
                                            placeholder="0.00" required
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;width:130px;">
                                    </div>
                                    <button type="submit"
                                        style="padding:7px 18px;border-radius:7px;background:#2563eb;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;">
                                        Add Cost
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                </div>
            </section>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
</body>

</html>