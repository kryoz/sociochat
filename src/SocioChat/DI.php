<?php

namespace SocioChat;

use SocioChat\Clients\UserCollection;
use SocioChat\Session\SessionHandler;

class DI extends \Core\DI
{

    /**
     * @return UserCollection
     */
    public function getUsers()
    {
        return $this->container->get('users');
    }

    /**
     * @return SessionHandler
     */
    public function getSession()
    {
        return $this->container->get('session');
    }
}
