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
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>