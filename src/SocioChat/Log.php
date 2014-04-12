<?php

namespace SocioChat;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SocioChat\TSingleton;

class Log
{
	use TSingleton;

	/**
	 * @var \Monolog\Logger
	 */
	protected $logger;

	public function __construct()
	{
		$this->logger = new Logger('Chat');
		$type = ChatConfig::get()->getConfig()->logger ?: STDOUT;
		$this->logger->pushHandler(new StreamHandler($type));
	}

	public function fetch()
	{
		return $this->logger;
	}
} 