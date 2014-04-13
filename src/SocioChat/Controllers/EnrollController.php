<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\PendingPrivates;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\Enum\TimEnum;
use SocioChat\Response\MessageResponse;

class EnrollController extends ControllerBase
{
	private $actionsMap = [
		'invite' => 'processInvite',
		'submit' => 'processSubmit'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$action = $chain->getRequest()['action'];

		if (!isset($this->actionsMap[$action])) {
			$this->errorResponse($chain->getFrom());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action'];
	}

	protected function processSubmit(ChainContainer $chain)
	{
		$duals = PendingDuals::get();
		$users = UserCollection::get();
		$user = $chain->getFrom();
		$lang = $user->getLang();

		if ($user->getProperties()->getTim()->getId() == TimEnum::ANY) {
			$user->send(['msg' => $lang->getPhrase('SelectTIMinProfile')]);
			return;
		}

		if ($user->isInPrivateChat()) {
			$user->send(['msg' => $lang->getPhrase('ThisFunctionWorkInPublicOnly')]);
			return;
		}

		if ($duals->getUserPosition($user)) {
			$user->send(['msg' => $lang->getPhrase('YouAlreadySentRequestOnSearch')]);
			return;
		}

		$tim = $user->getProperties()->getTim();

		if ($dualUserId = $duals->matchDual($user)) {
			$dualUser = $users->getClientById($dualUserId);
			$oldChatId = $user->getChatId();
			$newChatRoomId = uniqid('_', 1);

			$dualUser->setChatId($newChatRoomId);
			$dualUser->save();

			$user->setChatId($newChatRoomId);
			$user->save();

			$this->sendMatchResponse($users->getUsersByChatId($newChatRoomId), $lang->getPhrase('DualIsFound'));
			$this->renewGuestsList($oldChatId, $lang->getPhrase('DualizationStarted'));
			$this->sendRenewPositions($duals->getUsersByDualTim($tim));
			return;
		}

		$this->sendPendingResponse($user, $lang->getPhrase('DualPending'), true);
		$this->dualGuestsList($user);
	}

	protected function processInvite(ChainContainer $chain)
	{
		$users = UserCollection::get();
		$user = $chain->getFrom();
		$request = $chain->getRequest();
		$lang = $user->getLang();

		if (!isset($request[PropertiesDAO::USER_ID])) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('RequiredPropertyNotSpecified')]);
			return;
		}

		if (!$desiredUser = $users->getClientById($request[PropertiesDAO::USER_ID])) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('ThatUserNotFound')]);
			return;
		}

		if ($desiredUser->getId() == $user->getId()) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('YouCantInviteYourself')]);
			return;
		}

		if ($desiredUser->isInPrivateChat()) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('UserAlreadyInPrivate')]);
			return;
		}

		if ($user->isInPrivateChat()) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('YouAlreadyInPrivate')]);
			return;
		}

		$privates = PendingPrivates::get();
		list($inviterUserId, $time) = $privates->invite($user, $desiredUser, $this->getTimeoutCallableResponse());

		$remainingTime = time() - $time;

		if ($remainingTime < $privates->getTTL() && $inviterUserId) {
			$this->errorResponse($user, ['user_id' => $lang->getPhrase('YouAlreadySentInvitation', $privates->getTTL() - $remainingTime)]);
			return;
		} elseif (!$time && !$inviterUserId) {
			$newChatRoomId = uniqid('_', 1);

			$desiredUser->setChatId($newChatRoomId);
			$desiredUser->save();

			$user->setChatId($newChatRoomId);
			$user->save();

			$this->sendMatchResponse($users->getUsersByChatId($newChatRoomId), $lang->getPhrase('InvitationAccepted'));
			return;
		}

		$this->sendPendingResponse($user, $lang->getPhrase('SendInvitationFor', $desiredUser->getProperties()->getName()));
		$this->sendPendingResponse($desiredUser, $lang->getPhrase('UserInvitesYou', $user->getProperties()->getName(), $user->getId()));
	}

	private function getTimeoutCallableResponse()
	{
		return function(User $userInviter, User $desiredUser) {
			$response = (new MessageResponse())
				->setMsg($desiredUser->getLang()->getPhrase('UserInviteTimeout', $userInviter->getProperties()->getName()))
				->setChatId($desiredUser->getChatId())
				->setTime(null);

			(new UserCollection())
				->setResponse($response)
				->attach($desiredUser)
				->notify(false);

			$response = (new MessageResponse())
				->setMsg($userInviter->getLang()->getPhrase('SelfInviteTimeout', $desiredUser->getProperties()->getName()))
				->setChatId($userInviter->getChatId())
				->setTime(null);

			(new UserCollection())
				->setResponse($response)
				->attach($userInviter)
				->notify(false);
		};
	}

	private function sendPendingResponse(User $user, $msg, $dualchat = false)
	{
		$response = (new MessageResponse())
			->setMsg($msg)
			->setTime(null)
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChatId())) // список для нового чата
			->setChatId($user->getChatId());

		if ($dualchat) {
			$response->setDualChat('init');
		}

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}

	private function sendMatchResponse(array $users, $msg)
	{
		$notification = new UserCollection();

		foreach ($users as $user) {
			$notification->attach($user);
		}

		$user = $users[0];
		/* @var $user User */
		$response = (new MessageResponse())
			->setDualChat('match')
			->setMsg($msg)
			->setChatId($user->getChatId())
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChatId()));

		$notification
			->setResponse($response)
			->notify();
	}

	private function renewGuestsList($oldChatId, $msg)
	{
		$allUsers = UserCollection::get();
		$newCommonList = $allUsers->getUsersByChatId($oldChatId);
		$response = (new MessageResponse())
			->setTime(null)
			->setChatId($oldChatId)
			->setMsg($msg)
			->setGuests($newCommonList);

		$allUsers
			->setResponse($response)
			->notify();
	}

	private function dualGuestsList(User $user)
	{
		$dualUsers = UserCollection::get()->getUsersByChatId($user->getChatId());
		if (!$dual = TimEnum::create(PendingDuals::get()->getDualTim($user->getProperties()->getTim()))) {
			return;
		}

		foreach ($dualUsers as $n => $guest) {
			if ($guest->getProperties()->getTim()->getId() != $dual->getId() && $guest->getProperties()->getTim()->getId() != TimEnum::ANY) {
				unset($dualUsers[$n]);
			}
		}

		if (empty($dualUsers)) {
			return;
		}

		$collection = new UserCollection();
		foreach ($dualUsers as $guest) {
			$collection->attach($guest);
		}

		$response = (new MessageResponse())
			->setTime(null)
			->setChatId($user->getChatId())
			->setMsg($user->getLang()->getPhrase('DualIsWanted', $dual->getShortName()));

		$collection
			->setResponse($response)
			->notify(false);
	}

	private function sendRenewPositions(array $userIds)
	{
		if (empty($userIds)) {
			return;
		}

		$notification = new UserCollection();
		$users = UserCollection::get();

		foreach ($userIds as $userId) {
			$user = $users->getClientById($userId);
			$response = (new MessageResponse())
				->setMsg($user->getLang()->getPhrase('DualQueueShifted', count($userIds)))
				->setDualChat('init')
				->setTime(null)
				->setGuests(UserCollection::get()->getUsersByChatId($user->getChatId()))
				->setChatId($user->getChatId());

			$notification
				->attach($user)
				->setResponse($response);
		}

		$notification->notify(false);
	}
}