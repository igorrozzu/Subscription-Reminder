<?php

require_once __DIR__ . '/constants.php';

return [
    'notifications_before_period_in_seconds' => 3 * 24 * 60 * 60,
    'reminders_period_in_seconds' => 1 * 24 * 60 * 60,

    'max_jobs_amount_concurrently' => [
        JOB_EMAIL_SENDER => 500,
        JOB_SUBSCRIPTION_REMINDER => 500,
    ],
    'subscriptions_per_job' => [
        JOB_EMAIL_SENDER => 1000,
        JOB_SUBSCRIPTION_REMINDER => 1000,
    ],
    'test_db_rows' => 1000000,
];