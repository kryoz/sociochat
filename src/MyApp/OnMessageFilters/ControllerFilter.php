<?php

namespace MyApp\OnMessageFilters;

use MyApp\Chain\ChainContainer;
use MyApp\Chain\ChainInterface;
use MyApp\Controllers\AdminController;
use MyApp\Controllers\BlacklistController;
use MyApp\Controllers\ControllerBase;
use MyApp\Controllers\EnrollController;
use MyApp\Controllers\LoginController;
use MyApp\Controllers\MainChatController;
use MyApp\Controllers\MessageController;
use MyApp\Controllers\PingController;
use MyApp\Controllers\PropertiesController;

class ControllerFilter implements ChainInterface
{
	protected $map = [
		'Message' => MessageController::class,
		'Properties' => PropertiesController::class,
		'Enroll' => EnrollController::class,
		'MainChat' => MainChatController::class,
		'Ping' => PingController::class,
		'Login' => LoginController::class,
		'Blacklist' => BlacklistController::class,
		'Admin' => AdminController::class,
	];

	public function handleRequest(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		if (!isset($request['subject']) || !isset($this->map[$request['subject']])) {
			return false;
		}

		$controllerName = $this->map[$request['subject']];
		$controller = new $controllerName;
		/* @var $controller ControllerBase */

		if (!$controller->validateFields($chain) === false) {
			return false;
		}

		$controller->handleRequest($chain);
	}
}