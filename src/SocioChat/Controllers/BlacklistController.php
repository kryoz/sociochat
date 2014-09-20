<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class BlacklistController extends ControllerBase
{
	private $actionsMap = [
		'ban' => 'processAdd',
		'unban' => 'processRemove',
	];

	public function handleRequest(ChainContainer $chain)
	{
		$action = $chain->getRequest()['action'];
		if (!isset($this->actionsMap[$action])) {
			RespondError::make($chain->getFrom());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action', 'user_id'];
	}

	protected function processAdd(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$user = $chain->getFrom();

		if (!$banUser = DI::get()->getUsers()->getClientById($request[PropertiesDAO::USER_ID])) {
			RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('ThatUserNotFound')]);
			return;
		}

		if ($user->getBlacklist()->banUserId($banUser->getId())) {
			$user->save();
			$this->banResponse($user, $banUser);
		}
	}

	protected function processRemove(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$user = $chain->getFrom();

		if (!$unbanUser = DI::get()->getUsers()->getClientById($request['user_id'])) {
			RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('ThatUserNotFound')]);
			return;
		}

		$user->getBlacklist()->unbanUserId($unbanUser->getId());
		$user->save();

		$this->unbanResponse($user, $unbanUser);
	}

	private function banResponse(User $user, User $banUser)
	{
		$response = (new MessageResponse())
			->setMsg(MsgToken::create('UserBannedSuccessfully', $banUser->getProperties()->getName()))
			->setTime(null)
			->setChannelId($user->getChannelId())
			->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);

		$response = (new MessageResponse())
			->setMsg(MsgToken::create('UserBannedYou', $user->getProperties()->getName()))
			->setChannelId($banUser->getChannelId())
			->setTime(null);

		(new UserCollection())
			->attach($banUser)
			->setResponse($response)
			->notify(false);
	}

	private function unbanResponse(User $user, User $banUser)
	{
		$response = (new MessageResponse())
			->setMsg(MsgToken::create('UserIsUnbanned', $banUser->getProperties()->getName()))
			->setTime(null)
			->setChannelId($user->getChannelId())
			->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);

		$response = (new MessageResponse())
			->setMsg(MsgToken::create('UserUnbannedYou', $user->getProperties()->getName()))
			->setChannelId($banUser->getChannelId())
			->setTime(null);

		(new UserCollection())
			->attach($banUser)
			->setResponse($response)
			->notify(false);
	}
}