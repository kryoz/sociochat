<?php
namespace MyApp\Controllers;

use MyApp\Chain\ChainContainer;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\DAO\UserDAO;
use MyApp\Forms\Form;
use MyApp\Forms\Rules;
use MyApp\Log;
use MyApp\Response\MessageResponse;
use MyApp\Utils\Lang;

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
			->setMsg($msg)
			->setTime(null)
			->setChatId($from->getChatId())
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
			->setMsg($msg)
			->setTime(null)
			->setChatId($user->getChatId());

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
			->addRule(UserDAO::ID, Rules::UserOnline(),  Lang::get()->getPhrase('UserIsNotOnline'));

		if (!$form->validate()) {
			$this->errorResponse($from, $form->getErrors());
			Log::get()->fetch()->warn("Trying to find userId = $userId for private message but not found", [__CLASS__]);
			return false;
		}

		$recipient = $form->getResult(UserDAO::ID);
		/* @var $recipient User */

		return $recipient;
	}
} 