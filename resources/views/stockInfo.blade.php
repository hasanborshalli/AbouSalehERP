<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Stock info</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/stockInfo.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="stock-info" aria-label="Stock info page">

                <section class="dashboard-card stock-info__card">
                    <header class="stock-info__header">
                        <h2 class="stock-info__title">Stock info</h2>

                        <div class="stock-info__controls">

                            {{-- Search --}}
                            <div class="stock-info__search">
                                <input id="stockInfoSearch" type="text" class="stock-info__search-input"
                                    placeholder="Search item name..." aria-label="Search stock" />
                            </div>

                            {{-- Filter --}}
                            <div class="stock-info__filter">
                                <label class="stock-info__filter-label" for="typeFilter">Type</label>
                                <select id="typeFilter" class="stock-info__filter-select">
                                    <option value="all" selected>All</option>
                                    <option value="internal">Internal</option>
                                    <option value="external">External</option>
                                    <option value="sale">Sale</option>
                                </select>
                            </div>
                            <a onclick="event.preventDefault(); history.back();" class="stock-control__add-btn"
                                role="button">
                                Back
                            </a>
                        </div>
                    </header>


                    <div class="stock-info__table-wrap" aria-label="Stock info table">
                        <table class="stock-info__table" id="stockInfoTable">
                            <thead class="stock-info__thead">
                                <tr>
                                    <th class="stock-info__th stock-info__th--icon"></th>

                                    <th class="stock-info__th stock-info__th--sortable" data-key="code"
                                        data-type="number">
                                        Code <span class="stock-info__sort-indicator" aria-hidden="true"></span>
                                    </th>

                                    <th class="stock-info__th stock-info__th--sortable stock-info__th--name"
                                        data-key="name" data-type="text">
                                        Item <span class="stock-info__sort-indicator" aria-hidden="true"></span>
                                    </th>

                                    <th class="stock-info__th stock-info__th--sortable" data-key="type"
                                        data-type="text">
                                        Type <span class="stock-info__sort-indicator" aria-hidden="true"></span>
                                    </th>

                                    <th class="stock-info__th stock-info__th--sortable" data-key="qty"
                                        data-type="number">
                                        Quantity <span class="stock-info__sort-indicator" aria-hidden="true"></span>
                                    </th>

                                    <th class="stock-info__th stock-info__th--sortable stock-info__th--price"
                                        data-key="price" data-type="number">
                                        Price <span class="stock-info__sort-indicator" aria-hidden="true"></span>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="stock-info__tbody" id="stockInfoTbody">
                                @foreach ($items as $item)
                                <tr class="stock-info__row" data-code="{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}"
                                    data-name="{{ $item->name }}" data-type="{{ $item->type }}"
                                    data-qty="{{ $item->quantity }}" data-price="{{ $item->price }}">
                                    <td class="stock-info__td stock-info__td--icon">
                                        <img src="{{ $item->image_path ? asset('storage/'.$item->image_path) : asset('/img/item-placeholder.png') }}"
                                            alt="" class="stock-info__item-icon">
                                    </td>
                                    <td class="stock-info__td stock-info__td--code">{{ str_pad($item->id, 3, '0',
                                        STR_PAD_LEFT) }}</td>
                                    <td class="stock-info__td stock-info__td--name">
                                        {{ $item->name }}</td>
                                    <td class="stock-info__td">
                                        <span class="stock-info__pill">{{ ucfirst($item->type) }}</span>
                                    </td>
                                    <td class="stock-info__td">{{ $item->quantity }}</td>
                                    <td class="stock-info__td stock-info__td--price">{{ $item->price}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/stockInfo.js"> </script>
    <script src="/js/navSearch.js"></script>

</body>

</html>