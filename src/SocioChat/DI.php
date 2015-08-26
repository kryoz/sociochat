<?php

namespace SocioChat;

use Core\Memcache\Wrapper;
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

	/**
	 * @return Wrapper
	 */
	public function getMemcache()
	{
		return $this->container->get('memcache');
	}
}
