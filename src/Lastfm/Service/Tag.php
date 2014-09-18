<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Tag service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Tag extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('getInfo');
        $this->addMethod('getSimilar');
        $this->addMethod('getTopAlbums');
        $this->addMethod('getTopArtists');
        $this->addMethod('getTopTags');
        $this->addMethod('getTopTracks');
        $this->addMethod('getWeeklyArtistChart');
        $this->addMethod('getWeeklyChartList');
        $this->addMethod('search');
    }
}
