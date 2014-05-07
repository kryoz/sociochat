<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\ChatConfig;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\DAOBase;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DI;
use SocioChat\Message\MsgRaw;
use SocioChat\Response\MessageResponse;

class AdminController extends ControllerBase
{
	private $actionsMap = [
		'kickUser' => 'processKick',
		'nameLog' => 'processNameChangeHistory',
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
			$chain->getFrom()->send(['msg' => "user_id $assHoleId not found"]);
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

	protected function processNameChangeHistory(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$userId = isset($request['user_id']) ? $request['user_id'] : null;
		if (!$userId) {
			$chain->getFrom()->send(['msg' => "user_id not specified"]);
			return;
		}

		$list = NameChangeDAO::create()->getHistoryByUser($userId);

		$notify = (new UserCollection())->attach($chain->getFrom());
		$response = (new MessageResponse())
			->setChatId($chain->getFrom()->getChatId())
			->setMsg(MsgRaw::create($this->listFormatter($list)))
			->setGuests(null);

		$notify
			->setResponse($response)
			->notify(false);
	}

	private function listFormatter(array $list)
	{
		$html = '<table class="table table-striped">';

		/** @var $row NameChangeDAO */
		foreach ($list as $row) {
			$html .= '<tr>';
			$html .= '<td>'.$row->getDate().'</td>';
			$html .= '<td>'.$row->getName().'</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		return $html;
	}
}