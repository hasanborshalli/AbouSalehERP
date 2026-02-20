<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create project</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/createProject.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="create-project" aria-label="Create project page">

                <section class="dashboard-card create-project__card">
                    <header class="create-project__header">
                        <h2 class="create-project__title">Create new project</h2>

                        <a onclick="event.preventDefault(); history.back();" class="create-project__back">Back</a>
                    </header>

                    <form class="create-project__form" action="{{ route('apartments.createProject') }}" method="post">
                        @csrf
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- ================= Project Info ================= --}}
                        <section class="create-project__section">
                            <h3 class="create-project__section-title">Project information</h3>

                            <div class="create-project__grid">
                                <div class="create-project__field create-project__field--wide">
                                    <label class="create-project__label" for="project_name">Project name</label>
                                    <input class="create-project__input" id="project_name" name="project_name"
                                        type="text" placeholder="e.g. Abou Saleh Tower" required
                                        value="{{ old('project_name') }}">
                                </div>

                                <div class="create-project__field">
                                    <label class="create-project__label" for="project_code">Project code
                                        (optional)</label>
                                    <input class="create-project__input" id="project_code" name="project_code"
                                        type="text" placeholder="e.g. AST-01" value="{{ old('project_code') }}">
                                </div>



                                <div class="create-project__field">
                                    <label class="create-project__label" for="city">City</label>
                                    <input class="create-project__input" id="city" name="city" type="text"
                                        placeholder="e.g. Beirut" required value="{{ old('city') }}">
                                </div>

                                <div class="create-project__field">
                                    <label class="create-project__label" for="area">Area / Neighborhood</label>
                                    <input class="create-project__input" id="area" name="area" type="text"
                                        placeholder="e.g. Verdun / Hamra" value="{{ old('area') }}">
                                </div>

                                <div class="create-project__field create-project__field--wide">
                                    <label class="create-project__label" for="address">Address</label>
                                    <input class="create-project__input" id="address" name="address" type="text"
                                        placeholder="Street, building, landmarks..." value="{{ old('address') }}">
                                </div>


                                <div class="create-project__field">
                                    <label class="create-project__label" for="start_date">Start date
                                        (optional)</label>
                                    <input class="create-project__input" id="start_date" name="start_date" type="date"
                                        value="{{ old('start_date') }}">
                                </div>
                                <div class="create-project__field">
                                    <label class="create-project__label" for="estimated_completion_date">Handover date
                                        (optional)</label>
                                    <input class="create-project__input" id="estimated_completion_date"
                                        name="estimated_completion_date" type="date"
                                        value="{{ old('estimated_completion_date') }}">
                                </div>

                                <div class="create-project__field create-project__field--wide">
                                    <label class="create-project__label" for="notes">Notes (optional)</label>
                                    <textarea class="create-project__textarea" id="notes" name="notes" rows="3"
                                        placeholder="Any notes about the project...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </section>

                        {{-- ================= Project Materials (Inventory Items) ================= --}}
                        <section class="create-project__section">
                            <div class="create-project__section-row">
                                <h3 class="create-project__section-title">Project materials (from inventory)</h3>
                                <button type="button" class="create-project__mini-btn" id="cpAddMaterialBtn">
                                    + Add item
                                </button>
                            </div>

                            <p class="create-project__hint">
                                Select inventory items required for this project and the quantity needed.
                            </p>



                            <div class="cp-materials" id="cpMaterials">
                                {{-- Row 1 (default) --}}
                                <div class="cp-materials__row" data-row>
                                    <div class="cp-materials__field">
                                        <label class="cp-materials__label">Item</label>
                                        <select class="cp-materials__select" name="materials[item_id][]" required>
                                            <option value="" selected disabled>Select item</option>
                                            @foreach($inventoryItems as $it)
                                            <option value="{{ $it->id }}" data-unit="{{ $it->unit }}">{{ $it->name }}
                                            </option>

                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="cp-materials__field cp-materials__field--qty">
                                        <label class="cp-materials__label">Qty</label>
                                        <input class="cp-materials__input" name="materials[qty][]" type="number"
                                            step="0.01" min="0" placeholder="0" required>
                                    </div>

                                    <div class="cp-materials__field cp-materials__field--unit">
                                        <label class="cp-materials__label">Unit</label>
                                        <input class="cp-materials__input" name="materials[unit][]" type="text"
                                            placeholder="Auto" readonly>
                                    </div>


                                    <div class="cp-materials__field cp-materials__field--note">
                                        <label class="cp-materials__label">Note (optional)</label>
                                        <input class="cp-materials__input" name="materials[note][]" type="text"
                                            placeholder="e.g. for phase 1">
                                    </div>

                                    <div class="cp-materials__actions">
                                        <button type="button" class="cp-materials__remove" data-remove
                                            aria-label="Remove item">✕</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden template row (JS clones this) --}}
                            <template id="cpMaterialTemplate">
                                <div class="cp-materials__row" data-row>
                                    <div class="cp-materials__field">
                                        <label class="cp-materials__label">Item</label>
                                        <select class="cp-materials__select" name="materials[item_id][]" required>
                                            <option value="" selected disabled>Select item</option>
                                            @foreach($inventoryItems as $it)
                                            <option value="{{ $it->id }}" data-unit="{{ $it->unit }}">{{ $it->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="cp-materials__field cp-materials__field--qty">
                                        <label class="cp-materials__label">Qty</label>
                                        <input class="cp-materials__input" name="materials[qty][]" type="number"
                                            step="0.01" min="0" placeholder="0" required>
                                    </div>

                                    <div class="cp-materials__field cp-materials__field--unit">
                                        <label class="cp-materials__label">Unit</label>
                                        <input class="cp-materials__input" name="materials[unit][]" type="text"
                                            placeholder="Auto" readonly>
                                    </div>

                                    <div class="cp-materials__field cp-materials__field--note">
                                        <label class="cp-materials__label">Note (optional)</label>
                                        <input class="cp-materials__input" name="materials[note][]" type="text"
                                            placeholder="e.g. for phase 1">
                                    </div>

                                    <div class="cp-materials__actions">
                                        <button type="button" class="cp-materials__remove" data-remove
                                            aria-label="Remove item">✕</button>
                                    </div>
                                </div>
                            </template>

                        </section>


                        {{-- ================= Floors & Units ================= --}}
                        <section class="create-project__section" aria-label="Floors and units">
                            <div class="create-project__section-row">
                                <h3 class="create-project__section-title">Floors & apartments</h3>
                                <span class="create-project__badge">Auto-generated</span>
                            </div>

                            <div class="create-project__grid">
                                <div class="create-project__field">
                                    <label class="create-project__label" for="floor_count">How many floors?</label>
                                    <input class="create-project__input" id="floor_count" name="floor_count"
                                        type="number" min="1" step="1" placeholder="e.g. 6" required>
                                </div>

                                <div class="create-project__field create-project__field--wide">
                                    <p class="create-project__hint" style="margin:0;">
                                        After you enter the number of floors, a unit form will appear for each floor.
                                    </p>
                                </div>
                            </div>

                            <div class="cp-floors" id="cpFloorsWrap" aria-label="Floors forms"></div>

                            {{-- Template (JS clones this) --}}
                            <template id="cpFloorTemplate">
                                <section class="cp-floor" data-floor-card>
                                    <header class="cp-floor__header">
                                        <h4 class="cp-floor__title">Floor <span data-floor-number></span></h4>

                                        <div class="cp-floor__meta">
                                            <label class="cp-floor__meta-label">Units on this floor</label>
                                            <input class="cp-floor__units-count" type="number" min="1" step="1"
                                                value="1" data-units-count aria-label="Units count">
                                        </div>
                                    </header>

                                    <div class="cp-floor__units" data-units-wrap></div>

                                    {{-- Unit template inside each floor --}}
                                    <template data-unit-template>
                                        <div class="cp-unit" data-unit-row>
                                            <div class="cp-unit__grid">
                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Unit code</label>
                                                    <input class="cp-unit__input" type="text" placeholder="e.g. A-12"
                                                        data-name="unit_code">
                                                </div>

                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Bedrooms</label>
                                                    <input class="cp-unit__input" type="number" min="0" step="1"
                                                        placeholder="e.g. 2" data-name="bedrooms">
                                                </div>

                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Bathrooms</label>
                                                    <input class="cp-unit__input" type="number" min="0" step="1"
                                                        placeholder="e.g. 2" data-name="bathrooms">
                                                </div>

                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Area (m²)</label>
                                                    <input class="cp-unit__input" type="number" min="0" step="0.1"
                                                        placeholder="e.g. 130" data-name="area_m2">
                                                </div>

                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Price</label>
                                                    <input class="cp-unit__input" type="number" min="0" step="0.01"
                                                        placeholder="e.g. 150000" data-name="price">
                                                </div>

                                                <div class="cp-unit__field">
                                                    <label class="cp-unit__label">Status</label>
                                                    <select class="cp-unit__select" data-name="status">
                                                        <option value="available" selected>Available</option>
                                                        <option value="reserved">Reserved</option>
                                                        <option value="sold">Sold</option>
                                                    </select>
                                                </div>

                                                <div class="cp-unit__field cp-unit__field--wide">
                                                    <label class="cp-unit__label">Notes (optional)</label>
                                                    <input class="cp-unit__input" type="text"
                                                        placeholder="e.g. sea view" data-name="note">
                                                </div>
                                            </div>

                                            <div class="cp-unit__actions">
                                                <button type="button" class="cp-unit__remove" data-remove-unit
                                                    aria-label="Remove unit">Remove</button>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="cp-floor__footer">
                                        <button type="button" class="cp-floor__add-unit" data-add-unit>+ Add
                                            unit</button>
                                    </div>
                                </section>
                            </template>
                        </section>


                        {{-- ================= Actions ================= --}}
                        <div class="create-project__actions">
                            <button class="create-project__btn create-project__btn--primary" type="submit">
                                Create project
                            </button>

                            <a class="create-project__btn create-project__btn--ghost"
                                href="{{ route('apartments.overview') }}">
                                Cancel
                            </a>
                        </div>

                    </form>
                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/createProject.js"></script>
    <script src="/js/materials.js"></script>
    <script src="/js/navSearch.js"></script>


</body>

</html>