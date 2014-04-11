<?php
namespace SocioChat\OnMessageFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Utils\Lang;

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