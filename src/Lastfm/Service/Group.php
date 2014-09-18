<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Group service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Group extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('getHype');
        $this->addMethod('getMembers');
        $this->addMethod('getWeeklyAlbumChart');
        $this->addMethod('getWeeklyArtistChart');
        $this->addMethod('getWeeklyChartList');
        $this->addMethod('getWeeklyTrackChart');
    }
}
