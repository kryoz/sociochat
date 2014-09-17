<?php

use Monolog\Logger;
use SocioChat\Cron\CronExecutor;
use Core\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

set_error_handler(
    function ($errno, $errstr, $errfile, $errline) {
        $func = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        $line = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];
        echo "ERROR (calling {$func}() on l.$line) : $errstr</p>";
        return true;
    }
);

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';
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
	$logger->err($e);
	exit(1);
}