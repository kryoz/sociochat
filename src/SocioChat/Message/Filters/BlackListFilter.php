<?php

namespace SocioChat\Message\Filters;

class BlackListFilter implements ChainInterface
{

    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $text = $request['msg'];

        if (preg_match('~(kroleg\.tk)~uis', $text)) {
            $text = 'У меня бывают запоры и я писаюсь в кровать по ночам!';
        }

        $request['msg'] = $text;
        $chain->setRequest($request);
    }
}
