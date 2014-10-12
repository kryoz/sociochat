<?php

namespace SocioChat\Message\Filters;

class LineBreakFilter implements ChainInterface
{
    const MAX_BR = 4;

    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
        $request['msg'] = preg_replace('~(\|)~u', '<br>', $request['msg'], self::MAX_BR);

        $chain->setRequest($request);
    }
}
