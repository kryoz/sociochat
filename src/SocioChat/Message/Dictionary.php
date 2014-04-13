<?php

namespace SocioChat\Message;

use SocioChat\Log;
use SocioChat\TSingleton;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

class Dictionary
{
	use TSingleton;

	private $dictionary;

	public function __construct()
	{
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
			Log::get()->fetch()->warn("No dictionary for language code '$code' found", [__CLASS__]);
			return;
		}
		return $this->dictionary[$code];
	}
}