<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\DI;
use SocioChat\DIBuilder;

class ReloadConfig implements TextCommand
{

	public function isAllowed(User $user)
	{
		return $user->getRole()->isAdmin();
	}

	public function getHelp()
	{
		return '- reload ini files';
	}

	public function run(User $user, $args)
	{
		$container = DI::get()->container();
		DIBuilder::setupConfig($container);
		DIBuilder::setupDictionary($container);

		$logger = DI::get()->getLogger();
		$logger->info('Configs has been reloaded.');

		return ['Configs has been reloaded!', true];
	}
}