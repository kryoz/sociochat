<?php

use Monolog\Logger;
use SocioChat\Cron\CronExecutor;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

function CustomErrorHandler($errno, $errstr, $errfile, $errline)
{
	echo "ErrorHandler: $errfile line $errline: $errstr\n";
	return true;
}
set_error_handler('CustomErrorHandler');

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