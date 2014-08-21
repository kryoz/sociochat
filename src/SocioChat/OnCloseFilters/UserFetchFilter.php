<?php

namespace SocioChat\OnCloseFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\UserCollection;

class UserFetchFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$clients = UserCollection::get();
		$conn = $chain->getFrom()->getConnectionId();

		if (!$user = $clients->getClientByConnectionId($conn)) {
			return false;
		}

		$chain->setFrom($user);
		return true;
	}
}