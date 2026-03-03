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

                    <form class="add-client__form" action="{{ route('workers.store') }}" method="post">
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
                                <div class="add-client__field" style="grid-column:span 2;">
                                    <label class="add-client__label">Projects (optional — hold Ctrl/Cmd to select
                                        multiple)</label>
                                    <select class="add-client__select" name="project_ids[]" multiple size="4"
                                        style="height:auto; padding:6px 10px;">
                                        @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ in_array($p->id, old('project_ids', [])) ?
                                            'selected' : '' }}>
                                            {{ $p->name }}{{ $p->code ? ' ('.$p->code.')' : '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="add-client__field" style="grid-column:span 2;">
                                    <label class="add-client__label">Apartments (optional — hold Ctrl/Cmd to select
                                        multiple)</label>
                                    <select class="add-client__select" name="apartment_ids[]" multiple size="4"
                                        style="height:auto; padding:6px 10px;">
                                        @foreach($apartments as $apt)
                                        <option value="{{ $apt->id }}" {{ in_array($apt->id, old('apartment_ids', [])) ?
                                            'selected' : '' }}>
                                            {{ $apt->project?->name ? $apt->project->name.' — ' : '' }}{{
                                            $apt->unit_number ?? $apt->unit_code ?? 'Unit #'.$apt->id }}
                                        </option>
                                        @endforeach
                                    </select>
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

                        {{-- Payment schedule --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Payment schedule</h3>
                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="total_amount">Total contract amount
                                        ($)</label>
                                    <input class="add-client__input" id="total_amount" name="total_amount" type="number"
                                        min="0.01" step="0.01" placeholder="e.g. 2000.00" required
                                        value="{{ old('total_amount') }}" id="totalInput" />
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
                            <button type="submit" class="add-client__submit">Create worker &amp; generate
                                contract</button>
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
        const total   = document.getElementById('total_amount');
        const months  = document.getElementById('payment_months');
        const preview = document.getElementById('monthly_preview');
        function calc() {
            const t = parseFloat(total.value), m = parseInt(months.value);
            if (t > 0 && m > 0) {
                preview.value = '$' + (t / m).toFixed(2) + ' / month';
            } else {
                preview.value = '';
            }
        }
        total.addEventListener('input', calc);
        months.addEventListener('input', calc);
        calc();
    })();
    </script>
</body>

</html>