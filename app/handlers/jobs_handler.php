<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use function Func\Handlers\handle;


require_once __DIR__ . '/../constants.php';
require_once __DIR__ . '/../func/db_func.php';
require_once __DIR__ . '/../func/handlers/jobs_handler_func.php';
require_once __DIR__ . '/../exceptions/exception_handler.php';
$config = require_once __DIR__ . '/../config.php';
/**@var $db Connection */
$db = require_once __DIR__ . '/../db.php';

handle($db, $argv, $config);
