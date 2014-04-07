<?php
namespace MyApp\Utils;

use MyApp\Log;
use MyApp\TSingleton;
use Zend\Config\Config;

class Lang
{
	use TSingleton;

	/**
	 * @var \Zend\Config\Config
	 */
	private $lexicon;

	/**
	 * @param \Zend\Config\Config $lexicon
	 */
	public function setLexicon(Config $lexicon)
	{
		$this->lexicon = $lexicon;
	}

	public function getPhrase()
	{
		$args = func_get_args();
		$token = array_shift($args);

		if (!$this->lexicon || (!$template = $this->lexicon->get($token))) {
			Log::get()->fetch()->error('No localization was matched for '.$token, [__CLASS__]);
			return $token.' '.implode(', ', $args);
		}

		return vsprintf($template, $args);
	}
} 