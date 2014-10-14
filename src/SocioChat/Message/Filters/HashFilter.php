<?php

namespace SocioChat\Message\Filters;

use Core\Utils\DbQueryHelper;
use SocioChat\DAO\HashDAO;

class HashFilter implements ChainInterface
{
    const MIN_LENGTH = 10;
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

        if (preg_match('~(#[a-zа-я0-9-_]+)~uis', $text, $matches)) {
            $hash = $matches[1];

            if ((mb_strlen($hash) + self::MIN_LENGTH) > mb_strlen($text)) {
                return;
            }

            $msgList = HashDAO::create()->getListByHash($hash, 0, 10);
            $normText = mb_strtoupper($text);

            foreach ($msgList as $msg) {
                similar_text($normText, mb_strtoupper($msg->getMessage()), $metric);
                if ($metric == 0 || $metric > 90) {
                    return;
                }
            }

            $newHash = HashDAO::create()
                ->setUser($user)
                ->setDate(DbQueryHelper::timestamp2date())
                ->setMessage($text);
            $newHash->save();
        }
    }
}
