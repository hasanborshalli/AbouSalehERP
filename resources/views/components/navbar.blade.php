@php
$u = auth()->user();
$unread = $u?->unreadNotifications()?->count() ?? 0;
$latest = $u?->notifications()?->take(5)->get() ?? collect();
@endphp
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


    </div>


    <div class="nav-bell" style="position:relative;">
        <button type="button" class="nav-bell__btn" id="notifBellBtn" aria-label="Notifications">
            🔔
            @if($unread > 0)
            <span class="nav-bell__badge">{{ $unread }}</span>
            @endif
        </button>

        <div class="nav-bell__menu" id="notifBellMenu" style="display:none;">
            <div class="nav-bell__header">
                <strong>Notifications</strong>
                <a class="notif-btn notif-btn--soft" href="{{ route('client.notifications') }}">View all</a>
            </div>

            <div class="nav-bell__list">
                @forelse($latest as $n)
                <div class="nav-bell__item {{ $n->read_at ? '' : 'is-unread' }}">
                    <div class="nav-bell__title">{{ $n->title }}</div>
                    <div class="nav-bell__msg">{{ $n->message }}</div>
                    <div class="nav-bell__meta">{{ $n->created_at->diffForHumans() }}</div>

                    <div class="nav-bell__actions">
                        @if($n->url)
                        <a href="{{ $n->url }}" class="nav-notif-btn nav-notif-btn--primary">
                            Open
                        </a>
                        @endif

                        @if(!$n->read_at)
                        <form method="POST" action="{{ route('client.notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="nav-notif-btn nav-notif-btn--ghost">
                                Mark read
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="nav-bell__empty">No notifications</div>
                @endforelse
            </div>
        </div>
    </div>
</header>