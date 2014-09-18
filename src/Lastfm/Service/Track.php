<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Track service class
 *
 * @package Lastfm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Track extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('addTags', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('ban', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('getBuylinks');
        $this->addMethod('getCorrection');
        $this->addMethod('getFingerprintMetadata');
        $this->addMethod('getInfo');
        $this->addMethod('getShouts');
        $this->addMethod('getSimilar');
        $this->addMethod('getTags', true);
        $this->addMethod('getTopFans');
        $this->addMethod('getTopTags');
        $this->addMethod('love', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('removeTag', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('scrobble', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('search');
        $this->addMethod('share', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('unban', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('unlove', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('updateNowPlaying', true, Transport::HTTP_METHOD_POST);
    }
}
