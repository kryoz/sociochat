<?php
namespace SocioChat\OnOpenFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Chat;
use SocioChat\ChatConfig;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Log;
use SocioChat\MightyLoop;
use SocioChat\Session\SessionHandler;
use SocioChat\Utils\Lang;

class SessionFilter implements ChainInterface
{
	protected $sessionHandler;

	public function __construct(SessionHandler $handler)
	{
		$this->sessionHandler = $handler;
	}

	public function handleRequest(ChainContainer $chain)
	{
		$newUserWrapper = $chain->getFrom();
		$newUserWrapper->setLastMsgId((float) $newUserWrapper->getWSRequest()->getCookie('lastMsgId'));
		$logger = Log::get()->fetch();
		$clients = UserCollection::get();
		$lang = Lang::get();

		$sessionHandler = $this->sessionHandler;
		$sessionHandler->clean(ChatConfig::get()->getConfig()->session->lifetime);

		if (!$token = $newUserWrapper->getWSRequest()->getCookie('PHPSESSID')) {
			$logger->error("Unauthorized session, dropped", [__CLASS__]);

			$newUserWrapper->send(['msg' => $lang->getPhrase('UnAuthSession')]);
			$newUserWrapper->close();
			return false;
		}

		if ($sessionInfo = $sessionHandler->read($token)) {
			$user = UserDAO::create()->getById($sessionInfo['user_id']);

			if ($oldClient = $clients->getClientById($user->getId())) {

				if ($timer = $oldClient->getTimer()) {
					MightyLoop::get()->fetch()->cancelTimer($timer);
					$logger->info("Deffered disconnection timer canceled: connection_id = {$newUserWrapper->getConnectionId()} for user_id = {$sessionInfo['user_id']}", [__CLASS__]);

					if ($oldClient->getConnectionId()) {
						$oldClient
							->setAsyncDetach(false)
							->send(['disconnect' => 1]);
						$clients->detach($oldClient);

						$newUserWrapper->setLastMsgId(-1);
					}
				} else {
					// если не было таймера, то
					// либо это нормальное возвращение пользователя
					// либо попытка открыть вторую вкладку
					// перезагрузка окна

					if ($oldClient->getConnectionId()) {
						$oldClient
							->setAsyncDetach(false)
							->send(['msg' => $lang->getPhrase('DuplicateConnection'), 'disconnect' => 1]);
						$clients->detach($oldClient);

						$newUserWrapper->setLastMsgId(-1);

						$logger->info("Probably tabs duplication detected: detaching = {$oldClient->getConnectionId()} for user_id = {$oldClient->getId()}", [__CLASS__]);
					}
				}

				if ($newUserWrapper->getLastMsgId() != 0) {
					$logger->info("Re-established connection_id = {$newUserWrapper->getConnectionId()} for user_id = {$sessionInfo['user_id']} lastMsgId = {$newUserWrapper->getLastMsgId()}", [__CLASS__]);
				}
			}
		} else {
			$user = UserDAO::create()
				->setChatId(1)
				->setDateRegister(date('Y-m-d H:i:s'));

			$user->save();

			$id = $user->getId();
			$guestName = 'Гость'.$id;

			if (PropertiesDAO::create()->getByUserName($guestName)->getName()) {
				$guestName = 'Гость '.$id;
			}

			$properties = $user->getPropeties();
			$properties
				->setUserId($user->getId())
				->setName($guestName)
				->setSex(SexEnum::create(SexEnum::ANONYM))
				->setTim(TimEnum::create(TimEnum::ANY))
				->setNotifications([]);

			try {
				$properties->save();
			} catch (\PDOException $e) {
				$logger->error("PDO Exception: ".print_r($e, 1), [__CLASS__]);
			}


			$logger->info("Created new user with id = $id for connectionId = {$newUserWrapper->getConnectionId()}", [__CLASS__]);
		}

		$newUserWrapper->setUserDAO($user);
		$sessionHandler->store($token, $user->getId());
		$clients->attach($newUserWrapper);
	}
}