<?php

namespace SocioChat\Cron;

use SocioChat\DI;
use SocioChat\Enum\Enum;

class CronEnum extends Enum
{

	protected static $names = array(
		'sessionCleaner' => ServiceSessionCleaner::class,
		'activationsCleaner' => ServiceActivationsCleaner::class,
	);

	public function getServiceInstance()
	{
		$service = DI::get()->spawn($this->getName());

		if (!$service instanceof CronService) {
			throw new \Exception("Expects {$this->getName()} implements CronService interface");
		}
		return $service;
	}
}
