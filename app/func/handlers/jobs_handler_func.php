<?php

declare(strict_types=1);
namespace Func\Handlers;

use Doctrine\DBAL\Connection;
use function Func\{get_jobs, get_jobs_amount, update_job};

/**
 * @param Connection $db
 * @param array $argv
 * @param array $config
 * @param string $logPath
 * @return void
 * @throws \Doctrine\DBAL\Exception
 */
function handle(Connection $db, array $argv, array $config, string $logPath = 'logs/jobs-errors.log'): void
{
    $jobName = get_job_name($argv);
    $processingJobsAmount = get_jobs_amount($db, $jobName, JOB_STATUS_PROCESSING);
    $jobsAmount = $config['max_jobs_amount_concurrently'][$jobName] - $processingJobsAmount;
    if ($jobsAmount <= 0) {
        log_result(0);
        return;
    }
    $jobs = get_jobs($db, $jobName, JOB_STATUS_ACTIVE, $jobsAmount);
    foreach ($jobs as $job) {
        shell_exec("php jobs/{$jobName}" . "_job.php {$job['id']} >> $logPath &");
        update_job($db, ['status' => JOB_STATUS_PROCESSING], ['id' => $job['id']]);
    }

    log_result(count($jobs));
}

/**
 * @param int $jobAmount
 * @return void
 */
function log_result(int $jobAmount): void
{
    echo sprintf('Processing jobs: %s', $jobAmount);
}

/**
 * @param array $argv
 * @return string
 */
function get_job_name(array $argv): string
{
    $jobName = $argv[1];
    if (!in_array($jobName, [JOB_EMAIL_SENDER, JOB_SUBSCRIPTION_REMINDER])) {
        throw new \RuntimeException('Unknown job name ' . $jobName);
    }

    return $jobName;
}
