<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\Message\MsgRaw;
use SocioChat\Response\MessageResponse;

class AdminController extends ControllerBase
{
	private $actionsMap = [
		'kickUser' => 'processKick',
		'nameLog' => 'processNameChangeHistory',
		'getIp' => 'processGetIp'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();

		if ($user->getRole()->isAdmin() || $user->getRole()->isCreator()) {
			return;
		}

		$action = $chain->getRequest()['action'];

		if (!isset($this->actionsMap[$action])) {
			RespondError::make($chain->getFrom());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action', 'userId'];
	}

	protected function processKick(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$assHoleId = $request['userId'];
		$users = UserCollection::get();

		if (!$assHole = $users->getClientById($assHoleId)) {
			RespondError::make($chain->getFrom(), ['userId' => "userId = $assHoleId not found"]);
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

	protected function processGetIp(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$userId = $request['userId'];
		$users = UserCollection::get();

		if (!$user = $users->getClientById($userId)) {
			RespondError::make($chain->getFrom(), ['userId' => "userId = $userId not found"]);
			return;
		}

		$notify = (new UserCollection())->attach($chain->getFrom());
		$response = (new MessageResponse())
			->setChannelId($chain->getFrom()->getChannelId())
			->setMsg(MsgRaw::create($user->getProperties()->getName().' = '.$user->getIp()))
			->setGuests(null);

		$notify
			->setResponse($response)
			->notify(false);
	}

	protected function processNameChangeHistory(ChainContainer $chain)
	{
		$request = $chain->getRequest();
		$userId = $request['userId'];

		$list = NameChangeDAO::create()->getHistoryByUserId($userId);

		if (empty($list)) {
			RespondError::make($chain->getFrom(), ['userId' => "userId = $userId not found"]);
			return;
		}
		$notify = (new UserCollection())->attach($chain->getFrom());
		$response = (new MessageResponse())
			->setChannelId($chain->getFrom()->getChannelId())
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