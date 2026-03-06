<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Stock control</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/stockControl.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
</head>

<body class="app-shell">
    {{-- Mobile sidebar toggle (works with checkbox in layout) --}}
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="stock-control" aria-label="Stock control page">

                <section class="dashboard-card stock-control__card">
                    <header class="stock-control__header">
                        <h2 class="stock-control__title">Stock control</h2>

                        <div class="stock-control__header-actions">
                            <div class="stock-control__search">
                                <input id="stockSearchInput" class="stock-control__search-input" type="text"
                                    placeholder="Search item..." autocomplete="off" />
                            </div>

                            <a href="{{ route('inventory.add-item') }}" class="stock-control__add-btn" role="button">
                                Add item
                            </a>
                            <a onclick="event.preventDefault(); history.back();" class="stock-control__add-btn"
                                role="button">
                                Back
                            </a>
                        </div>
                    </header>


                    <div class="stock-control__table-wrap" aria-label="Stock table">
                        <table class="stock-control__table">
                            <thead class="stock-control__thead">
                                <tr>
                                    <th class="stock-control__th stock-control__th--icon"></th>
                                    <th class="stock-control__th">Code</th>
                                    <th class="stock-control__th stock-control__th--name">Item</th>
                                    <th class="stock-control__th">Type</th>
                                    <th class="stock-control__th">Quantity</th>
                                    <th class="stock-control__th stock-control__th--price">Price</th>
                                    <th class="stock-control__th stock-control__th--actions">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="stock-control__tbody">
                                @forelse($items as $item)
                                <tr class="stock-control__row">
                                    <td class="stock-control__td stock-control__td--icon">
                                        <img src="{{ $item->image_path ? asset('storage/'.$item->image_path) : asset('/img/item-placeholder.png') }}"
                                            alt="" class="stock-control__item-icon">
                                    </td>

                                    <td class="stock-control__td stock-control__td--code">
                                        {{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>

                                    <td class="stock-control__td stock-control__td--name">
                                        {{ $item->name }}
                                    </td>

                                    <td class="stock-control__td">
                                        <span class="stock-control__pill">{{ ucfirst($item->type) }}</span>
                                    </td>

                                    <td class="stock-control__td">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </td>

                                    <td class="stock-control__td stock-control__td--price">
                                        {{ number_format($item->price, 2) }}
                                    </td>

                                    <td class="stock-control__td stock-control__td--actions">
                                        <a href="{{ route('inventory.edit-item', $item->id) }}">
                                            <button class="stock-control__icon-btn stock-control__icon-btn--edit"
                                                type="button">✎</button>
                                        </a>

                                        <form action="{{ route('inventory.delete-item', $item->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button class="stock-control__icon-btn stock-control__icon-btn--delete"
                                                type="submit">🗑</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="stock-control__td" style="text-align:center; padding:16px;">
                                        No items yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                        <div style="margin-top:12px;">
                            {{ $items->links() }}
                        </div>

                    </div>
                </section>

            </section>
        </main>

        {{-- overlay for mobile sidebar --}}
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script>
        (function () {
  const input = document.getElementById('stockSearchInput');
  if (!input) return;

  const rows = document.querySelectorAll('.stock-control__tbody .stock-control__row');

  function normalize(str) {
    return (str || '').toString().trim().toLowerCase();
  }

  input.addEventListener('input', function () {
    const q = normalize(this.value);

    rows.forEach(row => {
      const nameCell = row.querySelector('[data-item-name]') || row.querySelector('.stock-control__td--name');
      const name = normalize(nameCell ? (nameCell.dataset.itemName || nameCell.textContent) : '');

      const match = name.includes(q);
      row.style.display = match ? '' : 'none';
    });
  });
})();
    </script>
    <script src="/js/navSearch.js"></script>

</body>

</html>