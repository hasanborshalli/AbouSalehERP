<div class="app-modal" id="{{ $id }}" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="app-modal__overlay" data-modal-close="{{ $id }}"></div>

    <div class="app-modal__panel" role="document" aria-label="{{ $title ?? 'Modal' }}">
        <header class="app-modal__header">
            <h3 class="app-modal__title">
                {{ $title ?? '' }}
            </h3>

            <button type="button" class="app-modal__close" data-modal-close="{{ $id }}" aria-label="Close">
                ✕
            </button>
        </header>

        <div class="app-modal__body">
            {{ $slot }}
        </div>
    </div>
</div>