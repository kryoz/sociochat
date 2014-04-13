<?php
namespace SocioChat\Controllers;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chat;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\UserDAO;
use SocioChat\Forms\Form;
use SocioChat\Forms\Rules;
use SocioChat\Forms\WrongRuleNameException;
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
			$this->errorResponse($chain->getFrom());
			return;
		}

		$user = $chain->getFrom();
		$request = $chain->getRequest();

		try {
			$form = (new Form())
				->import($request)
				->addRule('login', Rules::email(), 'Некорректный формат email')
				->addRule('password', Rules::password(), 'Пароль должен быть от 8 до 20 символов');
		} catch (WrongRuleNameException $e) {
			$this->errorResponse($user, ['property' => 'Некорректно указано свойство']);
			return;
		}

		if (!$form->validate()) {
			$this->errorResponse($user, $form->getErrors());
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
			$this->errorResponse($user, ['email' => $lang->getPhrase('InvalidLogin')]);
			return;
		}

		$oldUserId = $user->getId();
		$clients = UserCollection::get();

		if ($oldUserId == $userDAO->getId()) {
			$this->errorResponse($user, ['email' => $lang->getPhrase('AlreadyAuthorized')]);
			return;
		}

		if ($duplicatedUser = $clients->getClientById($userDAO->getId())) {
			$duplicatedUser
				->setAsyncDetach(false)
				->send(['msg' => $lang->getPhrase('DuplicateConnection'), 'disconnect' => 1]);
			Chat::get()->onClose($duplicatedUser->getConnection());
		}

		$user->setUserDAO($userDAO);
		Chat::getSessionEngine()->updateSessionId($user, $oldUserId);

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
			$this->errorResponse($user, ['email' => $user->getLang()->getPhrase('EmailAlreadyRegistered')]);
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
			->setChatId($user->getChatId())
			->setTime(null)
			->setMsg($user->getLang()->getPhrase('ProfileUpdated'));
		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}
}