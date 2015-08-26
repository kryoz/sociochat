<?php

namespace SocioChat\Application\OnCloseFilters;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;

class AdsFilter implements ChainInterface
{
    public function handleRequest(ChainContainer $chain)
    {
        $user = $chain->getFrom();

        $ads = \SocioChat\OnMessageFilters\AdsFilter::get();

        $ads->deleteAdTimer($user);
        $ads->deleteMsgTimer($user);
        $ads->deleteLastMsgId($user);

        return true;
    }
}