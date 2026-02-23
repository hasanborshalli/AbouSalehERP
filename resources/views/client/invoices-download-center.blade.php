@extends('client.layout')

@section('title', 'Client • Download Center')

@section('content')
<section class="client-page" aria-label="Download center">
    <div class="client-page__header">
        <h2>Download Center (Unpaid Invoices)</h2>
        <a class="client-back" href="{{ route('client.invoices') }}">← Back</a>
    </div>

    <div class="form-card" style="margin-bottom:14px;">
        <div style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
            <div>
                <div style="font-weight:900;">ZIP Bundle</div>
                <div style="opacity:.85; font-size:13px;">Download all unpaid invoice PDFs as one ZIP file.</div>
            </div>
            <form method="POST" action="{{ route('client.invoices.unpaid.zip') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Download ZIP</button>
            </form>
        </div>
    </div>

    <table class="client-table" aria-label="Unpaid invoices">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Due Date</th>
                <th>Amount</th>
                <th>PDF</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unpaid as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                    <td>{{ number_format((float)$inv->amount, 2) }}</td>
                    <td>
                        <div class="actions">
                            @if($inv->pdf_path)
                                <a class="btn" href="{{ route('client.invoices.pdf.download', $inv->id) }}">Download PDF</a>
                            @else
                                <span>PDF not generated yet.</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No unpaid invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
