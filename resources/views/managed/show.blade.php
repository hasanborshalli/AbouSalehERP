<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $property->address }} — Managed Property</title>
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

            {{-- Hero --}}
            <div class="mp-hero">
                <div>
                    <p class="mp-hero__address">{{ $property->address }}</p>
                    <p class="mp-hero__meta">
                        {{ $property->city }}{{ $property->area ? ' · '.$property->area : '' }}
                        @if($property->bedrooms) · {{ $property->bedrooms }} bed @endif
                        @if($property->bathrooms) · {{ $property->bathrooms }} bath @endif
                        @if($property->area_sqm) · {{ number_format($property->area_sqm,0) }} m² @endif
                        · Owner: {{ $property->owner_name }} ({{ $property->owner_phone }})
                        &nbsp;
                        <span class="badge badge-{{ $property->type }}" style="vertical-align:middle;">{{ ucfirst($property->type) }}</span>
                        @php $badge = $property->statusBadge(); @endphp
                        <span class="badge badge-{{ $property->status }}" style="vertical-align:middle; color:{{ $badge['color'] }}">{{ $badge['label'] }}</span>
                    </p>
                </div>
                <div class="mp-hero__actions">
                    <a href="{{ route('managed.agreement.pdf', $property) }}" target="_blank">📄 Agreement PDF</a>
                    <a href="{{ route('managed.edit', $property) }}">✏️ Edit</a>
                    @if(!in_array($property->status, ['sold','terminated']))
                    <form method="POST" action="{{ route('managed.terminate', $property) }}" style="display:inline;"
                        onsubmit="return confirm('Terminate this agreement?')">
                        @csrf @method('PATCH')
                        <button class="danger-btn" type="submit">⛔ Terminate</button>
                    </form>
                    @endif
                    <a href="{{ route('managed.index') }}" style="opacity:.7;">← Back</a>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════
                 FLIP SECTION
            ════════════════════════════════════════════════ --}}
            @if($property->isFlip())

            {{-- Profit summary if sold --}}
            @if($property->sale)
            @php
            $profit = $flipProfit;
            $isLoss = $profit < 0;
            @endphp
            <div class="profit-box {{ $isLoss ? 'loss' : '' }}">
                <p class="profit-box__title">{{ $isLoss ? '📉 Net Loss' : '📈 Net Profit Breakdown' }}</p>
                <div class="profit-box__rows">
                    <div class="profit-box__row">
                        <span>Sale Price (collected from buyer)</span>
                        <span class="money-green">${{ number_format($property->sale->sale_price, 2) }}</span>
                    </div>
                    <div class="profit-box__row">
                        <span>— Owner Payout</span>
                        <span class="money-red">-${{ number_format($property->sale->owner_payout_amount, 2) }}</span>
                    </div>
                    <div class="profit-box__row">
                        <span>— Total Renovation Expenses</span>
                        <span class="money-red">-${{ number_format($totalExpenses, 2) }}</span>
                    </div>
                    <div class="profit-box__row total">
                        <span>Company Net {{ $isLoss ? 'Loss' : 'Profit' }}</span>
                        <span>{{ $isLoss ? '-' : '' }}${{ number_format(abs($profit), 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- KPI --}}
            <div class="kpi-row">
                <div class="kpi">
                    <p class="kpi__label">Owner Asking</p>
                    <p class="kpi__value">${{ number_format($property->owner_asking_price, 0) }}</p>
                </div>
                <div class="kpi" style="border-color:rgba(220,38,38,.2);background:rgba(220,38,38,.04);">
                    <p class="kpi__label">Renovation Spent</p>
                    <p class="kpi__value money-red">${{ number_format($totalExpenses, 2) }}</p>
                </div>
                <div class="kpi">
                    <p class="kpi__label">Listing Price</p>
                    <p class="kpi__value">${{ $property->agreed_listing_price ? number_format($property->agreed_listing_price, 0) : '—' }}</p>
                </div>
                @if($property->sale)
                <div class="kpi" style="border-color:rgba(5,150,105,.2);background:rgba(5,150,105,.04);">
                    <p class="kpi__label">Sold For</p>
                    <p class="kpi__value money-green">${{ number_format($property->sale->sale_price, 0) }}</p>
                </div>
                <div class="kpi" style="border-color:rgba(37,99,235,.2);background:rgba(37,99,235,.04);">
                    <p class="kpi__label">Owner Paid Out</p>
                    <p class="kpi__value" style="color:{{ $property->sale->owner_paid_at ? '#2563eb' : '#d97706' }};">
                        {{ $property->sale->owner_paid_at ? '✓ Done' : 'Pending' }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Renovation Expenses --}}
            <div class="mp-section">
                <div class="mp-section__head">
                    <h3 class="mp-section__title">🔧 Renovation & Maintenance Expenses</h3>
                    @if(!in_array($property->status, ['sold','terminated']))
                    <button class="action-btn green" onclick="togglePanel('expense-form')">＋ Add Expense</button>
                    @endif
                </div>
                <div class="mp-section__body">

                    {{-- Add expense form --}}
                    @if(!in_array($property->status, ['sold','terminated']))
                    <div class="mp-form-panel" id="expense-form" style="display:none;">
                        <h4>Record Renovation Expense</h4>
                        <form method="POST" action="{{ route('managed.expenses.store', $property) }}">
                            @csrf
                            <div class="mp-form-grid">
                                <div class="mp-form-field">
                                    <label>Description *</label>
                                    <input name="description" type="text" placeholder="e.g. Painting all rooms"
                                        value="{{ old('description') }}" required />
                                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="mp-form-field">
                                    <label>Category</label>
                                    <select name="category">
                                        <option value="">— Select —</option>
                                        <option value="painting" {{ old('category')==='painting'?'selected':'' }}>Painting</option>
                                        <option value="plumbing" {{ old('category')==='plumbing'?'selected':'' }}>Plumbing</option>
                                        <option value="electrical" {{ old('category')==='electrical'?'selected':'' }}>Electrical</option>
                                        <option value="flooring" {{ old('category')==='flooring'?'selected':'' }}>Flooring</option>
                                        <option value="carpentry" {{ old('category')==='carpentry'?'selected':'' }}>Carpentry</option>
                                        <option value="cleaning" {{ old('category')==='cleaning'?'selected':'' }}>Cleaning</option>
                                        <option value="hvac" {{ old('category')==='hvac'?'selected':'' }}>HVAC</option>
                                        <option value="materials" {{ old('category')==='materials'?'selected':'' }}>Materials</option>
                                        <option value="other" {{ old('category')==='other'?'selected':'' }}>Other</option>
                                    </select>
                                </div>
                                <div class="mp-form-field">
                                    <label>Amount ($) *</label>
                                    <input name="amount" type="number" step="0.01" min="0.01"
                                        placeholder="1500.00" value="{{ old('amount') }}" required />
                                    @error('amount')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="mp-form-field">
                                    <label>Date *</label>
                                    <input name="expense_date" type="date"
                                        value="{{ old('expense_date', date('Y-m-d')) }}" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Vendor / Contractor</label>
                                    <input name="vendor_name" type="text" placeholder="Vendor name"
                                        value="{{ old('vendor_name') }}" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Notes</label>
                                    <input name="notes" type="text" placeholder="Optional note"
                                        value="{{ old('notes') }}" />
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;">
                                <button type="submit" class="btn-add">Save Expense</button>
                                <button type="button" class="btn-outline" onclick="togglePanel('expense-form')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($property->expenses->isEmpty())
                    <p class="mp-empty">No expenses recorded yet.</p>
                    @else
                    <table class="mp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Vendor</th>
                                <th class="num">Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($property->expenses->sortByDesc('expense_date') as $exp)
                            <tr style="{{ $exp->isVoided() ? 'opacity:.45;' : '' }}">
                                <td>{{ $exp->expense_date->format('d M Y') }}</td>
                                <td style="font-weight:600;">{{ $exp->description }}</td>
                                <td>{{ $exp->category ? ucfirst($exp->category) : '—' }}</td>
                                <td>{{ $exp->vendor_name ?? '—' }}</td>
                                <td class="num money-red">${{ number_format($exp->amount, 2) }}</td>
                                <td>
                                    @if($exp->isVoided())
                                    <span class="badge badge-terminated">Voided</span>
                                    @else
                                    <span class="badge badge-active">Active</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$exp->isVoided() && !in_array($property->status, ['sold','terminated']))
                                    <form method="POST"
                                        action="{{ route('managed.expenses.destroy', [$property, $exp]) }}"
                                        onsubmit="return confirm('Void this expense?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn red">Void</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right; font-weight:700;">Total Active Expenses</td>
                                <td class="num money-red">${{ number_format($totalExpenses, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                </div>
            </div>

            {{-- Sale Section --}}
            <div class="mp-section">
                <div class="mp-section__head">
                    <h3 class="mp-section__title">💰 Sale Transaction</h3>
                    @if(!$property->sale && $property->status === 'active')
                    <button class="action-btn green" onclick="togglePanel('sale-form')">Record Sale</button>
                    @endif
                </div>
                <div class="mp-section__body">

                    @if(!$property->sale && $property->status === 'active')
                    <div class="mp-form-panel" id="sale-form" style="display:none;">
                        <h4>Record Property Sale</h4>
                        <form method="POST" action="{{ route('managed.sale.store', $property) }}">
                            @csrf
                            <div class="mp-form-grid">
                                <div class="mp-form-field">
                                    <label>Buyer Name *</label>
                                    <input name="buyer_name" type="text" placeholder="Full name"
                                        value="{{ old('buyer_name') }}" required />
                                    @error('buyer_name')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="mp-form-field">
                                    <label>Buyer Phone</label>
                                    <input name="buyer_phone" type="tel" placeholder="+961 ..."
                                        value="{{ old('buyer_phone') }}" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Buyer Email</label>
                                    <input name="buyer_email" type="email" placeholder="buyer@email.com"
                                        value="{{ old('buyer_email') }}" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Final Sale Price ($) *</label>
                                    <input name="sale_price" type="number" step="0.01" min="0"
                                        placeholder="{{ $property->agreed_listing_price ?? '120000' }}"
                                        value="{{ old('sale_price', $property->agreed_listing_price) }}" required />
                                    @error('sale_price')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                                <div class="mp-form-field">
                                    <label>Sale Date *</label>
                                    <input name="sale_date" type="date"
                                        value="{{ old('sale_date', date('Y-m-d')) }}" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Notes</label>
                                    <input name="notes" type="text" placeholder="Optional"
                                        value="{{ old('notes') }}" />
                                </div>
                            </div>
                            <p style="font-size:12px;color:#6b7280;margin:0 0 12px;">
                                ℹ️ Owner payout will automatically be set to <strong>${{ number_format($property->owner_asking_price, 2) }}</strong> (owner asking price).
                            </p>
                            <div style="display:flex;gap:10px;">
                                <button type="submit" class="btn-add green">Confirm Sale</button>
                                <button type="button" class="btn-outline" onclick="togglePanel('sale-form')">Cancel</button>
                            </div>
                        </form>
                    </div>

                    @elseif($property->sale)
                    @php $sale = $property->sale; @endphp
                    <table class="mp-table">
                        <tbody>
                            <tr>
                                <td style="font-weight:600;">Buyer</td>
                                <td>{{ $sale->buyer_name }}
                                    @if($sale->buyer_phone) · {{ $sale->buyer_phone }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Sale Date</td>
                                <td>{{ $sale->sale_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Sale Price</td>
                                <td class="money-green">${{ number_format($sale->sale_price, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Owner Payout</td>
                                <td>
                                    <span class="money-red">${{ number_format($sale->owner_payout_amount, 2) }}</span>
                                    &nbsp;
                                    @if($sale->owner_paid_at)
                                    <span class="badge badge-sold">✓ Paid {{ $sale->owner_paid_at->format('d M Y') }}</span>
                                    @else
                                    <span class="badge badge-pending">Pending</span>
                                    <form method="POST" action="{{ route('managed.sale.payout', $property) }}"
                                        style="display:inline; margin-left:8px;"
                                        onsubmit="return confirm('Confirm you paid ${{ number_format($sale->owner_payout_amount,2) }} to owner?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn green">Mark Owner Paid</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @if($sale->notes)
                            <tr>
                                <td style="font-weight:600;">Notes</td>
                                <td>{{ $sale->notes }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    @else
                    <p class="mp-empty">No sale recorded yet. Property must be in Active status to record a sale.</p>
                    @endif

                </div>
            </div>

            @endif {{-- end flip --}}


            {{-- ═══════════════════════════════════════════════
                 RENTAL SECTION
            ════════════════════════════════════════════════ --}}
            @if($property->isRental())

            {{-- Renovation Expenses (also for rental) --}}
            <div class="mp-section">
                <div class="mp-section__head">
                    <h3 class="mp-section__title">🔧 Preparation Expenses</h3>
                    @if(!in_array($property->status, ['terminated']))
                    <button class="action-btn green" onclick="togglePanel('expense-form')">＋ Add Expense</button>
                    @endif
                </div>
                <div class="mp-section__body">
                    @if(!in_array($property->status, ['terminated']))
                    <div class="mp-form-panel" id="expense-form" style="display:none;">
                        <h4>Record Preparation Expense</h4>
                        <form method="POST" action="{{ route('managed.expenses.store', $property) }}">
                            @csrf
                            <div class="mp-form-grid">
                                <div class="mp-form-field">
                                    <label>Description *</label>
                                    <input name="description" type="text" placeholder="e.g. Deep cleaning" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Category</label>
                                    <select name="category">
                                        <option value="">— Select —</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="painting">Painting</option>
                                        <option value="plumbing">Plumbing</option>
                                        <option value="electrical">Electrical</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="mp-form-field">
                                    <label>Amount ($) *</label>
                                    <input name="amount" type="number" step="0.01" min="0.01" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Date *</label>
                                    <input name="expense_date" type="date" value="{{ date('Y-m-d') }}" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Vendor</label>
                                    <input name="vendor_name" type="text" placeholder="Vendor name" />
                                </div>
                            </div>
                            <div style="display:flex;gap:10px;">
                                <button type="submit" class="btn-add">Save</button>
                                <button type="button" class="btn-outline" onclick="togglePanel('expense-form')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($property->expenses->isEmpty())
                    <p class="mp-empty">No preparation expenses yet.</p>
                    @else
                    <table class="mp-table">
                        <thead>
                            <tr><th>Date</th><th>Description</th><th>Category</th><th class="num">Amount</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($property->expenses->sortByDesc('expense_date') as $exp)
                            <tr style="{{ $exp->isVoided() ? 'opacity:.45;' : '' }}">
                                <td>{{ $exp->expense_date->format('d M Y') }}</td>
                                <td style="font-weight:600;">{{ $exp->description }}</td>
                                <td>{{ $exp->category ? ucfirst($exp->category) : '—' }}</td>
                                <td class="num money-red">${{ number_format($exp->amount, 2) }}</td>
                                <td>{{ $exp->isVoided() ? '⊘ Voided' : '✓' }}</td>
                                <td>
                                    @if(!$exp->isVoided())
                                    <form method="POST" action="{{ route('managed.expenses.destroy', [$property, $exp]) }}"
                                        onsubmit="return confirm('Void this expense?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn red">Void</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:right;font-weight:700;">Total</td>
                                <td class="num money-red">${{ number_format($totalExpenses, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                </div>
            </div>

            {{-- Rental Summary KPIs --}}
            @if($rentalStats)
            <div class="kpi-row">
                <div class="kpi" style="border-color:rgba(5,150,105,.2);background:rgba(5,150,105,.04);">
                    <p class="kpi__label">Total Rent Collected</p>
                    <p class="kpi__value money-green">${{ number_format($rentalStats['total_collected'], 2) }}</p>
                </div>
                <div class="kpi" style="border-color:rgba(220,38,38,.2);background:rgba(220,38,38,.04);">
                    <p class="kpi__label">Paid to Owner</p>
                    <p class="kpi__value money-red">${{ number_format($rentalStats['total_owner_paid'], 2) }}</p>
                </div>
                <div class="kpi" style="border-color:rgba(37,99,235,.2);background:rgba(37,99,235,.04);">
                    <p class="kpi__label">Company Commission</p>
                    <p class="kpi__value money-blue">${{ number_format($rentalStats['total_commission'], 2) }}</p>
                </div>
                <div class="kpi">
                    <p class="kpi__label">Pending Payments</p>
                    <p class="kpi__value money-amber">{{ $rentalStats['pending_count'] }}</p>
                </div>
            </div>
            @endif

            {{-- Add Rental Contract --}}
            <div class="mp-section">
                <div class="mp-section__head">
                    <h3 class="mp-section__title">🔑 Rental Contracts</h3>
                    @php $hasActive = $property->rentals->where('status','active')->isNotEmpty(); @endphp
                    @if(!$hasActive && $property->status !== 'terminated')
                    <button class="action-btn green" onclick="togglePanel('rental-form')">＋ New Rental</button>
                    @endif
                </div>
                <div class="mp-section__body">

                    {{-- New rental form --}}
                    @if(!$hasActive && $property->status !== 'terminated')
                    <div class="mp-form-panel" id="rental-form" style="display:none;">
                        <h4>Create New Rental Contract</h4>
                        <form method="POST" action="{{ route('managed.rentals.store', $property) }}">
                            @csrf
                            <div class="mp-form-grid wide">
                                <div class="mp-form-field">
                                    <label>Tenant Name *</label>
                                    <input name="tenant_name" type="text" placeholder="Full name" required />
                                </div>
                                <div class="mp-form-field">
                                    <label>Tenant Phone</label>
                                    <input name="tenant_phone" type="tel" placeholder="+961 ..." />
                                </div>
                                <div class="mp-form-field">
                                    <label>Tenant Email</label>
                                    <input name="tenant_email" type="email" placeholder="tenant@email.com" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Monthly Rent ($) *</label>
                                    <input name="monthly_rent" type="number" step="0.01" min="0"
                                        placeholder="{{ $property->agreed_rent_price ?? '1500' }}"
                                        value="{{ $property->agreed_rent_price }}" required
                                        id="rent-input" oninput="calcRental()" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Company Commission (%) *</label>
                                    <input name="company_commission_pct" type="number" step="0.01" min="0" max="100"
                                        placeholder="{{ $property->company_commission_pct ?? '10' }}"
                                        value="{{ $property->company_commission_pct }}" required
                                        id="pct-input" oninput="calcRental()" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Security Deposit ($)</label>
                                    <input name="deposit_amount" type="number" step="0.01" min="0" placeholder="0" />
                                </div>
                                <div class="mp-form-field">
                                    <label>Start Date *</label>
                                    <input name="start_date" type="date" value="{{ date('Y-m-d') }}" required
                                        id="start-input" oninput="calcRental()" />
                                </div>
                                <div class="mp-form-field">
                                    <label>End Date *</label>
                                    <input name="end_date" type="date" required
                                        id="end-input" oninput="calcRental()" />
                                </div>
                            </div>
                            <div id="rental-preview" style="display:none; background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 16px; margin:12px 0; font-size:13px; color:#1e40af;">
                                Tenant pays: <strong id="pr-rent">—</strong>/mo &nbsp;·&nbsp;
                                Owner receives: <strong id="pr-owner">—</strong>/mo &nbsp;·&nbsp;
                                Company keeps: <strong id="pr-comm">—</strong>/mo &nbsp;·&nbsp;
                                <span id="pr-months">—</span> months = <strong id="pr-total">—</strong> total payments generated
                            </div>
                            <div style="display:flex;gap:10px;">
                                <button type="submit" class="btn-add green">Create Contract</button>
                                <button type="button" class="btn-outline" onclick="togglePanel('rental-form')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- List of rental contracts --}}
                    @forelse($property->rentals->sortByDesc('created_at') as $rental)
                    @php
                    $pendingPmts = $rental->payments->where('status','pending');
                    $collectedPmts = $rental->payments->where('status','collected');
                    @endphp
                    <div style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:16px; overflow:hidden;">
                        <div style="background:#fafafa; padding:14px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div>
                                <strong>{{ $rental->tenant_name }}</strong>
                                @if($rental->tenant_phone) · {{ $rental->tenant_phone }} @endif
                                <span class="badge badge-{{ $rental->status }}" style="margin-left:8px;">{{ ucfirst($rental->status) }}</span>
                            </div>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <span style="font-size:12px; color:#6b7280;">
                                    {{ $rental->start_date->format('d M Y') }} → {{ $rental->end_date->format('d M Y') }}
                                </span>
                                @if($rental->pdf_path)
                                <a class="action-btn blue" href="{{ route('managed.rentals.contract.pdf', [$property, $rental]) }}" target="_blank">📄 Contract</a>
                                @endif
                                @if($rental->status === 'active')
                                <form method="POST" action="{{ route('managed.rentals.end', [$property, $rental]) }}"
                                    onsubmit="return confirm('End this rental and remove future payments?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="action-btn red">End Rental</button>
                                </form>
                                @endif
                            </div>
                        </div>

                        {{-- Rental KPI mini row --}}
                        <div style="display:flex; gap:0; border-bottom:1px solid #f3f4f6;">
                            @foreach([
                                ['label'=>'Monthly Rent', 'val'=>'$'.number_format($rental->monthly_rent,2), 'color'=>'inherit'],
                                ['label'=>'Owner Share/mo', 'val'=>'$'.number_format($rental->owner_monthly_share,2), 'color'=>'#dc2626'],
                                ['label'=>'Commission/mo', 'val'=>'$'.number_format($rental->company_monthly_commission,2), 'color'=>'#2563eb'],
                                ['label'=>'Deposit', 'val'=>'$'.number_format($rental->deposit_amount,2), 'color'=>'inherit'],
                            ] as $k)
                            <div style="flex:1; padding:10px 14px; border-right:1px solid #f3f4f6;">
                                <p style="font-size:10px;font-weight:700;text-transform:uppercase;color:#9ca3af;margin:0 0 2px;">{{ $k['label'] }}</p>
                                <p style="font-size:15px;font-weight:800;margin:0;color:{{ $k['color'] }};">{{ $k['val'] }}</p>
                            </div>
                            @endforeach
                        </div>

                        {{-- Payment schedule --}}
                        <div style="padding:0;">
                            <table class="mp-table">
                                <thead>
                                    <tr>
                                        <th>Due Month</th>
                                        <th class="num">Rent Due</th>
                                        <th class="num">Owner Share</th>
                                        <th class="num">Commission</th>
                                        <th>Collected?</th>
                                        <th>Owner Paid?</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rental->payments->sortBy('due_date') as $pmt)
                                    <tr>
                                        <td>{{ $pmt->due_date->format('M Y') }}</td>
                                        <td class="num">${{ number_format($pmt->amount_due, 2) }}</td>
                                        <td class="num money-red">${{ number_format($pmt->owner_share, 2) }}</td>
                                        <td class="num money-blue">${{ number_format($pmt->company_commission, 2) }}</td>
                                        <td>
                                            @if($pmt->collected_at)
                                            <span style="color:#059669;font-weight:600;">✓ {{ $pmt->collected_at->format('d M') }}</span>
                                            @else
                                            <span style="color:#9ca3af;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($pmt->owner_paid_at)
                                            <span style="color:#059669;font-weight:600;">✓ {{ $pmt->owner_paid_at->format('d M') }}</span>
                                            @else
                                            <span style="color:#9ca3af;">—</span>
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            @if($pmt->status === 'pending' && $rental->status === 'active')
                                            <form method="POST"
                                                action="{{ route('managed.rentals.payments.collect', [$property, $rental, $pmt]) }}"
                                                style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="action-btn green" style="font-size:11px;">Collect</button>
                                            </form>
                                            @elseif($pmt->status === 'collected')
                                            <form method="POST"
                                                action="{{ route('managed.rentals.payments.payout', [$property, $rental, $pmt]) }}"
                                                style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="action-btn blue" style="font-size:11px;">Pay Owner</button>
                                            </form>
                                            @elseif($pmt->status === 'owner_paid')
                                            <span style="font-size:11px;color:#059669;font-weight:700;">✓ Done</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>Total</td>
                                        <td class="num">${{ number_format($rental->payments->sum('amount_due'),2) }}</td>
                                        <td class="num money-red">${{ number_format($rental->payments->sum('owner_share'),2) }}</td>
                                        <td class="num money-blue">${{ number_format($rental->payments->sum('company_commission'),2) }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @empty
                    <p class="mp-empty">No rental contracts yet. Create the first one above.</p>
                    @endforelse
                </div>
            </div>

            @endif {{-- end rental --}}

        </div>
    </main>
    <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
</div>

<script>
function togglePanel(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function calcRental() {
    const rent  = parseFloat(document.getElementById('rent-input')?.value) || 0;
    const pct   = parseFloat(document.getElementById('pct-input')?.value) || 0;
    const start = document.getElementById('start-input')?.value;
    const end   = document.getElementById('end-input')?.value;

    const comm    = (rent * pct / 100).toFixed(2);
    const owner   = (rent - comm).toFixed(2);
    const preview = document.getElementById('rental-preview');

    if (!rent || !pct) { if(preview) preview.style.display='none'; return; }

    let months = 0;
    if (start && end) {
        const s = new Date(start), e = new Date(end);
        months = (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) + 1;
        if (months < 0) months = 0;
    }

    if (preview) {
        preview.style.display = 'block';
        document.getElementById('pr-rent').textContent  = '$' + rent.toFixed(2);
        document.getElementById('pr-owner').textContent = '$' + owner;
        document.getElementById('pr-comm').textContent  = '$' + comm;
        document.getElementById('pr-months').textContent = months;
        document.getElementById('pr-total').textContent = months + ' entries';
    }
}
</script>
</body>
</html>
