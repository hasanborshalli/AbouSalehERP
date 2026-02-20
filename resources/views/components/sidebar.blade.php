@php($u = auth()->user())
<aside class="app-sidebar" aria-label="Sidebar">
    <div class="app-sidebar__profile">
        <div class="app-sidebar__avatar" aria-hidden="true">
            {{-- replace with real image if you want --}}
            <img src="{{$u->avatar }}" alt="" />
        </div>

        <div class="app-sidebar__meta">
            <div class="app-sidebar__name">{{ $u?->name ?? '—' }}</div>
            <div class="app-sidebar__id">ID {{str_pad($u?->login_id ?? $u?->id, 5, '0', STR_PAD_LEFT)}}</div>
        </div>
    </div>

    <nav class="app-sidebar__nav">
        <a class="app-sidebar__link {{ activeRoute('dashboard') }}" href="{{ route('dashboard') }}">
            <span>Dashboard</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('inventory*') }}" href="{{ route('inventory.overview') }}">
            <span>Inventory</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('clients*') }}" href="{{ route('clients.overview') }}">
            <span>Clients</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('invoices*') }}" href="{{ route('invoices.overview') }}">
            <span>Invoices</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('accounting*') }}" href="{{ route('accounting.overview') }}">
            <span>Accounting</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('apartments*') }}" href="{{ route('apartments.overview') }}">
            <span>Apartments</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('settings*') }}" href="{{ route('settings.overview') }}">
            <span>Settings</span>
        </a>
    </nav>

    <div class="app-sidebar__footer">
        <a class="app-sidebar__logout" href="{{ route('logout') }}">
            <span class="app-sidebar__logout-icon" aria-hidden="true">↩</span>
            <span>Logout</span>
        </a>
    </div>
</aside>