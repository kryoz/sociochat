<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Chart service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Chart extends Service
{
    protected function configure()
    {
        $this->addMethod('getHypedArtists');
        $this->addMethod('getHypedTracks');
        $this->addMethod('getLovedTracks');
        $this->addMethod('getTopArtists');
        $this->addMethod('getTopTags');
        $this->addMethod('getTopTracks');
    }
}
