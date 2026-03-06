@php($u = auth()->user())
<aside class="app-sidebar" aria-label="Sidebar">
    <div class="app-sidebar__profile">
        <div class="app-sidebar__avatar" aria-hidden="true">
            <img src="{{$u->avatar }}" alt="" />
        </div>
        <div class="app-sidebar__meta">
            <div class="app-sidebar__name">{{ $u?->name ?? '—' }}</div>
            <div class="app-sidebar__id">ID {{str_pad($u?->login_id ?? $u?->id, 5, '0', STR_PAD_LEFT)}}</div>
        </div>
    </div>

    <div class="app-sidebar__nav-wrap" id="sidebarNavWrap">
        <nav class="app-sidebar__nav" id="sidebarNav" onscroll="updateScrollHint()">
            <a class="app-sidebar__link {{ activeRoute('dashboard') }}" href="{{ route('dashboard') }}">
                <span>Dashboard</span>
            </a>
            <a class="app-sidebar__link {{ activeRoute('inventory*') }}" href="{{ route('inventory.overview') }}">
                <span>Inventory</span>
            </a>
            @if(auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
            <a class="app-sidebar__link {{ activeRoute('workers*') }}" href="{{ route('workers.index') }}">
                <span>Workers</span>
            </a>
            @endif
            <a class="app-sidebar__link {{ activeRoute('clients*') }}" href="{{ route('clients.overview') }}">
                <span>Clients</span>
            </a>
            <a class="app-sidebar__link {{ activeRoute('apartments*') }}" href="{{ route('apartments.overview') }}">
                <span>Apartments</span>
            </a>
            @if(auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
            <a class="app-sidebar__link {{ activeRoute('managed*') }}" href="{{ route('managed.index') }}">
                <span>Managed Props</span>
            </a>
            @endif
            <a class="app-sidebar__link {{ activeRoute('invoices*') }}" href="{{ route('invoices.overview') }}">
                <span>Invoices</span>
            </a>
            <a class="app-sidebar__link {{ activeRoute('accounting*') }}" href="{{ route('accounting.overview') }}">
                <span>Accounting</span>
            </a>

            @if(auth()->user()->role === 'owner' || auth()->user()->role === 'admin')
            <a class="app-sidebar__link {{ activeRoute('reports*') }}" href="{{ route('reports.index') }}">
                <span>Reports</span>
            </a>
            @endif


            <a class="app-sidebar__link {{ activeRoute('settings*') }}" href="{{ route('settings.overview') }}">
                <span>Settings</span>
            </a>
        </nav>

        <div class="app-sidebar__scroll-hint" id="scrollHint" aria-hidden="true">
            <span>&#8595;</span>
        </div>
    </div>

    <div class="app-sidebar__footer">
        <a class="app-sidebar__logout" href="{{ route('logout') }}">
            <span class="app-sidebar__logout-icon" aria-hidden="true">&#8617;</span>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script>
    function updateScrollHint() {
    var nav  = document.getElementById('sidebarNav');
    var hint = document.getElementById('scrollHint');
    if (!nav || !hint) return;
    var atBottom = nav.scrollTop + nav.clientHeight >= nav.scrollHeight - 8;
    hint.style.opacity = atBottom ? '0' : '1';
}
document.addEventListener('DOMContentLoaded', updateScrollHint);
</script>