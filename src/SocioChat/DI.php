<?php

namespace SocioChat;

use Orno\Cache\Adapter\ApcAdapter;
use Orno\Cache\Cache;
use Orno\Di\Container;

class DI
{
	use TSingleton;

	/**
	 * @var \Orno\Di\Container
	 */
	private $container;

	function __construct()
	{
		$cache = new Cache(new ApcAdapter());
		$this->container = new Container($cache);
	}

	/**
	 * @return \Orno\Di\Container
	 */
	public function container()
	{
		return $this->container;
	}


}