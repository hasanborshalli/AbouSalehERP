@extends('client.layout')

@section('title', 'Client • Invoices')

@section('content')
<section class="client-grid" aria-label="Client invoices">
    <article class="client-card">
        <div class="client-card__title">Invoice List</div>
        <div class="client-card__desc">A table with invoice information (number, dates, amount, status) and PDF access.
        </div>
        <a class="client-card__cta" href="{{ route('client.invoices.list') }}" aria-label="Open invoices list">
            <span>→</span>
        </a>
    </article>

    <article class="client-card">
        <div class="client-card__title">Receipts</div>
        <div class="client-card__desc">Download paid invoice receipts (or invoice PDFs if a separate receipt is not
            generated).</div>
        <a class="client-card__cta" href="{{ route('client.invoices.receipts') }}" aria-label="Open receipts">
            <span>→</span>
        </a>
    </article>

    <article class="client-card">
        <div class="client-card__title">Download Center</div>
        <div class="client-card__desc">Download official tax unpaid invoices for accounting (single PDFs or ZIP bundle).
        </div>
        <a class="client-card__cta" href="{{ route('client.invoices.download-center') }}"
            aria-label="Open download center">
            <span>→</span>
        </a>
    </article>

</section>
@endsection