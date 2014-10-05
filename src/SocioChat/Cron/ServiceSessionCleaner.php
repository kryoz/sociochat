<?php

namespace SocioChat\Cron;

use SocioChat\DI;
use SocioChat\Session\DBSessionHandler;

class ServiceSessionCleaner implements CronService
{

    /**
     * @param array $options
     */
    public function setup(array $options)
    {

    }

    /**
     * @return boolean
     */
    public function canRun()
    {
        return true;
    }

    /**
     * @return string|null
     */
    public function getLockName()
    {
        return 'SessionCleaner';
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return "Script to clean sessions\n";
    }

    public function run()
    {
        $sessionHandler = new DBSessionHandler();
        $config = DI::get()->getConfig();

        $sessionHandler->clean($config->session->lifetime);
    }
}
