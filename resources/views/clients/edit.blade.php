<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit client</title>

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- reuse add client styles --}}
    <link rel="stylesheet" href="/css/addClient.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="add-client" aria-label="Edit client page">

                <section class="dashboard-card add-client__card">
                    <header class="add-client__header">
                        <h2 class="add-client__title">Edit client</h2>

                        <a href="{{ route('clients.existing-clients') ?? '#' }}" class="add-client__back">
                            Back
                        </a>
                    </header>

                    <form class="add-client__form" method="post" action="{{ route('clients.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- ================= Personal info ================= --}}
                        <section class="add-client__section">
                            <h3 class="add-client__section-title">Client information</h3>

                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label">Full name</label>
                                    <input class="add-client__input" name="name" type="text"
                                        value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label">Phone number</label>
                                    <input class="add-client__input" name="phone" type="tel"
                                        value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label">Email</label>
                                    <input class="add-client__input" name="email" type="email"
                                        value="{{ old('email', $user->email) }}">
                                    @error('email')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- ================= Apartment ================= --}}
                        <section class="add-client__section" aria-label="Purchase information">
                            <h3 class="add-client__section-title">Apartment / purchase information</h3>

                            <div class="add-client__grid">
                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="apartment_id">Apartment</label>

                                    <select class="add-client__select" id="apartment_id" name="apartment_id" required>
                                        @foreach ($apartments as $apt)
                                        <option value="{{ $apt->id }}"
                                            data-project-name="{{ $apt->project->name ?? '' }}"
                                            data-unit-number="{{ $apt->unit_number ?? '' }}"
                                            data-location="{{ trim(($apt->project->city ?? '') . ' ' . ($apt->project->area ?? '')) }}"
                                            data-price="{{ $apt->price_total ?? '' }}" @selected(old('apartment_id',
                                            $contract?->apartment_id) == $apt->id)
                                            >
                                            {{ $apt->unit_number ?? $apt->unit_code }} — {{ $apt->project->name ?? '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('apartment_id')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="project_name">Project / Building</label>
                                    <input class="add-client__input" id="project_name" name="project_name" type="text"
                                        readonly value="{{ old('project_name') }}" />
                                    @error('project_name')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="unit_number">Unit / Apartment number</label>
                                    <input class="add-client__input" id="unit_number" name="unit_number" type="text"
                                        readonly value="{{ old('unit_number') }}" />
                                    @error('unit_number')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="location">Location</label>
                                    <input class="add-client__input" id="location" name="location" type="text" readonly
                                        value="{{ old('location') }}" />
                                    @error('location')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="total_price">Total price ($)</label>
                                    <input class="add-client__input" id="total_price" name="total_price" type="number"
                                        step="0.01" min="0" readonly
                                        value="{{ old('total_price', $contract?->total_price) }}" />
                                    @error('total_price')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="discount">Discount ($) (optional)</label>
                                    <input class="add-client__input" id="discount" name="discount" type="number"
                                        step="0.01" min="0" value="{{ old('discount', $contract?->discount ?? 0) }}" />
                                    @error('discount')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>


                        {{-- ================= Payments ================= --}}
                        <section class="add-client__section" aria-label="Payment plan">
                            <h3 class="add-client__section-title">Payment plan</h3>

                            <div class="add-client__grid">
                                <div class="add-client__field">
                                    <label class="add-client__label" for="down_payment">First payment / Down payment
                                        ($)</label>
                                    <input class="add-client__input" id="down_payment" name="down_payment" type="number"
                                        step="0.01" min="0" required
                                        value="{{ old('down_payment', $contract?->down_payment ?? 0) }}" />
                                    @error('down_payment')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="installment_months">Number of months</label>
                                    <input class="add-client__input" id="installment_months" name="installment_months"
                                        type="number" step="1" min="1" required
                                        value="{{ old('installment_months', $contract?->installment_months ?? 1) }}" />
                                    @error('installment_months')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="installment_amount">Monthly payment
                                        ($)</label>
                                    <input class="add-client__input" id="installment_amount" name="installment_amount"
                                        type="number" step="0.01" min="0" required
                                        value="{{ old('installment_amount', $contract?->installment_amount ?? 0) }}" />
                                    @error('installment_amount')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                    <div class="add-client__hint">
                                        Tip: you can type it manually, or press “Auto-calc”.
                                    </div>
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="contract_date">Contract date</label>
                                    <input class="add-client__input" id="contract_date" name="contract_date" type="date"
                                        value="{{ old('contract_date', $contract?->contract_date->format('Y-m-d')) }}"
                                        required />
                                    @error('contract_date')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="payment_start_date">Payment start date</label>
                                    <input class="add-client__input" id="payment_start_date" name="payment_start_date"
                                        type="date"
                                        value="{{ old('payment_start_date', $contract?->payment_start_date->format('Y-m-d')) }}"
                                        required />
                                    @error('payment_start_date')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field">
                                    <label class="add-client__label" for="late_fee">Late fee ($) (optional)</label>
                                    <input class="add-client__input" id="late_fee" name="late_fee" type="number"
                                        step="0.01" min="0" value="{{ old('late_fee', $contract?->late_fee ?? '') }}" />
                                    @error('late_fee')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="add-client__field add-client__field--wide">
                                    <label class="add-client__label" for="notes">Notes (optional)</label>
                                    <textarea class="add-client__textarea" id="notes" name="notes"
                                        rows="3">{{ old('notes', $contract?->notes) }}</textarea>
                                    @error('notes')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="add-client__calc">
                                <button class="add-client__calc-btn" type="button" id="autoCalcBtn">
                                    Auto-calc monthly payment
                                </button>

                                <div class="add-client__summary" aria-live="polite">
                                    <div><span>Net price:</span> <strong id="netPriceText">$0.00</strong></div>
                                    <div><span>Remaining after down payment:</span> <strong
                                            id="remainingText">$0.00</strong></div>
                                    <div><span>Total paid (down + months):</span> <strong
                                            id="totalPaidText">$0.00</strong></div>
                                </div>
                            </div>
                        </section>


                        {{-- ================= Actions ================= --}}
                        <div class="add-client__actions">
                            <button type="submit" class="add-client__btn add-client__btn--primary">
                                Save changes
                            </button>

                            <a href="{{ route('clients.existing-clients') ?? '#' }}"
                                class="add-client__btn add-client__btn--ghost">
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

</body>

</html>