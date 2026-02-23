@extends('client.layout')

@section('title', 'Client • Contract Overview')

@section('content')
<section class="client-page" aria-label="Contract overview">
    <div class="client-page__header">
        <h2>Contract Overview</h2>
        <a class="client-back" href="{{ route('client.contracts') }}">← Back</a>
    </div>

    <table class="client-table" aria-label="Contracts table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Start Date</th>
                <th>Estimated Completion</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            @php
            $status = $r['status'];
            $badge = $status === 'Completed' ? 'badge--completed' : ($status === 'Signed' ? 'badge--signed' :
            'badge--progress');
            @endphp
            <tr>
                <td>{{ $r['project_name'] ?? '—' }}</td>
                <td>{{ $r['start_date'] ? \Carbon\Carbon::parse($r['start_date'])->format('d M Y') : '—' }}</td>
                <td>{{ $r['estimated_completion_date'] ?
                    \Carbon\Carbon::parse($r['estimated_completion_date'])->format('d M Y') : '—' }}</td>
                <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No contracts found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if(isset($progressItems) && $progressItems->isNotEmpty())
    <div class="client-panel" style="margin-top:16px;">
        <h3 style="margin:0 0 10px 0;">Project Progress</h3>

        <div class="progress" style="margin-bottom:12px;">
            <div class="progress__bar" style="width: {{ $overallProgress }}%"></div>
        </div>
        <div class="progress__label">{{ $overallProgress }}%</div>

        <div style="margin-top:14px; display:grid; gap:10px;">
            @foreach($progressItems as $it)
            @php
            $cls = $it->status === 'done' ? 'badge--completed' : ($it->status === 'in_progress' ? 'badge--progress' :
            'badge--signed');
            $label = $it->status === 'done' ? 'Done' : ($it->status === 'in_progress' ? 'In Progress' : 'To Do');
            @endphp

            <div style="background:rgba(255,255,255,.45);border-radius:12px;padding:12px;">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                    <strong>{{ $it->title }}</strong>
                    <span class="badge {{ $cls }}">{{ $label }}</span>
                </div>

                @if($it->description)
                <div style="margin-top:6px;opacity:.85;">{{ $it->description }}</div>
                @endif

                <div style="margin-top:8px; font-size:12px; opacity:.8;">
                    Weight: {{ $it->weight }}%
                    @if($it->started_at) • Started: {{ $it->started_at->format('d M Y') }} @endif
                    @if($it->completed_at) • Completed: {{ $it->completed_at->format('d M Y') }} @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</section>
@endsection