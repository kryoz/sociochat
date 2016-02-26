<?php

use SocioChat\Cron\CronExecutor;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR . 'silex.php';

try {
    /* @var $cronExecutor CronExecutor */
    $cronExecutor = new CronExecutor;
    $cronExecutor->run($app);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}