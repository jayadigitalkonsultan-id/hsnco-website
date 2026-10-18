<?php

return [
    'login' => [
        'attempts_per_minute' => (int) env('SECURITY_LOGIN_ATTEMPTS_PER_MINUTE', 5),
        'ip_attempts_per_minute' => (int) env('SECURITY_LOGIN_IP_ATTEMPTS_PER_MINUTE', 20),
        'failures_before_lock' => (int) env('SECURITY_LOGIN_FAILURES_BEFORE_LOCK', 5),
        'failure_window_minutes' => (int) env('SECURITY_LOGIN_FAILURE_WINDOW_MINUTES', 15),
        'lock_minutes' => (int) env('SECURITY_LOGIN_LOCK_MINUTES', 15),
    ],
    'invitation' => [
        'expires_minutes' => (int) env('SECURITY_INVITATION_EXPIRES_MINUTES', 60),
        'resend_seconds' => (int) env('SECURITY_INVITATION_RESEND_SECONDS', 60),
    ],
    'otp' => [
        'expires_minutes' => (int) env('SECURITY_OTP_EXPIRES_MINUTES', 5),
        'max_attempts' => (int) env('SECURITY_OTP_MAX_ATTEMPTS', 5),
        'resend_seconds' => (int) env('SECURITY_OTP_RESEND_SECONDS', 60),
        'verify_per_minute' => (int) env('SECURITY_OTP_VERIFY_PER_MINUTE', 8),
    ],
    'audit_retention_days' => (int) env('SECURITY_AUDIT_RETENTION_DAYS', 365),
];
