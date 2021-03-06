<?php

namespace SocioChat\Application\OnCloseFilters;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\DI;

class UserFetchFilter implements ChainInterface
{
    public function handleRequest(ChainContainer $chain)
    {
        $clients = DI::get()->getUsers();
        $conn = $chain->getFrom()->getConnectionId();

        if (!$user = $clients->getClientByConnectionId($conn)) {
            return false;
        }

        $chain->setFrom($user);
        return true;
    }
}
