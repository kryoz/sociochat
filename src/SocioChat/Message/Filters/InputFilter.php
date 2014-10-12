<?php

namespace SocioChat\Message\Filters;

class InputFilter implements ChainInterface
{
    const MAX_MSG_LENGTH = 1024;
    const MAX_BR = 4;

    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $text = strip_tags(htmlentities(trim($request['msg'])));

        if (mb_strlen($text) > self::MAX_MSG_LENGTH) {
            $text = mb_strcut($text, 0, self::MAX_MSG_LENGTH) . '...';
        }

        $request['msg'] = $text;
        $chain->setRequest($request);
    }
}
