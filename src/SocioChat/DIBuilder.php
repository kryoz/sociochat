<?php
namespace SocioChat;

use Core\DB\DB;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orno\Di\Container;
use React\EventLoop\Factory as Loop;
use Core\Cache\Cache;
use Core\Cache\CacheApc;
use Core\Cache\CacheException;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\Dictionary;
use SocioChat\Message\Lang;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;


class DIBuilder
{
    public static function setupNormal(Container $container)
    {
        self::setupConfig($container);
        self::setupEventLoop($container);
        self::setupLogger($container);
        self::setupDB($container);
        self::setupDictionary($container);
        self::setupLang($container);
        self::setupCache($container);
        self::setupSession($container);
        self::setupUsers($container);
    }

    public static function setupConfig(Container $container)
    {
        $container->add(
            'config',
            function () {
                $DS = DIRECTORY_SEPARATOR;
                $confPath = ROOT . $DS . 'conf' . $DS;
                $reader = new Ini();
                $config = new Config($reader->fromFile($confPath . 'default.ini'));
                if (file_exists($confPath . 'local.ini')) {
                    $config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
                }

                return $config;
            },
            true
        );
    }

    /**
     * @param Container $container
     */
    public static function setupEventLoop(Container $container)
    {
        $container->add(
            'eventloop',
            function () {
                return Loop::create();
            },
            true
        );
    }

    /**
     * @param Container $container
     */
    public static function setupLogger(Container $container)
    {
        $container->add(
            'logger',
            function () use ($container) {
                $logger = new Logger('Chat');
                $type = $container->get('config')->logger ?: fopen('php://stdout', 'w');
                $logger->pushHandler(new StreamHandler($type));
                return $logger;
            },
            true
        );
    }

    /**
     * @param Container $container
     */
    public static function setupDB(Container $container)
    {
        $container->add('db', DB::class, true)
            ->withArgument('config');
    }

    /**
     * @param Container $container
     */
    public static function setupDictionary(Container $container)
    {
        $container->add('dictionary', Dictionary::class, true)
            ->withArgument('logger');
    }

    /**
     * @param Container $container
     */
    public static function setupLang(Container $container)
    {
        $container->add('lang', Lang::class)
            ->withArgument('dictionary');
    }

    /**
     * @param Container $container
     */
    public static function setupCache(Container $container)
    {
        $container->add(
            'cache',
            function () use ($container) {
                try {
                    $cache = new Cache(new CacheApc());
                } catch (CacheException $e) {
                    $container->get('logger')->err('Unable to initialize APC cache!');
                    die($e->getMessage());
                }

                return $cache;
            },
            true
        );
    }

    /**
     * @param Container $container
     */
    public static function setupSession(Container $container)
    {
        $container->add(
            'session',
            function () use ($container) {
                return new Session\DBSessionHandler();
            },
            true
        );
    }

    /**
     * @param Container $container
     */
    public static function setupUsers($container)
    {
        $container->add(
            'users',
            function () use ($container) {
                return new UserCollection();
            },
            true
        );
    }
}

