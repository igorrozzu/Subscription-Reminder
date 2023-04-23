<?php

require_once __DIR__ . '/../func/log_func.php';

/**
 * @param Throwable $e
 * @return void
 */
function exception_handler(Throwable $e): void
{
    $body = $e->getMessage() . ': ' . $e->getTraceAsString();
    log_error($body);
    echo $body;
}

set_exception_handler('exception_handler');
