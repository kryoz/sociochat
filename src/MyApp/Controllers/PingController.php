<?php
namespace MyApp\Controllers;

use MyApp\Chain\ChainContainer;
use MyApp\Clients\UserCollection;
use MyApp\Response\PingResponse;

class PingController extends ControllerBase
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$response = (new PingResponse())
			->setChatId($user->getChatId());

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify();
	}

	protected function getFields()
	{
		return [];
	}
}