<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;

interface TextCommand
{
	public function isAllowed(User $user);
	public function getHelp();
	public function run(User $user, $args);
}