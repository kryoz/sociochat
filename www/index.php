<?php

use Front\Controllers\BaseController;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Event;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'silex.php';

$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => ROOT.'/front.log',
    'monolig.level' => $isDebug ? Logger::DEBUG : Logger::INFO,
]);

$errHandler = ErrorHandler::register();
$errHandler->setDefaultLogger($app['monolog']);
if ($isDebug) {
    Symfony\Component\Debug\ExceptionHandler::register($isDebug);
}

$loader = new YamlFileLoader(new FileLocator(ROOT . '/conf'));
$collection = $loader->load('routes.yml');
$app['routes']->addCollection($collection);

$app->on(HttpKernel\KernelEvents::CONTROLLER, function (Event\FilterControllerEvent $event) use ($app) {
    /** @var BaseController $controller */
    $controller = $event->getController()[0];
    if ($controller instanceof BaseController) {
        $controller->injectApp($app);
    }
});

$app->run();
