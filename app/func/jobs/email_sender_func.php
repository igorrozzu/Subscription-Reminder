<?php

declare(strict_types=1);

namespace Func\Jobs\EmailSender;
use Doctrine\DBAL\{Connection, Result};
use function Func\{check_email,
    disable_email_sender_process,
    log_error,
    remove_job,
    send_notification_template,
    update_email_params_by_user_id,
    update_subscription_status_by_user_id};

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
            handle_one_user($db, $user);
        } catch (\Exception $e) {
            log_error($e->getMessage() . ': ' . $e->getTraceAsString() . ' payload: ' . json_encode($user));
            disable_email_sender_process($db, [$user['id']]);
        }
    }

    remove_job($db, $job['id']);
}

/**
 * @param Connection $db
 * @param array $user
 * @return bool
 * @throws \Doctrine\DBAL\Exception
 */
function handle_one_user(Connection $db, array $user): bool
{
    $isValid = has_user_valid_email($db, $user);
    if (!$isValid) {
        update_subscription_status($db, SUBSCRIPTION_STATUS_INACTIVE, $user);
        return false;
    }

    send_notification_template($user);
    update_subscription_status($db, SUBSCRIPTION_STATUS_NOTIFICATION_SENT, $user);

    return true;
}

/**
 * @param Connection $db
 * @param string $status
 * @param array $user
 * @return void
 * @throws \Doctrine\DBAL\Exception
 */
function update_subscription_status(Connection $db, string $status, array $user): void
{
    $db->beginTransaction();
    try {
        update_subscription_status_by_user_id($db, $status, $user['id']);
        disable_email_sender_process($db, [$user['id']]);
    } catch (\Exception $e) {
        $db->rollBack();
        throw $e;
    }
    $db->commit();
}

/**
 * @param Connection $db
 * @param array $user
 * @return bool
 * @throws \Doctrine\DBAL\Exception
 */
function has_user_valid_email(Connection $db, array $user): bool
{
    if (!$user['checked']) {
        $isValid = check_email($user['email']);
        update_email_params_by_user_id($db, $isValid, $user['id']);
    } else {
        $isValid = $user['valid'];
    }

    return (bool) $isValid;
}
