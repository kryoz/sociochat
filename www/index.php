<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Routing\Loader\YamlFileLoader;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'silex.php';

$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => ROOT.'/front.log',
]);

$errHandler = ErrorHandler::register();
$errHandler->setDefaultLogger($app['monolog']);
if ($isDebug) {
    Symfony\Component\Debug\ExceptionHandler::register($isDebug);
}

$loader = new YamlFileLoader(new FileLocator(ROOT . '/conf'));
$collection = $loader->load('routes.yml');
$app['routes']->addCollection($collection);

$app->run();
