<?php
namespace SocioChat\Controllers;


use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
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
				$this->errorResponse($user, $user->getLang()->getPhrase('RequiredPropertyNotSpecified'));
				return false;
			}
		}
	}

	abstract public function handleRequest(ChainContainer $chain);

	abstract protected function getFields();

	protected function errorResponse(User $user, $errors = null)
	{
		$response = (new ErrorResponse())
			->setErrors(is_array($errors) ? $errors : [$errors ?: $user->getLang()->getPhrase('RequiredActionNotSpecified')])
			->setChatId($user->getChatId());

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify();
	}
} 