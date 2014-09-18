<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Album service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Album extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('addTags', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('getBuyLinks');
        $this->addMethod('getInfo');
        $this->addMethod('getShouts');
        $this->addMethod('addTags', true);
        $this->addMethod('getTopTags');
        $this->addMethod('removeTag', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('search');
        $this->addMethod('share', true, Transport::HTTP_METHOD_POST);
    }
}
