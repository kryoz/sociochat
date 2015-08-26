<?php
namespace SocioChat\Controllers;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserDAO;
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

        if (!$banUser = UserDAO::create()->getById($request[PropertiesDAO::USER_ID])) {
            RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('ThatUserNotFound')]);
            return;
        }

        if ($banUser->getId() == $user->getId()) {
            RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('CantDoToYourself')]);
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

        if (!$unbanUser = UserDAO::create()->getById($request['user_id'])) {
            RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('ThatUserNotFound')]);
            return;
        }

	    if ($unbanUser->getId() == $user->getId()) {
		    RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('CantDoToYourself')]);
		    return;
	    }

        $user->getBlacklist()->unbanUserId($unbanUser->getId());
        $user->save();

        $this->unbanResponse($user, $unbanUser);
    }

    private function banResponse(User $user, UserDAO $banUserDAO)
    {
        $response = (new MessageResponse())
            ->setMsg(MsgToken::create('UserBannedSuccessfully', $banUserDAO->getPropeties()->getName()))
            ->setTime(null)
            ->setChannelId($user->getChannelId())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify(false);

	    if ($banUser = DI::get()->getUsers()->getClientById($banUserDAO->getId())) {
		    $response = (new MessageResponse())
			    ->setMsg(MsgToken::create('UserBannedYou', $user->getProperties()->getName()))
			    ->setChannelId($banUser->getChannelId())
			    ->setTime(null);

		    (new UserCollection())
			    ->attach($banUser)
			    ->setResponse($response)
			    ->notify(false);
	    }

    }

    private function unbanResponse(User $user, UserDAO $unBanUserDAO)
    {
        $response = (new MessageResponse())
            ->setMsg(MsgToken::create('UserIsUnbanned', $unBanUserDAO->getPropeties()->getName()))
            ->setTime(null)
            ->setChannelId($user->getChannelId())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify(false);

	    if ($unBanUser = DI::get()->getUsers()->getClientById($unBanUserDAO->getId())) {
		    $response = (new MessageResponse())
			    ->setMsg(MsgToken::create('UserUnbannedYou', $user->getProperties()->getName()))
			    ->setChannelId($unBanUser->getChannelId())
			    ->setTime(null);

		    (new UserCollection())
			    ->attach($unBanUser)
			    ->setResponse($response)
			    ->notify(false);
	    }
    }
}
