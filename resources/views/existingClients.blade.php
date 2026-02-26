<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clients</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/confirmModal.css">
    {{-- page specific --}}
    <link rel="stylesheet" href="/css/existingClients.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            @if (session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">X</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            <section class="clients-index" aria-label="Clients page">

                <section class="dashboard-card clients-index__card">
                    <header class="clients-index__header">
                        <div class="clients-index__header-left">
                            <h2 class="clients-index__title">Existing clients</h2>

                            <div class="clients-index__search" role="search">
                                <img class="clients-index__search-ico" src="/img/search.svg" alt="" aria-hidden="true">
                                <input id="clientsSearch" type="text"
                                    placeholder="Search by name, phone, apartment..." />
                            </div>
                        </div>

                        <div class="clients-index__header-right">
                            <select id="clientsStatus" class="clients-index__filter" aria-label="Filter by status">
                                <option value="all" selected>All</option>
                                <option value="active">Active</option>
                                <option value="late">Late</option>
                                <option value="completed">Completed</option>
                            </select>

                            <a href="{{ route('clients.add-client') ?? '#' }}" class="clients-index__add-btn">Add
                                client</a>
                        </div>
                    </header>

                    <div class="clients-index__layout">
                        {{-- Left: table --}}
                        <div class="clients-index__table-wrap" aria-label="Clients table">
                            <table class="clients-index__table">
                                <thead class="clients-index__thead">
                                    <tr>
                                        <th data-key="code" class="clients-index__th is-sortable">Code <span
                                                class="clients-index__sort"></span></th>
                                        <th data-key="name" class="clients-index__th is-sortable">Client <span
                                                class="clients-index__sort"></span></th>
                                        <th data-key="phone" class="clients-index__th">Phone</th>
                                        <th data-key="apt" class="clients-index__th">Apartment</th>
                                        <th data-key="total" class="clients-index__th is-sortable">Total <span
                                                class="clients-index__sort"></span></th>
                                        <th data-key="remaining" class="clients-index__th is-sortable">Remaining <span
                                                class="clients-index__sort"></span></th>
                                        <th data-key="status" class="clients-index__th">Status</th>
                                        <th class="clients-index__th clients-index__th--actions">Actions</th>
                                    </tr>
                                </thead>

                                <tbody id="clientsTbody" class="clients-index__tbody">

                                    @foreach($clients as $client) @php $contract=$client->
                                    contract;
                                    $user=$client->user;
                                    $paidInvoices = $contract
                                    ? $contract->invoices()
                                    ->where('status', 'paid')
                                    ->sum('amount')
                                    : 0;

                                    $downPayment = $contract->down_payment ?? 0;
                                    $paid = max(0, $paidInvoices + $downPayment);
                                    $paidMonths=$contract?->invoices?->where('status','paid')->count() ??0;
                                    $lateMonths = $contract?->invoices()->where('status', 'overdue')->count() ?? 0;
                                    $remainingMonths=$contract?->invoices?->where('status','pending')->count()??0;
                                    $remaining = max(0, $contract->final_price - $paid);
                                    $nextdue=$contract?->nextPendingInvoice?->issue_date;
                                    $totalLateFeesPaid = $contract
                                    ? $contract->invoices()->where('status', 'paid')->sum('late_fee_amount')
                                    : 0;
                                    $totalLateFeesApplied = $contract
                                    ? $contract->invoices()->where('status', 'overdue')->sum('late_fee_amount')
                                    : 0;
                                    if ($remaining <= 0) { $status='completed' ; } else { $status=$client->
                                        contract->invoices()
                                        ->where('status', 'overdue')
                                        ->exists()
                                        ? 'late'
                                        : 'active';
                                        }
                                        $contractPdfUrl = $contract?->pdf_path
                                        ? asset('storage/'.$contract->pdf_path)
                                        : null;
                                        @endphp <tr class="clients-index__row"
                                            data-route="{{ route('contracts.progress.editor',$contract->id) }}"
                                            data-code="CL-{{str_pad( $client->user_id,5,'0', STR_PAD_LEFT) }}"
                                            data-id="{{ $client->id }}" data-email="{{ $user->email }}"
                                            data-name="{{ $user->name }}" data-phone="{{ $user->phone }}"
                                            data-apt="{{ $contract?->apartment->unit_number }}"
                                            data-total="{{ $contract->total_price }}"
                                            data-discount="{{ $contract->discount }}"
                                            data-finalPrice="{{ $contract->final_price }}"
                                            data-remaining="{{ $remaining }}" data-status="{{ $status }}"
                                            data-project="{{ $contract->project->name }}"
                                            data-location="{{ $contract->project->city }}, {{ $contract->project->area }}, {{ $contract->project->address }}"
                                            data-contractdate="{{ $contract->contract_date }}"
                                            data-down="{{ $contract->down_payment }}"
                                            data-months="{{ $contract->installment_months }}"
                                            data-monthly="{{ $contract->installment_amount }}" data-paid="{{ $paid }}"
                                            data-paidmonths="{{ $paidMonths }}" data-late-months="{{ $lateMonths }}"
                                            data-late-fees-applied="{{ $totalLateFeesApplied }}"
                                            data-late-fees-paid="{{ $totalLateFeesPaid }}"
                                            data-remainingmonths="{{ $remainingMonths }}" data-nextdue="{{ $nextdue }}"
                                            data-notes="{{ $contract->notes }}"
                                            data-contract-pdf="{{ $contractPdfUrl }}">
                                            <td class="clients-index__td">CL-{{str_pad( $client->user_id,5,'0',
                                                STR_PAD_LEFT)
                                                }}
                                            </td>
                                            <td class="clients-index__td clients-index__td--strong">{{
                                                $user->name
                                                }}</td>
                                            <td class="clients-index__td">{{ $user->phone }}</td>
                                            <td class="clients-index__td">{{
                                                $contract?->apartment->unit_number
                                                }}
                                            </td>
                                            <td class="clients-index__td">${{
                                                number_format($contract->final_price)
                                                }}</td>
                                            <td class="clients-index__td">${{ number_format($remaining) }}</td>

                                            <td class="clients-index__td">
                                                <span
                                                    class="clients-index__status clients-index__status--{{ $status }}">
                                                    {{ ucfirst($status) }}
                                                </span>
                                            </td>

                                            <td class="clients-index__td clients-index__td--actions">
                                                <button class="clients-index__btn clients-index__btn--view"
                                                    type="button">
                                                    View
                                                </button>

                                                <a class="clients-index__icon-btn clients-index__icon-btn--edit"
                                                    href="{{ route('clients.edit-client',$user->id) }}"
                                                    aria-label="Edit client">✎</a>

                                                <button class="clients-index__icon-btn clients-index__icon-btn--delete"
                                                    type="button" aria-label="Delete client"
                                                    data-delete="{{$user->id }}">
                                                    🗑
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Right: details panel --}}
                        <aside class="clients-index__details" aria-label="Client details">
                            <div class="clients-index__details-head">
                                <h3 class="clients-index__details-title">Client details</h3>
                                <div class="clients-index__details-sub" id="detailsCode">Select a client</div>
                            </div>

                            <div class="clients-index__details-body" id="detailsBody">
                                <div class="clients-index__empty">
                                    Click “View” on any client to see details here.
                                </div>
                            </div>
                        </aside>
                    </div>
                </section>

            </section>
            <div class="confirm-modal" id="confirmModal">
                <div class="confirm-modal__backdrop"></div>

                <div class="confirm-modal__box">
                    <h3 class="confirm-modal__title">Delete Client</h3>
                    <p class="confirm-modal__text">
                        Are you sure you want to delete this Client?
                        <br>This action <strong>cannot be undone</strong>.
                    </p>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel"
                            onclick="closeConfirmModal()">
                            Cancel
                        </button>

                        <button type="button" class="confirm-modal__btn confirm-modal__btn--danger"
                            id="confirmDeleteBtn">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="/js/existingClients.js">
    </script>
    <script src="/js/navSearch.js"></script>

</body>

</html>