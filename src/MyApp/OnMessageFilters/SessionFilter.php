<?php
namespace MyApp\OnMessageFilters;

use MyApp\Chain\ChainContainer;
use MyApp\Chain\ChainInterface;
use MyApp\Utils\Lang;

class SessionFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$client = $chain->getFrom();
		$token = $client->getWSRequest()->getCookie('PHPSESSID');

		if (!$token) {
			$client->send(['msg' => Lang::get()->getPhrase('UnAuthSession')]);
			$client->close();
			return false;
		}
	}
}