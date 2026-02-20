<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="app-shell dashboard-page">
    {{-- CSS-only sidebar toggle --}}
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <div class="app-shell__sidebar">
        <x-sidebar />
    </div>

    <div class="app-shell__main">
        <x-navbar />

        <main class="app-content dashboard-content">
            <section class="dashboard-grid">
                {{-- Row 1: Views (wide) --}}
                <section class="dashboard-card dashboard-card--views">
                    <div class="dashboard-card__header">
                        <h2 class="dashboard-card__title">Views</h2>
                    </div>

                    <div class="dashboard-metrics">
                        <div class="dashboard-metric">
                            <div class="dashboard-metric__icon"></div>
                            <div class="dashboard-metric__text">
                                <div class="dashboard-metric__value">{{ number_format($totalProducts) }}</div>
                                <div class="dashboard-metric__label">Total products</div>
                            </div>
                        </div>

                        <div class="dashboard-metric">
                            <div class="dashboard-metric__icon"></div>
                            <div class="dashboard-metric__text">
                                <div class="dashboard-metric__value">{{number_format($totalOrders)}}</div>
                                <div class="dashboard-metric__label">Orders</div>
                            </div>
                        </div>

                        <div class="dashboard-metric">
                            <div class="dashboard-metric__icon"></div>
                            <div class="dashboard-metric__text">
                                <div class="dashboard-metric__value">{{ number_format($totalStock) }}</div>
                                <div class="dashboard-metric__label">Total stock</div>
                            </div>
                        </div>

                        <div class="dashboard-metric dashboard-metric--danger">
                            <div class="dashboard-metric__icon dashboard-metric__icon--danger"></div>
                            <div class="dashboard-metric__text">
                                <div class="dashboard-metric__value">{{ number_format($outOfStock) }}</div>
                                <div class="dashboard-metric__label">Out of stock</div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Row 2: 3 cards --}}
                <section class="dashboard-card dashboard-card--users">
                    <div class="dashboard-card__header">
                        <h2 class="dashboard-card__title">No of users</h2>
                    </div>

                    <div class="dashboard-users">
                        <div class="dashboard-users__box"></div>
                        <div class="dashboard-users__count">{{ number_format($totalUsers) }}</div>
                        <div class="dashboard-users__sub">Total users</div>
                    </div>
                </section>

                <section class="dashboard-card dashboard-card--inventory">
                    <div class="dashboard-card__header">
                        <h2 class="dashboard-card__title">Inventory values</h2>
                    </div>


                    <div class="dashboard-inv">
                        <div class="dashboard-inv__chart">
                            <canvas id="inventoryPie"></canvas>
                        </div>

                        <div class="dashboard-inv__legend">
                            @php
                            $swatches = ['a', 'b', 'c', 'd', 'e']; // extend if needed
                            @endphp
                            @foreach ($pieLabels as $label)
                            <div class="dashboard-legend">
                                <span
                                    class="dashboard-legend__swatch dashboard-legend__swatch--{{ $swatches[$loop->index] ?? 'a' }}"></span>
                                <span class="dashboard-legend__text">{{$label}}</span>
                            </div>
                            @endforeach

                        </div>
                    </div>

                </section>

                <section class="dashboard-card dashboard-card--top">
                    <div class="dashboard-card__header">
                        <h2 class="dashboard-card__title">Top clients</h2>
                    </div>

                    <ul class="dashboard-top">
                        @forelse($topClients as $c)
                        <li>{{ $c->user->name }}</li>
                        @empty
                        <li>No clients yet</li>
                        @endforelse
                    </ul>
                </section>

                {{-- Row 3: Expenses (wide) --}}
                <section class="dashboard-card dashboard-card--expenses">
                    <div class="dashboard-card__header dashboard-card__header--row">
                        <h2 class="dashboard-card__title">Expenses vs profits</h2>
                        <span class="dashboard-card__muted">Last 6 month</span>
                    </div>

                    <div class="dashboard-expenses-chart">
                        <canvas id="expensesChart"></canvas>
                    </div>
                </section>

            </section>
        </main>

        {{-- overlay for mobile sidebar --}}
        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>

        <div class="dashboard-stock-chart">
            <canvas id="stockValueChart"></canvas>
        </div>
    </div>
    <script>
        //this is pie chart
        const pieCtx = document.getElementById('inventoryPie');
new Chart(pieCtx, {
    type: 'pie', // or 'pie'
    data: {
        labels: @json($pieLabels),
        datasets: [{
            data: @json($pieValues),
            backgroundColor: [
                '#5fe7ea', // light cyan
                '#3fb6d6', // blue
                '#2a7fb0'  // dark blue
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false // we use custom legend on the right
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ctx.label + ': ' + ctx.raw + '%';
                    }
                }
            }
        }
    }
});


//this is chart expenses vs profits

const expCtx = document.getElementById('expensesChart');

const labels = @json($labels);
const profits = @json($revenues);
const expenses = @json($expenses);
const net = @json($net);

new Chart(expCtx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Expenses',
        data: expenses,
        tension: 0.35,
        fill: true,
        pointRadius: 2,
        borderWidth: 4,
        borderColor: '#5fe7ea',
        backgroundColor: 'rgba(95, 231, 234, 0.25)',
      },
      {
        label: 'Profits',
        data: profits,
        tension: 0.35,
        fill: true,
        pointRadius: 2,
        borderWidth: 4,
        borderColor: '#3fb6d6',
        backgroundColor: 'rgba(63, 182, 214, 0.25)',
      },
      {
        label: 'Net',
        data: net,
        tension: 0.35,
        fill: true,
        pointRadius: 2,
        borderWidth: 4,
        borderColor: '#2a7fb0',
        backgroundColor: 'rgba(42, 127, 176, 0.25)',
      },
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
        labels: {
          usePointStyle: true,
          pointStyle: 'circle',
          boxWidth: 8,
          padding: 14
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        border: { display: false },
        ticks: { color: 'rgba(0,0,0,0.55)', font: { size: 11 } }
      },
      y: {
        beginAtZero: true,
        grid: { color: 'rgba(0,0,0,0.10)' },
        border: { display: false },
        ticks: { color: 'rgba(0,0,0,0.55)', font: { size: 11 } }
      }
    }
  }
});
    </script>
    <script src="/js/navSearch.js"></script>

</body>

</html>