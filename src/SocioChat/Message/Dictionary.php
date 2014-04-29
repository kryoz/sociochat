<?php

namespace SocioChat\Message;

use Monolog\Logger;
use SocioChat\TSingleton;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

class Dictionary
{
	private $dictionary;
	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
		$this->loadTranslations();
	}

	public function loadTranslations()
	{
		$DS = DIRECTORY_SEPARATOR;
		$confPath = ROOT.$DS.'conf'.$DS.'lang'.$DS;

		foreach (glob($confPath.'*.ini') as $file) {
			$fileName = pathinfo($file, PATHINFO_FILENAME);
			$reader = new Ini();
			$this->dictionary[$fileName] = new Config($reader->fromFile($file));
		}
	}

	public function getLang($code)
	{
		if (!isset($this->dictionary[$code])) {
			$this->logger->warn("No dictionary for language code '$code' found", [__CLASS__]);
			$code = 'en';
		}
		return $this->dictionary[$code];
	}
}