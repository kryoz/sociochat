<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\ChatConfig;
use SocioChat\Clients\UserCollection;
use SocioChat\DI;

class AdminController extends ControllerBase
{
	private $actionsMap = [
		'kickUser' => 'processKick'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$container = DI::get()->container();
		$container->get('logger')->alert('An attempt to use admin controller by userId = '.$user->getId());

		if ($user->getUserDAO()->getToken() != $container->get('config')->adminToken) {
			return;
		}

		$action = $chain->getRequest()['action'];

		if (!isset($this->actionsMap[$action])) {
			$this->errorResponse($chain->getFrom());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action'];
	}

	protected function processKick(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$assHoleId = isset($request['user_id']) ? $request['user_id'] : null;
		$users = UserCollection::get();

		if (!$assHoleId || !$assHole = $users->getClientById($assHoleId)) {
			$chain->getFrom()->send(['msg' => "User_id $assHoleId not found"]);
			return;
		}

		$assHole->getConnection()->
		$assHole
			->setAsyncDetach(false)
			->send(
				[
					'disconnect' => 1,
					'msg' => isset($request['reason']) ? $request['reason'] : null
				]
			);

		Chat::get()->onClose($assHole->getConnection());
	}
}