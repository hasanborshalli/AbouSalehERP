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

                            {{-- Managed Properties --}}
                            <div style="margin-top:16px;">
                                <h4 class="add-client__section-title" style="font-size:12px;margin-bottom:8px;">Managed
                                    Properties</h4>
                                @if($managedProperties->isEmpty())
                                <p style="font-size:12px;color:rgba(0,0,0,.4);margin:0;">No managed properties
                                    available.</p>
                                @else
                                <div class="assign-panel" style="margin-top:0;">
                                    @foreach($managedProperties as $mp)
                                    <div class="assign-row" data-mp-row="{{ $mp->id }}">
                                        <label class="assign-check">
                                            <input type="checkbox" class="mp-cb" data-mp-id="{{ $mp->id }}"
                                                value="{{ $mp->id }}">
                                            <span class="assign-label">
                                                {{ $mp->address }}
                                                @if($mp->city) <span style="opacity:.45;font-size:11px;">({{ $mp->city
                                                    }})</span> @endif
                                                <span
                                                    style="opacity:.4;font-size:11px;font-weight:400;margin-left:4px;">{{
                                                    ucfirst($mp->type) }} · {{ ucfirst($mp->status) }}</span>
                                            </span>
                                        </label>
                                        <input type="hidden" name="managed_property_ids[]" value="{{ $mp->id }}"
                                            class="mp-id-hidden" disabled>
                                        <div class="assign-cost">
                                            <span class="assign-cost__symbol">$</span>
                                            <input type="number" class="assign-cost__input cost-input"
                                                name="managed_property_costs[{{ $mp->id }}]" min="0.01" step="0.01"
                                                placeholder="Cost">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
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
        document.addEventListener('DOMContentLoaded', function () {

        // ── Helpers ──────────────────────────────────────────────────────
        function recalcTotal() {
            var total = 0;
            document.querySelectorAll('.cost-input').forEach(function (inp) {
                if (!inp.disabled && inp.value) {
                    total += parseFloat(inp.value) || 0;
                }
            });
            var totalBar   = document.getElementById('totalBar');
            var totalDisp  = document.getElementById('totalDisplay');
            var totalField = document.getElementById('total_amount');
            if (totalBar)  totalBar.style.display  = total > 0 ? 'flex' : 'none';
            if (totalDisp) totalDisp.textContent    = '$' + total.toFixed(2);
            if (totalField && total > 0) totalField.value = total.toFixed(2);
            recalcMonthly();
        }

        function recalcMonthly() {
            var total   = parseFloat(document.getElementById('total_amount')?.value) || 0;
            var months  = parseInt(document.getElementById('payment_months')?.value) || 0;
            var preview = document.getElementById('monthly_preview');
            if (preview) {
                preview.value = (total > 0 && months > 0)
                    ? '$' + (total / months).toFixed(2) + ' / month'
                    : '';
            }
        }

        // ── Project checkbox: disable its apartments when checked ─────────
        document.querySelectorAll('.proj-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var projectId  = this.dataset.projectId;
                var checked    = this.checked;
                var hiddenProj = this.closest('.assign-row').querySelector('.proj-id-hidden');
                var costDiv    = this.closest('.assign-row').querySelector('.assign-cost');

                if (hiddenProj) hiddenProj.disabled = !checked;
                if (costDiv) {
                    costDiv.style.display = checked ? 'flex' : 'none';
                    var costInput = costDiv.querySelector('.cost-input');
                    if (costInput) {
                        costInput.disabled = !checked;
                        if (!checked) costInput.value = '';
                    }
                }

                document.querySelectorAll('.assign-row--apt[data-parent="' + projectId + '"]').forEach(function (aptRow) {
                    var aptCb     = aptRow.querySelector('.apt-cb');
                    var aptHidden = aptRow.querySelector('.apt-id-hidden');
                    var aptCost   = aptRow.querySelector('.assign-cost');
                    var aptInput  = aptRow.querySelector('.cost-input');

                    if (checked) {
                        aptRow.style.opacity  = '0.35';
                        aptRow.style.pointerEvents = 'none';
                        if (aptCb)     { aptCb.checked = false; aptCb.disabled = true; }
                        if (aptHidden) aptHidden.disabled = true;
                        if (aptCost)   aptCost.style.display = 'none';
                        if (aptInput)  { aptInput.disabled = true; aptInput.value = ''; }
                    } else {
                        aptRow.style.opacity  = '';
                        aptRow.style.pointerEvents = '';
                        if (aptCb)    aptCb.disabled = false;
                    }
                });

                recalcTotal();
            });
        });

        // ── Apartment checkbox ────────────────────────────────────────────
        document.querySelectorAll('.apt-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var checked    = this.checked;
                var aptRow     = this.closest('.assign-row--apt');
                var aptHidden  = aptRow.querySelector('.apt-id-hidden');
                var costDiv    = aptRow.querySelector('.assign-cost');
                var costInput  = aptRow.querySelector('.cost-input');

                if (aptHidden) aptHidden.disabled = !checked;
                if (costDiv)   costDiv.style.display = checked ? 'flex' : 'none';
                if (costInput) {
                    costInput.disabled = !checked;
                    if (!checked) costInput.value = '';
                }

                recalcTotal();
            });
        });

        // ── Managed Property checkbox ─────────────────────────────────────
        document.querySelectorAll('.mp-cb').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var checked   = this.checked;
                var row       = this.closest('[data-mp-row]');
                var mpHidden  = row.querySelector('.mp-id-hidden');
                var costDiv   = row.querySelector('.assign-cost');
                var costInput = costDiv?.querySelector('.cost-input');

                if (mpHidden)  mpHidden.disabled  = !checked;
                if (costDiv)   costDiv.style.display = checked ? 'flex' : 'none';
                if (costInput) {
                    costInput.disabled = !checked;
                    if (!checked) costInput.value = '';
                }

                recalcTotal();
            });
        });

        // ── Cost input changes ────────────────────────────────────────────
        document.querySelectorAll('.cost-input').forEach(function (inp) {
            inp.addEventListener('input', recalcTotal);
        });

        // ── Manual total / months change ──────────────────────────────────
        document.getElementById('total_amount')?.addEventListener('input', recalcMonthly);
        document.getElementById('payment_months')?.addEventListener('input', recalcMonthly);

        // ── Init: hide all cost inputs ────────────────────────────────────
        document.querySelectorAll('.assign-cost').forEach(function (div) {
            div.style.display = 'none';
        });
    });
    </script>
</body>

</html>