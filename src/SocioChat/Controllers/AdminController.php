<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\ChatConfig;
use SocioChat\Clients\UserCollection;
use SocioChat\Log;

class AdminController extends ControllerBase
{
	private $actionsMap = [
		'reloadConfig' => 'processReload',
		'kickUser' => 'processKick'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();

		Log::get()->fetch()->alert('An attempt to use admin controller by userId = '.$user->getId());

		if ($user->getUserDAO()->getToken() != ChatConfig::get()->getConfig()->adminToken) {
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

	protected function processReload(ChainContainer $chain)
	{
		ChatConfig::get()->loadConfigs();
		Log::get()->fetch()->info('Configuration reloaded');
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