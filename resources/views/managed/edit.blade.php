<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit — {{ $property->address }}</title>
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
<aside class="app-shell__sidebar"><x-sidebar /></aside>
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
                        <h2 class="mp-create-card__title">Edit Property</h2>
                        <a class="mp-back" href="{{ route('managed.show', $property) }}">← Back</a>
                    </div>

                    <form method="POST" action="{{ route('managed.update', $property) }}">
                        @csrf @method('PUT')
                        <div class="mp-create-card__body">

                            {{-- Owner Info --}}
                            <div class="mp-create-section">
                                <p class="mp-create-section-title">Owner Information</p>
                                <div class="mp-create-grid">
                                    <div class="mp-create-field">
                                        <label>Owner Full Name *</label>
                                        <input name="owner_name" type="text"
                                            value="{{ old('owner_name', $property->owner_name) }}" required />
                                        @error('owner_name')<span class="form-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Owner Phone *</label>
                                        <input name="owner_phone" type="tel"
                                            value="{{ old('owner_phone', $property->owner_phone) }}" required />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Owner Email</label>
                                        <input name="owner_email" type="email"
                                            value="{{ old('owner_email', $property->owner_email) }}" />
                                    </div>
                                </div>
                            </div>

                            {{-- Property Details --}}
                            <div class="mp-create-section">
                                <p class="mp-create-section-title">Property Details</p>
                                <div class="mp-create-grid">
                                    <div class="mp-create-field mp-create-field--wide">
                                        <label>Full Address *</label>
                                        <input name="address" type="text"
                                            value="{{ old('address', $property->address) }}" required />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>City</label>
                                        <input name="city" type="text"
                                            value="{{ old('city', $property->city) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Area / Neighborhood</label>
                                        <input name="area" type="text"
                                            value="{{ old('area', $property->area) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Bedrooms</label>
                                        <input name="bedrooms" type="number" min="0"
                                            value="{{ old('bedrooms', $property->bedrooms) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Bathrooms</label>
                                        <input name="bathrooms" type="number" min="0"
                                            value="{{ old('bathrooms', $property->bathrooms) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Area (m²)</label>
                                        <input name="area_sqm" type="number" step="0.01"
                                            value="{{ old('area_sqm', $property->area_sqm) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Agreement Date *</label>
                                        <input name="agreement_date" type="date"
                                            value="{{ old('agreement_date', $property->agreement_date->format('Y-m-d')) }}" required />
                                    </div>
                                    <div class="mp-create-field mp-create-field--wide">
                                        <label>Description</label>
                                        <textarea name="description" rows="3">{{ old('description', $property->description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Financials --}}
                            <div class="mp-create-section">
                                <p class="mp-create-section-title">Financials</p>
                                <div class="mp-create-grid">
                                    <div class="mp-create-field">
                                        <label>Owner Asking Price ($) *</label>
                                        <input name="owner_asking_price" type="number" step="0.01"
                                            value="{{ old('owner_asking_price', $property->owner_asking_price) }}" required />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Estimated Renovation Cost ($)</label>
                                        <input name="estimated_renovation_cost" type="number" step="0.01"
                                            value="{{ old('estimated_renovation_cost', $property->estimated_renovation_cost) }}" />
                                    </div>
                                    @if($property->isFlip())
                                    <div class="mp-create-field">
                                        <label>Listing Price ($)</label>
                                        <input name="agreed_listing_price" type="number" step="0.01"
                                            value="{{ old('agreed_listing_price', $property->agreed_listing_price) }}" />
                                    </div>
                                    @endif
                                    @if($property->isRental())
                                    <div class="mp-create-field">
                                        <label>Expected Monthly Rent ($)</label>
                                        <input name="agreed_rent_price" type="number" step="0.01"
                                            value="{{ old('agreed_rent_price', $property->agreed_rent_price) }}" />
                                    </div>
                                    <div class="mp-create-field">
                                        <label>Company Commission (%)</label>
                                        <input name="company_commission_pct" type="number" step="0.001"
                                            value="{{ old('company_commission_pct', $property->company_commission_pct) }}" />
                                    </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="mp-create-footer">
                            <a class="btn-outline" href="{{ route('managed.show', $property) }}">Cancel</a>
                            <button type="submit" class="btn-add">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
    <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
</div>
</body>
</html>
