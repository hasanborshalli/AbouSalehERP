<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Record Purchase</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/addItem.css" /> {{-- reuse your add-item form styling --}}
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

            @if ($errors->any())
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">X</span>
                <span class="alert__text">
                    Please fix the errors and try again.
                </span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <section class="add-item" aria-label="Record purchase page">
                <section class="dashboard-card add-item__card">
                    <header class="add-item__header">
                        <h2 class="add-item__title">Record purchase</h2>
                        <a href="{{ route('accounting.overview') }}" class="add-item__back">Back</a>
                    </header>

                    <form class="add-item__form" action="{{ route('accounting.purchases.store') }}" method="post">
                        @csrf

                        <div class="add-item__grid">
                            <div class="add-item__field add-item__field--wide">
                                <label class="add-item__label" for="inventory_item_id">Item</label>
                                <select class="add-item__select" id="inventory_item_id" name="inventory_item_id"
                                    required>
                                    <option value="" disabled {{ old('inventory_item_id') ? '' : 'selected' }}>
                                        Select item
                                    </option>
                                    @foreach($items as $it)
                                    <option value="{{ $it->id }}" {{ (string)old('inventory_item_id')===(string)$it->id
                                        ? 'selected' : '' }}>
                                        {{ $it->name }} (Stock: {{ $it->quantity }} {{ $it->unit }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('inventory_item_id') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="purchase_date">Purchase date</label>
                                <input class="add-item__input" id="purchase_date" name="purchase_date" type="date"
                                    value="{{ old('purchase_date', now()->toDateString()) }}" required />
                                @error('purchase_date') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="qty">Quantity</label>
                                <input class="add-item__input" id="qty" name="qty" type="number" step="1" min="1"
                                    placeholder="1" value="{{ old('qty', 1) }}" required />
                                @error('qty') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="unit_cost">Unit cost</label>
                                <input class="add-item__input" id="unit_cost" name="unit_cost" type="number" step="0.01"
                                    min="0" placeholder="0.00" value="{{ old('unit_cost') }}" required />
                                @error('unit_cost') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="vendor_name">Vendor (optional)</label>
                                <input class="add-item__input" id="vendor_name" name="vendor_name" type="text"
                                    placeholder="Supplier name" value="{{ old('vendor_name') }}" />
                                @error('vendor_name') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field add-item__field--wide">
                                <label class="add-item__label" for="notes">Notes (optional)</label>
                                <input class="add-item__input" id="notes" name="notes" type="text"
                                    placeholder="Invoice #, delivery note..." value="{{ old('notes') }}" />
                                @error('notes') <p style="color:red">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="add-item__actions">
                            <button class="add-item__btn add-item__btn--primary" type="submit">Save</button>
                            <a class="add-item__btn add-item__btn--ghost"
                                href="{{ route('accounting.overview') }}">Cancel</a>
                        </div>
                    </form>
                </section>
            </section>

        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>