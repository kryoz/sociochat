<?php

namespace SocioChat\Application\OnMessageFilters;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\Controllers\BlacklistController;
use SocioChat\Controllers\ControllerBase;
use SocioChat\Controllers\ChannelController;
use SocioChat\Controllers\LoginController;
use SocioChat\Controllers\MainChatController;
use SocioChat\Controllers\MessageController;
use SocioChat\Controllers\NoteController;
use SocioChat\Controllers\PingController;
use SocioChat\Controllers\PropertiesController;

class ControllerFilter implements ChainInterface
{
    protected $map = [
        'Message' => MessageController::class,
        'Properties' => PropertiesController::class,
        'Channel' => ChannelController::class,
        'MainChat' => MainChatController::class,
        'Ping' => PingController::class,
        'Login' => LoginController::class,
        'Blacklist' => BlacklistController::class,
        'Note' => NoteController::class,
    ];

    public function handleRequest(ChainContainer $chain)
    {
        $request = $chain->getRequest();
        if (!isset($request['subject']) || !isset($this->map[$request['subject']])) {
            return false;
        }

        $controllerName = $this->map[$request['subject']];
        $controller = new $controllerName;
        /* @var $controller ControllerBase */

        if ($controller->validateFields($chain) === false) {
            return false;
        }

        $controller->handleRequest($chain);
    }
}
