@php($u = auth()->user())

<aside class="app-sidebar" aria-label="Client Sidebar">
    <div class="app-sidebar__profile">
        <div class="app-sidebar__avatar" aria-hidden="true">
            <img src="{{ $u->avatar }}" alt="" />
        </div>

        <div class="app-sidebar__meta">
            <div class="app-sidebar__name">{{ $u?->name ?? '—' }}</div>
            <div class="app-sidebar__id">ID {{ str_pad($u?->login_id ?? $u?->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <nav class="app-sidebar__nav">
        <a class="app-sidebar__link {{ activeRoute('client.contracts*') }}" href="{{ route('client.contracts') }}">
            <span>Contracts</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('client.invoices*') }}" href="{{ route('client.invoices') }}">
            <span>Invoices</span>
        </a>
        <a class="app-sidebar__link {{ activeRoute('client.settings*') }}" href="{{ route('client.settings') }}">
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