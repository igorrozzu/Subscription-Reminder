<?php

declare(strict_types=1);

namespace Func\Jobs\SubscriptionReminder;
use Doctrine\{DBAL\Connection, DBAL\Result};
use function Func\{disable_subscription_reminder_process,
    log_error,
    remove_job,
    send_reminder_template,
    update_reminder_sent_at};

/**
 * @param Connection $db
 * @param Result $queryResult
 * @param array $job
 * @return void
 * @throws \Doctrine\DBAL\Exception
 */
function handle(Connection $db, Result $queryResult, array $job): void
{
    foreach ($queryResult->iterateAssociative() as $user) {
        try {
            send_reminder_template($user);
            update_reminder_sent_at($db, $user['id']);
        } catch (\Exception $e) {
            log_error($e->getMessage() . ': ' . $e->getTraceAsString() . ' payload: ' . json_encode($user));
        }
        disable_subscription_reminder_process($db, [$user['id']]);
    }

    remove_job($db, $job['id']);
}
