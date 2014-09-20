<?php

namespace SocioChat;

use SocioChat\Clients\UserCollection;

class DI extends \Core\DI
{

	/**
	 * @return UserCollection
	 */
	public function getUsers()
	{
		return $this->container->get('users');
	}
} 