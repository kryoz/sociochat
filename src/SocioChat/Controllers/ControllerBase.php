<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\Response\ErrorResponse;

abstract class ControllerBase
{
	public function validateFields(ChainContainer $chain)
	{
		$request = $chain->getRequest();

		if (empty($this->getFields())) {
			return;
		}

		$user = $chain->getFrom();

		foreach ($this->getFields() as $field) {
			if (!isset($request[$field])) {
				RespondError::make($user, $user->getLang()->getPhrase('RequiredPropertyNotSpecified'));
				return false;
			}
		}
	}

	abstract public function handleRequest(ChainContainer $chain);

	abstract protected function getFields();
} 