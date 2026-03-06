<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Managed Property</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/managed.css">
    <link rel="stylesheet" href="/css/responsive.css">
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">
            <div class="mp">

                @if($errors->any())
                <div class="alert alert--error">
                    <ul style="margin:0;padding-left:16px;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div class="mp-create-wrap">
                    <div class="mp-create-card">
                        <div class="mp-create-card__head">
                            <h2 class="mp-create-card__title">Add Managed Property</h2>
                            <a class="mp-back" href="{{ route('managed.index') }}">← Back</a>
                        </div>

                        <form method="POST" action="{{ route('managed.store') }}">
                            @csrf
                            <div class="mp-create-card__body">

                                {{-- Service Type --}}
                                <div class="mp-create-section">
                                    <p class="mp-create-section-title">Service Type</p>
                                    <div class="type-toggle">
                                        <input type="radio" id="type_flip" name="type" value="flip" {{
                                            old('type', 'flip' )==='flip' ? 'checked' : '' }}>
                                        <label for="type_flip">🔨 Flip (Buy & Sell)</label>

                                        <input type="radio" id="type_rental" name="type" value="rental" {{
                                            old('type')==='rental' ? 'checked' : '' }}>
                                        <label for="type_rental">🔑 Rental Management</label>
                                    </div>
                                    @error('type')<span class="form-error">{{ $message }}</span>@enderror
                                </div>

                                {{-- Owner Info --}}
                                <div class="mp-create-section">
                                    <p class="mp-create-section-title">Owner Information</p>
                                    <div class="mp-create-grid">
                                        <div class="mp-create-field">
                                            <label for="owner_name">Owner Full Name *</label>
                                            <input id="owner_name" name="owner_name" type="text" placeholder="John Doe"
                                                value="{{ old('owner_name') }}" required />
                                            @error('owner_name')<span class="form-error">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="owner_phone">Owner Phone *</label>
                                            <input id="owner_phone" name="owner_phone" type="tel" placeholder="+961 ..."
                                                value="{{ old('owner_phone') }}" required />
                                            @error('owner_phone')<span class="form-error">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="owner_email">Owner Email</label>
                                            <input id="owner_email" name="owner_email" type="email"
                                                placeholder="owner@email.com" value="{{ old('owner_email') }}" />
                                            @error('owner_email')<span class="form-error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Property Details --}}
                                <div class="mp-create-section">
                                    <p class="mp-create-section-title">Property Details</p>
                                    <div class="mp-create-grid">
                                        <div class="mp-create-field mp-create-field--wide">
                                            <label for="address">Full Address *</label>
                                            <input id="address" name="address" type="text"
                                                placeholder="Street, Building, Floor..." value="{{ old('address') }}"
                                                required />
                                            @error('address')<span class="form-error">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="city">City</label>
                                            <input id="city" name="city" type="text" placeholder="Beirut"
                                                value="{{ old('city') }}" />
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="area">Area / Neighborhood</label>
                                            <input id="area" name="area" type="text" placeholder="Hamra, Achrafieh..."
                                                value="{{ old('area') }}" />
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="bedrooms">Bedrooms</label>
                                            <input id="bedrooms" name="bedrooms" type="number" min="0" max="20"
                                                value="{{ old('bedrooms') }}" />
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="bathrooms">Bathrooms</label>
                                            <input id="bathrooms" name="bathrooms" type="number" min="0" max="20"
                                                value="{{ old('bathrooms') }}" />
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="area_sqm">Area (m²)</label>
                                            <input id="area_sqm" name="area_sqm" type="number" step="0.01" min="0"
                                                value="{{ old('area_sqm') }}" />
                                        </div>
                                        <div class="mp-create-field">
                                            <label for="agreement_date">Agreement Date *</label>
                                            <input id="agreement_date" name="agreement_date" type="date"
                                                value="{{ old('agreement_date', date('Y-m-d')) }}" required />
                                            @error('agreement_date')<span class="form-error">{{ $message
                                                }}</span>@enderror
                                        </div>
                                        <div class="mp-create-field mp-create-field--wide">
                                            <label for="description">Description / Notes</label>
                                            <textarea id="description" name="description" rows="3"
                                                placeholder="Property condition, features, notes...">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- Flip Financials --}}
                                <div class="type-section" id="flip-section">
                                    <div class="mp-create-section">
                                        <p class="mp-create-section-title">Flip Financials</p>
                                        <div class="mp-create-grid">
                                            <div class="mp-create-field">
                                                <label for="owner_asking_price">Owner Asking Price ($) *</label>
                                                <input id="owner_asking_price" name="owner_asking_price" type="number"
                                                    step="0.01" min="0" placeholder="100000"
                                                    value="{{ old('owner_asking_price') }}" />
                                                <small style="color:#6b7280;font-size:11px;">What owner expects to
                                                    receive when sold</small>
                                                @error('owner_asking_price')<span class="form-error">{{ $message
                                                    }}</span>@enderror
                                            </div>
                                            <div class="mp-create-field">
                                                <label for="estimated_renovation_cost">Estimated Renovation Cost
                                                    ($)</label>
                                                <input id="estimated_renovation_cost" name="estimated_renovation_cost"
                                                    type="number" step="0.01" min="0" placeholder="10000"
                                                    value="{{ old('estimated_renovation_cost') }}" />
                                                <small style="color:#6b7280;font-size:11px;">Budget estimate (actual
                                                    expenses tracked separately)</small>
                                            </div>
                                            <div class="mp-create-field">
                                                <label for="agreed_listing_price">Agreed Listing Price ($)</label>
                                                <input id="agreed_listing_price" name="agreed_listing_price"
                                                    type="number" step="0.01" min="0" placeholder="120000"
                                                    value="{{ old('agreed_listing_price') }}" />
                                                <small style="color:#6b7280;font-size:11px;">Target sale price agreed
                                                    with owner</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Rental Financials --}}
                                <div class="type-section" id="rental-section">
                                    <div class="mp-create-section">
                                        <p class="mp-create-section-title">Rental Financials</p>
                                        <div class="mp-create-grid">
                                            <div class="mp-create-field">
                                                <label for="owner_asking_price_r">Owner Asking Price ($)</label>
                                                <input name="owner_asking_price" type="number" step="0.01" min="0"
                                                    placeholder="100000" value="{{ old('owner_asking_price') }}" />
                                                <small style="color:#6b7280;font-size:11px;">Property value (for
                                                    reference)</small>
                                            </div>
                                            <div class="mp-create-field">
                                                <label for="agreed_rent_price">Expected Monthly Rent ($)</label>
                                                <input id="agreed_rent_price" name="agreed_rent_price" type="number"
                                                    step="0.01" min="0" placeholder="1500"
                                                    value="{{ old('agreed_rent_price') }}" />
                                                <small style="color:#6b7280;font-size:11px;">Target monthly rent from
                                                    tenant</small>
                                                @error('agreed_rent_price')<span class="form-error">{{ $message
                                                    }}</span>@enderror
                                            </div>
                                            <div class="mp-create-field">
                                                <label for="company_commission_pct">Company Commission (%)</label>
                                                <input id="company_commission_pct" name="company_commission_pct"
                                                    type="number" step="0.001" min="0" max="100" placeholder="10"
                                                    value="{{ old('company_commission_pct') }}" />
                                                <small style="color:#6b7280;font-size:11px;">% of monthly rent the
                                                    company keeps</small>
                                                @error('company_commission_pct')<span class="form-error">{{ $message
                                                    }}</span>@enderror
                                            </div>
                                            <div class="mp-create-field">
                                                <label for="estimated_renovation_cost_r">Renovation Cost ($)</label>
                                                <input name="estimated_renovation_cost" type="number" step="0.01"
                                                    min="0" placeholder="5000"
                                                    value="{{ old('estimated_renovation_cost') }}" />
                                                <small style="color:#6b7280;font-size:11px;">Initial prep costs
                                                    (optional)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- end body --}}

                            <div class="mp-create-footer">
                                <a class="btn-outline" href="{{ route('managed.index') }}">Cancel</a>
                                <button type="submit" class="btn-add">Save Property</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script>
        const flipSection   = document.getElementById('flip-section');
const rentalSection = document.getElementById('rental-section');
const radios = document.querySelectorAll('input[name="type"]');

function updateSections() {
    const val = document.querySelector('input[name="type"]:checked')?.value;
    flipSection.classList.toggle('visible', val === 'flip');
    rentalSection.classList.toggle('visible', val === 'rental');

    // Disable all inputs in the hidden section so they don't get submitted
    flipSection.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = (val !== 'flip');
    });
    rentalSection.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = (val !== 'rental');
    });
}
radios.forEach(r => r.addEventListener('change', updateSections));
updateSections();
    </script>
</body>

</html>