<?php
namespace SocioChat\Application\OnOpenFilters;

use SocioChat\DAO\OnlineDAO;
use SocioChat\DI;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\ChannelNotifier;
use SocioChat\Message\Msg;
use SocioChat\Message\MsgRaw;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;
use SocioChat\Response\UserPropetiesResponse;

class ResponseFilter implements ChainInterface
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(ChainContainer $chain)
    {
        $users = DI::get()->getUsers();
        $user = $chain->getFrom();

        $this->sendNickname($user);
        $this->handleHistory($user);
        $this->notifyChat($user, $users);

	    $onlineList = OnlineDAO::create()->getByUserId($user->getId());
	    $onlineList->setUserId($user->getId());
	    $onlineList->save();
    }

    /**
     * @param User $user
     */
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

            if ($user->getLastMsgId()) {
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

    /**
     * @param User $user
     */
    private function handleHistory(User $user)
    {
        ChannelNotifier::uploadHistory($user);

        if (file_exists(ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'motd.txt') && !$user->getLastMsgId()) {
            $motd = file_get_contents(ROOT . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'motd.txt');

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
