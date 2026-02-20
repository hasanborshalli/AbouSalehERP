<header class="app-navbar" aria-label="Top navigation">
    <div class="app-navbar__left">
        {{-- Mobile sidebar toggle (works with checkbox in layout) --}}
        <label for="sidebarToggle" class="app-navbar__burger" aria-label="Open menu">
            <img src="/img/burgermenu.svg" alt="Open menu" />
        </label>


        <img class="app-navbar__logo" src="/img/abosaleh-logo.png" alt="Abou Saleh Logo" />
        @php($u = auth()->user())
        <h1 class="app-navbar__title">Welcome {{$u?->name ?? '—'}}!</h1>
    </div>

    <div class="app-navbar__right">
        <div class="app-navbar__search">
            <span class="app-navbar__search-icon" aria-hidden="true">
                <img src="/img/search.svg" class="search-icon">
            </span>

            <input id="navSearchInput" type="search" placeholder="Search pages..." aria-label="Search pages"
                autocomplete="off" />

            <div class="app-navbar__search-results" id="navSearchResults" hidden></div>
        </div>


        <button class="app-navbar__bell" type="button" aria-label="Notifications">🔔</button>
    </div>
</header>