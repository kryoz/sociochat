<?php

namespace SocioChat\Cron;

use Silex\Application;
use SocioChat\DAO\TmpSessionDAO;
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

    public function run(Application $app)
    {
        $sessionHandler = new DBSessionHandler();
        $config = $app['config'];

        $sessionHandler->clean($config->session->lifetime);

	    TmpSessionDAO::create()->dropAll();
    }
}
