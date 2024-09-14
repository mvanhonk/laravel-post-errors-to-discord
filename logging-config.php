<?php

return [
    'channels' => [
        // ... other channels ...

        'discord' => [
            'driver' => 'custom',
            'via' => App\Logging\DiscordLogger::class,
            'level' => env('LOG_LEVEL', 'error'),
            'url' => env('DISCORD_WEBHOOK_URL'),
        ],
    ],
];
