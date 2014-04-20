<?php

namespace SocioChat;

use Orno\Di\Container;

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