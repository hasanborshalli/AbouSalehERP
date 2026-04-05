@php
$u = auth()->user();
$unread = $u?->unreadNotifications()?->count() ?? 0;
$latest = $u?->notifications()?->take(5)->get() ?? collect();
$isWorker = $u?->role === 'worker';
$isClient = $u?->role === 'client';
@endphp
{{-- RTL CSS loaded globally via navbar component --}}
<link rel="stylesheet" href="/css/rtl.css">
<header class="app-navbar" aria-label="Top navigation">
    <div class="app-navbar__left">
        <label for="sidebarToggle" class="app-navbar__burger" aria-label="Open menu">
            <img src="/img/burgermenu.svg" alt="Open menu" />
        </label>
        <img class="app-navbar__logo" src="/img/abosaleh-logo.png" alt="Abou Saleh Logo" />
        <h1 class="app-navbar__title">Welcome {{ $u?->name ?? '—' }}!</h1>
    </div>

    <div class="app-navbar__right">
        <div class="app-navbar__search">
            <span class="app-navbar__search-icon" aria-hidden="true">
                <img src="/img/search.svg" class="search-icon">
            </span>
            <input id="navSearchInput" type="search" placeholder="{{ __('Search Pages') }}..."
                aria-label="{{ __('ui.search') }}" autocomplete="off" />
            <div class="app-navbar__search-results" id="navSearchResults" hidden></div>
        </div>

        {{-- Google Translate language toggle --}}
        <div class="lang-toggle" id="langToggleWrap">
            <button type="button" class="lang-toggle__btn lang-toggle__btn--active" id="langBtnEN"
                onclick="switchLang('en')" translate="no">EN</button>
            <button type="button" class="lang-toggle__btn" id="langBtnAR" onclick="switchLang('ar')"
                translate="no">العربية</button>
        </div>
        {{-- Hidden Google Translate element --}}
        <div id="google_translate_element" style="display:none;"></div>
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
                @if($isClient)
                <a class="notif-btn notif-btn--soft" href="{{ route('client.notifications') }}">View all</a>
                @endif
            </div>

            <div class="nav-bell__list">
                @forelse($latest as $n)
                <div class="nav-bell__item {{ $n->read_at ? '' : 'is-unread' }}">
                    <div class="nav-bell__title">{{ $n->title }}</div>
                    <div class="nav-bell__msg">{{ $n->message }}</div>
                    <div class="nav-bell__meta">{{ $n->created_at->diffForHumans() }}</div>
                    <div class="nav-bell__actions">
                        @if($n->url)
                        <a href="{{ $n->url }}" class="nav-notif-btn nav-notif-btn--primary">Open</a>
                        @endif
                        @if(!$n->read_at)
                        @if($isClient)
                        <form method="POST" action="{{ route('client.notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="nav-notif-btn nav-notif-btn--ghost">Mark read</button>
                        </form>
                        @elseif($isWorker)
                        <form method="POST" action="{{ route('worker.notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="nav-notif-btn nav-notif-btn--ghost">Mark read</button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>
                @empty
                <div class="nav-bell__empty">No notifications</div>
                @endforelse
            </div>
        </div>
    </div>
    {{-- Nav search: role injected here so navSearch.js is role-aware.
    This is the ONE place navSearch.js is loaded — remove any per-page includes. --}}
    <script>
        window.NAV_ROLE="{{ auth()->user()?->role ?? 'admin' }}";
    </script>
    <script src="/js/navSearch.js" defer></script>

    {{-- Google Translate integration --}}
    <script>
        // Initialize Google Translate
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,ar',
            autoDisplay: false,
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }

    function switchLang(lang) {
        var btnEN = document.getElementById('langBtnEN');
        var btnAR = document.getElementById('langBtnAR');

        if (lang === 'en') {
            // Restore to English — reload without translate cookie
            var frame = document.querySelector('.goog-te-banner-frame');
            if (frame) {
                // Click "Show original" inside the translate bar if visible
                try {
                    var innerDoc = frame.contentDocument || frame.contentWindow.document;
                    var restoreBtn = innerDoc.querySelector('.goog-te-button button');
                    if (restoreBtn) restoreBtn.click();
                } catch(e) {}
            }
            // Remove Google translate cookies and reload
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + location.hostname + ';';
            localStorage.setItem('siteLang', 'en');
            if (btnEN) { btnEN.classList.add('lang-toggle__btn--active'); }
            if (btnAR) { btnAR.classList.remove('lang-toggle__btn--active'); }
            location.reload();
            return;
        }

        // Translate to Arabic
        localStorage.setItem('siteLang', 'ar');
        if (btnEN) { btnEN.classList.remove('lang-toggle__btn--active'); }
        if (btnAR) { btnAR.classList.add('lang-toggle__btn--active'); }

        // Set the Google Translate cookie directly
        document.cookie = 'googtrans=/en/ar; path=/';
        document.cookie = 'googtrans=/en/ar; path=/; domain=' + location.hostname;

        // Trigger translation via the hidden select element
        var sel = document.querySelector('.goog-te-combo');
        if (sel) {
            sel.value = 'ar';
            sel.dispatchEvent(new Event('change'));
        } else {
            // Widget not loaded yet — reload with cookie set
            location.reload();
        }
    }

    // On page load: restore the button active state from localStorage
    document.addEventListener('DOMContentLoaded', function () {
        var lang = localStorage.getItem('siteLang') || 'en';
        var btnEN = document.getElementById('langBtnEN');
        var btnAR = document.getElementById('langBtnAR');

        if (lang === 'ar') {
            if (btnEN) btnEN.classList.remove('lang-toggle__btn--active');
            if (btnAR) btnAR.classList.add('lang-toggle__btn--active');
        } else {
            if (btnEN) btnEN.classList.add('lang-toggle__btn--active');
            if (btnAR) btnAR.classList.remove('lang-toggle__btn--active');
        }

        // Hide the Google Translate banner bar that appears at the top
        var style = document.createElement('style');
        style.textContent = [
            '.goog-te-banner-frame { display:none !important; }',
            'body { top: 0 !important; }',
            '.skiptranslate { display:none !important; }',
            '#goog-gt-tt { display:none !important; }',
            '.goog-te-balloon-frame { display:none !important; }',
        ].join('');
        document.head.appendChild(style);
    });
    </script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
</header>