<?php
namespace SocioChat;

use SocioChat\TSingleton;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

class ChatConfig
{
	use TSingleton;

	/**
	 * @var Config
	 */
	private $config;

	public function __construct()
	{
		$this->loadConfigs();
	}

	/**
	 * @return \Zend\Config\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	public function loadConfigs()
	{
		$DS = DIRECTORY_SEPARATOR;
		$confPath = ROOT.$DS.'conf'.$DS;
		$reader = new Ini();
		$config = new Config($reader->fromFile($confPath . 'default.ini'));
		if (file_exists($confPath . 'local.ini')) {
			$config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
		}

		$this->config = $config;
	}
}