<?php

namespace SocioChat;

use Monolog\Logger;
use Orno\Di\Container;
use ReflectionClass;
use SocioChat\Cache\Cache;
use Zend\Config\Config;

class DI
{
	use TSingleton;

	/**
	 * @var \Orno\Di\Container
	 */
	private $container;

	public function __construct()
	{
		//$cache = new Cache(new ApcAdapter());
		$this->container = new Container();
	}

	/**
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->container->get('logger');
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->container->get('config');
	}

	/**
	 * @return Cache
	 */
	public function getCache()
	{
		return $this->container->get('cache');
	}

	public function spawn($className)
	{
		$constructorArgs = func_get_args();
		array_shift($constructorArgs);

		$reflectionClass = new ReflectionClass($className);
		$object = !empty($constructorArgs)
			? $reflectionClass->newInstanceArgs($constructorArgs)
			: $reflectionClass->newInstance();

		return $object;
	}

	/**
	 * @return \Orno\Di\Container
	 */
	public function container()
	{
		return $this->container;
	}

	public function __sleep()
	{
		return [];
	}

	public function __wakeup()
	{
		$this->__construct();
	}


}