<?php
namespace SocioChat;

use SocioChat\TSingleton;
use SocioChat\Utils\Lang;
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
		$confPath = ROOT.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR;
		$reader = new Ini();
		$config = new Config($reader->fromFile($confPath . 'default.ini'));
		if (file_exists($confPath . 'local.ini')) {
			$config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
		}

		$this->config = $config;

		Lang::get()->setLexicon(new Config($reader->fromFile($confPath . 'lang.ini')));
	}
}