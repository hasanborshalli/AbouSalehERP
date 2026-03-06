<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Project</title>

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- page specific (optional) --}}
    <link rel="stylesheet" href="/css/project.css" />
    <link rel="stylesheet" href="/css/reportsProject.css" />
    <link rel="stylesheet" href="/css/alert.css" />

</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="project-page" aria-label="Project page">

                <div class="project-page__header">
                    <h2 class="project-page__title">
                        {{ $project->name }}
                        @if($project->code)
                        <span class="project-page__muted">— {{ $project->code }}</span>
                        @endif
                    </h2>

                    <div style="display:flex; gap:8px;">
                        <a class="project-page__back" onclick="event.preventDefault(); history.back();">
                            Back
                        </a>

                        <a class="project-page__edit" href="{{ route('apartments.edit-project', $project->id) }}">
                            Edit project
                        </a>
                    </div>
                </div>


                <div class="project-page__grid">

                    {{-- Top row --}}
                    <div class="project-page__top">

                        {{-- Project info --}}
                        <section class="dashboard-card" aria-label="Project information">
                            <h3 style="margin:0 0 10px;">Project information</h3>

                            <div class="project-page__meta">
                                <div class="project-page__kv">
                                    <div class="project-page__k">Status</div>
                                    <div class="project-page__v">
                                        <span class="project-page__pill">{{ ucfirst($project->status) }}</span>
                                    </div>
                                </div>

                                <div class="project-page__kv">
                                    <div class="project-page__k">Manager</div>
                                    <div class="project-page__v">
                                        {{ $project->manager?->name ?? '—' }}
                                    </div>
                                </div>

                                <div class="project-page__kv">
                                    <div class="project-page__k">City</div>
                                    <div class="project-page__v">{{ $project->city ?? '—' }}</div>
                                </div>

                                <div class="project-page__kv">
                                    <div class="project-page__k">Area</div>
                                    <div class="project-page__v">{{ $project->area ?? '—' }}</div>
                                </div>

                                <div class="project-page__kv" style="grid-column: 1 / -1;">
                                    <div class="project-page__k">Address</div>
                                    <div class="project-page__v">{{ $project->address ?? '—' }}</div>
                                </div>

                                <div class="project-page__kv">
                                    <div class="project-page__k">Start date</div>
                                    <div class="project-page__v">{{ $project->start_date ?? '—' }}</div>
                                </div>

                                <div class="project-page__kv">
                                    <div class="project-page__k">Estimated completion</div>
                                    <div class="project-page__v">{{ $project->estimated_completion_date ?? '—' }}</div>
                                </div>

                                <div class="project-page__kv" style="grid-column: 1 / -1;">
                                    <div class="project-page__k">Notes</div>
                                    <div class="project-page__v">{{ $project->notes ?? '—' }}</div>
                                </div>
                            </div>
                        </section>

                        {{-- Stats --}}
                        <section class="dashboard-card" aria-label="Project stats">
                            <h3 style="margin:0 0 10px;">Stats</h3>

                            <div class="project-page__stats">
                                <div class="project-page__stat">
                                    <div class="project-page__stat-title">Floors</div>
                                    <div class="project-page__stat-value">{{ $stats['floors'] }}</div>
                                </div>
                                <div class="project-page__stat">
                                    <div class="project-page__stat-title">Apartments</div>
                                    <div class="project-page__stat-value">{{ $stats['apartments'] }}</div>
                                </div>
                                <div class="project-page__stat">
                                    <div class="project-page__stat-title">Sold</div>
                                    <div class="project-page__stat-value">{{ $stats['sold'] }}</div>
                                </div>
                                <div class="project-page__stat">
                                    <div class="project-page__stat-title">Available</div>
                                    <div class="project-page__stat-value">{{ $stats['available'] }}</div>
                                </div>
                            </div>

                            <div style="margin-top:10px;" class="project-page__muted">
                                Reserved: {{ $stats['reserved'] }}
                            </div>
                        </section>

                    </div>

                    {{-- Materials --}}
                    <section class="dashboard-card" aria-label="Project materials">
                        <div class="project-page__header" style="margin:0 0 10px;">
                            <h3 style="margin:0;">Materials (Inventory)</h3>
                        </div>

                        @if($project->inventoryItems->count())
                        <div class="project-page__table-wrap">
                            <table class="project-page__table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Needed</th>
                                        <th>Unit</th>
                                        <th>Current stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->inventoryItems as $it)
                                    <tr>
                                        <td>{{ $it->name }}</td>
                                        <td>{{ $it->pivot->quantity_needed }}</td>
                                        <td>{{ $it->pivot->unit ?? $it->unit ?? '—' }}</td>
                                        <td>{{ $it->quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="project-page__muted">No materials added to this project yet.</div>
                        @endif
                    </section>

                    {{-- Floors & apartments --}}
                    <section class="dashboard-card" aria-label="Floors and apartments">
                        <h3 style="margin:0 0 10px;">Floors & apartments</h3>

                        @if($project->floors->count())
                        <div style="display:grid; gap:10px;">
                            @foreach($project->floors->sortBy('floor_number') as $floor)
                            @php
                            $units = $floor->apartments ?? collect();
                            $sold = $units->where('status','sold')->count();
                            $reserved = $units->where('status','reserved')->count();
                            $available = $units->where('status','available')->count();
                            @endphp

                            <details class="project-page__floor">
                                <summary>
                                    Floor {{ $floor->floor_number }}
                                    <span class="project-page__muted">
                                        — Units: {{ $units->count() }} | Sold: {{ $sold }} | Available: {{ $available }}
                                    </span>
                                </summary>

                                <div class="project-page__floor-sub">
                                    @if($units->count())
                                    <div class="project-page__table-wrap">
                                        <table class="project-page__table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Status</th>
                                                    <th>Bedrooms</th>
                                                    <th>Bathrooms</th>
                                                    <th>Area (sqm)</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($units as $u)
                                                <tr>
                                                    <td>{{ $u->unit_number }}</td>
                                                    <td><span class="project-page__pill">{{ ucfirst($u->status)
                                                            }}</span></td>
                                                    <td>{{ $u->bedrooms ?? '—' }}</td>
                                                    <td>{{ $u->bathrooms ?? '—' }}</td>
                                                    <td>{{ $u->area_sqm ?? '—' }}</td>
                                                    <td>{{ $u->price_total ?? '—' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                    <div class="project-page__muted">No units on this floor yet.</div>
                                    @endif
                                </div>
                            </details>
                            @endforeach
                        </div>
                        @else
                        <div class="project-page__muted">No floors found for this project yet.</div>
                        @endif
                    </section>

                </div>
            </section>

            {{-- ════════════════════════════════════════════════
            EDITABLE SECTIONS: Costs & Materials
            ════════════════════════════════════════════════ --}}

            @if(session('success'))
            <div class="alert alert--success" data-alert style="margin:0 24px 0;">
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert--error" data-alert style="margin:0 24px 0;">
                <span class="alert__icon">✕</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <div class="rpt" style="padding:0 24px 32px;">

                <h3 style="font-size:15px;font-weight:800;margin:24px 0 14px;color:#111;">Project-Level Materials</h3>

                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">🧱</span> Inventory Used by This
                        Project</p>
                    @if($project->inventoryUsages->isNotEmpty())
                    <table class="rpt-table" style="margin-bottom:14px;">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="num">Qty</th>
                                <th>Unit</th>
                                <th class="num">Unit Price</th>
                                <th class="num">Line Cost</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->inventoryUsages as $u)
                            @php $lp = (float)($u->inventoryItem->price ?? 0) * (float)$u->quantity_needed; @endphp
                            <tr>
                                <td>{{ $u->inventoryItem->name ?? '—' }}</td>
                                <td class="num">{{ number_format($u->quantity_needed, 2) }}</td>
                                <td>{{ $u->unit ?? $u->inventoryItem->unit ?? '—' }}</td>
                                <td class="num">${{ number_format((float)($u->inventoryItem->price ?? 0), 2) }}</td>
                                <td class="num bold">${{ number_format($lp, 2) }}</td>
                                <td>
                                    <form method="post"
                                        action="{{ route('projects.materials.destroy', [$project, $u]) }}"
                                        onsubmit="return confirm('Remove this material and restore stock?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">✕ Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total project materials cost</td>
                                <td class="num">${{ number_format($projectMaterialsCost, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="muted" style="font-size:13px;padding:0 0 10px;">No project-level materials added yet.</p>
                    @endif

                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add material to this project</p>
                        <form method="post" action="{{ route('projects.materials.store', $project) }}">
                            @csrf
                            <div class="rpt-add-form__grid rpt-add-form__grid--mat">
                                <div>
                                    <label>Inventory item</label>
                                    <select name="inventory_item_id" required>
                                        <option value="" disabled selected>Select item…</option>
                                        @foreach($inventoryItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ $item->quantity }}
                                            {{ $item->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label>Quantity</label>
                                    <input type="number" name="quantity_needed" min="0.01" step="0.01" placeholder="0"
                                        required>
                                </div>
                                <button type="submit" class="rpt-add-form__submit"
                                    style="align-self:flex-end;">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                <h3 style="font-size:15px;font-weight:800;margin:24px 0 14px;color:#111;">Project Additional Costs</h3>

                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">📋</span> Expected &amp; Actual Costs
                    </p>
                    @if($projCosts->isNotEmpty())
                    <table class="rpt-table" style="margin-bottom:14px;">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="num">Expected</th>
                                <th class="num">Actual</th>
                                <th class="num">Variance</th>
                                <th>Settle / Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projCosts as $c)
                            @php $variance = $c->variance(); $settled = $c->isSettled(); @endphp
                            <tr>
                                <td>{{ $c->description }}</td>
                                <td class="muted">{{ $c->category ?? '—' }}</td>
                                <td class="num">${{ number_format($c->expected_amount, 2) }}</td>
                                <td class="num">{{ $settled ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                                <td class="num">
                                    @if($settled)
                                    @if($variance > 0) <span class="badge badge--over">▲ ${{ number_format($variance, 2)
                                        }} over</span>
                                    @elseif($variance < 0) <span class="badge badge--under">▼ ${{
                                        number_format(abs($variance), 2) }} saved</span>
                                        @else <span class="badge badge--paid">On budget</span>
                                        @endif
                                        @else — @endif
                                </td>
                                <td>
                                    @if(!$settled)
                                    <form class="settle-form" method="post"
                                        action="{{ route('projects.costs.settle', [$project, $c]) }}">
                                        @csrf @method('PATCH')
                                        <input type="number" name="actual_amount" step="0.01" min="0"
                                            placeholder="Actual $" required>
                                        <button type="submit">✔ Settle</button>
                                    </form>
                                    @else
                                    <span class="badge badge--paid">Settled {{ $c->actual_entered_at?->format('Y-m-d')
                                        }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$settled)
                                    <form method="post" action="{{ route('projects.costs.destroy', [$project, $c]) }}"
                                        onsubmit="return confirm('Delete this cost entry?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-del">✕</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Totals</td>
                                <td class="num">${{ number_format($projCostsExpected, 2) }}</td>
                                <td class="num">${{ number_format($projCostsActual, 2) }}</td>
                                <td class="num">
                                    @php $pv = $projCostsActual - $projCostsExpected; @endphp
                                    @if($pv > 0) <span class="badge badge--over">▲ ${{ number_format($pv, 2) }}</span>
                                    @elseif($pv < 0) <span class="badge badge--under">▼ ${{ number_format(abs($pv), 2)
                                        }}</span>
                                        @else — @endif
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                    @else
                    <p class="muted" style="font-size:13px;padding:0 0 10px;">No additional costs added yet.</p>
                    @endif

                    <div class="rpt-add-form">
                        <p class="rpt-add-form__title">＋ Add expected cost for this project</p>
                        <form method="post" action="{{ route('projects.costs.store', $project) }}">
                            @csrf
                            <div class="rpt-add-form__grid">
                                <div>
                                    <label>Description</label>
                                    <input type="text" name="description" placeholder="e.g. Scaffolding hire" required>
                                </div>
                                <div>
                                    <label>Category</label>
                                    <input type="text" name="category" placeholder="e.g. equipment">
                                </div>
                                <div>
                                    <label>Expected Amount ($)</label>
                                    <input type="number" name="expected_amount" min="0" step="0.01" placeholder="0.00"
                                        required>
                                </div>
                            </div>
                            <div style="margin-top:8px;">
                                <button type="submit" class="rpt-add-form__submit">Add Cost</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Per-unit edit links --}}
                <h3 style="font-size:15px;font-weight:800;margin:24px 0 14px;color:#111;">Edit Per-Unit Costs &amp;
                    Materials</h3>
                <div class="rpt-section">
                    <p class="rpt-section__title"><span class="rpt-section__icon">🏘️</span> Select a unit to edit its
                        costs &amp; materials</p>
                    <div class="rpt-apts-grid">
                        @foreach($project->floors->sortBy('floor_number') as $floor)
                        @foreach($floor->apartments as $apt)
                        <a class="rpt-apt-card" href="{{ route('apartments.unit', $apt->id) }}">
                            <p class="rpt-apt-card__unit">Unit {{ $apt->unit_number }}</p>
                            <p class="rpt-apt-card__floor">Floor {{ $floor->floor_number }}</p>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key">Status</span>
                                <span class="badge badge--status-{{ $apt->status }}">{{ ucfirst($apt->status) }}</span>
                            </div>
                            <div class="rpt-apt-card__row">
                                <span class="rpt-apt-card__key" style="color:rgba(42,127,176,.8);font-weight:700;">✏️
                                    Edit costs</span>
                            </div>
                        </a>
                        @endforeach
                        @endforeach
                    </div>
                </div>

            </div>{{-- end rpt wrapper --}}

        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>