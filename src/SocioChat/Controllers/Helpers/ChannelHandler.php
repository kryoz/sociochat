<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingPrivates;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\Forms\Form;
use SocioChat\Forms\Rules;
use SocioChat\Forms\WrongRuleNameException;
use SocioChat\Message\MsgContainer;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class ChannelHandler
{
	public static function joinPrivate(ChainContainer $chain)
	{
		$users = UserCollection::get();
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
	}

	public static function joinPublic(ChainContainer $chain)
	{
		$users = UserCollection::get();
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
				->addRule('channelId', Rules::existsChannel(), $lang->getPhrase('ChannelNotExists'));
		} catch (WrongRuleNameException $e) {
			RespondError::make($user);
			return;
		}

		$channelId = trim($form->getValue('channelId'));
		$user->setChannelId($channelId);
		$user->save(false);

		$user->setLastMsgId(0);

		ChannelNotifier::uploadHistory($user, $users, true);
		ChannelNotifier::welcome($user, $users);
		ChannelNotifier::indentifyChat($user);
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
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChannelId())) // список для нового чата
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
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChannelId()));

		$notification
			->setResponse($response)
			->notify();
	}
} 