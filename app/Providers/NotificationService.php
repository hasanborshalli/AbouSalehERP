<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService
{
    public function createOnce(array $payload): ?UserNotification
    {
        // required: user_id, key, type, title, message
        return UserNotification::firstOrCreate(
            [
                'user_id' => $payload['user_id'],
                'key'     => $payload['key'],
            ],
            [
                'type'        => $payload['type'],
                'title'       => $payload['title'],
                'message'     => $payload['message'],
                'url'         => $payload['url'] ?? null,
                'entity_type' => $payload['entity_type'] ?? null,
                'entity_id'   => $payload['entity_id'] ?? null,
            ]
        );
    }
}