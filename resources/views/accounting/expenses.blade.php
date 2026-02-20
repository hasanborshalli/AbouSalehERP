<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Record Expense</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/addItem.css" /> {{-- reuse form styling --}}
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
                <span class="alert__text">Please fix the errors and try again.</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif

            <section class="add-item" aria-label="Record expense page">
                <section class="dashboard-card add-item__card">
                    <header class="add-item__header">
                        <h2 class="add-item__title">Record expense</h2>
                        <a href="{{ route('accounting.overview') }}" class="add-item__back">Back</a>
                    </header>

                    <form class="add-item__form" action="{{ route('accounting.expenses.store') }}" method="post">
                        @csrf

                        <div class="add-item__grid">
                            <div class="add-item__field">
                                <label class="add-item__label" for="expense_date">Expense date</label>
                                <input class="add-item__input" id="expense_date" name="expense_date" type="date"
                                    value="{{ old('expense_date', now()->toDateString()) }}" required />
                                @error('expense_date') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field">
                                <label class="add-item__label" for="amount">Amount</label>
                                <input class="add-item__input" id="amount" name="amount" type="number" step="0.01"
                                    min="0" placeholder="0.00" value="{{ old('amount') }}" required />
                                @error('amount') <p style="color:red">{{ $message }}</p> @enderror
                            </div>

                            <div class="add-item__field add-item__field--wide">
                                <label class="add-item__label" for="category">Category</label>
                                <select class="add-item__select" id="category" name="category" required>
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category
                                    </option>
                                    @foreach($categories as $c)
                                    <option value="{{ $c }}" {{ old('category')===$c ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_',' ', $c)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category') <p style="color:red">{{ $message }}</p> @enderror
                                <small style="opacity:.7;">If you need custom categories later, we can store them in
                                    DB.</small>
                            </div>

                            <div class="add-item__field add-item__field--wide">
                                <label class="add-item__label" for="description">Description (optional)</label>
                                <input class="add-item__input" id="description" name="description" type="text"
                                    placeholder="Example: Electricity bill - Jan" value="{{ old('description') }}" />
                                @error('description') <p style="color:red">{{ $message }}</p> @enderror
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