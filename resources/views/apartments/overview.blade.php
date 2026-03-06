<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Apartments</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/apartments.css" />
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
            @if (session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">X</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            <section class="apartments" aria-label="Apartments page">

                <div class="apartments__grid">

                    {{-- Left: Existing projects --}}
                    <section class="dashboard-card apartments__card apartments__card--existing">
                        <header class="apartments__card-header">
                            <h2 class="apartments__title">Existing projects</h2>

                            <a class="apartments__arrow" href="{{ route('apartments.existing-projects') }}"
                                aria-label="Go to existing projects">
                                <img src="/img/arrow-right.svg" alt="" class="apartments__arrow-icon">
                            </a>
                        </header>

                        <div class="apartments__projects">
                            @forelse ($projects as $project)
                            <a href="{{ route('apartments.project', $project->id) }}" class="apartments__project">
                                {{ $project->name }}
                            </a>
                            @empty
                            <div class="apartments__empty">No projects yet.</div>
                            @endforelse
                        </div>

                    </section>


                    {{-- Right column: New projects + stats --}}
                    <div class="apartments__right">

                        {{-- New projects --}}
                        <section class="dashboard-card apartments__card apartments__card--new">
                            <header class="apartments__card-header">
                                <h2 class="apartments__title">New projects</h2>

                                <a class="apartments__arrow apartments__arrow--small"
                                    href="{{ route('apartments.create-project')  }}" aria-label="Create new project">
                                    <img src="/img/arrow-right.svg" alt="" class="apartments__arrow-icon">
                                </a>
                            </header>

                            <div class="apartments__body apartments__body--new">
                                <p class="apartments__note apartments__note--small">
                                    This section will navigate to “Create new project”.
                                </p>
                                <p class="apartments__note apartments__note--small">
                                    (إنشاء مشروع جديد + إضافة وحدات/شقق + أسعار + حالة البيع)
                                </p>
                            </div>
                        </section>

                        {{-- Number of apartments --}}
                        <section class="dashboard-card apartments__card apartments__card--stats">
                            <header class="apartments__card-header apartments__card-header--tight">
                                <h2 class="apartments__title">Number of apartments</h2>
                            </header>

                            <div class="apartments__stats">
                                <div class="apartments__stat">
                                    <div class="apartments__stat-title">Sold</div>
                                    <div class="apartments__stat-value">{{$soldCount}}</div>
                                </div>

                                <div class="apartments__stat">
                                    <div class="apartments__stat-title">Not sold yet</div>
                                    <div class="apartments__stat-value">{{$notSoldCount}}</div>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/navSearch.js"></script>

</body>

</html>