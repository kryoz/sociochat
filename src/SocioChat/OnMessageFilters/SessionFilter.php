<?php
namespace SocioChat\OnMessageFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;

class SessionFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$currentToken = $user->getWSRequest()->getCookie('token');

		if (!$currentToken || $currentToken != $user->getToken()) {
			$user->send(['msg' => $user->getLang()->getPhrase('UnAuthSession'), 'refreshToken' => 1]);
			$user->close();
			return false;
		}
	}
}