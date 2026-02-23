@extends('client.layout')

@section('title', 'Client • Project Manager')

@section('content')
<section class="client-page" aria-label="Project manager">
    <div class="client-page__header">
        <h2>Project Manager</h2>
        <a class="client-back" href="{{ route('client.contracts') }}">← Back</a>
    </div>

    <table class="client-table" aria-label="Managers table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Manager</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $c)
                <tr>
                    <td>{{ $c->project?->name ?? '—' }}</td>
                    <td>{{ $c->project?->manager?->name ?? '—' }}</td>
                    <td>{{ $c->project?->manager?->phone ?? '—' }}</td>
                    <td>{{ $c->project?->manager?->email ?? '—' }}</td>
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
