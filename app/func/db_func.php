<?php

declare(strict_types=1);

namespace Func;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Connection;

/**
 * @param Connection $db
 * @param int $validDate
 * @return Result
 * @throws \Doctrine\DBAL\Exception
 */
function get_users_by_subscription_time(Connection $db, int $validDate): Result
{
    return $db->executeQuery("SELECT u.id, username, email, validts FROM users u INNER JOIN subscriptions s ON
    u.id=s.user_id INNER JOIN emails e on u.id = e.user_id WHERE validts < ? AND status=? AND confirmed = true 
    AND email_sender_process = false;", [$validDate, SUBSCRIPTION_STATUS_ACTIVE]);
}

/**
 * @param Connection $db
 * @param int $reminderLastDate
 * @return Result
 * @throws \Doctrine\DBAL\Exception
 */
function get_users_with_valid_email(Connection $db, int $reminderLastDate): Result
{
    $now = time();
    return $db->executeQuery("SELECT u.id id, email, checked, valid FROM users u INNER JOIN emails e ON
u.id = e.user_id INNER JOIN subscriptions s on u.id = s.user_id WHERE validts < $now AND
(reminder_sent_at IS NULL OR reminder_sent_at < to_timestamp(?)) AND confirmed = true 
AND e.checked = true AND e.valid = true AND subscription_reminder_process = false;", [$reminderLastDate]);
}

/**
 * @param Connection $db
 * @param string $processName
 * @param array $ids
 * @return Result
 * @throws \Doctrine\DBAL\Exception
 */
function get_processing_users_by_ids(Connection $db, string $processName, array $ids): Result
{
    $ids = join(',', $ids);
    return $db->executeQuery(
        "SELECT u.id id, username, email, checked, valid FROM users u INNER JOIN emails e ON
    u.id = e.user_id INNER JOIN subscriptions s on u.id = s.user_id WHERE $processName=true AND u.id IN ($ids);"
    );
}

/**
 * @param Connection $db
 * @param array $ids
 * @return Result
 * @throws \Doctrine\DBAL\Exception
 */
function get_users_with_email_sender_process(Connection $db, array $ids): Result
{
    return get_processing_users_by_ids($db, 'email_sender_process', $ids);
}

/**
 * @param Connection $db
 * @param array $ids
 * @return Result
 * @throws \Doctrine\DBAL\Exception
 */
function get_users_with_subscription_reminder_process(Connection $db, array $ids): Result
{
    return get_processing_users_by_ids($db, 'subscription_reminder_process', $ids);
}

/**
 * @param Connection $db
 * @param string $status
 * @param int $id
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function update_subscription_status_by_user_id(Connection $db, string $status, int $id): int
{
    return $db->update('subscriptions', ["status" => $status], ["user_id" => $id]);
}

/**
 * @param Connection $db
 * @param int $isValid
 * @param int $id
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function update_email_params_by_user_id(Connection $db, int $isValid, int $id): int
{
    return $db->update('emails', ["checked" => true, "valid" => $isValid], ["user_id" => $id]);
}

/**
 * @param Connection $db
 * @param array $ids
 * @param string $processName
 * @param bool $processValue
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function update_subscription_process(Connection $db, array $ids, string $processName, bool $processValue): int
{
    $ids = join(',', $ids);
    return $db->executeStatement("UPDATE subscriptions SET $processName = ? WHERE user_id IN ($ids)", [
        (int) $processValue,
    ]);
}

function update_reminder_sent_at(Connection $db, int $id): int
{
    return $db->executeStatement("UPDATE subscriptions SET reminder_sent_at = CURRENT_TIMESTAMP WHERE user_id = ?;", [
        $id,
    ]);
}

/**
 * @param Connection $db
 * @param array $ids
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function disable_email_sender_process(Connection $db, array $ids): int
{
    return update_subscription_process($db, $ids, 'email_sender_process', false);
}

/**
 * @param Connection $db
 * @param array $ids
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function disable_subscription_reminder_process(Connection $db, array $ids): int
{
    return update_subscription_process($db, $ids, 'subscription_reminder_process', false);
}

/**
 * @param Connection $db
 * @param array $ids
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function enable_email_sender_process(Connection $db, array $ids): int
{
    return update_subscription_process($db, $ids, 'email_sender_process', true);
}

/**
 * @param Connection $db
 * @param array $ids
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function enable_subscription_reminder_process(Connection $db, array $ids): int
{
    return update_subscription_process($db, $ids, 'subscription_reminder_process', true);
}

/**
 * @param Connection $db
 * @param string $name
 * @param string $status
 * @param string $payload
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function dispatch_job(Connection $db, string $name, string $status, string $payload): int
{
    return $db->insert('queue_jobs', ['name' => $name, "status" => $status, 'payload' => $payload]);
}

/**
 * @param Connection $db
 * @param string $name
 * @param string $status
 * @param int $amount
 * @return array
 * @throws \Doctrine\DBAL\Exception
 */
function get_jobs(Connection $db, string $name, string $status, int $amount = 10): array
{
    $query = $db->executeQuery(
        "SELECT * FROM queue_jobs WHERE name = ? AND status = ? LIMIT ?;",
        [$name, $status, $amount]
    );
    return $query->fetchAllAssociative();
}

/**
 * @param Connection $db
 * @param string $name
 * @param string $status
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function get_jobs_amount(Connection $db, string $name, string $status): int
{
    $query = $db->executeQuery(
        "SELECT COUNT(*) FROM queue_jobs WHERE name = ? AND status = ?;",
        [$name, $status]
    );
    return $query->fetchOne();
}

/**
 * @param Connection $db
 * @param array $params
 * @param array $searchParams
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function update_job(Connection $db, array $params, array $searchParams): int
{
    return $db->update('queue_jobs', $params, $searchParams);
}

/**
 * @param Connection $db
 * @param int $id
 * @return array
 * @throws \Doctrine\DBAL\Exception
 */
function get_job_by_id(Connection $db, int $id): array
{
    $query = $db->executeQuery("SELECT * FROM queue_jobs WHERE id = ? LIMIT 1;", [$id]);
    $result = $query->fetchAllAssociative();
    return array_shift($result);
}

/**
 * @param Connection $db
 * @param int $id
 * @return int
 * @throws \Doctrine\DBAL\Exception
 */
function remove_job(Connection $db, int $id): int
{
    return $db->delete('queue_jobs', ['id' => $id]);
}