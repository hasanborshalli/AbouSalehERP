<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Worker</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/addClient.css">
    <style>
        /* ─── Assignment selector ─── */
        .assign-panel {
            border: 2px solid rgba(0, 0, 0, .07);
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255, 255, 255, .6);
        }

        .assign-panel__head {
            background: rgba(0, 0, 0, .03);
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            color: rgba(0, 0, 0, .5);
            text-transform: uppercase;
            border-bottom: 2px solid rgba(0, 0, 0, .06);
        }

        .assign-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            transition: background .15s;
        }

        .assign-row:last-child {
            border-bottom: none;
        }

        .assign-row:hover {
            background: rgba(42, 127, 176, .04);
        }

        .assign-row--apt {
            padding-left: 36px;
            background: rgba(0, 0, 0, .01);
        }

        .assign-row--apt .assign-label {
            font-size: 12.5px;
            color: rgba(0, 0, 0, .65);
        }

        .assign-row--apt.is-disabled {
            opacity: .38;
            pointer-events: none;
        }

        .assign-check {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            color: rgba(0, 0, 0, .8);
            user-select: none;
        }

        .assign-check input[type=checkbox] {
            width: 16px;
            height: 16px;
            accent-color: rgba(42, 127, 176, .9);
            cursor: pointer;
            flex-shrink: 0;
        }

        .assign-cost {
            display: none;
            align-items: center;
            gap: 6px;
        }

        .assign-cost.is-visible {
            display: flex;
        }

        .assign-cost__symbol {
            font-size: 13px;
            font-weight: 700;
            color: rgba(42, 127, 176, .8);
        }

        .assign-cost__input {
            width: 120px;
            padding: 5px 10px;
            border: 2px solid rgba(42, 127, 176, .25);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(0, 0, 0, .8);
            outline: none;
            transition: border-color .15s;
        }

        .assign-cost__input:focus {
            border-color: rgba(42, 127, 176, .6);
        }

        .assign-total-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            padding: 10px 16px;
            background: rgba(42, 127, 176, .07);
            border-radius: 10px;
            border: 2px solid rgba(42, 127, 176, .15);
        }

        .assign-total-bar__label {
            font-size: 13px;
            font-weight: 700;
            color: rgba(0, 0, 0, .6);
        }

        .assign-total-bar__value {
            font-size: 18px;
            font-weight: 800;
            color: rgba(42, 127, 176, .9);
        }

        .assign-empty {
            padding: 16px;
            text-align: center;
            opacity: .4;
            font-size: 13px;
        }

        /* ─── Submit button ─── */
        .wrk-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 999px;
            border: none;
            background: rgba(42, 127, 176, .9);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s, transform .1s;
            box-shadow: 0 4px 16px rgba(42, 127, 176, .25);
        }

        .wrk-submit-btn:hover {
            background: rgba(42, 127, 176, 1);
            transform: translateY(-1px);
        }

        .wrk-submit-btn:active {
            transform: translateY(0);
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
            <section class="add-client">
                <section class="dashboard-card add-client__card">
                    <header class="add-client__header">
                        <h2 class="add-client__title">Add new worker / contractor</h2>
                        <a href="{{ route('workers.index') }}" class="add-client__back">← Back</a>
                    </header>

                    <form class="add-client__form" action="{{ route('workers.store') }}" method="post" id="workerForm">
                        @csrf

                        @if(session('error'))
                        <div class="alert alert--error">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                        <div class="alert alert--error">
                            <ul style="margin:0;padding-left:16px;">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- Worker info --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Worker information</h3>
                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="name">Full name</label>
                                    <input class="add-client__input" id="name" name="name" type="text"
                                        placeholder="Worker full name" required value="{{ old('name') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="phone">Phone</label>
                                    <input class="add-client__input" id="phone" name="phone" type="tel"
                                        placeholder="+961 ..." required value="{{ old('phone') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="email">Email (for portal access)</label>
                                    <input class="add-client__input" id="email" name="email" type="email"
                                        placeholder="worker@email.com" required value="{{ old('email') }}" />
                                </div>
                            </div>
                        </section>

                        {{-- Contract info --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Contract information</h3>
                            <div class="add-client__grid">
                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="scope_of_work">Scope of work</label>
                                    <input class="add-client__input" id="scope_of_work" name="scope_of_work" type="text"
                                        placeholder="e.g. Electricity wiring – Floors 1–5" required
                                        value="{{ old('scope_of_work') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="category">Category</label>
                                    <input class="add-client__input" id="category" name="category" type="text"
                                        placeholder="e.g. electrical, plumbing" value="{{ old('category') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="contract_date">Contract date</label>
                                    <input class="add-client__input" id="contract_date" name="contract_date" type="date"
                                        required value="{{ old('contract_date') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="start_date">Start date</label>
                                    <input class="add-client__input" id="start_date" name="start_date" type="date"
                                        value="{{ old('start_date') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="expected_end_date">Expected end date</label>
                                    <input class="add-client__input" id="expected_end_date" name="expected_end_date"
                                        type="date" value="{{ old('expected_end_date') }}" />
                                </div>
                            </div>
                        </section>

                        {{-- Project & Apartment assignment with per-item costs --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Project & Apartment Assignment</h3>
                            <p style="font-size:13px; color:rgba(0,0,0,.5); margin-bottom:14px;">
                                Check a project to assign it and enter its cost. Checking a project disables its
                                individual apartments (they're covered by the project cost). You can also assign
                                standalone apartments directly.
                            </p>

                            @if($projects->isEmpty())
                            <p class="assign-empty">No projects found. You can still create the contract without
                                assignment.</p>
                            @else
                            <div class="assign-panel" id="assignPanel">
                                @foreach($projects as $project)
                                {{-- Project row --}}
                                <div class="assign-row" data-project-row="{{ $project->id }}">
                                    <label class="assign-check">
                                        <input type="checkbox" class="proj-cb" data-project-id="{{ $project->id }}"
                                            value="{{ $project->id }}">
                                        <span class="assign-label">
                                            {{ $project->name }}
                                            @if($project->code) <span style="opacity:.5; font-weight:400;">({{
                                                $project->code }})</span> @endif
                                            @if($project->apartments->count())
                                            <span
                                                style="opacity:.4; font-size:11px; font-weight:400; margin-left:4px;">{{
                                                $project->apartments->count() }} unit(s)</span>
                                            @endif
                                        </span>
                                    </label>
                                    {{-- Hidden field submits the project ID when checked; disabled when unchecked --}}
                                    <input type="hidden" name="project_ids[]" value="{{ $project->id }}"
                                        class="proj-id-hidden" disabled>
                                    <div class="assign-cost">
                                        <span class="assign-cost__symbol">$</span>
                                        <input type="number" class="assign-cost__input cost-input"
                                            name="project_costs[{{ $project->id }}]" min="0.01" step="0.01"
                                            placeholder="Project cost">
                                    </div>
                                </div>

                                {{-- Apartment rows under this project --}}
                                @foreach($project->apartments as $apt)
                                <div class="assign-row assign-row--apt" data-apt-row="{{ $apt->id }}"
                                    data-parent="{{ $project->id }}">
                                    <label class="assign-check">
                                        <input type="checkbox" class="apt-cb" data-apt-id="{{ $apt->id }}"
                                            data-parent="{{ $project->id }}" value="{{ $apt->id }}">
                                        <span class="assign-label">
                                            Unit {{ $apt->unit_number ?? 'Unit #'.$apt->id }}
                                            @if($apt->bedrooms) · {{ $apt->bedrooms }}BR @endif
                                        </span>
                                    </label>
                                    <input type="hidden" name="apartment_ids[]" value="{{ $apt->id }}"
                                        class="apt-id-hidden" disabled>
                                    <div class="assign-cost">
                                        <span class="assign-cost__symbol">$</span>
                                        <input type="number" class="assign-cost__input cost-input"
                                            name="apartment_costs[{{ $apt->id }}]" min="0.01" step="0.01"
                                            placeholder="Unit cost">
                                    </div>
                                </div>
                                @endforeach
                                @endforeach
                            </div>

                            <div class="assign-total-bar" id="totalBar" style="display:none;">
                                <span class="assign-total-bar__label">Contract total (from assignments)</span>
                                <span class="assign-total-bar__value" id="totalDisplay">$0.00</span>
                            </div>
                            @endif
                        </section>

                        {{-- Payment schedule --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Payment schedule</h3>
                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="total_amount">Total contract amount
                                        ($)</label>
                                    <input class="add-client__input" id="total_amount" name="total_amount" type="number"
                                        min="0.01" step="0.01"
                                        placeholder="Auto-filled from assignments above, or enter manually"
                                        value="{{ old('total_amount') }}" />
                                    <p style="font-size:11px; opacity:.5; margin-top:4px;">Auto-calculated from
                                        assignments. You can override if no assignments.</p>
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="payment_months">Number of monthly
                                        payments</label>
                                    <input class="add-client__input" id="payment_months" name="payment_months"
                                        type="number" min="1" max="120" placeholder="e.g. 10" required
                                        value="{{ old('payment_months') }}" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label">Monthly amount (auto-calculated)</label>
                                    <input class="add-client__input" id="monthly_preview" type="text" readonly
                                        placeholder="Fill total + months above" style="opacity:.7;" />
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="first_payment_date">First payment date</label>
                                    <input class="add-client__input" id="first_payment_date" name="first_payment_date"
                                        type="date" required value="{{ old('first_payment_date') }}" />
                                </div>
                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="notes">Notes</label>
                                    <input class="add-client__input" id="notes" name="notes" type="text"
                                        placeholder="Any additional notes..." value="{{ old('notes') }}" />
                                </div>
                            </div>
                        </section>

                        <div class="add-client__actions" style="margin-top:24px;">
                            <button type="submit" class="wrk-submit-btn">
                                ✦ Create worker &amp; generate contract
                            </button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>
    <script>
        (function(){
        // ── Assignment selector logic ────────────────────────────────────────
        const totalInput   = document.getElementById('total_amount');
        const monthsInput  = document.getElementById('payment_months');
        const preview      = document.getElementById('monthly_preview');
        const totalDisplay = document.getElementById('totalDisplay');
        const totalBar     = document.getElementById('totalBar');

        function sumAllCosts() {
            let sum = 0;
            document.querySelectorAll('.cost-input').forEach(inp => {
                const v = parseFloat(inp.value);
                if (!isNaN(v) && v > 0) sum += v;
            });
            return sum;
        }

        function refreshTotal() {
            const sum = sumAllCosts();
            if (sum > 0) {
                totalDisplay.textContent = '$' + sum.toFixed(2);
                totalBar.style.display = 'flex';
                totalInput.value = sum.toFixed(2);
            } else {
                totalBar.style.display = 'none';
                totalInput.value = '';
            }
            refreshPreview();
        }

        function refreshPreview() {
            const t = parseFloat(totalInput.value);
            const m = parseInt(monthsInput.value);
            preview.value = (t > 0 && m > 0) ? '$' + (t / m).toFixed(2) + ' / month' : '';
        }

        // Project checkbox logic
        document.querySelectorAll('.proj-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const pid    = this.dataset.projectId;
                const costEl = this.closest('.assign-row').querySelector('.assign-cost');
                const costInp = costEl.querySelector('input[type=number]');
                const aptRows = document.querySelectorAll(`.assign-row--apt[data-parent="${pid}"]`);

                if (this.checked) {
                    // Enable + show project cost input
                    costInp.disabled = false;
                    costEl.classList.add('is-visible');
                    this.closest('.assign-row').querySelector('.proj-id-hidden').disabled = false;
                    // Disable + uncheck all apartments under this project
                    aptRows.forEach(row => {
                        const aptCb   = row.querySelector('.apt-cb');
                        const aptCost = row.querySelector('.assign-cost');
                        const aptInp  = aptCost.querySelector('input[type=number]');
                        const aptHid  = row.querySelector('.apt-id-hidden');
                        row.classList.add('is-disabled');
                        aptCb.checked = false;
                        aptCost.classList.remove('is-visible');
                        if (aptInp) { aptInp.value = ''; aptInp.disabled = true; }
                        if (aptHid) aptHid.disabled = true;
                    });
                } else {
                    // Disable + hide project cost
                    costInp.disabled = true;
                    costInp.value = '';
                    costEl.classList.remove('is-visible');
                    this.closest('.assign-row').querySelector('.proj-id-hidden').disabled = true;
                    // Re-enable apartments
                    aptRows.forEach(row => row.classList.remove('is-disabled'));
                }
                refreshTotal();
            });
        });

        // Apartment checkbox logic
        document.querySelectorAll('.apt-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const costEl = this.closest('.assign-row').querySelector('.assign-cost');
                const inp = costEl.querySelector('input[type=number]');
                if (this.checked) {
                    inp.disabled = false;
                    costEl.classList.add('is-visible');
                    const hid = this.closest('.assign-row').querySelector('.apt-id-hidden');
                    if (hid) hid.disabled = false;
                } else {
                    inp.disabled = true;
                    inp.value = '';
                    costEl.classList.remove('is-visible');
                    const hid = this.closest('.assign-row').querySelector('.apt-id-hidden');
                    if (hid) hid.disabled = true;
                }
                refreshTotal();
            });
        });

        // Disable ALL cost inputs on page load (only enabled when checkbox is checked)
        document.querySelectorAll('.cost-input').forEach(inp => inp.disabled = true);

        // Re-calc when cost inputs change
        document.querySelectorAll('.cost-input').forEach(inp => {
            inp.addEventListener('input', refreshTotal);
        });

        // Manual total + months → preview
        totalInput.addEventListener('input', refreshPreview);
        monthsInput.addEventListener('input', refreshPreview);

        refreshTotal();
    })();
    </script>
</body>

</html>