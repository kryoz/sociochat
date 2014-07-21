<?php
namespace SocioChat\OnOpenFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\ChannelNotifier;
use SocioChat\DI;
use SocioChat\Message\Msg;
use SocioChat\Message\MsgRaw;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;
use SocioChat\Response\UserPropetiesResponse;

class ResponseFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$users = UserCollection::get();
		$user = $chain->getFrom();

		$this->sendNickname($user);
		$this->handleHistory($user);
		$this->notifyChat($user, $users);
	}

	public function sendNickname(User $user)
	{
		$response = (new UserPropetiesResponse())
			->setUserProps($user)
			->setChannelId($user->getChannelId());

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
		$channelId = $user->getChannelId();

		DI::get()->getLogger()->info("Total user count {$userCollection->getTotalCount()}", [__CLASS__]);

		if ($user->isInPrivateChat()) {
			$dualUsers = new UserCollection();
			$dualUsers->attach($user);

			$response = (new MessageResponse())
				->setTime(null)
				->setGuests($userCollection->getUsersByChatId($channelId))
				->setChannelId($channelId);

			if ($userCollection->getClientsCount($channelId) > 1) {
				$dualUsers = $userCollection;
				$response
					->setMsg(MsgToken::create('PartnerIsOnline'))
					->setDualChat('match');
			} elseif ($num = PendingDuals::get()->getUserPosition($user)) {
				$response
					->setMsg(MsgToken::create('StillInDualSearch', $num))
					->setDualChat('init');
			} else {
				$response
					->setMsg(MsgToken::create('YouAreAlone'))
					->setDualChat('match');
			}

			if ($user->getLastMsgId() > 0) {
				$response->setMsg(Msg::create(null));
			}

			$dualUsers
				->setResponse($response)
				->notify(false);
		} else {
			ChannelNotifier::welcome($user, $userCollection);
			ChannelNotifier::indentifyChat($user, $userCollection, true);
		}
	}

	private function handleHistory(User $user)
	{
		ChannelNotifier::uploadHistory($user);

		if (file_exists(ROOT.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'motd.txt') && $user->getLastMsgId() == 0) {
			$motd = file_get_contents(ROOT.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'motd.txt');

			$client = (new UserCollection())
				->attach($user);
			$response = (new MessageResponse())
				->setChannelId($user->getChannelId())
				->setMsg(MsgRaw::create($motd));
			$client
				->setResponse($response)
				->notify(false);
		}
	}
}
