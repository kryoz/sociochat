<?php

namespace Lastfm\Service;

use Lastfm\Service;

/**
 * Tasteometer service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Tasteometer extends Service
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->addMethod('compare');
        $this->addMethod('compareGroup');
    }
}
