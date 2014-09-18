<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Geo service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Geo extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('getEvents');
        $this->addMethod('getMetroArtistChart');
        $this->addMethod('getMetroHypeArtistChart');
        $this->addMethod('getMetroHypeTrackChart');
        $this->addMethod('getMetroTrackChart');
        $this->addMethod('getMetroUniqueArtistChart');
        $this->addMethod('getMetroUniqueTrackChart');
        $this->addMethod('getMetroWeeklyChartlist');
        $this->addMethod('getMetros');
        $this->addMethod('getTopArtists');
        $this->addMethod('getTopTracks');
    }
}
