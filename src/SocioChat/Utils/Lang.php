<?php
namespace SocioChat\Utils;

use SocioChat\Log;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

class Lang
{
	/**
	 * @var \Zend\Config\Config
	 */
	private $lexicon;

	/**
	 * @param Config $lexicon
	 * @return $this
	 */
	public function setLexicon(Config $lexicon)
	{
		$this->lexicon = $lexicon;
		return $this;
	}

	/**
	 * @param $httpAcceptLanguage
	 * @return $this
	 */
	public function setLexiconByHTTPpreference($httpAcceptLanguage)
	{
		switch ($httpAcceptLanguage) {
			case 'en' : $langFile = 'en'; break;
			case 'ru' :
			default: $langFile = 'ru';
		}

		$langFile .= '.ini';

		$DS = DIRECTORY_SEPARATOR;
		$confPath = ROOT.$DS.'conf'.$DS.'lang'.$DS;

		$reader = new Ini();
		$this->setLexicon(new Config($reader->fromFile($confPath.$langFile)));
		return $this;
	}

	public function getPhrase()
	{
		$args = func_get_args();
		$token = array_shift($args);

		if (!$this->lexicon) {
			Log::get()->fetch()->warn('No localization was set', [__CLASS__]);
			return $token.' '.implode(', ', $args);
		}

		$scope = $this->lexicon;

		foreach (explode('.', $token) as $part) {
			if (!$newScope = $scope->get($part)) {
				Log::get()->fetch()->warn('No localization was matched for '.$part, [__CLASS__]);
				return $token.' '.implode(', ', $args);
			}

			$scope = $newScope;
		}


		return vsprintf($scope, $args);
	}
} 