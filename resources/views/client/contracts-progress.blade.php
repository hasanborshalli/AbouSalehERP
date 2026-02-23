@extends('client.layout')

@section('title', 'Client • Progress Tracker')

@section('content')
<section class="client-page" aria-label="Progress tracker">
    <div class="client-page__header">
        <h2>Progress Tracker</h2>
        <a class="client-back" href="{{ route('client.contracts') }}">← Back</a>
    </div>

    @forelse($rows as $r)
        @php
            $c = $r['contract'];
            $status = $r['status'];
            $badge = $status === 'Completed' ? 'badge--completed' : ($status === 'Signed' ? 'badge--signed' : 'badge--progress');
        @endphp
        <div class="progress-row">
            <div class="progress-row__top">
                <div>
                    <div style="font-weight: 900;">{{ $c->project?->name ?? 'Project' }} • Contract #{{ $c->id }}</div>
                    <div style="opacity:.85; font-size:13px;">Apartment: {{ $c->apartment?->unit_number ?? '—' }}</div>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="badge {{ $badge }}">{{ $status }}</span>
                    <div style="font-weight:900;">{{ number_format($r['progress'], 2) }}%</div>
                </div>
            </div>

            <div class="progress-bar" role="progressbar" aria-valuenow="{{ $r['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar__fill" style="width: {{ $r['progress'] }}%;"></div>
            </div>

            <div style="margin-top:10px; font-size:13px; opacity:.9;">
                Paid: <strong>{{ number_format($r['paid_amount'], 2) }}</strong>
                / Final Price: <strong>{{ number_format($r['final_price'], 2) }}</strong>
            </div>
        </div>
    @empty
        <div class="form-card">No contracts found.</div>
    @endforelse
</section>
@endsection
