<?php

use Monolog\Logger;
use SocioChat\Cron\CronExecutor;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR . 'silex.php';

try {
    $app->register(new Silex\Provider\MonologServiceProvider(), [
        'monolog.logfile' => ROOT.'/cron.log',
        'monolog.level' => $app['debug'] ? Logger::DEBUG : Logger::INFO,
    ]);
    $app['translator']->setLocale('ru');

    /* @var $cronExecutor CronExecutor */
    $cronExecutor = new CronExecutor;
    $cronExecutor->run($app);
} catch (Exception $e) {
    $app['logger']->addAlert($e->getMessage());
    exit(1);
}