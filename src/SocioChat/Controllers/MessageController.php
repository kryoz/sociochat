<?php
namespace SocioChat\Controllers;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use SocioChat\Message\Filters\Chain;
use SocioChat\Message\Filters\CommandFilter;
use SocioChat\Message\Filters\HashFilter;
use SocioChat\Message\Filters\InputFilter;
use SocioChat\Message\Filters\LineBreakFilter;
use SocioChat\Message\Filters\MusicFilter;
use SocioChat\Message\Msg;
use SocioChat\Response\MessageResponse;
use SocioChat\Utils\RudeFilter;

class MessageController extends ControllerBase
{
    public function handleRequest(ChainContainer $chain)
    {
        $isSelf = $this->filterInput($chain);

        $clients = DI::get()->getUsers();
        $from = $chain->getFrom();
        $request = $chain->getRequest();

	    if (!$request['msg']) {
		    return;
	    }

        $recipient = $this->searchUser($from, $request['to']);

        if ($recipient) {
            $this->sendPrivate($from, $recipient, $request['msg']);
            return;
        } elseif ($recipient === false) {
            return;
        }

        $this->sendPublic($clients, $from, $request['msg'], $isSelf);
    }

    protected function getFields()
    {
        return ['msg', 'to'];
    }

    private function sendPrivate(User $from, User $recipient, $msg)
    {
        $log = false;

        $response = (new MessageResponse())
            ->setMsg(Msg::create($msg))
            ->setFilteredMsg(Msg::create(RudeFilter::parse($msg)))
            ->setTime(null)
            ->setChannelId($from->getChannelId());

        $sender = new UserCollection();

        if ($from->getId() != $recipient->getId()) {
            $response
                ->setFrom($from)
                ->setToUserName($recipient->getProperties()->getName());
            $sender->attach($recipient);
            $log = true;
        }

        $sender
            ->attach($from)
            ->setResponse($response)
            ->notify($log);
    }

    private function sendPublic(UserCollection $clients, User $user, $msg, $isSelf)
    {
        $props = $user->getProperties();

	    $filteredMsg = RudeFilter::parse($msg);
	    if (mb_strlen($filteredMsg) != mb_strlen($msg)) {
		    $props->setRudeCount($props->getRudeCount() + 1);
	    }

	    $props->setWordsCount($props->getWordsCount() + mb_substr_count($msg, ' ') + 1);

        $response = (new MessageResponse())
            ->setMsg(Msg::create($msg))
            ->setFilteredMsg(Msg::create($filteredMsg))
            ->setTime(null)
            ->setChannelId($user->getChannelId());

        if (!$isSelf) {
            $response->setFrom($user);
        }
        $clients
            ->setResponse($response)
            ->notify();
    }

    private function searchUser(User $from, $userId)
    {
        if ($userId == '') {
            return null;
        }

        if ($userId == $from->getId()) {
            return $from;
        }

        $form = (new Form())
            ->import([UserDAO::ID => $userId])
            ->addRule(UserDAO::ID, Rules::isUserOnline(), $from->getLang()->getPhrase('UserIsNotOnline'));

        if (!$form->validate()) {
            RespondError::make($from, $form->getErrors());
            DI::get()->getLogger()->warn(
                "Trying to find userId = $userId for private message but not found",
                [__CLASS__]
            );
            return false;
        }

        $recipient = $form->getResult(UserDAO::ID);
        /* @var $recipient User */

        return $recipient;
    }

    private function filterInput(ChainContainer $appChain)
    {
        $from = $appChain->getFrom();
        $request = $appChain->getRequest();
        unset($request['self']); // skip direct command from client

        $chain = (new Chain)
            ->setRequest($request)
            ->setUser($from)
            ->addHandler(new InputFilter())
            ->addHandler(new LineBreakFilter())
            ->addHandler(new CommandFilter())
            ->addHandler(new MusicFilter())
            ;
        $chain->run();

        $request = $chain->getRequest();
        if (isset($request['self'])) {
            unset($request['self']);
            $appChain->setRequest($request);
            return true;
        }
        $appChain->setRequest($request);
    }
}
