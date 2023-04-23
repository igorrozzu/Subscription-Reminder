<?php

declare(strict_types=1);

namespace Func;
/**
 * @param string $body
 * @param string $path
 * @return void
 */
function log_info(string $body, string $path = 'info.log'): void
{
    $log_info = [
        "timestamp" => date("Y-m-d H:i:s"),
        "message" => $body,
    ];

    $path =  __DIR__ . '/../logs/' . $path;
    file_put_contents($path, json_encode($log_info) . PHP_EOL, FILE_APPEND);
}

/**
 * @param string $body
 * @param string $path
 * @return void
 */
function log_error(string $body, string $path = 'errors.log'): void
{
    log_info($body, $path);
}
