<?php
namespace SocioChat\Controllers;

use Core\Utils\DbQueryHelper;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chat;
use SocioChat\Application\OnOpenFilters\ResponseFilter;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\OnlineDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\ReferralDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DAO\UserKarmaDAO;
use SocioChat\DI;
use Core\Form\Form;
use SocioChat\Forms\Rules;
use Core\Form\WrongRuleNameException;
use SocioChat\Message\Msg;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;
use SocioChat\Response\UserPropetiesResponse;

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
                ->addRule('login', Rules::email(), 'Некорректный формат email')
                ->addRule('password', Rules::password(), 'Пароль должен быть от 8 до 20 символов');
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
        $logger = DI::get()->getLogger();

        if (!$userDAO = $this->validateLogin($request)) {
            RespondError::make($user, ['email' => $lang->getPhrase('InvalidLogin')]);
            return;
        }

        $oldUserId = $user->getId();
        $oldChannelId = $user->getChannelId();

        $clients = DI::get()->getUsers();

        if ($oldUserId == $userDAO->getId()) {
            RespondError::make($user, ['email' => $lang->getPhrase('AlreadyAuthorized')]);
            return;
        }

        if ($duplicatedUser = $clients->getClientById($userDAO->getId())) {
            if ($timer = $duplicatedUser->getDisconnectTimer()) {
                DI::get()->container()->get('eventloop')->cancelTimer($timer);
                $logger->info(
                    "Deffered disconnection timer canceled: "
                    ."connection_id = {$duplicatedUser->getConnectionId()} for userId = {$duplicatedUser->getId()}"
                );
            }

            $duplicatedUser
                ->setAsyncDetach(false)
                ->send(['msg' => $lang->getPhrase('DuplicateConnection'), 'disconnect' => 1]);
            Chat::get()->onClose($duplicatedUser->getConnection());
        }

        $userDAO->setChatId($oldChannelId);
        $user->setUserDAO($userDAO);
        $clients->updateKeyOfUserId($oldUserId);

        DI::get()->getSession()->updateSessionId($user, $oldUserId);
        $logger->info("LoginController::login success for " . $user->getId());

        $this->sendNotifyResponse($user);

        $responseFilter = new ResponseFilter();
        $responseFilter->sendNickname($user, $clients);
        $responseFilter->notifyChat($user, $clients);

	    $onlineList = OnlineDAO::create();
	    $onlineList->updateUserId($oldUserId, $userDAO->getId());
    }

    protected function processRegister(ChainContainer $chain)
    {
        $user = $chain->getFrom();
        $request = $chain->getRequest();
        $email = $request['login'];

        $duplUser = UserDAO::create()->getByEmail($email);
		$isSameUser = $duplUser->getId() == $user->getId();

        if ($duplUser->getId() && !$isSameUser) {
            RespondError::make($user, ['email' => $user->getLang()->getPhrase('EmailAlreadyRegistered')]);
            return;
        }

	    if (!$user->isRegistered()) {
		    $this->checkReferral($user);
	    }

        $userDAO = $user->getUserDAO();
        $userDAO
            ->setEmail($email)
            ->setPassword(password_hash($request['password'], PASSWORD_BCRYPT));
        $userDAO->save();

        $this->sendNotifyResponse($user);
    }

	/**
	 * @param array $request
	 * @return UserDAO|null
	 */
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

	    $response = (new UserPropetiesResponse())
		    ->setUserProps($user)
		    ->setChannelId($user->getChannelId());

	    (new UserCollection())
		    ->attach($user)
		    ->setResponse($response)
		    ->notify(false);
    }

	private function checkReferral(User $user)
	{
		$ref = ReferralDAO::create()->getFirstRefByUserId($user->getId());
		if (!$ref) {
			return;
		}

		$users = DI::get()->getUsers();

		if ($refUserOnline = $users->getClientById($ref->getRefUserId())) {
			$refUser = $refUserOnline->getUserDAO();
		} else {
			$refUser = UserDAO::create()->getById($ref->getRefUserId());
		}

		if (!$refUser->getId()) {
			return;
		}

		$mark = UserKarmaDAO::create()
			->setUserId($refUser->getId())
			->setEvaluator($user)
			->setMark(5)
			->setDateRegister(DbQueryHelper::timestamp2date());
		$mark->save();

		$props = $refUser->getPropeties();
		$props->setKarma($props->getKarma()+5);

		if ($refUserOnline) {
			$refUserOnline->save();

			$response = (new MessageResponse())
				->setGuests($users->getUsersByChatId($refUserOnline->getChannelId()))
				->setChannelId($refUserOnline->getChannelId())
				->setTime(null);

			$users
				->setResponse($response)
				->notify();

			$response = (new MessageResponse())
				->setMsg(MsgToken::create('profile.referralKarma'))
				->setChannelId($refUserOnline->getChannelId())
				->setTime(null);

			(new UserCollection)
				->attach($refUserOnline)
				->setResponse($response)
				->notify(false);
		} else {
			$props->save(false);
		}

		DI::get()->getLogger()->info('Added karma to referral userId '.$props->getUserId());
	}
}
