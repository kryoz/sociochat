<?php
namespace SocioChat\Message;

use Monolog\Logger;
use SocioChat\DI;

class Lang
{
	/**
	 * @var \Zend\Config\Config
	 */
	private $lexicon;
	/**
	 * @var Dictionary
	 */
	private $dictionary;
	private $lang;

	public function __construct(Dictionary $dictionary)
	{
		$this->dictionary = $dictionary;
	}


	/**
	 * @param $langCode
	 * @return $this
	 */
	public function setLangByCode($langCode)
	{
		$this->lexicon = $this->dictionary->getLang($langCode);
		$this->lang = $langCode;
		return $this;
	}

	public function getPhrase()
	{
		return $this->getPhraseByArray(func_get_args());
	}

	public function getPhraseByArray(array $args)
	{
		$token = array_shift($args);
		$logger = DI::get()->getLogger();

		if (!$this->lexicon) {
			$logger->warn('No localization was set', [__CLASS__]);
			return $token.' '.implode(', ', $args);
		}

		$scope = $this->lexicon;

		foreach (explode('.', $token) as $part) {
			if (!$newScope = $scope->get($part)) {
				$logger->warn('No localization was matched for '.$part.' (lang = '.$this->lang.')', [__CLASS__]);
				return $token.' '.implode(', ', $args);
			}

			$scope = $newScope;
		}

		return vsprintf($scope, $args);
	}
} 