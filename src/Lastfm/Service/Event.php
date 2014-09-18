<?php

namespace Lastfm\Service;

use Lastfm\Service;
use Lastfm\Transport;

/**
 * Event service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Event extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('attend', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('getAttendees');
        $this->addMethod('getInfo');
        $this->addMethod('getShouts');
        $this->addMethod('share', true, Transport::HTTP_METHOD_POST);
        $this->addMethod('shout', true, Transport::HTTP_METHOD_POST);
    }
}
