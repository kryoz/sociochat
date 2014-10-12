<?php

namespace SocioChat\Application\OnMessageFilters;

use SocioChat\DI;
use Core\TSingleton;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Response\ErrorResponse;

class FloodFilter implements ChainInterface
{
    use TSingleton;
    private $otherTimers = [];
    private $pingTimers = [];

    public function handleRequest(ChainContainer $chain)
    {
        $user = $chain->getFrom();

        if (isset($chain->getRequest()['subject']) && $chain->getRequest()['subject'] == 'Ping') {
            return $this->manageRequest($user, $this->pingTimers, false);
        }

        return $this->manageRequest($user, $this->otherTimers);
    }

    /**
     * @param User $user
     * @param array $timers
     * @param bool $doResponse
     * @return bool
     */
    private function manageRequest(User $user, array &$timers, $doResponse = true)
    {
        if ($this->isRequestHot($user, $timers)) {
            if ($doResponse) {
                $this->respondFloodError($user);
            }
            $this->setTimer($user, $timers);
            return false;
        }

        $this->setTimer($user, $timers);
    }

    /**
     * @param User $user
     * @param array $timers
     * @return bool
     */
    private function isRequestHot(User $user, array $timers)
    {
        return isset($timers[$user->getId()]) ? $timers[$user->getId()] : false;
    }

    /**
     * @param User $user
     */
    private function respondFloodError(User $user)
    {
        $response = (new ErrorResponse())
            ->setChannelId($user->getChannelId())
            ->setErrors(['flood' => $user->getLang()->getPhrase('FloodDetected')]);

        (new UserCollection())
            ->setResponse($response)
            ->attach($user)
            ->notify(false);
    }

    /**
     * @param User $user
     * @param array $timers
     * @return array
     */
    private function setTimer(User $user, array &$timers)
    {
        $container = DI::get()->container();
        $loop = $container->get('eventloop');
        $config = $container->get('config');
        $timeout = $config->flood->timeout;
        $penalty = $config->flood->penalty;

        $timerCallback = function () use ($user, &$timers) {
            unset($timers[$user->getId()]);
        };

        if (isset($timers[$user->getId()])) {
            $loop->cancelTimer($timers[$user->getId()]);
            $timeout += $penalty;
        }

        $timer = $loop->addTimer($timeout, $timerCallback);

        $timers[$user->getId()] = $timer;
    }
}