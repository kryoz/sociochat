<?php

namespace SocioChat\Message\Filters;

use SocioChat\DI;

class InputFilter implements ChainInterface
{
    const MAX_BR = 4;
    const CUT = '...';

    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $text = strip_tags(htmlentities(trim($request['msg'])));
		$msgMaxLength = DI::get()->getConfig()->msgMaxLength;
        if (mb_strlen($text) > $msgMaxLength) {
            $text = mb_strcut($text, 0, $msgMaxLength) . self::CUT;
        }

        $request['msg'] = $text;
        $chain->setRequest($request);
    }
}
