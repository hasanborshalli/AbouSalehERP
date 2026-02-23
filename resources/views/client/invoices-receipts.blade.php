@extends('client.layout')

@section('title', 'Client • Receipts')

@section('content')
<section class="client-page" aria-label="Receipts">
    <div class="client-page__header">
        <h2>Receipts</h2>
        <a class="client-back" href="{{ route('client.invoices') }}">← Back</a>
    </div>

    <table class="client-table" aria-label="Receipts table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Paid Date</th>
                <th>Amount</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
            <tr>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('d M Y') : '—' }}</td>
                <td>{{ number_format((float)$inv->amount) }} $</td>
                <td>
                    <div class="actions">
                        <a class="btn" href="{{ route('client.invoices.receipt.download', $inv->id) }}">Download
                            Receipt</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No paid invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection