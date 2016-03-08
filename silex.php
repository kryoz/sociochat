<?php

use SocioChat\DI;
use SocioChat\DIBuilder;
use Front\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;

require_once __DIR__ . '/config.php';

$app = new Application();
$container = DI::get()->container();
DIBuilder::setupNormal($container);

$app['config'] = $container->get('config');
$isDebug = $app['config']->isDebug;
$app['debug'] = $isDebug;
$app['memcache'] = $container->get('memcache');

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => ['en'],
));
$app['translator'] = $app->share($app->extend('translator', function(Translator $translator, $app) {
    $translator->addLoader('ini', new \Front\Loader\IniFileLoader());

    $translator->addResource('ini', ROOT.'/conf/lang/en.ini', 'en');
    $translator->addResource('ini', ROOT.'/conf/lang/ru.ini', 'ru');

    return $translator;
}));

$app->before(
    function (Request $request) use ($app) {
        $app['translator']->setLocale($request->getPreferredLanguage(['ru', 'en']));
    }
);

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => ROOT.'/views',
]);

$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());