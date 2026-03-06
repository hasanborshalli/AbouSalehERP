<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contract progress</title>

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- page specific --}}
    <link rel="stylesheet" href="/css/contractProgressEditor.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            <section class="cp-editor" aria-label="Contract progress editor">

                <section class="dashboard-card cp-editor__card">
                    <header class="cp-editor__header">
                        <h2 class="cp-editor__title">Progress tracker</h2>

                        <a onclick="event.preventDefault(); history.back();" class="cp-editor__back">Back</a>
                    </header>

                    {{-- Alerts --}}
                    @if (session('success'))
                    <div class="alert alert--success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert--danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                    <div class="alert alert--danger">
                        <ul>
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Context --}}
                    <div class="cp-editor__context">
                        <div class="cp-editor__context-item">
                            <span class="cp-editor__label">Contract ID</span>
                            <span class="cp-editor__value">#{{ $contract->id }}</span>
                        </div>

                        <div class="cp-editor__context-item">
                            <span class="cp-editor__label">Client</span>
                            <span class="cp-editor__value">{{ $contract->client?->name ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- Add new step --}}
                    <section class="cp-editor__section" aria-label="Add progress step">
                        <div class="cp-editor__section-row">
                            <h3 class="cp-editor__section-title">Add new step</h3>
                        </div>

                        <form class="cp-editor__form" method="POST"
                            action="{{ route('contracts.progress.store', $contract->id) }}">
                            @csrf

                            <div class="cp-editor__grid">
                                <div class="cp-editor__field cp-editor__field--wide">
                                    <label class="cp-editor__label" for="title">Step title</label>
                                    <input class="cp-editor__input" id="title" name="title" type="text"
                                        placeholder="e.g. Electricity wiring" required>
                                </div>

                                <div class="cp-editor__field">
                                    <label class="cp-editor__label" for="weight">Weight (%)</label>
                                    <input class="cp-editor__input" id="weight" name="weight" type="number" min="1"
                                        max="100" value="10" required>
                                </div>

                                <div class="cp-editor__field cp-editor__field--wide">
                                    <label class="cp-editor__label" for="description">Description (optional)</label>
                                    <textarea class="cp-editor__textarea" id="description" name="description" rows="2"
                                        placeholder="Optional notes..."></textarea>
                                </div>
                            </div>

                            <div class="cp-editor__actions">
                                <button class="cp-editor__btn cp-editor__btn--primary" type="submit">Add step</button>
                            </div>
                        </form>
                    </section>

                    {{-- Existing steps --}}
                    <section class="cp-editor__section" aria-label="Existing steps">
                        <div class="cp-editor__section-row">
                            <h3 class="cp-editor__section-title">Steps</h3>
                        </div>

                        @if($items->isEmpty())
                        <p class="cp-editor__empty">No steps yet.</p>
                        @endif

                        <div class="cp-steps">
                            @foreach($items as $it)
                            <div class="cp-steps__card" aria-label="Progress item">
                                <form class="cp-steps__form" method="POST"
                                    action="{{ route('contracts.progress.update', [$contract->id, $it->id]) }}">
                                    @csrf

                                    <div class="cp-steps__grid">
                                        <div class="cp-steps__field cp-steps__field--wide">
                                            <label class="cp-editor__label">Title</label>
                                            <input class="cp-editor__input" name="title" value="{{ $it->title }}"
                                                required>
                                        </div>

                                        <div class="cp-steps__field">
                                            <label class="cp-editor__label">Weight (%)</label>
                                            <input class="cp-editor__input" name="weight" type="number" min="1"
                                                max="100" value="{{ $it->weight }}" required>
                                        </div>

                                        <div class="cp-steps__field">
                                            <label class="cp-editor__label">Sort order</label>
                                            <input class="cp-editor__input" name="sort_order" type="number" min="0"
                                                value="{{ $it->sort_order }}" required>
                                        </div>

                                        <div class="cp-steps__field cp-steps__field--wide">
                                            <label class="cp-editor__label">Description</label>
                                            <textarea class="cp-editor__textarea" name="description" rows="2"
                                                placeholder="Optional...">{{ $it->description }}</textarea>
                                        </div>

                                        <div class="cp-steps__field">
                                            <label class="cp-editor__label">Status</label>
                                            <select class="cp-editor__select" name="status" required>
                                                <option value="todo" {{ $it->status==='todo'?'selected':'' }}>To do
                                                </option>
                                                <option value="in_progress" {{ $it->status==='in_progress'?'selected':''
                                                    }}>In progress</option>
                                                <option value="done" {{ $it->status==='done'?'selected':'' }}>Done
                                                </option>
                                            </select>
                                        </div>

                                        <div class="cp-steps__field">
                                            <label class="cp-editor__label">Started at</label>
                                            <input class="cp-editor__input" type="date" name="started_at"
                                                value="{{ optional($it->started_at)->format('Y-m-d') }}">
                                        </div>

                                        <div class="cp-steps__field">
                                            <label class="cp-editor__label">Completed at</label>
                                            <input class="cp-editor__input" type="date" name="completed_at"
                                                value="{{ optional($it->completed_at)->format('Y-m-d') }}">
                                        </div>
                                    </div>

                                    <div class="cp-steps__actions">
                                        <button class="cp-editor__btn cp-editor__btn--primary"
                                            type="submit">Save</button>
                                    </div>
                                </form>

                                <form method="POST"
                                    action="{{ route('contracts.progress.destroy', [$contract->id, $it->id]) }}"
                                    onsubmit="return confirm('Delete this step?')" class="cp-steps__delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="cp-editor__btn cp-editor__btn--danger" type="submit">Delete</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </section>

                </section>

            </section>
        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>

    <script src="/js/navSearch.js"></script>
</body>

</html>