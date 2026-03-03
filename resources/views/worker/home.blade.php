@extends('worker.layout')
@section('title', 'Worker Portal')

@section('content')
@php
$totalContract = $contracts->sum('total_amount');
$totalPaid = $contracts->flatMap->payments->where('status','paid')->sum('amount');
$totalPending = $contracts->flatMap->payments->where('status','pending')->sum('amount');
$nextPayment = $contracts->flatMap->pendingPayments->sortBy('due_date')->first();
@endphp

<section class="client-page">
    <div class="client-page__header">
        <h2>Welcome, {{ auth()->user()->name }}</h2>
    </div>

    {{-- KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; margin-bottom:20px;">
        <div class="client-panel" style="padding:16px 20px;">
            <div
                style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.5;margin-bottom:6px;">
                Total Contracted</div>
            <div style="font-size:26px;font-weight:800;">${{ number_format($totalContract,2) }}</div>
        </div>
        <div class="client-panel"
            style="padding:16px 20px; border-color:rgba(21,128,61,.2); background:rgba(21,128,61,.04);">
            <div
                style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.5;margin-bottom:6px;">
                Total Received</div>
            <div style="font-size:26px;font-weight:800;color:#15803d;">${{ number_format($totalPaid,2) }}</div>
        </div>
        <div class="client-panel"
            style="padding:16px 20px; border-color:rgba(217,119,6,.2); background:rgba(217,119,6,.04);">
            <div
                style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.5;margin-bottom:6px;">
                Still Owed</div>
            <div style="font-size:26px;font-weight:800;color:#d97706;">${{ number_format($totalPending,2) }}</div>
        </div>
        <div class="client-panel" style="padding:16px 20px;">
            <div
                style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.5;margin-bottom:6px;">
                Active Contracts</div>
            <div style="font-size:26px;font-weight:800;">{{ $contracts->count() }}</div>
        </div>
    </div>

    @if($nextPayment)
    <div class="client-panel"
        style="padding:16px 20px; border-color:rgba(42,127,176,.2); background:rgba(42,127,176,.04); margin-bottom:20px;">
        <div style="font-size:12px;font-weight:700;opacity:.6;margin-bottom:4px;">⏰ Next Scheduled Payment</div>
        <div style="font-size:18px;font-weight:700;">
            ${{ number_format($nextPayment->amount,2) }}
            <span style="font-size:13px;font-weight:400;opacity:.65;">due {{ $nextPayment->due_date->format('d M Y')
                }}</span>
        </div>
        <div style="font-size:12px;opacity:.55;margin-top:2px;">{{ $nextPayment->contract->scope_of_work }}</div>
    </div>
    @endif

    {{-- Quick links --}}
    <section class="client-grid">
        <article class="client-card">
            <div class="client-card__title">My Contracts</div>
            <div class="client-card__desc">View your active contracts, scope of work, and download contract PDFs.</div>
            <a class="client-card__cta" href="{{ route('worker.contracts') }}"><span>→</span></a>
        </article>
        <article class="client-card">
            <div class="client-card__title">Payment Schedule</div>
            <div class="client-card__desc">Track all monthly payments — what's been paid and what's upcoming.</div>
            <a class="client-card__cta" href="{{ route('worker.payments') }}"><span>→</span></a>
        </article>
        <article class="client-card">
            <div class="client-card__title">Settings</div>
            <div class="client-card__desc">Update your profile, contact info, and password.</div>
            <a class="client-card__cta" href="{{ route('worker.settings') }}"><span>→</span></a>
        </article>
    </section>
</section>
@endsection