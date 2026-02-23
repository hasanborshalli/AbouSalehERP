@extends('client.layout')
@section('title','Client • Notifications')

@section('content')
<section class="client-page" aria-label="Notifications">
    <div class="client-page__header notif-header">
        <div class="notif-header__left">
            <h2 class="notif-header__title">Notifications</h2>
        </div>

        <div class="notif-header__right">
            <span class="notif-pill">
                Unread: <strong>{{ $unreadCount }}</strong>
            </span>

            <form method="POST" action="{{ route('client.notifications.readAll') }}">
                @csrf
                <button class="notif-btn notif-btn--ghost" type="submit">
                    Mark all read
                </button>
            </form>

            <a class="notif-btn notif-btn--soft" href="{{ route('client.contracts') }}">
                ← Back
            </a>
        </div>
    </div>

    <div class="client-panel" style="margin-top:12px;">
        @if($notifications->isEmpty())
        <p>No notifications.</p>
        @else
        <div class="notif-list">
            @foreach($notifications as $n)
            <article class="notif-card {{ $n->read_at ? '' : 'notif-card--unread' }}">
                <div class="notif-card__row">
                    <div class="notif-card__main">
                        <div class="notif-card__title-row">
                            <h3 class="notif-card__title">{{ $n->title }}</h3>
                            @if(!$n->read_at)
                            <span class="notif-badge">NEW</span>
                            @endif
                        </div>

                        <p class="notif-card__message">{{ $n->message }}</p>

                        <div class="notif-card__meta">
                            {{ $n->created_at->format('d M Y • H:i') }}
                        </div>
                    </div>

                    <div class="notif-card__actions">
                        @if($n->url)
                        <a href="{{ $n->url }}" class="notif-btn notif-btn--primary">Open</a>
                        @endif

                        @if(!$n->read_at)
                        <form method="POST" action="{{ route('client.notifications.read', $n->id) }}">
                            @csrf
                            <button class="notif-btn notif-btn--ghost" type="submit">Mark read</button>
                        </form>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <div style="margin-top:12px;">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</section>
@endsection