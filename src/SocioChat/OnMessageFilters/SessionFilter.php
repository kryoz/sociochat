<?php
namespace SocioChat\OnMessageFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;

class SessionFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$token = $user->getWSRequest()->getCookie('token');

		if (!$token) {
			$user->send(['msg' => $user->getLang()->getPhrase('UnAuthSession')]);
			$user->close();
			return false;
		}
	}
}