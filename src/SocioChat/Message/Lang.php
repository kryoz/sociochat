<?php
namespace SocioChat\Message;

use SocioChat\Log;


class Lang
{
	/**
	 * @var \Zend\Config\Config
	 */
	private $lexicon;

	/**
	 * @param $langCode
	 * @return $this
	 */
	public function setLangByCode($langCode)
	{
		$this->lexicon = Dictionary::get()->getLang($langCode);
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