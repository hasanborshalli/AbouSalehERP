<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inventory</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/inventory.css" />
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="app-shell inventory-page">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <div class="app-shell__sidebar">
        <x-sidebar />
    </div>

    <div class="app-shell__main">
        <x-navbar />

        <main class="app-content inventory-content">
            <section class="inventory-grid">

                <!-- Top row: Stock control -->
                <section class="inventory-card inventory-card--control">
                    <h2 class="inventory-card__title">Stock control</h2>

                    <div class="inventory-control">
                        <div class="inventory-control__text">Enter to<br />edit stock</div>

                        <a class="inventory-control__go" href="{{ route('inventory.stock-control') }}"
                            aria-label="Go to edit stock">
                            <img src="/img/arrow-right.svg" alt="" />
                        </a>
                    </div>
                </section>

                <!-- Top row: Value of stock chart -->
                <section class="inventory-card inventory-card--value">
                    <h2 class="inventory-card__title">Value of stock</h2>

                    <div class="inventory-chart">
                        <canvas id="stockValueChart"></canvas>
                    </div>
                </section>

                <section class="inventory-card inventory-card--table">
                    <div class="inventory-tablebar">
                        <h2 class="inventory-tablebar__title">stock info</h2>

                        <a class="inventory-tablebar__action" href="{{ route('inventory.stock-info') }}"
                            aria-label="Open stock info">
                            <img src="/img/arrow-right.svg" alt="" />
                        </a>
                    </div>

                    <!-- ONE scrolling container -->
                    <div class="inv-table">
                        <table class="inv-table__table">
                            <thead class="inv-table__head">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>

                            <tbody class="inv-table__body">
                                @foreach ($items as $item)
                                <tr>
                                    <td>{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->price }}</td>
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

    <!-- Chart.js -->
    <script>
        window.stockChartData = {
        labels: @json($chartLabels),
        values: @json($chartValues)
    };
    </script>
    <script src="/js/inventory.js"></script>
    <script src="/js/navSearch.js"></script>

</body>

</html>