<?php

use Monolog\Logger;
use SocioChat\Cron\CronExecutor;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

//$setupErrorHandler = 1;
require_once dirname(__DIR__).DIRECTORY_SEPARATOR . 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */
$logger = $container->get('logger');
/* @var $logger Logger */

try {
    /* @var $cronExecutor CronExecutor */
    $cronExecutor = new CronExecutor;
    $cronExecutor->run();
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}