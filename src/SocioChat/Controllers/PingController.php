<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\UserCollection;
use SocioChat\Response\PingResponse;

class PingController extends ControllerBase
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$response = (new PingResponse())
			->setChannelId($user->getChatId());

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