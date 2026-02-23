<?php

namespace App\Observers;

use App\Models\ContractProgressItem;
use App\Services\NotificationService;

class ContractProgressItemObserver
{
    public function updated(ContractProgressItem $item): void
    {
        if (!$item->wasChanged('status')) return;

        $old = $item->getOriginal('status');
        $new = $item->status;

        // Only notify when moving TO these states
        if (!in_array($new, ['in_progress', 'done'])) return;

        // Avoid useless duplicate state (shouldn't happen but safe)
        if ($old === $new) return;

        $item->loadMissing('contract:id,client_user_id');

        $clientUserId = (int) ($item->contract?->client_user_id);
        if (!$clientUserId) return;

        // Title & message logic
        if ($new === 'in_progress') {
            $title = "Progress started: {$item->title}";
            $message = "A project step moved to In Progress: {$item->title}";
        } else { // done
            $title = "Progress completed: {$item->title}";
            $message = "A project step was marked Done: {$item->title}";
        }

        $key = "progress_item:{$item->id}:{$old}_to_{$new}";

        app(NotificationService::class)->createOnce([
            'user_id'     => $clientUserId,
            'key'         => $key,
            'type'        => 'progress',
            'title'       => $title,
            'message'     => $message,
            'url'         => '/client/contracts/overview',
            'entity_type' => 'progress_item',
            'entity_id'   => $item->id,
        ]);
    }
}