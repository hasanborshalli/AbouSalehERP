<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add client</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    {{-- page specific --}}
    <link rel="stylesheet" href="/css/addClient.css" />
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
            <section class="add-client" aria-label="Add client page">

                <section class="dashboard-card add-client__card">
                    <header class="add-client__header">
                        <h2 class="add-client__title">Add new client</h2>

                        <a href="{{ route('clients.overview') ?? '#' }}" class="add-client__back">
                            Back
                        </a>
                    </header>

                    <form class="add-client__form" action="{{ route('clients.createClient') }}" method="post">
                        @csrf
                        @if (session('error'))
                        <div class="alert alert--error" data-alert>
                            <span class="alert__icon">X</span>
                            <span class="alert__text">{{ session('error') }}</span>
                            <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                        </div>
                        @endif
                        @if ($errors->any())
                        <div class="alert alert--error" data-alert>
                            <span class="alert__icon">X</span>
                            <span class="alert__text">
                                Please fix the following errors:
                                <ul style="margin:6px 0 0 16px; padding:0;">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </span>
                            <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                        </div>
                        @endif
                        {{-- Client info --}}
                        <section class="add-client__section" aria-label="Client information">
                            <h3 class="add-client__section-title">Client information</h3>

                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="name">Full name</label>
                                    <input class="add-client__input" id="name" name="name" type="text"
                                        placeholder="Client full name" required value="{{ old('name') }}" />
                                    @error('name')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="phone">Phone number</label>
                                    <input class="add-client__input" id="phone" name="phone" type="tel"
                                        placeholder="+961 ..." required value="{{ old('phone') }}" />
                                    @error('phone')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="email">Email</label>
                                    <input class="add-client__input" id="email" name="email" type="email"
                                        placeholder="example@email.com" value="{{ old('email') }}" />
                                    @error('email')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>


                            </div>
                        </section>

                        {{-- Purchase info --}}
                        <section class="add-client__section" aria-label="Purchase information">
                            <h3 class="add-client__section-title">Apartment / purchase information</h3>

                            <div class="add-client__grid">
                                {{-- Apartment selector (will come from DB later) --}}
                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="apartment_id">Apartment</label>

                                    <select class="add-client__select" id="apartment_id" name="apartment_id" required
                                        value="{{ old('apartment_id') }}">
                                        <option value="" selected disabled>Select apartment</option>

                                        @foreach ($apartments as $apartment)
                                        <option value="{{ $apartment->id }}"
                                            data-project-name="{{ $apartment->project->name ?? '' }}"
                                            data-unit-number="{{ $apartment->unit_number ?? '' }}"
                                            data-location="{{ trim(($apartment->project->city ?? '') . ' ' . ($apartment->project->area ?? '')) }}"
                                            data-price="{{ $apartment->price_total ?? '' }}"
                                            data-floor="{{ $apartment->floor?->floor_number }}"
                                            data-area="{{ $apartment->area_sqm ?? '' }}"
                                            data-bedrooms="{{ $apartment->bedrooms ?? '' }}"
                                            data-bathrooms="{{ $apartment->bathrooms ?? '' }}"
                                            data-notes="{{ $apartment->notes ?? '' }}">
                                            {{ $apartment->unit_number ?? $apartment->unit_code }} — {{
                                            $apartment->project->name ?? '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('apartment_id')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Auto-filled fields (now editable, later you can lock them if you want) --}}
                                <div class="add-client__field">
                                    <label class="add-client__label" for="project_name">Project / Building</label>
                                    <input class="add-client__input" id="project_name" name="project_name" type="text"
                                        placeholder="Auto-filled from apartment" readonly
                                        value="{{ old(key: 'project_name') }}" />
                                    @error('project_name')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="unit_number">Unit / Apartment number</label>
                                    <input class="add-client__input" id="unit_number" name="unit_number" type="text"
                                        placeholder="Auto-filled from apartment" readonly
                                        value="{{ old('unit_number') }}" />
                                    @error('unit_number')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="location">Location</label>
                                    <input class="add-client__input" id="location" name="location" type="text"
                                        placeholder="Auto-filled from apartment" readonly
                                        value="{{ old('location') }}" />
                                    @error('location')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="add-client__field">
                                    <label class="add-client__label" for="apt_floor">Floor</label>
                                    <input class="add-client__input" id="apt_floor" type="text" readonly
                                        value="{{ old('apt_floor') }}" />
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="apt_area">Area (m²)</label>
                                    <input class="add-client__input" id="apt_area" type="text" readonly
                                        value="{{ old('apt_area') }}" />
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="apt_bedrooms">Bedrooms</label>
                                    <input class="add-client__input" id="apt_bedrooms" type="text" readonly
                                        value="{{ old('apt_bedrooms') }}" />
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="apt_bathrooms">Bathrooms</label>
                                    <input class="add-client__input" id="apt_bathrooms" type="text" readonly
                                        value="{{ old('apt_bathrooms') }}" />
                                </div>

                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="apt_notes">Apartment notes</label>
                                    <textarea class="add-client__textarea" id="apt_notes" rows="2"
                                        readonly>{{ old('apt_notes') }}</textarea>
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="contract_date">Contract date</label>
                                    <input class="add-client__input" id="contract_date" name="contract_date" type="date"
                                        value="{{ old('contract_date') }}" />
                                    @error('contract_date')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="total_price">Total price ($)</label>
                                    <input class="add-client__input" id="total_price" name="total_price" type="number"
                                        step="0.01" min="0" placeholder="0.00" required readonly
                                        value="{{ old('total_price') }}" />
                                    @error('total_price')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="discount">Discount ($) (optional)</label>
                                    <input class="add-client__input" id="discount" name="discount" type="number"
                                        step="0.01" min="0" placeholder="0.00" value="{{ old( 'discount') }}" />
                                    @error('discount')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>


                        {{-- Payment Type --}}
                        <section class="add-client__section" aria-label="Payment type">
                            <h3 class="add-client__section-title">Payment type</h3>

                            <div class="add-client__type-toggle">
                                <label class="add-client__type-option">
                                    <input type="radio" name="payment_type" value="cash" id="typeCash" checked>
                                    <span class="add-client__type-card" id="typeCashCard">
                                        <span class="add-client__type-icon">💵</span>
                                        <span class="add-client__type-label">Cash / Installments</span>
                                        <span class="add-client__type-desc">Client pays by cash, bank transfer, or
                                            monthly installments</span>
                                    </span>
                                </label>

                                <label class="add-client__type-option">
                                    <input type="radio" name="payment_type" value="in_kind" id="typeInKind">
                                    <span class="add-client__type-card" id="typeInKindCard">
                                        <span class="add-client__type-icon">📦</span>
                                        <span class="add-client__type-label">In-Kind (Inventory Items)</span>
                                        <span class="add-client__type-desc">Client pays by delivering inventory items
                                            (e.g. steel, cement)</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        {{-- In-Kind Items Section (shown when in_kind selected) --}}
                        <section class="add-client__section" id="inKindSection" style="display:none;"
                            aria-label="In-kind items">
                            <h3 class="add-client__section-title">Items to be received from client</h3>

                            <div class="add-client__field add-client__field--wide" style="margin-bottom:16px;">
                                <label class="add-client__label" for="in_kind_notes">In-kind agreement notes
                                    (optional)</label>
                                <textarea class="add-client__textarea" id="in_kind_notes" name="in_kind_notes" rows="2"
                                    placeholder="Describe the in-kind agreement (e.g. 10 tons of steel rebar grade 60)">{{ old('in_kind_notes') }}</textarea>
                            </div>

                            <div id="ikItemsContainer">
                                {{-- rows injected by JS --}}
                            </div>

                            <button type="button" class="add-client__add-item-btn" id="addIkItemBtn">
                                + Add item
                            </button>

                            <div class="add-client__ik-total">
                                Total estimated value: <strong id="ikTotalDisplay">$0.00</strong>
                            </div>
                        </section>

                        {{-- Payment plan (hidden for in-kind) --}}
                        <section class="add-client__section" id="cashPaymentSection" aria-label="Payment plan">
                            <h3 class="add-client__section-title">Payment plan</h3>

                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="down_payment">First payment / Down payment
                                        ($)</label>
                                    <input class="add-client__input" id="down_payment" name="down_payment" type="number"
                                        step="0.01" min="0" placeholder="0.00" required
                                        value="{{ old('down_payment') }}" />
                                    @error('down_payment')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="installment_months">Number of months</label>
                                    <input class="add-client__input" id="installment_months" name="installment_months"
                                        type="number" step="1" min="1" placeholder="Example: 12" required
                                        value="{{ old('installment_months') }}" />
                                    @error('installment_months')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="installment_amount">Monthly payment
                                        ($)</label>
                                    <input class="add-client__input" id="installment_amount" name="installment_amount"
                                        type="number" step="0.01" min="0" placeholder="0.00" required readonly
                                        style="background:#f3f4f6;cursor:not-allowed;"
                                        value="{{ old('installment_amount') }}" />
                                    @error('installment_amount')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                    <div class="add-client__hint">
                                        Calculated automatically from price, discount, down payment, and months.
                                    </div>
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="payment_start_date">Payment start date</label>
                                    <input class="add-client__input" id="payment_start_date" name="payment_start_date"
                                        type="date" value="{{ old('payment_start_date') }}" />
                                    @error('payment_first_date')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="late_fee">Late fee ($) (optional)</label>
                                    <input class="add-client__input" id="late_fee" name="late_fee" type="number"
                                        step="0.01" min="0" placeholder="0.00" value="{{ old('late_fee') }}" />
                                    @error('late_fee')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="notes">Notes (optional)</label>
                                    <textarea class="add-client__textarea" id="notes" name="notes" rows="3"
                                        placeholder="Special terms, due date rules, remarks...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="add-client__calc">
                                <div class="add-client__summary" aria-live="polite">
                                    <div><span>Net price:</span> <strong id="netPriceText">$0.00</strong></div>
                                    <div><span>Remaining after down payment:</span> <strong
                                            id="remainingText">$0.00</strong></div>
                                    <div><span>Total paid (down + months):</span> <strong
                                            id="totalPaidText">$0.00</strong></div>
                                </div>
                            </div>
                        </section>

                        <div class="add-client__actions">
                            <button class="add-client__btn add-client__btn--primary" type="submit">
                                Save client
                            </button>

                            <a class="add-client__btn add-client__btn--ghost"
                                href="{{ route('clients.overview') ?? '#' }}">
                                Cancel
                            </a>
                        </div>
                    </form>
                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="/js/addClient.js"></script>
    <script src="/js/navSearch.js"></script>
    <script>
        // Auto-calculate monthly payment whenever relevant fields change
        function autoCalcMonthly() {
            var apartmentSelect = document.getElementById('apartment_id');
            var discount        = parseFloat(document.getElementById('discount')?.value) || 0;
            var downPayment     = parseFloat(document.getElementById('down_payment')?.value) || 0;
            var months          = parseInt(document.getElementById('installment_months')?.value) || 0;
            var amountField     = document.getElementById('installment_amount');

            var price = 0;
            if (apartmentSelect && apartmentSelect.selectedOptions[0]) {
                price = parseFloat(apartmentSelect.selectedOptions[0].dataset.price) || 0;
            }

            var net       = Math.max(price - discount, 0);
            var remaining = Math.max(net - downPayment, 0);
            var monthly   = (months > 0) ? (remaining / months) : 0;

            if (amountField) {
                amountField.value = monthly > 0 ? monthly.toFixed(2) : '';
            }

            // Update summary display if elements exist
            var netEl  = document.getElementById('netPriceText');
            var remEl  = document.getElementById('remainingText');
            var totEl  = document.getElementById('totalPaidText');
            if (netEl) netEl.textContent = '$' + net.toFixed(2);
            if (remEl) remEl.textContent = '$' + remaining.toFixed(2);
            if (totEl) totEl.textContent = '$' + (downPayment + monthly * months).toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Trigger on every relevant field change
            ['apartment_id', 'discount', 'down_payment', 'installment_months'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', autoCalcMonthly);
                if (el) el.addEventListener('change', autoCalcMonthly);
            });

            // Also fire once immediately in case of old() values
            autoCalcMonthly();

            // Keep button working as fallback (if it still exists in addClient.js)
            var btn = document.getElementById('autoCalcBtn');
            if (btn) btn.addEventListener('click', autoCalcMonthly);
        });
    </script>
</body>

</html>