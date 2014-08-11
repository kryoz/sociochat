<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\Forms\Form;
use SocioChat\Forms\Rules;
use SocioChat\Log;
use SocioChat\Message\Msg;
use SocioChat\Response\MessageResponse;

class MessageController extends ControllerBase
{
	public function handleRequest(ChainContainer $chain)
	{
		$clients = UserCollection::get();
		$from = $chain->getFrom();
		$request = $chain->getRequest();
		$recipient = $this->searchUser($from, $request['to']);

		if ($recipient) {
			$this->sendPrivate($from, $recipient, $request['msg']);
			return;
		} elseif ($recipient === false) {
			return;
		}

		$this->sendPublic($clients, $from, $request['msg']);
	}

	protected function getFields()
	{
		return ['msg', 'to'];
	}

	private function sendPrivate(User $from, User $recipient, $msg)
	{
		$response = (new MessageResponse())
			->setFrom($from)
			->setMsg(Msg::create($msg))
			->setTime(null)
			->setChannelId($from->getChannelId())
			->setToUserName($recipient->getProperties()->getName());

		(new UserCollection())
			->attach($from)
			->attach($recipient)
			->setResponse($response)
			->notify();
	}

	private function sendPublic(UserCollection $clients, User $user, $msg)
	{
		$response = (new MessageResponse())
			->setFrom($user)
			->setMsg(Msg::create($msg))
			->setTime(null)
			->setChannelId($user->getChannelId());

		$clients
			->setResponse($response)
			->notify();
	}

	private function searchUser(User $from, $userId)
	{
		if ($userId == '' || $userId == $from->getId()) {
			return null;
		}

		$form = (new Form())
			->import([UserDAO::ID => $userId])
			->addRule(UserDAO::ID, Rules::isUserOnline(), $from->getLang()->getPhrase('UserIsNotOnline'));

		if (!$form->validate()) {
			RespondError::make($from, $form->getErrors());
			DI::get()->container()->get('logger')->warn("Trying to find userId = $userId for private message but not found", [__CLASS__]);
			return false;
		}

		$recipient = $form->getResult(UserDAO::ID);
		/* @var $recipient User */

		return $recipient;
	}
} 