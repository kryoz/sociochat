<?php
namespace MyApp\Controllers;


use MyApp\Chain\ChainContainer;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\Response\ErrorResponse;
use MyApp\Utils\Lang;

abstract class ControllerBase
{
	public function validateFields(ChainContainer $chain)
	{
		$request = $chain->getRequest();

		if (empty($this->getFields())) {
			return;
		}

		foreach ($this->getFields() as $field) {
			if (!isset($request[$field])) {
				$this->errorResponse($chain->getFrom(), Lang::get()->getPhrase('RequiredPropertyNotSpecified'));
				return false;
			}
		}
	}

	abstract public function handleRequest(ChainContainer $chain);

	abstract protected function getFields();

	protected function errorResponse(User $user, $errors = null)
	{
		$response = (new ErrorResponse())
			->setErrors(is_array($errors) ? $errors : [$errors ?: Lang::get()->getPhrase('RequiredActionNotSpecified')])
			->setChatId($user->getChatId());

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify();
	}
} 