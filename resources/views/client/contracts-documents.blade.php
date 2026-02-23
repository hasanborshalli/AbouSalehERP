@extends('client.layout')

@section('title', 'Client • Contract Documents')

@section('content')
<section class="client-page" aria-label="Contract documents">
    <div class="client-page__header">
        <h2>Contract Documents</h2>
        <a class="client-back" href="{{ route('client.contracts') }}">← Back</a>
    </div>

    <table class="client-table" aria-label="Documents table">
        <thead>
            <tr>
                <th>Contract</th>
                <th>Project</th>
                <th>Apartment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $c)
            <tr>
                <td>#{{ $c->id }}</td>
                <td>{{ $c->project?->name ?? '—' }}</td>
                <td>{{ $c->apartment?->unit_number ?? '—' }}</td>
                <td>
                    <div class="actions">
                        @if($c->pdf_path)
                        <a class="btn" target="_blank" href="{{ route('client.contracts.pdf.view', $c->id) }}">View
                            PDF</a>
                        <a class="btn" href="{{ route('client.contracts.pdf.download', $c->id) }}">Download</a>
                        @else
                        <span>PDF not generated yet.</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No contracts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection