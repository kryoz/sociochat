<?php
namespace SocioChat;

use Core\BaseException;
use Core\DB\DB;
use Core\Memcache\Wrapper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orno\Di\Container;
use React\Dns\Resolver\Factory;
use React\HttpClient\Factory as HttpFactory;
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
	    self::setupMemcache($container);
        self::setupSession($container);
        self::setupUsers($container);
	    self::setupHttpClient($container);
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
                $config = $container->get('config');
                $file = $config->logger ?: null;
                if ($file && !file_exists($file)) {
                    throw new BaseException("Check log file path in your config ($file)");
                } elseif (!$file) {
                    $file = fopen('php://stdout', 'w');
                }
                $logHandler = (new StreamHandler($file))
                    ->setLevel($config->isDebug ? Logger::DEBUG : Logger::INFO);
                $logger->pushHandler($logHandler);
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
            ->withArguments(['config', true]);
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
                    $container->get('logger')->error('Unable to initialize APC cache!');
                    throw new BaseException('Unable to initialize APC cache!');
                }

                return $cache;
            },
            true
        );
    }

	/**
	 * @param Container $container
	 */
	public static function setupMemcache(Container $container)
	{
		$container->add(
			'memcache',
			function () use ($container) {
				try {
					$servers = [
						['127.0.0.1', 11211]
					];
					$wrapper = new Wrapper('sociochat', $servers);
					$wrapper->toggleAll();
				} catch (\Exception $e) {
					$container->get('logger')->error('Unable to initialize memcached!');
                    throw new BaseException('Unable to initialize memcached!');
				}

				return $wrapper;
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

	/**
	 * @param Container $container
	 */
	public static function setupHttpClient($container)
	{
		$container->add(
			'httpClient',
			function () use ($container) {
				$dnsResolverFactory = new Factory;
				$loop = $container->get('eventloop');
				$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);
				$factory = new HttpFactory();
                return $factory->create($loop, $dnsResolver);
			},
			true
		);
	}
}

