<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Library service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Library extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('addAlbum', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('addArtist', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('addTrack', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('getAlbums');
        $this->addMethod('getArtists');
        $this->addMethod('getTracks');
        $this->addMethod('removeAlbum', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('removeAlbum', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('removeArtist', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('removeScrobble', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('removeTrack', true, Transport::HTTP_METHOD_POST);
    }
}
