<?php

namespace SocioChat\Cron;

use Core\BaseException;
use Core\Enum\Enum;

class CronEnum extends Enum
{

    protected static $names = [
        'sessionCleaner' => ServiceSessionCleaner::class,
        'activationsCleaner' => ServiceActivationsCleaner::class,
	    'avatarCleaner' => ServiceAvatarCleaner::class,
	    'mailer' => ServiceMailer::class,
	    'onlineMonitor' => ServiceOnlineMonitor::class,
    ];

    public function getServiceInstance()
    {
        $className = $this->getName();
        $service = new $className;

        if (!$service instanceof CronService) {
            throw new BaseException("Expects {$this->getName()} implements CronService interface");
        }
        return $service;
    }
}
