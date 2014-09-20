<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingPrivates;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use Core\Form\Form;
use SocioChat\DI;
use SocioChat\Forms\Rules;
use Core\Form\WrongRuleNameException;
use SocioChat\Message\MsgContainer;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class ChannelHandler
{
	public static function joinPrivate(ChainContainer $chain)
	{
		$users = DI::get()->getUsers();
		$user = $chain->getFrom();
		$lang = $user->getLang();

		if (!$desiredUser = self::checkRestrictions($chain, $users)) {
			return;
		}

		$privates = PendingPrivates::get();

		list($inviterUserId, $time) = $privates->invite($user, $desiredUser, self::getTimeoutCallableResponse());

		$remainingTime = time() - $time;

		if ($remainingTime < $privates->getTTL() && $inviterUserId) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('YouAlreadySentInvitation', $privates->getTTL() - $remainingTime)]);
			return;
		} elseif (!$time && !$inviterUserId) {
			$newChatRoomId = uniqid('_', 1);

			$desiredUser->setChannelId($newChatRoomId);
			$desiredUser->save();

			ChannelsCollection::get()->createChannel($newChatRoomId);
			$user->setChannelId($newChatRoomId);
			$user->save();

			self::sendMatchResponse($users->getUsersByChatId($newChatRoomId), MsgToken::create('InvitationAccepted'));
			return;
		}

		self::sendPendingResponse($user, MsgToken::create('SendInvitationFor', $desiredUser->getProperties()->getName()));
		self::sendPendingResponse($desiredUser, MsgToken::create('UserInvitesYou', $user->getProperties()->getName(), $user->getId()));
		ChannelNotifier::updateChannelInfo($users, ChannelsCollection::get());
	}

	public static function joinPublic(ChainContainer $chain)
	{
		$users = DI::get()->getUsers();
		$user = $chain->getFrom();
		$lang = $user->getLang();
		$request = $chain->getRequest();

		if ($user->isInPrivateChat()) {
			RespondError::make($user,[PropertiesDAO::USER_ID => $lang->getPhrase('YouCantLeavePrivate')]);
			return;
		}

		try {
			$form = (new Form())
				->import($request)
				->addRule('channelId', Rules::existsChannel(), $lang->getPhrase('ChannelNotExists'))
				->addRule('channelId', Rules::verifyOnJoinRule($user));
		} catch (WrongRuleNameException $e) {
			RespondError::make($user);
			return;
		}

		if (!$form->validate()) {
			RespondError::make($user, $form->getErrors());
			return;
		}

		$channelId = trim($form->getValue('channelId'));
		$oldChannelId = $user->getChannelId();

		$user->setChannelId($channelId);
		$user->save(false);

		$user->setLastMsgId(0);

		ChannelNotifier::uploadHistory($user, true);
		ChannelNotifier::welcome($user, $users);
		ChannelNotifier::updateGuestsList($users, $oldChannelId);
		ChannelNotifier::indentifyChat($user, $users);
	}

	public static function setChannelName(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$request = $chain->getRequest();
		$lang = $user->getLang();

		if (!isset($request['name']) || !isset($request['channelId'])) {
			RespondError::make($user);
			return;
		}

		try {
			$form = (new Form())
				->import($request)
				->addRule('channelId', Rules::existsChannel(), $lang->getPhrase('ChannelNotExists'))
				->addRule('name', Rules::namePattern(100, true), $lang->getPhrase('InvalidNameFormat'), '_nameFormat')
				->addRule('name', Rules::channelNameDuplication(), $lang->getPhrase('InvalidNameFormat'), '_nameUnique');
		} catch (WrongRuleNameException $e) {
			RespondError::make($user, ['property' => $lang->getPhrase('InvalidProperty')]);
			return;
		}

		if (!$form->validate()) {
			RespondError::make($user, $form->getErrors());
			return;
		}

		$channel = ChannelsCollection::get()->getChannelById($request['channelId']);

		if ($channel->getOwnerId() != $user->getId()) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('InsufficientRights')]);
			return;
		}

		$channel->setName($request['name']);
	}

	private static function checkRestrictions(ChainContainer $chain, UserCollection $users)
	{
		$user = $chain->getFrom();
		$request = $chain->getRequest();
		$lang = $user->getLang();

		if (!isset($request[PropertiesDAO::USER_ID])) {
			RespondError::make($user);
			return;
		}

		if (!$desiredUser = $users->getClientById($request[PropertiesDAO::USER_ID])) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('ThatUserNotFound')]);
			return;
		}

		if ($desiredUser->getId() == $user->getId()) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('YouCantInviteYourself')]);
			return;
		}

		if ($desiredUser->isInPrivateChat()) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('UserAlreadyInPrivate')]);
			return;
		}

		if ($user->isInPrivateChat()) {
			RespondError::make($user, [PropertiesDAO::USER_ID => $lang->getPhrase('YouAlreadyInPrivate')]);
			return;
		}

		return $desiredUser;
	}

	private static function getTimeoutCallableResponse()
	{
		return function(User $userInviter, User $desiredUser) {
			$response = (new MessageResponse())
				->setMsg(MsgToken::create('UserInviteTimeout', $userInviter->getProperties()->getName()))
				->setChannelId($desiredUser->getChannelId())
				->setTime(null);

			(new UserCollection())
				->setResponse($response)
				->attach($desiredUser)
				->notify(false);

			$response = (new MessageResponse())
				->setMsg(MsgToken::create('SelfInviteTimeout', $desiredUser->getProperties()->getName()))
				->setChannelId($userInviter->getChannelId())
				->setTime(null);

			(new UserCollection())
				->setResponse($response)
				->attach($userInviter)
				->notify(false);
		};
	}

	private static function sendPendingResponse(User $user, MsgContainer $msg)
	{
		$response = (new MessageResponse())
			->setMsg($msg)
			->setTime(null)
			->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId())) // список для нового чата
			->setChannelId($user->getChannelId());


		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}

	private static function sendMatchResponse(array $users, MsgContainer $msg)
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
			->setChannelId($user->getChannelId())
			->setGuests($users);

		$notification
			->setResponse($response)
			->notify();
	}
} 