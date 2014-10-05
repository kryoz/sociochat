<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use SocioChat\Message\Msg;
use SocioChat\Response\MessageResponse;
use SocioChat\Utils\RudeFilter;

class MessageController extends ControllerBase
{
    const MAX_MSG_LENGTH = 1024;
    const MAX_BR = 4;

    public function handleRequest(ChainContainer $chain)
    {
        $clients = DI::get()->getUsers();
        $from = $chain->getFrom();
        $request = $chain->getRequest();
        $recipient = $this->searchUser($from, $request['to']);

        $request['msg'] = $this->filterInput($request['msg']);

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
            ->setFilteredMsg(Msg::create(RudeFilter::parse($msg)))
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
        $user->incMessagesCount();

        $response = (new MessageResponse())
            ->setFrom($user)
            ->setMsg(Msg::create($msg))
            ->setFilteredMsg(Msg::create(RudeFilter::parse($msg)))
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
            DI::get()->getLogger()->warn("Trying to find userId = $userId for private message but not found",
                [__CLASS__]);
            return false;
        }

        $recipient = $form->getResult(UserDAO::ID);
        /* @var $recipient User */

        return $recipient;
    }

    private function filterInput($msg)
    {
        $text = strip_tags(htmlentities($msg));

        if (mb_strlen($text) > self::MAX_MSG_LENGTH) {
            $text = mb_strcut($text, 0, self::MAX_MSG_LENGTH) . '...';
        }

        $text = preg_replace('~(\|)~u', '<br>', $text, self::MAX_BR);

        return $text;
    }
} 