@extends('worker.layout')
@section('title', 'Worker • Payments')

@section('content')
<section class="client-page">
    <div class="client-page__header">
        <h2>Payment Schedule</h2>
    </div>

    {{-- Summary strip --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px; margin-bottom:18px;">
        <div class="client-panel" style="padding:14px 18px;">
            <div
                style="font-size:10px;font-weight:700;opacity:.5;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                Total Received</div>
            <div style="font-size:22px;font-weight:800;color:#15803d;">${{ number_format($totalPaid,2) }}</div>
        </div>
        <div class="client-panel" style="padding:14px 18px;">
            <div
                style="font-size:10px;font-weight:700;opacity:.5;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                Still Owed</div>
            <div style="font-size:22px;font-weight:800;color:#d97706;">${{ number_format($totalPending,2) }}</div>
        </div>
        <div class="client-panel" style="padding:14px 18px;">
            <div
                style="font-size:10px;font-weight:700;opacity:.5;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                Total Payments</div>
            <div style="font-size:22px;font-weight:800;">{{ $payments->count() }}</div>
        </div>
    </div>

    <table class="client-table">
        <thead>
            <tr>
                <th>Payment #</th>
                <th>Contract / Scope</th>
                <th>Due Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Paid On</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
            <tr>
                <td style="font-family:monospace; font-size:12px;">{{ $p->payment_number }}</td>
                <td>
                    <div style="font-weight:600; font-size:13px;">{{ $p->contract->scope_of_work }}</div>
                    @if($p->contract->project)
                    <div style="font-size:11px; opacity:.55;">{{ $p->contract->project->name }}</div>
                    @endif
                </td>
                <td>{{ $p->due_date->format('d M Y') }}</td>
                <td><strong>${{ number_format($p->amount,2) }}</strong></td>
                <td>
                    @if($p->status === 'paid')
                    <span class="badge badge--completed">Paid</span>
                    @else
                    <span class="badge badge--progress">Pending</span>
                    @endif
                </td>
                <td style="font-size:12px;opacity:.7;">{{ $p->paid_at ? $p->paid_at->format('d M Y') : '—' }}</td>
                <td>
                    @if($p->status === 'paid' && $p->receipt_path)
                    <a class="btn" href="{{ route('worker.payments.receipt', $p) }}">↓ Receipt</a>
                    @elseif($p->status === 'paid')
                    <span style="font-size:11px;opacity:.45;">Generating…</span>
                    @else
                    —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;opacity:.5;padding:20px;">No payments yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection