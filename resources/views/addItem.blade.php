<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add item</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/addItem.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="add-item" aria-label="Add item page">

                <section class="dashboard-card add-item__card">
                    <header class="add-item__header">
                        <h2 class="add-item__title">Add item</h2>

                        <a href="{{ route('inventory.stock-control') }}" class="add-item__back"
                            aria-label="Back to stock control">
                            Back
                        </a>
                    </header>

                    <form class="add-item__form" action="{{ route('inventory.create-item') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="add-item__grid">
                            <div class="add-item__field">
                                <label class="add-item__label" for="item_name">Item name</label>
                                <input class="add-item__input" id="item_name" name="item_name" type="text"
                                    placeholder="Enter item name" required value="{{ old('item_name') }}" />
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="item_price">Item price</label>
                                <input class="add-item__input" id="item_price" name="item_price" type="number"
                                    step="0.01" min="0" placeholder="0.00" required value="{{ old('item_price') }}" />
                                <small style="opacity:.7;">(Use as selling price if this is a sale item, otherwise keep
                                    it as your default price.)</small>
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="item_type">Item type</label>
                                <select class="add-item__select" id="item_type" name="item_type" required>
                                    <option value="" disabled {{ old('item_type') ? '' : 'selected' }}>Select type
                                    </option>
                                    <option value="internal" {{ old('item_type')==='internal' ? 'selected' : '' }}>
                                        Internal</option>
                                    <option value="external" {{ old('item_type')==='external' ? 'selected' : '' }}>
                                        External</option>
                                    <option value="sale" {{ old('item_type')==='sale' ? 'selected' : '' }}>Sale</option>
                                </select>
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="item_unit">Unit</label>
                                <input class="add-item__input" id="item_unit" name="item_unit" type="text"
                                    placeholder="Ex: Kg-L-Pcs" required value="{{ old('item_unit') }}" />
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="item_quantity">Purchase quantity</label>
                                <input class="add-item__input" id="item_quantity" name="item_quantity" type="number"
                                    step="1" min="0" placeholder="0" required value="{{ old('item_quantity', 0) }}" />
                                <small style="opacity:.7;">If you set quantity > 0, it will be recorded as a purchase
                                    (cash-basis).</small>
                            </div>

                            {{-- ✅ Purchase details (cash-basis expense) --}}
                            <div class="add-item__field">
                                <label class="add-item__label" for="purchase_date">Purchase date</label>
                                <input class="add-item__input" id="purchase_date" name="purchase_date" type="date"
                                    value="{{ old('purchase_date', now()->toDateString()) }}" />
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="purchase_unit_cost">
                                    Unit cost <span style="opacity:.7;">(required if qty > 0)</span>
                                </label>
                                <input class="add-item__input" id="purchase_unit_cost" name="purchase_unit_cost"
                                    type="number" step="0.01" min="0" placeholder="0.00"
                                    value="{{ old('purchase_unit_cost') }}" />
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="vendor_name">Vendor (optional)</label>
                                <input class="add-item__input" id="vendor_name" name="vendor_name" type="text"
                                    placeholder="Ex: Supplier name" value="{{ old('vendor_name') }}" />
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="payment_method">Payment method</label>
                                <select class="add-item__select" id="payment_method" name="payment_method" required>
                                    <option value="cash" {{ old('payment_method','cash')==='cash' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option value="bank" {{ old('payment_method')==='bank' ? 'selected' : '' }}>Bank
                                    </option>
                                    <option value="other" {{ old('payment_method')==='other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>
                            <div class="add-item__field add-item__field--wide">
                                <label class="add-item__label" for="purchase_notes">Purchase notes (optional)</label>
                                <input class="add-item__input" id="purchase_notes" name="purchase_notes" type="text"
                                    placeholder="Ex: invoice #, delivery note..." value="{{ old('purchase_notes') }}" />
                            </div>

                            {{-- Image --}}
                            <div class="add-item__field add-item__field--file add-item__field--wide">
                                <label class="add-item__label" for="item_image">Item image</label>

                                <div class="add-item__file-row">
                                    <label class="add-item__file" for="item_image">
                                        <span class="add-item__file-text">Choose image</span>
                                        <span class="add-item__file-hint">PNG/JPG</span>
                                    </label>

                                    <div class="add-item__preview" aria-label="Image preview">
                                        <img id="itemImagePreview" class="add-item__preview-img" alt="Preview" />
                                        <span id="itemImagePlaceholder" class="add-item__preview-placeholder">No
                                            image</span>
                                    </div>
                                </div>

                                <input class="add-item__file-input" id="item_image" name="item_image" type="file"
                                    accept="image/*" />
                            </div>
                        </div>

                        <div class="add-item__actions">
                            <button class="add-item__btn add-item__btn--primary" type="submit">
                                Add
                            </button>
                            <a class="add-item__btn add-item__btn--ghost" href="{{ route('inventory.stock-control') }}">
                                Cancel
                            </a>
                        </div>
                    </form>
                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/addItem.js"></script>
    <script src="/js/navSearch.js"></script>

</body>

</html>