<?php

declare(strict_types=1);

namespace Func;
/**
 * @param string $email
 * @return int
 */
function check_email(string $email): int
{
    log_info("check_email: $email");
    sleep(rand(1, 60));

    return rand(0, 1);
}

/**
 * @param string $email
 * @param string $from
 * @param string $to
 * @param string $subj
 * @param string $body
 * @return int
 */
function send_email(string $email, string $from, string $to, string $subj, string $body): int
{
    sleep(rand(1, 10));

    return rand(0, 1);
}

/**
 * @param array $user
 * @return int
 */
function send_reminder_template(array $user): int
{
    log_info("send_reminder: " . $user['username'] . " " . $user['email']);
    $username = $user['username'];
    $subject = "$username, your subscription has been expired";
    $body = "Hello $username,\n\nYour subscription has been expired. Please renew it to continue using our service.
    \n\nBest regards,\nThe Subscription Team";
    return send_email($user['email'], 'subscriptions@example.com', $username, $subject, $body);
}

/**
 * @param array $user
 * @return int
 */
function send_notification_template(array $user): int
{
    log_info("send_notification: " . $user['username'] . " " . $user['email']);
    $username = $user['username'];
    $subject = "$username, your subscription is expiring soon";
    $body = "Hello $username,\n\nYour subscription is expiring soon. Please renew it to continue using our service.
    \n\nBest regards,\nThe Subscription Team";
    return send_email($user['email'], 'subscriptions@example.com', $username, $subject, $body);
}
