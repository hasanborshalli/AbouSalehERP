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
                                @if($contract->project) · Project: {{ $contract->project->name }} @endif
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
                    <form class="add-contract-form" method="post" action="{{ route('workers.addContract', $worker) }}">
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
                                <label>Projects (optional — hold Ctrl/Cmd to select multiple)</label>
                                @php $allProjects = \App\Models\Project::orderByDesc('created_at')->get(['id','name']);
                                @endphp
                                <select name="project_ids[]" multiple size="4"
                                    style="width:100%; padding:6px 8px; border-radius:8px; border:2px solid rgba(0,0,0,.1); height:auto;">
                                    @foreach($allProjects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Apartments (optional — hold Ctrl/Cmd to select multiple)</label>
                                @php $allApartments = \App\Models\Apartment::with('project')->orderBy('id')->get();
                                @endphp
                                <select name="apartment_ids[]" multiple size="4"
                                    style="width:100%; padding:6px 8px; border-radius:8px; border:2px solid rgba(0,0,0,.1); height:auto;">
                                    @foreach($allApartments as $apt)
                                    <option value="{{ $apt->id }}">
                                        {{ $apt->project?->name ? $apt->project->name.' — ' : '' }}{{ $apt->unit_number
                                        ?? $apt->unit_code ?? 'Unit #'.$apt->id }}
                                    </option>
                                    @endforeach
                                </select>
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
                            <div>
                                <label>Total amount ($)</label>
                                <input type="number" name="total_amount" min="0.01" step="0.01" placeholder="2000.00"
                                    required id="nc_total" />
                            </div>
                            <div>
                                <label>Number of monthly payments</label>
                                <input type="number" name="payment_months" min="1" max="120" placeholder="10" required
                                    id="nc_months" />
                            </div>
                            <div>
                                <label>Monthly (preview)</label>
                                <input type="text" id="nc_preview" readonly placeholder="Auto" style="opacity:.7;" />
                            </div>
                            <div>
                                <label>First payment date</label>
                                <input type="date" name="first_payment_date" required
                                    value="{{ now()->addMonth()->format('Y-m-d') }}" />
                            </div>
                            <div class="full">
                                <label>Notes</label>
                                <input type="text" name="notes" placeholder="Optional notes" />
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">Add Contract</button>
                    </form>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
    <script>
        (function(){
        const t = document.getElementById('nc_total');
        const m = document.getElementById('nc_months');
        const p = document.getElementById('nc_preview');
        function calc(){ const tv=parseFloat(t.value),mv=parseInt(m.value); p.value=(tv>0&&mv>0)?'$'+(tv/mv).toFixed(2)+' / mo':''; }
        t.addEventListener('input',calc); m.addEventListener('input',calc);
    })();
    </script>
</body>

</html>