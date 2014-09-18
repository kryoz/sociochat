<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Playlist service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Playlist extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('addTrack', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('create', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('fetch');
    }
}
