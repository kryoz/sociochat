<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Artist service class
 *
 * @package Lastfm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Artist extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('addTags', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('getCorrection');
        $this->addMethod('getEvents');
        $this->addMethod('getImages');
        $this->addMethod('getInfo');
        $this->addMethod('getPastEvents');
        $this->addMethod('getPodcast');
        $this->addMethod('getShouts');
        $this->addMethod('getSimilar');
        $this->addMethod('getTags', true);
        $this->addMethod('getTopAlbums');
        $this->addMethod('getTopFans');
        $this->addMethod('getTopTags');
        $this->addMethod('getTopTracks');
        $this->addMethod('removeTag', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('search');
        $this->addMethod('share', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('shout', true, Transport::HTTP_METHOD_POST);
    }
}
