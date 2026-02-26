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
    @php
    // Make sure items are ordered (id or position column if you have)
    $items = $progressItems->values();

    // Color palette per step index (0-based)
    $stepColors = [
    '#ef4444', // step 1 red
    '#3b82f6', // step 2 blue
    '#22c55e', // step 3 green
    '#f59e0b', // step 4 amber
    '#a855f7', // step 5 purple
    '#06b6d4', // step 6 cyan
    '#f97316', // step 7 orange
    '#64748b', // step 8 slate
    ];

    // Weight sum safety
    $totalWeight = max(1, (int) $items->sum('weight'));

    // Overall progress from controller can stay, but we also compute a safe number (0..100)
    $computedOverall = (int) round(
    $items->sum(fn($it) => ($it->status === 'done' ? (int)$it->weight : 0)) * 100 / $totalWeight
    );
    $overall = isset($overallProgress) ? (int)$overallProgress : $computedOverall;
    @endphp

    <div class="client-panel progress-panel" style="margin-top:16px;">
        <div class="progress-panel__head">
            <h3 class="progress-panel__title">Project Progress</h3>
            <div class="progress-panel__pct">{{ $overall }}%</div>
        </div>

        {{-- Segmented overall bar: each step takes its weight (%) and is filled if done --}}
        <div class="segbar" aria-label="Overall progress">
            @foreach($items as $i => $it)
            @php
            $color = $stepColors[$i % count($stepColors)];
            $w = round(((int)$it->weight / $totalWeight) * 100, 2);
            $isDone = $it->status === 'done';
            $isProg = $it->status === 'in_progress';
            $label = $isDone ? 'Done' : ($isProg ? 'In Progress' : 'To Do');
            @endphp

            <div class="segbar__seg"
                style="width: {{ $w }}%; background: {{ $isDone ? $color : 'rgba(15,23,42,0.10)' }};"
                title="Step {{ $i+1 }} • {{ $it->title }} • {{ $label }} • {{ $it->weight }}%">
            </div>
            @endforeach
        </div>

        <div class="progress-items">
            @foreach($items as $i => $it)
            @php
            $color = $stepColors[$i % count($stepColors)];
            $isDone = $it->status === 'done';
            $isProg = $it->status === 'in_progress';

            $badgeCls = $isDone ? 'badge--done' : ($isProg ? 'badge--prog' : 'badge--todo');
            $label = $isDone ? 'Done' : ($isProg ? 'In Progress' : 'To Do');

            // mini fill: done=100, in_progress=50, todo=0 (tweak if you later store per-step %)
            $mini = $isDone ? 100 : ($isProg ? 50 : 0);
            @endphp

            <div class="pitem">
                <div class="pitem__top">
                    <div class="pitem__left">
                        <div class="pitem__step" style="background: {{ $color }};">
                            Step {{ $i+1 }}
                        </div>
                        <div class="pitem__title">{{ $it->title }}</div>
                    </div>

                    <span class="badge {{ $badgeCls }}">{{ $label }}</span>
                </div>

                @if($it->description)
                <div class="pitem__desc">{{ $it->description }}</div>
                @endif

                <div class="pitem__bar">
                    <div class="pitem__fill" style="width: {{ $mini }}%; background: {{ $color }};"></div>
                </div>

                <div class="pitem__meta">
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