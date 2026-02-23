<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Client Portal')</title>
    <link rel="icon" href="/img/abosaleh-logo.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">

    {{-- client portal --}}
    <link rel="stylesheet" href="/css/clientPortal.css" />

    @stack('styles')
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-client-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script src="/js/clientNavSearch.js"></script>
</body>

</html>