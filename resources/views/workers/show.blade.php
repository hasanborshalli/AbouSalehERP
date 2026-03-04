<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Worker – {{ $worker->name }}</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <style>
        .wrk {
            max-width: 1100px;
            margin: 0 auto;
        }

        .wrk-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 20px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .45);
            border: 2px solid rgba(0, 0, 0, .07);
            margin-bottom: 14px;
        }

        .wrk-hero__name {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 700;
        }

        .wrk-hero__meta {
            font-size: 13px;
            opacity: .55;
        }

        .wrk-back {
            text-decoration: none;
            color: rgba(0, 0, 0, .65);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .55);
            border: 1px solid rgba(0, 0, 0, .1);
        }

        .wrk-section {
            background: rgba(255, 255, 255, .45);
            border: 2px solid rgba(0, 0, 0, .07);
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 14px;
        }

        .wrk-section__title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px;
            color: rgba(0, 0, 0, .7);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wrk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .wrk-table th {
            text-align: left;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            color: rgba(0, 0, 0, .45);
            border-bottom: 2px solid rgba(0, 0, 0, .07);
            background: rgba(0, 0, 0, .03);
        }

        .wrk-table td {
            padding: 9px 12px;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            vertical-align: middle;
        }

        .wrk-table tr:last-child td {
            border-bottom: none;
        }

        .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .bold {
            font-weight: 700;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge--paid {
            background: rgba(21, 128, 61, .1);
            color: #15803d;
        }

        .badge--pending {
            background: rgba(217, 119, 6, .1);
            color: #d97706;
        }

        .settle-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .settle-form input {
            width: 120px;
            padding: 5px 8px;
            border-radius: 8px;
            border: 1.5px solid rgba(0, 0, 0, .15);
            font-size: 12px;
        }

        .settle-form button {
            padding: 5px 12px;
            border-radius: 8px;
            border: none;
            background: rgba(21, 128, 61, .15);
            color: #15803d;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .settle-form button:hover {
            background: rgba(21, 128, 61, .25);
        }

        .link-btn {
            color: rgba(42, 127, 176, .9);
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        .contract-card {
            border: 1.5px solid rgba(0, 0, 0, .08);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, .4);
        }

        .contract-card__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .contract-card__title {
            font-weight: 700;
            font-size: 15px;
            margin: 0;
        }

        .contract-card__meta {
            font-size: 12px;
            opacity: .6;
            margin-top: 3px;
        }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .kpi {
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid rgba(0, 0, 0, .07);
            background: rgba(255, 255, 255, .4);
        }

        .kpi__label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            opacity: .5;
            margin: 0 0 4px;
        }

        .kpi__value {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .add-contract-form {
            background: rgba(0, 0, 0, .025);
            border: 1.5px dashed rgba(0, 0, 0, .1);
            border-radius: 12px;
            padding: 16px;
            margin-top: 12px;
        }

        .add-contract-form__title {
            font-size: 12px;
            font-weight: 700;
            color: rgba(0, 0, 0, .55);
            margin: 0 0 12px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media(max-width:640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .kpi-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-grid label {
            font-size: 11px;
            opacity: .6;
            display: block;
            margin-bottom: 3px;
        }

        .form-grid input,
        .form-grid select,
        .form-grid textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1.5px solid rgba(0, 0, 0, .12);
            font-size: 13px;
            box-sizing: border-box;
        }

        .form-grid .full {
            grid-column: 1/-1;
        }

        .btn-submit {
            padding: 8px 18px;
            border-radius: 999px;
            border: none;
            background: rgba(42, 127, 176, .15);
            color: rgba(42, 127, 176, .9);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: rgba(42, 127, 176, .25);
        }
    </style>
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

                <div class="wrk-hero">
                    <div>
                        <h2 class="wrk-hero__name">{{ $worker->name }}</h2>
                        <div class="wrk-hero__meta">
                            {{ $worker->email }}
                            @if($worker->phone) · {{ $worker->phone }} @endif
                            · ID {{ str_pad($worker->id, 5, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                    <a class="wrk-back" href="{{ route('workers.index') }}">← Workers</a>
                </div>

                @foreach($worker->workerContracts as $contract)
                @php
                $paid = $contract->payments->where('status','paid')->sum('amount');
                $pending = $contract->payments->where('status','pending')->sum('amount');
                @endphp
                <div class="wrk-section">
                    <div class="contract-card__head">
                        <div>
                            <p class="contract-card__title">📋 {{ $contract->scope_of_work }}</p>
                            <p class="contract-card__meta">
                                {{ ucfirst($contract->category ?? 'General') }}
                                @php
                                $linkedProjects = \App\Models\Project::whereIn('id', $contract->allProjectIds())->get();
                                $linkedApartments = \App\Models\Apartment::whereIn('id',
                                $contract->allApartmentIds())->with('project')->get();
                                @endphp
                                @if($linkedProjects->isNotEmpty())
                                · Projects: {{ $linkedProjects->pluck('name')->join(', ') }}
                                @endif
                                @if($linkedApartments->isNotEmpty())
                                · Units: {{ $linkedApartments->map(fn($a) => ($a->project?->name ? $a->project->name.' –
                                ' : '').'Unit '.($a->unit_number ?? '#'.$a->id))->join(', ') }}
                                @endif
                                · Contract date: {{ $contract->contract_date->format('d M Y') }}
                                @if($contract->pdf_path)
                                · <a class="link-btn" href="{{ route('workers.contract.pdf', $contract) }}"
                                    target="_blank">View PDF</a>
                                · <a class="link-btn"
                                    href="{{ route('workers.contract.pdf.download', $contract) }}">Download PDF</a>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="kpi-row">
                        <div class="kpi">
                            <p class="kpi__label">Total Contract</p>
                            <p class="kpi__value">${{ number_format($contract->total_amount, 2) }}</p>
                        </div>
                        <div class="kpi" style="border-color:rgba(21,128,61,.2); background:rgba(21,128,61,.05);">
                            <p class="kpi__label">Paid</p>
                            <p class="kpi__value" style="color:#15803d;">${{ number_format($paid, 2) }}</p>
                        </div>
                        <div class="kpi" style="border-color:rgba(217,119,6,.2); background:rgba(217,119,6,.05);">
                            <p class="kpi__label">Remaining</p>
                            <p class="kpi__value" style="color:#d97706;">${{ number_format($pending, 2) }}</p>
                        </div>
                    </div>

                    <table class="wrk-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Due Date</th>
                                <th class="num">Amount</th>
                                <th>Status</th>
                                <th>Paid On</th>
                                <th>Mark Paid / Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->payments as $p)
                            <tr>
                                <td class="bold">{{ $p->installment_index }}</td>
                                <td>{{ $p->due_date->format('d M Y') }}</td>
                                <td class="num bold">${{ number_format($p->amount, 2) }}</td>
                                <td>
                                    @if($p->status === 'paid')
                                    <span class="badge badge--paid">Paid</span>
                                    @else
                                    <span class="badge badge--pending">Pending</span>
                                    @endif
                                </td>
                                <td style="font-size:12px; opacity:.7;">{{ $p->paid_at ? $p->paid_at->format('d M Y') :
                                    '—' }}</td>
                                <td>
                                    @if($p->status === 'pending')
                                    <form class="settle-form" method="post"
                                        action="{{ route('workers.payments.markPaid', $p) }}">
                                        @csrf @method('PATCH')
                                        <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}">
                                        <button type="submit" class="btn-submit"
                                            style="padding:6px 14px; border-radius:999px; border:none; background:rgba(21,128,61,.12); color:#15803d; font-size:12px; font-weight:700; cursor:pointer;">✔
                                            Mark Paid</button>
                                    </form>
                                    @elseif($p->receipt_path)
                                    <a class="link-btn" href="{{ route('workers.payments.receipt', $p) }}">↓ Receipt</a>
                                    @else
                                    <span style="font-size:12px; opacity:.5;">Receipt generating…</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach

                {{-- Add new contract --}}
                <div class="wrk-section">
                    <p class="wrk-section__title">➕ Add new contract for this worker</p>
                    <form class="add-contract-form" method="post" action="{{ route('workers.addContract', $worker) }}"
                        id="addContractForm">
                        @csrf
                        <div class="form-grid">
                            <div class="full">
                                <label>Scope of work</label>
                                <input type="text" name="scope_of_work" placeholder="e.g. Plumbing – Block B"
                                    required />
                            </div>
                            <div>
                                <label>Category</label>
                                <input type="text" name="category" placeholder="e.g. plumbing" />
                            </div>
                            <div>
                                <label>Contract date</label>
                                <input type="date" name="contract_date" required value="{{ now()->format('Y-m-d') }}" />
                            </div>
                            <div>
                                <label>Start date</label>
                                <input type="date" name="start_date" />
                            </div>
                            <div>
                                <label>Expected end date</label>
                                <input type="date" name="expected_end_date" />
                            </div>
                        </div>

                        {{-- Project & Apartment assignment --}}
                        <div style="margin-top:14px;">
                            <label
                                style="display:block; font-size:12px; font-weight:700; color:rgba(0,0,0,.5); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                                Project & Apartment Assignment
                            </label>
                            <p style="font-size:12px; color:rgba(0,0,0,.45); margin-bottom:10px;">
                                Check a project to assign it. Checking a project disables its individual apartments.
                                Enter a cost for each selection — the total will be auto-calculated.
                            </p>
                            <div class="nc-assign-panel"
                                style="border:1.5px solid rgba(0,0,0,.08); border-radius:12px; overflow:hidden; background:#fff;">
                                @foreach($projects as $proj)
                                <div style="display:flex; align-items:center; gap:10px; padding:9px 14px; border-bottom:1px solid rgba(0,0,0,.05);"
                                    data-nc-proj-row="{{ $proj->id }}">
                                    <label
                                        style="display:flex; align-items:center; gap:7px; flex:1; cursor:pointer; font-size:13px; font-weight:600; color:rgba(0,0,0,.8);">
                                        <input type="checkbox" class="nc-proj-cb" data-project-id="{{ $proj->id }}"
                                            style="width:15px;height:15px;accent-color:rgba(42,127,176,.9);cursor:pointer;">
                                        {{ $proj->name }}
                                        @if($proj->apartments->count())
                                        <span style="opacity:.4; font-size:11px; font-weight:400;">{{
                                            $proj->apartments->count() }} unit(s)</span>
                                        @endif
                                    </label>
                                    <input type="hidden" name="project_ids[]" value="{{ $proj->id }}"
                                        class="nc-proj-id-hidden" disabled>
                                    <div class="nc-proj-cost" style="display:none; align-items:center; gap:5px;">
                                        <span
                                            style="font-size:13px; font-weight:700; color:rgba(42,127,176,.8);">$</span>
                                        <input type="number" class="nc-cost-input" name="project_costs[{{ $proj->id }}]"
                                            min="0.01" step="0.01" placeholder="Cost" disabled
                                            style="width:110px; padding:5px 8px; border:2px solid rgba(42,127,176,.25); border-radius:7px; font-size:13px; font-weight:600;">
                                    </div>
                                </div>
                                @foreach($proj->apartments as $apt)
                                <div style="display:flex; align-items:center; gap:10px; padding:8px 14px 8px 34px; border-bottom:1px solid rgba(0,0,0,.04); background:rgba(0,0,0,.01);"
                                    data-nc-apt-row="{{ $apt->id }}" data-nc-apt-parent="{{ $proj->id }}"
                                    class="nc-apt-row">
                                    <label
                                        style="display:flex; align-items:center; gap:7px; flex:1; cursor:pointer; font-size:12.5px; color:rgba(0,0,0,.65); font-weight:500;">
                                        <input type="checkbox" class="nc-apt-cb" data-apt-id="{{ $apt->id }}"
                                            data-apt-parent="{{ $proj->id }}"
                                            style="width:14px;height:14px;accent-color:rgba(42,127,176,.9);cursor:pointer;">
                                        Unit {{ $apt->unit_number ?? '#'.$apt->id }}
                                        @if($apt->bedrooms) · {{ $apt->bedrooms }}BR @endif
                                    </label>
                                    <input type="hidden" name="apartment_ids[]" value="{{ $apt->id }}"
                                        class="nc-apt-id-hidden" disabled>
                                    <div class="nc-apt-cost" style="display:none; align-items:center; gap:5px;">
                                        <span
                                            style="font-size:13px; font-weight:700; color:rgba(42,127,176,.8);">$</span>
                                        <input type="number" class="nc-cost-input"
                                            name="apartment_costs[{{ $apt->id }}]" min="0.01" step="0.01"
                                            placeholder="Cost" disabled
                                            style="width:110px; padding:5px 8px; border:2px solid rgba(42,127,176,.25); border-radius:7px; font-size:13px; font-weight:600;">
                                    </div>
                                </div>
                                @endforeach
                                @endforeach
                            </div>

                            <div id="nc-total-bar"
                                style="display:none; margin-top:8px; padding:9px 14px; background:rgba(42,127,176,.07); border-radius:9px; border:1.5px solid rgba(42,127,176,.15); display:none; justify-content:space-between; align-items:center;">
                                <span style="font-size:12px; font-weight:700; color:rgba(0,0,0,.55);">Contract total
                                    (from assignments)</span>
                                <span id="nc-total-display"
                                    style="font-size:17px; font-weight:800; color:rgba(42,127,176,.9);">$0.00</span>
                            </div>
                        </div>

                        <div class="form-grid" style="margin-top:14px;">
                            <div>
                                <label>Total amount ($)</label>
                                <input type="number" name="total_amount" min="0.01" step="0.01"
                                    placeholder="Auto-filled or enter manually" id="nc_total"
                                    style="width:100%; padding:8px 10px; border:2px solid rgba(0,0,0,.1); border-radius:8px; font-size:13px;" />
                            </div>
                            <div>
                                <label>Number of monthly payments</label>
                                <input type="number" name="payment_months" min="1" max="120" placeholder="10" required
                                    id="nc_months"
                                    style="width:100%; padding:8px 10px; border:2px solid rgba(0,0,0,.1); border-radius:8px; font-size:13px;" />
                            </div>
                            <div>
                                <label>Monthly (preview)</label>
                                <input type="text" id="nc_preview" readonly placeholder="Auto"
                                    style="opacity:.7; width:100%; padding:8px 10px; border:2px solid rgba(0,0,0,.07); border-radius:8px; font-size:13px;" />
                            </div>
                            <div>
                                <label>First payment date</label>
                                <input type="date" name="first_payment_date" required
                                    value="{{ now()->addMonth()->format('Y-m-d') }}"
                                    style="width:100%; padding:8px 10px; border:2px solid rgba(0,0,0,.1); border-radius:8px; font-size:13px;" />
                            </div>
                            <div class="full">
                                <label>Notes</label>
                                <input type="text" name="notes" placeholder="Optional notes"
                                    style="width:100%; padding:8px 10px; border:2px solid rgba(0,0,0,.1); border-radius:8px; font-size:13px;" />
                            </div>
                        </div>
                        <button type="submit" class="btn-submit"
                            style="margin-top:14px; padding:10px 22px; border-radius:999px; border:none; background:rgba(42,127,176,.9); color:#fff; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 3px 10px rgba(42,127,176,.25);">Add
                            Contract</button>
                    </form>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
    <script>
        (function(){
        // ── Add-contract assignment logic ────────────────────────────────────
        const ncTotal   = document.getElementById('nc_total');
        const ncMonths  = document.getElementById('nc_months');
        const ncPreview = document.getElementById('nc_preview');
        const ncTotalBar = document.getElementById('nc-total-bar');
        const ncTotalDisplay = document.getElementById('nc-total-display');

        function ncSumCosts() {
            let sum = 0;
            document.querySelectorAll('#addContractForm .nc-cost-input').forEach(inp => {
                const v = parseFloat(inp.value);
                if (!isNaN(v) && v > 0) sum += v;
            });
            return sum;
        }
        function ncRefreshTotal() {
            const sum = ncSumCosts();
            if (sum > 0) {
                ncTotalDisplay.textContent = '$' + sum.toFixed(2);
                ncTotalBar.style.display = 'flex';
                ncTotal.value = sum.toFixed(2);
            } else {
                ncTotalBar.style.display = 'none';
                ncTotal.value = '';
            }
            ncRefreshPreview();
        }
        function ncRefreshPreview() {
            const t = parseFloat(ncTotal.value), m = parseInt(ncMonths.value);
            ncPreview.value = (t > 0 && m > 0) ? '$' + (t/m).toFixed(2) + ' / mo' : '';
        }

        document.querySelectorAll('.nc-proj-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const pid     = this.dataset.projectId;
                const row     = this.closest('[data-nc-proj-row]');
                const costDiv = row.querySelector('.nc-proj-cost');
                const costInp = costDiv.querySelector('input[type=number]');
                const hidInp  = row.querySelector('.nc-proj-id-hidden');
                const aptRows = document.querySelectorAll(`.nc-apt-row[data-nc-apt-parent="${pid}"]`);
                if (this.checked) {
                    costDiv.style.display = 'flex';
                    costInp.disabled = false;
                    hidInp.disabled  = false;
                    aptRows.forEach(row => {
                        const aptCb   = row.querySelector('.nc-apt-cb');
                        const aptCost = row.querySelector('.nc-apt-cost');
                        const aptInp  = aptCost.querySelector('input[type=number]');
                        const aptHid  = row.querySelector('.nc-apt-id-hidden');
                        row.style.opacity = '.3';
                        row.style.pointerEvents = 'none';
                        aptCb.checked = false;
                        aptCost.style.display = 'none';
                        if (aptInp) { aptInp.value = ''; aptInp.disabled = true; }
                        if (aptHid) aptHid.disabled = true;
                    });
                } else {
                    costDiv.style.display = 'none';
                    costInp.value = ''; costInp.disabled = true;
                    hidInp.disabled = true;
                    aptRows.forEach(row => {
                        row.style.opacity = '';
                        row.style.pointerEvents = '';
                    });
                }
                ncRefreshTotal();
            });
        });

        document.querySelectorAll('.nc-apt-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const costDiv = this.closest('.nc-apt-row').querySelector('.nc-apt-cost');
                const costInp = costDiv.querySelector('input[type=number]');
                const hidInp  = this.closest('.nc-apt-row').querySelector('.nc-apt-id-hidden');
                costDiv.style.display = this.checked ? 'flex' : 'none';
                costInp.disabled = !this.checked;
                if (hidInp) hidInp.disabled = !this.checked;
                if (!this.checked) costInp.value = '';
                ncRefreshTotal();
            });
        });

        document.querySelectorAll('#addContractForm .nc-cost-input').forEach(inp => {
            inp.addEventListener('input', ncRefreshTotal);
        });

        ncTotal.addEventListener('input', ncRefreshPreview);
        ncMonths.addEventListener('input', ncRefreshPreview);
    })();
    </script>
</body>

</html>