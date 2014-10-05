<?php

namespace SocioChat\OnMessageFilters;

use SocioChat\DI;
use Core\TSingleton;
use React\EventLoop\LibEventLoop;
use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\MsgRaw;
use SocioChat\Response\MessageResponse;

class AdsFilter implements ChainInterface
{
    use TSingleton;

    private $adTimers = [];
    private $msgTimers = [];
    private $lastMsgIds = [];

    public function handleRequest(ChainContainer $chain)
    {
        $user = $chain->getFrom();
        if (isset($chain->getRequest()['subject']) && $chain->getRequest()['subject'] == 'Message') {
            $this->setMsgTimer($user);
            return true;
        }

        if (!isset($this->adTimers[$user->getId()])) {
            $this->setAdLoop($user);
        }

        return true;
    }

    public function deleteAdTimer(User $user)
    {
        $ad = isset($this->adTimers[$user->getId()]) ? $this->adTimers[$user->getId()] : false;

        if ($ad) {
            $this->getLoop()->cancelTimer($ad);
            unset($this->adTimers[$user->getId()]);
        }
    }

    public function deleteMsgTimer(User $user)
    {
        $msg = isset($this->msgTimers[$user->getId()]) ? $this->msgTimers[$user->getId()] : false;

        if ($msg) {
            $this->getLoop()->cancelTimer($msg);
            unset($this->msgTimers[$user->getId()]);
        }
    }

    public function deleteLastMsgId(User $user)
    {
        unset($this->lastMsgIds[$user->getId()]);
    }

    private function setAdLoop(User $user)
    {
        $config = DI::get()->getConfig();
        $loop = $this->getLoop();

        $timeout = $config->ads->adInterval;

        $timerCallback = function () use ($user, $config) {
            if (isset($this->msgTimers[$user->getId()])) {
                return;
            }

            if (isset($this->lastMsgIds[$user->getId()])) {
                $current = (int)$user->getLastMsgId();
                $last = (int)$this->lastMsgIds[$user->getId()];
                $diff = (int)$config->ads->historyDiffSize;
                if ($current - $last < $diff) {
                    return;
                }
            }

            DI::get()->getLogger()->info('Ad fired for user_id = ' . $user->getId(), ['Show_ad']);
            $this->lastMsgIds[$user->getId()] = $user->getLastMsgId();
            $this->showAd($user);
        };

        $timer = $loop->addPeriodicTimer($timeout, $timerCallback);

        $this->adTimers[$user->getId()] = $timer;
    }

    private function setMsgTimer(User $user)
    {
        $config = DI::get()->getConfig();
        $loop = $this->getLoop();

        $timeout = $config->ads->msgTimeOut;

        $timerCallback = function () use ($user) {
            unset($this->msgTimers[$user->getId()]);
        };

        if (isset($this->msgTimers[$user->getId()])) {
            $loop->cancelTimer($this->msgTimers[$user->getId()]);
        }

        $timer = $loop->addTimer($timeout, $timerCallback);

        $this->msgTimers[$user->getId()] = $timer;
    }

    private function showAd(User $user)
    {
        $msg = MsgRaw::create('<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
						<ins class="adsbygoogle"
						     style="display:inline-block;width:320px;height:50px"
						     data-ad-client="ca-pub-1352019659330191"
						     data-ad-slot="9172675664"></ins>
						<script>
							(adsbygoogle = window.adsbygoogle || []).push({});
						</script>');

        $response = (new MessageResponse())
            ->setChannelId($user->getChannelId())
            ->setMsg($msg);

        (new UserCollection())
            ->setResponse($response)
            ->attach($user)
            ->notify(false);
    }

    /**
     * @return LibEventLoop
     */
    private function getLoop()
    {
        return DI::get()->container()->get('eventloop');
    }
}