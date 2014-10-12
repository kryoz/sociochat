<?php

namespace SocioChat\Message\Filters;

use SocioChat\Application\Chat;
use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DI;

class CommandFilter implements ChainInterface
{
    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $text = $request['msg'];
        $user = $chain->getUser();

        $map = [
            'getip' => 'processGetIp',
            'kick' => 'processKick',
            'names' => 'processNameChangeHistory',
            'me' => 'processMe'
        ];

        if (preg_match('~^\/(\S+) (.*)$~uis', $text, $matches)) {
            $command = $matches[1];
            $arg = $matches[2];

            if (isset($map[$command])) {
                if ($response = $this->{$map[$command]}($user, $arg, $chain)) {
                    $this->changeRequest($chain, $response[0], $response[1]);
                    return;
                }
                $this->changeRequest($chain, "Недостаточно прав");
            }
        }
    }

    protected function processKick(User $user, $text)
    {
        if (!($user->getRole()->isAdmin() || $user->isCreator())) {
            return;
        }

        $text = explode(' ', $text);

        $assHoleName = $text[0];
        $users = DI::get()->getUsers();

        if (!$assHole = $users->getClientByName($assHoleName)) {
            return ["$assHoleName not found", 1];
        }

        $assHole
            ->setAsyncDetach(false)
            ->send(
                [
                    'disconnect' => 1,
                    'msg' => isset($text[1]) ? $text[1] : null
                ]
            );

        Chat::get()->onClose($assHole->getConnection());

        return ["$assHoleName кикнут", false];
    }

    protected function processGetIp(User $user, $request)
    {
        if (!$user->getRole()->isAdmin()) {
            return;
        }

        $request = explode(' ', $request);

        $name = $request[0];
        $users = DI::get()->getUsers();

        if (!$targetUser = $users->getClientByName($name)) {
            RespondError::make($user, ['userId' => "$name not found"]);
            return;
        }

        return [$targetUser->getProperties()->getName() . ' ip = ' . $user->getIp(), true];
    }

    protected function processNameChangeHistory(User $user, $request)
    {
        $request = explode(' ', $request);

        $name = $request[0];
        $users = DI::get()->getUsers();

        if (!$targetUser = $users->getClientByName($name)) {
            RespondError::make($user, ['userId' => "$name not found"]);
            return;
        }

        $list = NameChangeDAO::create()->getHistoryByUserId($targetUser->getId());

        $html = '<table class="table table-striped">';

        /** @var $row NameChangeDAO */
        foreach ($list as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $row->getDateRaw() . '</td>';
            $html .= '<td>' . $row->getName() . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return [$html, true];
    }

    protected function processMe(User $user, $text)
    {
        return [$user->getProperties()->getName().' '.$text, false];
    }

    private function changeRequest(Chain $chain, $msg, $isPrivate = true)
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
