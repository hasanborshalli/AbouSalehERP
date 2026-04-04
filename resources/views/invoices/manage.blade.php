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
    <link rel="stylesheet" href="/css/responsive.css" />
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
                                    placeholder="Search by invoice #, client, phone, unit..."
                                    value="{{ $search ?? '' }}" />
                            </div>
                        </div>

                        <div class="invoices__header-right">
                            <select id="invStatus" class="invoices__filter" aria-label="Filter by status">
                                <option value="all" {{ ($status ?? 'all' )==='all' ? 'selected' : '' }}>All</option>
                                <option value="pending" {{ ($status ?? '' )==='pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="paid" {{ ($status ?? '' )==='paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ ($status ?? '' )==='overdue' ? 'selected' : '' }}>Overdue
                                </option>
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
                                        data-receipt-pdf="{{ $inv->receipt_path }}"
                                        data-payment-type="{{ $inv->payment_type ?? 'cash' }}">
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
                                <div class="invoices__empty">Click "View" to see details here.</div>
                            </div>
                        </aside>
                        {{-- Pagination --}}
                        {{ $invoices->links('vendor.pagination.custom') }}
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

                <div class="confirm-modal__box confirm-modal__box--wide">
                    <h3 class="confirm-modal__title">Mark invoice as paid</h3>

                    {{-- Payment type toggle --}}
                    <div class="inv-modal__type-toggle">
                        <button type="button" class="inv-modal__type-btn inv-modal__type-btn--active" id="typeCashBtn">
                            💵 Cash Payment
                        </button>
                        <button type="button" class="inv-modal__type-btn" id="typeInKindBtn">
                            📦 In-Kind (Inventory)
                        </button>
                    </div>

                    {{-- CASH SECTION --}}
                    <div id="cashSection">
                        <p class="confirm-modal__text">This will set status to <strong>paid</strong>.</p>

                        <div class="inv-modal__grid">
                            <div class="inv-modal__field inv-modal__field--wide">
                                <label class="inv-modal__label">Paid date (optional)</label>
                                <input class="inv-modal__input" type="date" id="paidDate">
                            </div>

                            <div class="inv-modal__field inv-modal__field--wide">
                                <label class="inv-modal__label">
                                    Amount received
                                    <span id="amountPaidHint"
                                        style="margin-left:6px; opacity:.55; font-size:12px; font-weight:400;"></span>
                                </label>
                                <input class="inv-modal__input" type="number" id="amountPaidInput" min="0" step="0.01"
                                    placeholder="Enter amount received from client">
                                {{-- Live feedback shown below the input --}}
                                <div id="amountPaidDiff"
                                    style="margin-top:8px; font-size:13px; font-weight:600; min-height:18px; line-height:1.4;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- IN-KIND SECTION --}}
                    <div id="inKindSection" style="display:none;">
                        <p class="confirm-modal__text">
                            Client pays by <strong>delivering inventory items</strong> to the company.
                            Items will be <strong>added to stock</strong>. Enter any amount — you agree the deal
                            face-to-face.
                        </p>

                        <div id="inkindRows">
                            {{-- Item rows injected by JS --}}
                        </div>

                        <button type="button" class="inv-modal__add-row-btn" id="addInKindRowBtn">
                            + Add item
                        </button>

                        <div class="inkind-summary">
                            <span>Items total value: <strong id="inkindItemsTotal">$0.00</strong></span>
                        </div>

                        <div class="inv-modal__field" style="margin-top:10px;">
                            <label class="inv-modal__label">Notes (optional)</label>
                            <input type="text" class="inv-modal__input" id="inkindPaymentNotes"
                                placeholder="e.g. 10 tons steel rebar received at warehouse">
                        </div>
                    </div>

                    {{-- CONFIRMATION STEP (shown instead of above before final submit) --}}
                    <div id="inkindConfirmStep" style="display:none;">
                        <div class="inkind-confirm-header">📋 Please confirm the following items:</div>
                        <table class="inkind-confirm-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody id="inkindConfirmBody"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align:right; font-weight:700;">Total value:</td>
                                    <td id="inkindConfirmTotal" style="font-weight:700;"></td>
                                </tr>
                            </tfoot>
                        </table>
                        <p class="inkind-confirm-note">These items will be <strong>added to stock</strong>. This action
                            cannot be undone.</p>
                    </div>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel" id="inkindBackBtn"
                            style="display:none;">
                            ← Back
                        </button>
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
    <script>
        // Fix: before the paidConfirmBtn submits, ensure in-kind item fields
    // are not disabled and have correct names so they reach the server.
    document.addEventListener('DOMContentLoaded', function () {
        var confirmBtn = document.getElementById('paidConfirmBtn');
        if (!confirmBtn) return;

        confirmBtn.addEventListener('click', function (e) {
            var paymentType = document.querySelector('input[name="payment_type"]:checked')
                           || { value: 'cash' };
            var isInKind = (paymentType.value === 'in_kind')
                        || document.getElementById('inKindSection')?.style.display !== 'none';
            if (!isInKind) return;

            // Re-enable any disabled inputs in inkindRows before submit
            var container = document.getElementById('inkindRows');
            if (!container) return;
            container.querySelectorAll('input[disabled], select[disabled]').forEach(function (el) {
                el.disabled = false;
            });

            // Ensure each row has the correct index-based names
            var rows = container.querySelectorAll('[data-ik-row]');
            rows.forEach(function (row, idx) {
                row.querySelectorAll('[data-ik-field]').forEach(function (el) {
                    var field = el.dataset.ikField;
                    el.name = 'items[' + idx + '][' + field + ']';
                });
            });
        }, true); // capture phase so it runs before invoicesManage.js handler
    });
    </script>

</body>

</html>