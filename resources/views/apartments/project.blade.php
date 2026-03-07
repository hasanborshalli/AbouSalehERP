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

                        <a class="project-page__edit" href="{{ route('reports.project.show', $project->id) }}">
                            📊 View Report
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

                        @if($project->inventoryUsages->count())
                        <div class="project-page__table-wrap">
                            <table class="project-page__table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Needed</th>
                                        <th>Unit</th>
                                        <th>Current stock</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->inventoryUsages as $usage)
                                    <tr>
                                        <td>{{ $usage->inventoryItem->name ?? '—' }}</td>
                                        <td>{{ $usage->quantity_needed }}</td>
                                        <td>{{ $usage->unit ?? $usage->inventoryItem->unit ?? '—' }}</td>
                                        <td>{{ $usage->inventoryItem->quantity ?? '—' }}</td>
                                        <td>
                                            <form method="post"
                                                action="{{ route('projects.materials.destroy', [$project, $usage->id]) }}"
                                                onsubmit="return confirm('Remove this material?')">
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
                        <div class="project-page__muted">No materials added to this project yet.</div>
                        @endif

                        {{-- Add material form --}}
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(0,0,0,0.07);">
                            <p style="font-size:13px;font-weight:600;margin:0 0 10px;">＋ Add material to this project
                            </p>
                            <form method="post" action="{{ route('projects.materials.store', $project) }}">
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
                                        <label style="font-size:12px;display:block;margin-bottom:4px;">Quantity
                                            Needed</label>
                                        <input type="number" name="quantity_needed" step="0.01" min="0.01"
                                            placeholder="0.00" required
                                            style="padding:7px 10px;border-radius:7px;border:1px solid #d1d5db;font-size:13px;width:130px;">
                                    </div>
                                    <button type="submit"
                                        style="padding:7px 18px;border-radius:7px;background:#2563eb;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;">
                                        Add
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>

                    {{-- Additional Costs --}}
                    <section class="dashboard-card" aria-label="Project additional costs">
                        <h3 style="margin:0 0 14px;">📋 Additional Costs</h3>

                        @if($project->additionalCosts->isNotEmpty())
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
                                    @foreach($project->additionalCosts as $c)
                                    @php $settled = $c->isSettled(); @endphp
                                    <tr>
                                        <td>{{ $c->description }}</td>
                                        <td class="project-page__muted">{{ $c->category ?? '—' }}</td>
                                        <td>${{ number_format($c->expected_amount, 2) }}</td>
                                        <td>{{ $settled ? '$'.number_format($c->actual_amount, 2) : '—' }}</td>
                                        <td>
                                            @if(!$settled)
                                            <form method="post" style="display:flex;gap:6px;align-items:center;"
                                                action="{{ route('projects.costs.settle', [$project, $c]) }}">
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
                                                action="{{ route('projects.costs.destroy', [$project, $c]) }}"
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

                        <div style="padding-top:14px;border-top:1px solid rgba(0,0,0,0.07);">
                            <p style="font-size:13px;font-weight:600;margin:0 0 10px;">＋ Add expected cost</p>
                            <form method="post" action="{{ route('projects.costs.store', $project) }}">
                                @csrf
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                                    <div>
                                        <label
                                            style="font-size:12px;display:block;margin-bottom:4px;">Description</label>
                                        <input type="text" name="description" placeholder="e.g. Electrical" required
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
                                                    <th></th>
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
                                                    <td>
                                                        <a href="{{ route('apartments.unit', $u->id) }}"
                                                            style="font-size:12px;padding:4px 10px;border-radius:6px;background:rgba(42,127,176,0.1);color:rgba(42,127,176,0.9);text-decoration:none;white-space:nowrap;">
                                                            Manage unit →
                                                        </a>
                                                    </td>
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
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>