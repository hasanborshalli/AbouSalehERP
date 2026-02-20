<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clients</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}

    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    {{-- page specific --}}
    <link rel="stylesheet" href="/css/clients.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="clients" aria-label="Clients page">
                @if (session('success'))
                <div class="alert alert--success" data-alert>
                    <span class="alert__icon">✔</span>
                    <span class="alert__text">{{ session('success') }}</span>
                    <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
                </div>
                @endif
                <section class="dashboard-card clients__card">
                    <div class="clients__grid">

                        {{-- Left: New clients --}}
                        <article class="clients-panel clients-panel--new">
                            <header class="clients-panel__header">
                                <h2 class="clients-panel__title">New clients</h2>

                                <a href="{{ route('clients.add-client') }}" class="clients-panel__arrow"
                                    aria-label="Go to new clients">
                                    <span class="clients-panel__arrow-icon"><img src="/img/arrow-right.svg"
                                            class="arrow-right"></span>
                                </a>
                            </header>

                            <div class="clients-panel__body">
                                <p class="clients-panel__big">
                                    Enter to add<br>new client
                                </p>


                            </div>
                        </article>

                        {{-- Middle: Existing clients --}}
                        <article class="clients-panel clients-panel--existing">
                            <header class="clients-panel__header">
                                <h2 class="clients-panel__title">Existing clients</h2>

                                <a href="{{ route('clients.existing-clients') }}"
                                    class="clients-panel__arrow clients-panel__arrow--small"
                                    aria-label="Go to existing clients">
                                    <span class="clients-panel__arrow-icon"><img src="/img/arrow-right.svg"
                                            class="arrow-right"></span>
                                </a>
                            </header>



                            <div class="clients-list" aria-label="Existing clients list">
                                @foreach($existingClients as $client)
                                <div class="clients-list__row">
                                    <span class="clients-list__num">{{ $client->id +1 }}.</span>
                                    <span class="clients-list__name">{{ $client->user->name }}</span>
                                </div>
                                @endforeach
                            </div>

                        </article>

                        {{-- Right: Stats --}}
                        <aside class="clients-stats" aria-label="Clients stats">
                            <div class="clients-stat">
                                <h3 class="clients-stat__title">Number<br>of clients</h3>
                                <div class="clients-stat__value">{{$clients_count}}</div>
                            </div>

                            <div class="clients-stat">
                                <h3 class="clients-stat__title">Clients<br>volume</h3>
                                <div class="clients-stat__value clients-stat__value--money">
                                    {{number_format($clients_volume)}}$</div>
                            </div>
                        </aside>

                    </div>
                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>