@extends('worker.layout')
@section('title', 'Worker • Contracts')

@section('content')
<section class="client-page">
    <div class="client-page__header">
        <h2>My Contracts</h2>
    </div>

    @forelse($contracts as $contract)
    @php
    $paid = $contract->payments->where('status','paid')->sum('amount');
    $pending = $contract->payments->where('status','pending')->sum('amount');
    $pct = $contract->total_amount > 0 ? round(($paid / $contract->total_amount) * 100) : 0;
    @endphp
    <div class="client-panel" style="margin-bottom:16px; padding:18px 20px;">
        <div
            style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <div>
                <div style="font-size:16px; font-weight:800; margin-bottom:4px;">{{ $contract->scope_of_work }}</div>
                <div style="font-size:12px; opacity:.55;">
                    {{ ucfirst($contract->category ?? 'General') }}
                    @if($contract->project) · {{ $contract->project->name }} @endif
                    · Contract date: {{ $contract->contract_date->format('d M Y') }}
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                @if($contract->pdf_path)
                <a href="{{ route('worker.contracts.pdf.view', $contract) }}" target="_blank"
                    style="padding:6px 14px; border-radius:999px; background:rgba(42,127,176,.12); color:rgba(42,127,176,.9); text-decoration:none; font-size:12px; font-weight:700;">
                    View Contract PDF
                </a>
                <a href="{{ route('worker.contracts.pdf.download', $contract) }}"
                    style="padding:6px 14px; border-radius:999px; background:rgba(0,0,0,.06); color:rgba(0,0,0,.7); text-decoration:none; font-size:12px; font-weight:700;">
                    ↓ Download
                </a>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div style="margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; font-size:12px; opacity:.6; margin-bottom:5px;">
                <span>Payment progress</span>
                <span>{{ $pct }}% received</span>
            </div>
            <div style="height:8px; border-radius:999px; background:rgba(0,0,0,.08); overflow:hidden;">
                <div
                    style="height:100%; width:{{ $pct }}%; background:#15803d; border-radius:999px; transition:width .3s;">
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px;">
            <div
                style="text-align:center; padding:10px; border-radius:10px; background:rgba(0,0,0,.03); border:1px solid rgba(0,0,0,.07);">
                <div
                    style="font-size:10px; font-weight:700; opacity:.5; text-transform:uppercase; letter-spacing:.04em;">
                    Total</div>
                <div style="font-size:18px; font-weight:800; margin-top:3px;">${{
                    number_format($contract->total_amount,2) }}</div>
            </div>
            <div
                style="text-align:center; padding:10px; border-radius:10px; background:rgba(21,128,61,.05); border:1px solid rgba(21,128,61,.15);">
                <div
                    style="font-size:10px; font-weight:700; opacity:.5; text-transform:uppercase; letter-spacing:.04em;">
                    Received</div>
                <div style="font-size:18px; font-weight:800; color:#15803d; margin-top:3px;">${{ number_format($paid,2)
                    }}</div>
            </div>
            <div
                style="text-align:center; padding:10px; border-radius:10px; background:rgba(217,119,6,.05); border:1px solid rgba(217,119,6,.15);">
                <div
                    style="font-size:10px; font-weight:700; opacity:.5; text-transform:uppercase; letter-spacing:.04em;">
                    Remaining</div>
                <div style="font-size:18px; font-weight:800; color:#d97706; margin-top:3px;">${{
                    number_format($pending,2) }}</div>
            </div>
        </div>

        {{-- Payment schedule table --}}
        <table class="client-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Paid On</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract->payments->sortBy('installment_index') as $p)
                <tr>
                    <td>{{ $p->installment_index }}</td>
                    <td>{{ $p->due_date->format('d M Y') }}</td>
                    <td><strong>${{ number_format($p->amount,2) }}</strong></td>
                    <td>
                        @if($p->status === 'paid')
                        <span class="badge badge--completed">Paid</span>
                        @else
                        <span class="badge badge--progress">Pending</span>
                        @endif
                    </td>
                    <td>{{ $p->paid_at ? $p->paid_at->format('d M Y') : '—' }}</td>
                    <td>
                        @if($p->status === 'paid' && $p->receipt_path)
                        <a class="btn" href="{{ route('worker.payments.receipt', $p) }}">↓ Receipt</a>
                        @elseif($p->status === 'paid')
                        <span style="font-size:12px;opacity:.5;">Generating…</span>
                        @else
                        —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="client-panel" style="padding:30px; text-align:center; opacity:.5;">
        No contracts assigned yet.
    </div>
    @endforelse
</section>
@endsection