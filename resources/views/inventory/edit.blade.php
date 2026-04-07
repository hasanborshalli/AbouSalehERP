<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit item</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/addItem.css" />
    <link rel="stylesheet" href="/css/responsive.css" />
    <style>
        .form-error {
            color: #dc2626;
            font-size: 12px;
            margin: 2px 0 0;
        }
    </style>
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />
    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>
    <div class="app-shell__main">
        <x-navbar />
        <main class="dashboard-content">
            <section class="add-item">
                <section class="dashboard-card add-item__card">
                    <header class="add-item__header">
                        <h2 class="add-item__title">Edit item</h2>
                        <a href="{{ route('inventory.stock-control') }}" class="add-item__back">Back</a>
                    </header>
                    <form class="add-item__form" action="{{ route('inventory.update-item',$inventoryItem->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(session('success'))
                        <div class="alert alert--success" data-alert>
                            <span class="alert__icon">✓</span>
                            <span class="alert__text">{{ session('success') }}</span>
                            <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                        </div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert--error" data-alert>
                            <span class="alert__icon">✕</span>
                            <span class="alert__text">{{ session('error') }}</span>
                            <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                        </div>
                        @endif
                        @if($errors->any())
                        <div class="alert alert--error" data-alert>
                            <span class="alert__icon">✕</span>
                            <span class="alert__text">Please fix the errors below.</span>
                            <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                        </div>
                        @endif

                        <div class="add-item__grid">
                            <div class="add-item__field">
                                <label class="add-item__label" for="item_name">Item name</label>
                                <input class="add-item__input" id="item_name" name="item_name" type="text"
                                    placeholder="Enter item name" required
                                    value="{{ old('item_name', $inventoryItem->name) }}" />
                                @error('item_name')<p class="form-error">{{ $message }}</p>@enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="item_name_ar">Item name (Arabic)</label>
                                <input class="add-item__input" id="item_name_ar" name="item_name_ar" type="text"
                                    placeholder="مثال: أسمنت" dir="rtl"
                                    value="{{ old('item_name_ar', $inventoryItem->name_ar) }}" />
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="item_price">Item price</label>
                                <input class="add-item__input" id="item_price" name="item_price" type="number"
                                    step="0.01" min="0" placeholder="0.00" required
                                    value="{{ old('item_price', $inventoryItem->price) }}" />
                                @error('item_price')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="item_type">Item type</label>
                                <select class="add-item__select" id="item_type" name="item_type" required>
                                    <option value="" disabled>Select type</option>
                                    <option value="internal" {{ old('item_type', $inventoryItem->type) === 'internal' ?
                                        'selected' : '' }}>Internal</option>
                                    <option value="external" {{ old('item_type', $inventoryItem->type) === 'external' ?
                                        'selected' : '' }}>External</option>
                                    <option value="sale" {{ old('item_type', $inventoryItem->type) === 'sale' ?
                                        'selected' : '' }}>Sale</option>
                                </select>
                                @error('item_type')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="add-item__field">
                                <label class="add-item__label" for="item_unit">Unit</label>
                                <input class="add-item__input" id="item_unit" name="item_unit" type="text"
                                    placeholder="Ex: Kg-L-Pcs" required
                                    value="{{ old('item_unit', $inventoryItem->unit) }}" />
                                @error('item_unit')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="add-item__field add-item__field--checkbox">
                                <label class="add-item__checkbox">
                                    <input type="checkbox" name="is_out_of_stock" value="1" {{ old('is_out_of_stock',
                                        $inventoryItem->is_out_of_stock) ? 'checked' : '' }}/>
                                    <span class="add-item__checkbox-box"></span>
                                    <span class="add-item__checkbox-label">Set as out of stock</span>
                                </label>
                            </div>
                            <div class="add-item__field add-item__field--file">
                                <label class="add-item__label" for="item_image">Item image</label>
                                <div class="add-item__file-row">
                                    <label class="add-item__file" for="item_image">
                                        <span class="add-item__file-text">Choose image</span>
                                        <span class="add-item__file-hint">PNG/JPG</span>
                                    </label>
                                    <div class="add-item__preview">
                                        @if($inventoryItem->image_path)
                                        <img src="{{ asset('/storage/' . $inventoryItem->image_path) }}"
                                            alt="Item image" id="itemImagePreview" class="add-item__preview-img">
                                        @else
                                        <img id="itemImagePreview" class="add-item__preview-img" alt="Preview" />
                                        <span id="itemImagePlaceholder" class="add-item__preview-placeholder">No
                                            image</span>
                                        @endif
                                    </div>
                                </div>
                                <input class="add-item__file-input" id="item_image" name="item_image" type="file"
                                    accept="image/*" />
                                @error('item_image')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="add-item__actions">
                            <button class="add-item__btn add-item__btn--primary" type="submit">Save changes</button>
                            <a class="add-item__btn add-item__btn--ghost"
                                href="{{ route('inventory.stock-control') }}">Cancel</a>
                        </div>
                    </form>
                </section>
            </section>
        </main>
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/editItem.js"></script>
    <script src="/js/navSearch.js"></script>
</body>

</html>