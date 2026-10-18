<?php

$recipients = env('ENQUIRY_NOTIFY_TO', '');

return [
    'notify_to' => is_string($recipients)
        ? array_values(array_unique(array_filter(
            array_map('trim', explode(',', $recipients)),
            fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        )))
        : [],
    'queue' => env('ENQUIRY_NOTIFICATION_QUEUE', 'emails'),
    'rate_limit_per_minute' => (int) env('ENQUIRY_RATE_LIMIT_PER_MINUTE', 5),
    'retention_days' => max(1, (int) env('ENQUIRY_RETENTION_DAYS', 365)),
];
