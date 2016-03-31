<?php

namespace SocioChat\Message\Filters;

use SocioChat\Message\Commands\GetIp;
use SocioChat\Message\Commands\Karma;
use SocioChat\Message\Commands\Kick;
use SocioChat\Message\Commands\Mail;
use SocioChat\Message\Commands\Me;
use SocioChat\Message\Commands\Names;
use SocioChat\Message\Commands\Sudo;
use SocioChat\Message\Commands\Sync;
use SocioChat\Message\Commands\TextCommand;

class CommandFilter implements ChainInterface
{
    protected $commandsMap = [
        'getip' => GetIp::class,
        'kick' => Kick::class,
        'names' => Names::class,
        'me' => Me::class,
        'mail' => Mail::class,
	    'sync' => Sync::class,
	    'sudo' => Sudo::class,
        'karma' => Karma::class,
    ];

    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $text = $request['msg'];
        $user = $chain->getUser();

        $map = $this->commandsMap;

        if (preg_match('~^\/(\S+) (.*)$~uis', $text, $matches)) {
            $command = $matches[1];
            $args = $matches[2];

            if (isset($map[$command])) {
                /** @var TextCommand $obj */
                $obj = new $map[$command];
                if (!$obj->isAllowed($user)) {
                    $this->injectCommand($chain, "Недостаточно прав для выполнения команды!");
                    return false;
                }

                if ($response = $obj->run($user, $args)) {
                    list($html, $isPrivate) = $response;
                    $this->injectCommand($chain, $html, $isPrivate);
                    return false;
                }

                $this->injectCommand($chain, "Ошибочная команда!");
                return false;
            }
        } elseif ($text == '/help' || $text == '/?') {
            $html = '<table class="table table-striped">';

            foreach ($this->commandsMap as $token => $class) {
                /** @var TextCommand $obj */
                $obj = new $class;
                if ($obj->isAllowed($user)) {
                    $html .= '<tr>';
                    $html .= '<td><b>/' . $token . '</b></td>';
                    $html .= '<td>' . htmlentities($obj->getHelp()) . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</table>';

            $this->injectCommand($chain, $html, true);
            return false;
        }
    }

    protected function injectCommand(Chain $chain, $msg, $isPrivate = true)
    {
        $request = $chain->getRequest();
        $request['msg'] = $msg;

        if ($isPrivate) {
            $request['to'] = $chain->getUser()->getId();
        } else {
            $request['self'] = 1;
        }

        $chain->setRequest($request);
    }
}
