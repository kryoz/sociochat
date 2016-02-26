<?php

namespace SocioChat\Cron;

use Silex\Application;
use SocioChat\DAO\ActivationsDAO;

class ServiceActivationsCleaner implements CronService
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
        return 'ActivationsCleaner';
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
        ActivationsDAO::create()->dropUsedActivations();
    }
}
