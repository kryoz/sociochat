<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\UserNotesDAO;
use SocioChat\DI;
use SocioChat\Message\MsgRaw;
use SocioChat\Response\MessageResponse;

class NoteController extends ControllerBase
{
    private $actionsMap = [
        'save' => 'processSave',
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
        return ['action', 'user_id', UserNotesDAO::NOTE];
    }

    protected function processSave(ChainContainer $chain)
    {
        $request = $chain->getRequest();
        $user = $chain->getFrom();

        if (!$notedUser = DI::get()->getUsers()->getClientById($request[UserNotesDAO::USER_ID])) {
            RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('ThatUserNotFound')]);
            return;
        }

        if ($notedUser->getId() == $user->getId()) {
            RespondError::make($user, ['user_id' => $user->getLang()->getPhrase('CantDoToYourself')]);
            return;
        }

        $dao = $user->getUserNotes()
            ->setUserId($user->getId())
            ->setNotedUserId($notedUser->getId())
            ->setNote($this->filterInput($request[UserNotesDAO::NOTE]));
        $dao->save();

        $this->guestResponse($user);
    }

    private function filterInput($msg)
    {
        $text = strip_tags(htmlentities($msg));

        if (mb_strlen($text) > 255) {
            $text = mb_strcut($text, 0, 255);
        }

        return $text;
    }

    private function guestResponse(User $user)
    {
        $response = (new MessageResponse())
            ->setMsg(MsgRaw::create(''))
            ->setTime(null)
            ->setChannelId($user->getChannelId())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify(false);
    }
}