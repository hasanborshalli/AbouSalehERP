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


                        {{-- Payment plan --}}
                        <section class="add-client__section" aria-label="Payment plan">
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
                                        type="number" step="0.01" min="0" placeholder="0.00" required
                                        value="{{ old('installment_amount') }}" />
                                    @error('installment_amount')
                                    <p style="color:red">{{ $message }}</p>
                                    @enderror
                                    <div class="add-client__hint">
                                        Tip: you can type it manually, or press “Auto-calc”.
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
</body>

</html>