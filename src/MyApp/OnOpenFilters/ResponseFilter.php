<?php
namespace MyApp\OnOpenFilters;

use MyApp\Chain\ChainContainer;
use MyApp\Chain\ChainInterface;
use MyApp\Clients\ChatsCollection;
use MyApp\Clients\PendingDuals;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\Log;
use MyApp\Response\MessageResponse;
use MyApp\Response\UserPropetiesResponse;
use MyApp\Utils\Lang;

class ResponseFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$clients = UserCollection::get();
		$user = $chain->getFrom();

		$this->sendNickname($user, $clients);
		$this->handleHistory($user);
		$this->notifyChat($user, $clients);
	}

	public function sendNickname(User $user, UserCollection $clients)
	{
		$response = (new UserPropetiesResponse())
			->setUserProps($user)
			->setChatId($user->getChatId())
			->setGuests($clients->getUsersByChatId($user->getChatId()));

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}

	/**
	 * @param User $user
	 * @param UserCollection $userCollection
	 */
	public function notifyChat(User $user, UserCollection $userCollection)
	{
		$chatId = $user->getChatId();
		$users = $userCollection->getUsersByChatId($chatId);
		$usersCount = count($users);
		$lang = Lang::get();

		Log::get()->fetch()->info("Total user count {$userCollection->getTotalCount()}", [__CLASS__]);

		if ($user->isInPrivateChat()) {
			$dualUsers = new UserCollection();
			$dualUsers->attach($user);

			$response = (new MessageResponse())
				->setTime(null)
				->setGuests($userCollection->getUsersByChatId($chatId))
				->setChatId($chatId);

			if ($usersCount > 1) {
				$dualUsers = $userCollection;
				$response
					->setMsg($lang->getPhrase('PartnerIsOnline'))
					->setDualChat('match');
			} elseif ($num = PendingDuals::get()->getUserPosition($user)) {
				$response
					->setMsg($lang->getPhrase('StillInDualSearch', $num))
					->setDualChat('init');
			} else {
				$response
					->setMsg($lang->getPhrase('YouAreAlone'))
					->setDualChat('match');
			}

			if ($user->getLastMsgId() > 0) {
				$response->setMsg(null);
			}

			$dualUsers
				->setResponse($response)
				->notify(false);
		} else {
			$response = (new MessageResponse())
				->setTime(null)
				->setGuests($userCollection->getUsersByChatId($chatId))
				->setChatId($chatId);

			if ($user->getLastMsgId() == 0) {
				$response->setMsg($lang->getPhrase('WelcomeUser', $usersCount, $user->getProperties()->getName()));
			}
			$userCollection
				->setResponse($response)
				->notify();

			$this->notifyOnPendingDuals($user);
		}
	}

	public function notifyOnPendingDuals(User $user)
	{
		if (!empty(PendingDuals::get()->getUsersByDualTim($user->getProperties()->getTim()))) {
			$response = (new MessageResponse())
				->setMsg(Lang::get()->getPhrase('DualIsWanted', $user->getProperties()->getTim()->getShortName()))
				->setTime(null)
				->setChatId($user->getChatId());
			(new UserCollection())
				->attach($user)
				->setResponse($response)
				->notify(false);
		}
	}

	private function handleHistory(User $user)
	{
		$history = ChatsCollection::get();
		$log = $history->getHistory($user);
		$client = (new UserCollection())
			->attach($user);

		foreach ($log as $response) {
			/* @var $response MessageResponse */

			if ($response->getToUserName()) {
				$name = $user->getProperties()->getName();
				if ($response->getToUserName() == $name || $response->getFromName() == $name) {
					$client->setResponse($response)->notify(false);
				}
				continue;
			}

			$client->setResponse($response)->notify(false);
		}

		if (file_exists(ROOT.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'motd.txt') && $user->getLastMsgId() == 0) {
			$motd = file_get_contents(ROOT.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'motd.txt');
			$response = (new MessageResponse())
				->setChatId(1) // TODO chatRoom
				->setMsg($motd);
			$client
				->setResponse($response)
				->notify(false);
		}
	}
}
