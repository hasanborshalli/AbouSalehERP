<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoices</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/confirmModal.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/invoicesManage.css" />
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

            <section class="invoices" aria-label="Invoices page">
                <section class="dashboard-card invoices__card">
                    <header class="invoices__header">
                        <div class="invoices__header-left">
                            <h2 class="invoices__title">Manage invoices</h2>

                            <div class="invoices__search" role="search">
                                <img class="invoices__search-ico" src="/img/search.svg" alt="" aria-hidden="true">
                                <input id="invSearch" type="text"
                                    placeholder="Search by invoice #, client, phone, unit..." />
                            </div>
                        </div>

                        <div class="invoices__header-right">
                            <select id="invStatus" class="invoices__filter" aria-label="Filter by status">
                                <option value="all" selected>All</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                    </header>

                    <div class="invoices__layout">
                        {{-- Left table --}}
                        <div class="invoices__table-wrap" aria-label="Invoices table">
                            <table class="invoices__table">
                                <thead class="invoices__thead">
                                    <tr>
                                        <th class="invoices__th">Invoice #</th>
                                        <th class="invoices__th">Client</th>
                                        <th class="invoices__th">Unit</th>
                                        <th class="invoices__th">Issue date</th>
                                        <th class="invoices__th">Due date</th>
                                        <th class="invoices__th">Amount</th>
                                        <th class="invoices__th">Status</th>
                                        <th class="invoices__th invoices__th--actions">Actions</th>
                                    </tr>
                                </thead>

                                <tbody id="invTbody" class="invoices__tbody">
                                    @foreach($invoices as $inv)
                                    @php
                                    $contract = $inv->contract;
                                    $client = $contract?->client;
                                    $unit = $contract?->apartment?->unit_number ?? $contract?->apartment?->unit_code ??
                                    '-';
                                    $project = $contract?->project?->name ?? '-';
                                    $status = $inv->status;
                                    @endphp

                                    <tr class="invoices__row" data-id="{{ $inv->id }}"
                                        data-invoice-number="{{ $inv->invoice_number }}"
                                        data-client="{{ $client?->name ?? '-' }}"
                                        data-phone="{{ $client?->phone ?? '-' }}"
                                        data-email="{{ $client?->email ?? '-' }}" data-unit="{{ $unit }}"
                                        data-project="{{ $project }}" data-amount="{{ $inv->amount }}"
                                        data-status="{{ $status }}" data-issue="{{ $inv->issue_date }}"
                                        data-due="{{ $inv->due_date }}" data-late-fee="{{ $inv->late_fee_amount ?? 0 }}"
                                        data-contract-id="{{ $contract?->id }}"
                                        data-contract-date="{{ $contract?->contract_date }}"
                                        data-payment-start="{{ $contract?->payment_start_date }}"
                                        data-contract-pdf="{{ $contract?->pdf_path }}"
                                        data-invoice-pdf="{{ $inv->pdf_path }}"
                                        data-receipt-pdf="{{ $inv->receipt_path }}">
                                        <td class="invoices__td invoices__td--strong">{{ $inv->invoice_number }}</td>
                                        <td class="invoices__td">{{ $client?->name ?? '-' }}</td>
                                        <td class="invoices__td">{{ $unit }}</td>
                                        <td class="invoices__td">{{ $inv->issue_date }}</td>
                                        <td class="invoices__td">{{ $inv->due_date }}</td>
                                        @php
                                        $lateFee = (float) ($inv->late_fee_amount ?? 0);
                                        $totalDue = (float) $inv->amount + $lateFee;
                                        @endphp
                                        <td class="invoices__td">
                                            @if($status === 'overdue' && $lateFee > 0)
                                            ${{ number_format($totalDue, 2) }}
                                            <div style="font-size:12px; opacity:.75;">Late fee: ${{
                                                number_format($lateFee, 2) }}</div>
                                            @else
                                            ${{ number_format($inv->amount, 2) }}
                                            @endif
                                        </td>

                                        <td class="invoices__td">
                                            <span class="invoices__status invoices__status--{{ $status }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>

                                        <td class="invoices__td invoices__td--actions">
                                            <button class="invoices__btn invoices__btn--view"
                                                type="button">View</button>
                                            @if($status !== 'paid')
                                            <button class="invoices__icon-btn invoices__icon-btn--edit" type="button"
                                                aria-label="Edit dates">
                                                ✎
                                            </button>


                                            <button class="invoices__icon-btn invoices__icon-btn--paid" type="button"
                                                aria-label="Mark paid">
                                                ✔
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Right details panel --}}
                        <aside class="invoices__details" aria-label="Invoice details">
                            <div class="invoices__details-head">
                                <h3 class="invoices__details-title">Invoice details</h3>
                                <div class="invoices__details-sub" id="invDetailsTitle">Select an invoice</div>
                            </div>

                            <div class="invoices__details-body" id="invDetailsBody">
                                <div class="invoices__empty">Click “View” to see details here.</div>
                            </div>
                        </aside>
                    </div>
                </section>
            </section>

            {{-- Edit dates modal --}}
            <div class="confirm-modal" id="editDatesModal" aria-hidden="true">
                <div class="confirm-modal__backdrop"></div>

                <div class="confirm-modal__box">
                    <h3 class="confirm-modal__title">Edit invoice dates</h3>

                    <div class="inv-modal__grid">
                        <div class="inv-modal__field">
                            <label class="inv-modal__label">Issue date</label>
                            <input class="inv-modal__input" type="date" id="editIssueDate">
                        </div>

                        <div class="inv-modal__field">
                            <label class="inv-modal__label">Due date</label>
                            <input class="inv-modal__input" type="date" id="editDueDate">
                        </div>
                    </div>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel" id="editCancelBtn">
                            Cancel
                        </button>

                        <button type="button" class="confirm-modal__btn confirm-modal__btn--danger" id="editSaveBtn">
                            Save
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mark paid modal --}}
            <div class="confirm-modal" id="markPaidModal" aria-hidden="true">
                <div class="confirm-modal__backdrop"></div>

                <div class="confirm-modal__box">
                    <h3 class="confirm-modal__title">Mark invoice as paid</h3>
                    <p class="confirm-modal__text">
                        This will set status to <strong>paid</strong>.
                    </p>

                    <div class="inv-modal__grid">
                        <div class="inv-modal__field inv-modal__field--wide">
                            <label class="inv-modal__label">Paid date (optional)</label>
                            <input class="inv-modal__input" type="date" id="paidDate">
                        </div>
                    </div>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel" id="paidCancelBtn">
                            Cancel
                        </button>

                        <button type="button" class="confirm-modal__btn confirm-modal__btn--danger" id="paidConfirmBtn">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>

        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="/js/invoicesManage.js"></script>
    <script src="/js/navSearch.js"></script>

</body>

</html>