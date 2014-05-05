<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\ChatsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\MainChatDualsHandler;
use SocioChat\Controllers\Helpers\MainChatPrivateHandler;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class MainChatController extends ControllerBase
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$users = UserCollection::get();

		MainChatDualsHandler::run($user, $users);
		MainChatPrivateHandler::run($user, $users, ChatsCollection::get());
	}

	protected function getFields()
	{
		return [];
	}
}
