<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Existing projects</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    {{-- shared --}}
    <link rel="stylesheet" href="/css/dashboard.css" />
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/alert.css">
    <link rel="stylesheet" href="/css/confirmModal.css">
    {{-- page specific --}}
    <link rel="stylesheet" href="/css/existingProjects.css" />
</head>

<body class="app-shell">
    <input class="app-shell__toggle" type="checkbox" id="sidebarToggle" />

    <aside class="app-shell__sidebar">
        <x-sidebar />
    </aside>

    <div class="app-shell__main">
        <x-navbar />

        <main class="dashboard-content">
            @if (session('success'))
            <div class="alert alert--success" data-alert>
                <span class="alert__icon">✔</span>
                <span class="alert__text">{{ session('success') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert--error" data-alert>
                <span class="alert__icon">X</span>
                <span class="alert__text">{{ session('error') }}</span>
                <button class="alert__close" onclick="this.parentElement.remove()">✕</button>
            </div>
            @endif
            <section class="projects-index" aria-label="Existing projects page">

                <section class="dashboard-card projects-index__card">
                    <header class="projects-index__header">
                        <h2 class="projects-index__title">Existing projects</h2>

                        <div class="projects-index__tools">
                            <div class="projects-index__search" role="search">
                                <img class="projects-index__search-icon" src="/img/search.svg" alt="">
                                <input id="projectsSearch" type="search" placeholder="Search project..."
                                    aria-label="Search projects">
                            </div>

                            <a class="projects-index__btn" href="{{ route('apartments.create-project')  }}">
                                Create project
                            </a>
                        </div>
                    </header>



                    <div class="projects-index__table-wrap" aria-label="Projects table">
                        <table class="projects-index__table">
                            <thead class="projects-index__thead">
                                <tr>
                                    <th class="projects-index__th">Code</th>
                                    <th class="projects-index__th projects-index__th--name">Project name</th>
                                    <th class="projects-index__th">City</th>
                                    <th class="projects-index__th">Area</th>
                                    <th class="projects-index__th">Floors</th>
                                    <th class="projects-index__th">Sold</th>
                                    <th class="projects-index__th">Not sold</th>
                                    <th class="projects-index__th projects-index__th--actions">Actions</th>
                                </tr>
                            </thead>

                            <tbody id="projectsTbody" class="projects-index__tbody">
                                @foreach($projects as $p)
                                <tr class="projects-index__row" data-code="{{ $p->code }}" data-name="{{ $p->name }}"
                                    data-city="{{ $p->city }}" data-area="{{ $p->area }}">

                                    <td class="projects-index__td projects-index__td--code">{{ $p->code ?? '-' }}</td>

                                    <td class="projects-index__td projects-index__td--name">
                                        <a class="projects-index__link"
                                            href="{{ route('apartments.project', $p->id) }}">
                                            {{ $p->name }}
                                        </a>
                                    </td>

                                    <td class="projects-index__td">{{ $p->city ?? '-' }}</td>
                                    <td class="projects-index__td">{{ $p->area ?? '-' }}</td>
                                    <td class="projects-index__td">{{ (int) $p->floors_count }}</td>
                                    <td class="projects-index__td">{{ (int) $p->sold_count }}</td>
                                    <td class="projects-index__td">{{ (int) $p->not_sold_count }}</td>

                                    <td class="projects-index__td projects-index__td--actions">
                                        <a class="projects-index__icon-btn projects-index__icon-btn--edit"
                                            href="{{ route('apartments.edit-project', $p->id) }}"
                                            aria-label="Edit project">✎</a>

                                        <form action="{{ route('apartments.delete-project', $p->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="projects-index__icon-btn projects-index__icon-btn--delete"
                                                aria-label="Delete project"
                                                onclick="event.preventDefault(); deleteProject(this);">🗑</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                </section>
            </section>
            <div class="confirm-modal" id="confirmModal">
                <div class="confirm-modal__backdrop"></div>

                <div class="confirm-modal__box">
                    <h3 class="confirm-modal__title">Delete project</h3>
                    <p class="confirm-modal__text">
                        Are you sure you want to delete this project?
                        <br>This action <strong>cannot be undone</strong>.
                    </p>

                    <div class="confirm-modal__actions">
                        <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel"
                            onclick="closeConfirmModal()">
                            Cancel
                        </button>

                        <button type="button" class="confirm-modal__btn confirm-modal__btn--danger"
                            id="confirmDeleteBtn">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

        </main>

        <label class="app-shell__overlay" for="sidebarToggle" aria-hidden="true"></label>
    </div>
    <script src="/js/existingProjects.js">
    </script>
    <script src="/js/navSearch.js"></script>

</body>

</html>