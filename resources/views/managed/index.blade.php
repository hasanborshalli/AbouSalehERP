<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Managed Properties</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/managed.css">
    <link rel="stylesheet" href="/css/responsive.css">
</head>
<body class="app-shell">
<input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
<aside class="app-shell__sidebar"><x-sidebar /></aside>
<div class="app-shell__main">
    <x-navbar />
    <main class="dashboard-content">
        <div class="mp">

            @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif

            <div class="mp-header">
                <h2>🏠 Managed Properties</h2>
                <a class="btn-add" href="{{ route('managed.create') }}">＋ Add Property</a>
            </div>

            {{-- KPI stats --}}
            <div class="mp-kpi-row">
                <div class="mp-kpi">
                    <p class="mp-kpi__label">Total</p>
                    <p class="mp-kpi__value">{{ $stats['total'] }}</p>
                </div>
                <div class="mp-kpi">
                    <p class="mp-kpi__label">Flip</p>
                    <p class="mp-kpi__value blue">{{ $stats['flip'] }}</p>
                </div>
                <div class="mp-kpi">
                    <p class="mp-kpi__label">Rental</p>
                    <p class="mp-kpi__value purple">{{ $stats['rental'] }}</p>
                </div>
                <div class="mp-kpi">
                    <p class="mp-kpi__label">Active</p>
                    <p class="mp-kpi__value amber">{{ $stats['active'] }}</p>
                </div>
                <div class="mp-kpi">
                    <p class="mp-kpi__label">Sold / Completed</p>
                    <p class="mp-kpi__value green">{{ $stats['sold'] }}</p>
                </div>
            </div>

            {{-- Filter tabs --}}
            @php $filter = request('filter', 'all'); @endphp
            <div class="mp-tabs">
                <a class="mp-tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('managed.index') }}">All</a>
                <a class="mp-tab {{ $filter === 'flip' ? 'active' : '' }}" href="{{ route('managed.index', ['filter'=>'flip']) }}">Flip</a>
                <a class="mp-tab {{ $filter === 'rental' ? 'active' : '' }}" href="{{ route('managed.index', ['filter'=>'rental']) }}">Rental</a>
                <a class="mp-tab {{ $filter === 'active' ? 'active' : '' }}" href="{{ route('managed.index', ['filter'=>'active']) }}">Active</a>
                <a class="mp-tab {{ $filter === 'sold' ? 'active' : '' }}" href="{{ route('managed.index', ['filter'=>'sold']) }}">Sold</a>
            </div>

            @php
            $filtered = $properties;
            if ($filter === 'flip')   $filtered = $properties->where('type', 'flip');
            if ($filter === 'rental') $filtered = $properties->where('type', 'rental');
            if ($filter === 'active') $filtered = $properties->whereIn('status', ['active','rented']);
            if ($filter === 'sold')   $filtered = $properties->where('status', 'sold');
            @endphp

            @if($filtered->isEmpty())
            <div class="mp-empty">
                <strong>No properties found</strong>
                Add your first managed property using the button above.
            </div>
            @else
            <div class="mp-grid">
                @foreach($filtered as $prop)
                @php
                $badge = $prop->statusBadge();
                @endphp
                <div class="mp-card">
                    <div class="mp-card__top">
                        <div>
                            <p class="mp-card__address">{{ $prop->address }}</p>
                            <p class="mp-card__sub">
                                {{ $prop->city }}{{ $prop->area ? ' · ' . $prop->area : '' }}
                                @if($prop->bedrooms) · {{ $prop->bedrooms }} bed @endif
                                @if($prop->area_sqm) · {{ number_format($prop->area_sqm, 0) }} m² @endif
                            </p>
                        </div>
                        <div class="mp-card__badges">
                            <span class="badge badge-{{ $prop->type }}">{{ ucfirst($prop->type) }}</span>
                            <span class="badge badge-{{ $prop->status }}" style="color: {{ $badge['color'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="mp-card__body">
                        <div>
                            <p class="mp-card__stat-label">Owner</p>
                            <p class="mp-card__stat-value">{{ $prop->owner_name }}</p>
                        </div>
                        <div>
                            <p class="mp-card__stat-label">Owner Asking</p>
                            <p class="mp-card__stat-value">${{ number_format($prop->owner_asking_price, 0) }}</p>
                        </div>
                        @if($prop->isFlip())
                        <div>
                            <p class="mp-card__stat-label">Listing Price</p>
                            <p class="mp-card__stat-value">
                                {{ $prop->agreed_listing_price ? '$'.number_format($prop->agreed_listing_price, 0) : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="mp-card__stat-label">Expenses</p>
                            <p class="mp-card__stat-value money-red">${{ number_format($prop->totalExpenses(), 0) }}</p>
                        </div>
                        @endif
                        @if($prop->isRental())
                        <div>
                            <p class="mp-card__stat-label">Monthly Rent</p>
                            <p class="mp-card__stat-value">${{ number_format($prop->agreed_rent_price, 0) }}</p>
                        </div>
                        <div>
                            <p class="mp-card__stat-label">Commission</p>
                            <p class="mp-card__stat-value">{{ $prop->company_commission_pct }}%</p>
                        </div>
                        @endif
                    </div>
                    <div class="mp-card__footer">
                        <span style="font-size:11px; color:#9ca3af;">
                            {{ $prop->agreement_date->format('d M Y') }}
                        </span>
                        <a class="action-btn blue" href="{{ route('managed.show', $prop) }}">View →</a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </main>
    <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
</div>
</body>
</html>
