<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Auth service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Auth extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('getMobileSession');
        $this->addMethod('getSession');
        $this->addMethod('getToken');
    }
}
