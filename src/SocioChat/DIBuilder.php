<?php
namespace SocioChat;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orno\Di\Container;
use React\EventLoop\Factory as Loop;
use SocioChat\Cache\Cache;
use SocioChat\Cache\CacheApc;
use SocioChat\Cache\CacheException;
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
				$type = $container->get('config')->logger ? : fopen('php://stdout', 'w');
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
					$cache = [];
					$container->get('logger')->err('Unable to initialize APC cache!');
				}

				return $cache;
			},
			true
		);
	}
}

