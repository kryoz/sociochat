<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Venue service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Venue extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('getEvents');
        $this->addMethod('getPastEvents');
        $this->addMethod('search');
    }
}
