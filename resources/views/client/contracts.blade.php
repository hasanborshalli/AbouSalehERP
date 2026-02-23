@extends('client.layout')

@section('title', 'Client • Contracts')

@section('content')
<section class="client-grid" aria-label="Client contracts">
    <article class="client-card">
        <div class="client-card__title">Contract Overview</div>
        <div class="client-card__desc">View a table showing Project Name, Start Date, Estimated Completion, and Status.</div>
        <a class="client-card__cta" href="{{ route('client.contracts.overview') }}" aria-label="Open contract overview">
            <span>→</span>
        </a>
    </article>

    <article class="client-card">
        <div class="client-card__title">Project Manager</div>
        <div class="client-card__desc">See the name and contact details of the manager assigned to your project.</div>
        <a class="client-card__cta" href="{{ route('client.contracts.manager') }}" aria-label="Open project manager page">
            <span>→</span>
        </a>
    </article>

    <article class="client-card">
        <div class="client-card__title">Document Access</div>
        <div class="client-card__desc">View or download your contract PDF documents.</div>
        <a class="client-card__cta" href="{{ route('client.contracts.documents') }}" aria-label="Open contract documents">
            <span>→</span>
        </a>
    </article>

    <article class="client-card">
        <div class="client-card__title">Progress Tracker</div>
        <div class="client-card__desc">Track how much of the contract is completed based on paid amount vs final price.</div>
        <a class="client-card__cta" href="{{ route('client.contracts.progress') }}" aria-label="Open progress tracker">
            <span>→</span>
        </a>
    </article>
</section>
@endsection
