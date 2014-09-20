<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Controllers\Helpers\MainChatDualsHandler;
use SocioChat\Controllers\Helpers\MainChatPrivateHandler;
use SocioChat\DI;

class MainChatController extends ControllerBase
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$users = DI::get()->getUsers();
		$channels = ChannelsCollection::get();

		MainChatDualsHandler::run($user, $users);
		MainChatPrivateHandler::run($user, $users, $channels);
	}

	protected function getFields()
	{
		return [];
	}
}
