<?php
namespace SocioChat\Controllers;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Controllers\Helpers\ChannelHandler;
use SocioChat\Controllers\Helpers\DualChatHandler;
use SocioChat\Controllers\Helpers\RespondError;

class ChannelController extends ControllerBase
{
    private $actionsMap = [
        'join' => 'processJoin',
        'dualSearch' => 'processDualRoulette',
        //'setName' => 'setChatName',
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
        return ['action'];
    }

    protected function processDualRoulette(ChainContainer $chain)
    {
        DualChatHandler::run($chain);
    }

    protected function processJoin(ChainContainer $chain)
    {
        if (isset($chain->getRequest()['user_id'])) {
            ChannelHandler::joinPrivate($chain);
        } else {
            ChannelHandler::joinPublic($chain);
        }

    }

    protected function setChatName(ChainContainer $chain)
    {
        ChannelHandler::setChannelName($chain);
    }
}
