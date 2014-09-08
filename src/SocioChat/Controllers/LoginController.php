<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\UserDAO;
use Core\DI;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use Core\Form\WrongRuleNameException;
use SocioChat\Message\MsgToken;
use SocioChat\OnOpenFilters\ResponseFilter;
use SocioChat\Response\MessageResponse;

class LoginController extends ControllerBase
{
	private $actionsMap = [
		'enter' => 'processLogin',
		'register' => 'processRegister'
	];

	public function handleRequest(ChainContainer $chain)
	{
		$action = $chain->getRequest()['action'];

		if (!isset($this->actionsMap[$action])) {
			RespondError::make($chain->getFrom());
			return;
		}

		$user = $chain->getFrom();
		$request = $chain->getRequest();

		try {
			$form = (new Form())
				->import($request)
				->addRule('login', Rules::email())
				->addRule('password', Rules::password());
		} catch (WrongRuleNameException $e) {
			RespondError::make($user, ['property' => 'Некорректно указано свойство']);
			return;
		}

		if (!$form->validate()) {
			RespondError::make($user, $form->getErrors());
			return;
		}

		$this->{$this->actionsMap[$action]}($chain);
	}

	protected function getFields()
	{
		return ['action', 'login', 'password'];
	}

	protected function processLogin(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$request = $chain->getRequest();
		$lang = $user->getLang();

		if (!$userDAO = $this->validateLogin($request)) {
			RespondError::make($user, ['email' => $lang->getPhrase('InvalidLogin')]);
			return;
		}

		$oldUserId = $user->getId();
		$oldChannelId = $user->getChannelId();

		$clients = UserCollection::get();

		if ($oldUserId == $userDAO->getId()) {
			RespondError::make($user, ['email' => $lang->getPhrase('AlreadyAuthorized')]);
			return;
		}

		if ($duplicatedUser = $clients->getClientById($userDAO->getId())) {
			$duplicatedUser
				->setAsyncDetach(false)
				->send(['msg' => $lang->getPhrase('DuplicateConnection'), 'disconnect' => 1]);
			Chat::get()->onClose($duplicatedUser->getConnection());
		}

		$userDAO->setChatId($oldChannelId);

		$user->setUserDAO($userDAO);
		$clients->updateKeyOfUserId($oldUserId);

		Chat::getSessionEngine()->updateSessionId($user, $oldUserId);
		DI::get()->getLogger()->info("LoginController::login success for ".$user->getId());
		$this->sendNotifyResponse($user);

		$responseFilter = new ResponseFilter();
		$responseFilter->sendNickname($user, $clients);
		$responseFilter->notifyChat($user, $clients);
	}

	protected function processRegister(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$request = $chain->getRequest();
		$email = $request['login'];

		$duplUser = UserDAO::create()->getByEmail($email);

		if ($duplUser->getId() && $duplUser->getId() != $user->getId()) {
			RespondError::make($user, ['email' => $user->getLang()->getPhrase('EmailAlreadyRegistered')]);
			return;
		}

		$userDAO = $user->getUserDAO();
		$userDAO
			->setEmail($email)
			->setPassword(password_hash($request['password'], PASSWORD_BCRYPT));
		$userDAO->save();

		$this->sendNotifyResponse($user);
	}

	private function validateLogin(array $request)
	{
		$email = $request['login'];
		$password = $request['password'];

		$user = UserDAO::create()->getByEmail($email);

		if (!$user->getId()) {
			return;
		}

		if (!password_verify($password, $user->getPassword())) {
			return;
		}

		return $user;
	}

	/**
	 * @param $user
	 */
	private function sendNotifyResponse(User $user)
	{
		$response = (new MessageResponse())
			->setChannelId($user->getChannelId())
			->setTime(null)
			->setMsg(MsgToken::create('ProfileUpdated'));
		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}
}