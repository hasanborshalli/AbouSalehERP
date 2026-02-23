@extends('client.layout')

@section('title', 'Client • Invoice List')

@section('content')
<section class="client-page" aria-label="Invoice list">
    <div class="client-page__header">
        <h2>Invoice List</h2>
        <a class="client-back" href="{{ route('client.invoices') }}">← Back</a>
    </div>

    <table class="client-table" aria-label="Invoices table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                @php
                    $status = $inv->status === 'paid' ? 'Completed' : 'In Progress';
                    $badge = $inv->status === 'paid' ? 'badge--completed' : 'badge--progress';
                @endphp
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->issue_date ? \Carbon\Carbon::parse($inv->issue_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                    <td>{{ number_format((float)$inv->amount, 2) }}</td>
                    <td><span class="badge {{ $badge }}">{{ $inv->status === 'paid' ? 'Paid' : 'Pending' }}</span></td>
                    <td>
                        <div class="actions">
                            @if($inv->pdf_path)
                                <a class="btn" target="_blank" href="{{ route('client.invoices.pdf.view', $inv->id) }}">View PDF</a>
                                <a class="btn" href="{{ route('client.invoices.pdf.download', $inv->id) }}">Download</a>
                            @else
                                <span>PDF not generated yet.</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
