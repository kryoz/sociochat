<?php
namespace SocioChat\OnOpenFilters;

use SocioChat\DI;
use Monolog\Logger;
use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Enum\UserRoleEnum;
use SocioChat\Message\Lang;

class SessionFilter implements ChainInterface
{

	public function handleRequest(ChainContainer $chain)
	{
		$newUserWrapper = $chain->getFrom();
		$container = DI::get()->container();

		$logger = $container->get('logger');
		/* @var $logger Logger */
		$clients = DI::get()->getUsers();
		$socketRequest = $newUserWrapper->getWSRequest();
		/* @var $socketRequest \Guzzle\Http\Message\Request */

		$langCode = $socketRequest->getCookie('lang') ?: 'ru';
		$lang = $container->get('lang')->setLangByCode($langCode);
		/* @var $lang Lang */
		$newUserWrapper
			->setIp($socketRequest->getHeader('X-Real-IP'))
			->setLastMsgId((int) $socketRequest->getCookie('lastMsgId'))
			->setLanguage($lang);

		$sessionHandler = DI::get()->getSession();

		$logger->info("Incoming connection IP = {$newUserWrapper->getIp()}, token = {$socketRequest->getCookie('token')}, lastMsgId = {$newUserWrapper->getLastMsgId()}", [__CLASS__]);

		try {
			if (!$token = $socketRequest->getCookie('token')) {
				throw new InvalidSessionException('No token');
			}

			/** @var SessionDAO $sessionInfo */
			$sessionInfo = $sessionHandler->read($token);
			if (!$sessionInfo) {
				throw new InvalidSessionException('Wrong token '.$token);
			}

		} catch (InvalidSessionException $e) {
			$logger->error(
				"Unauthorized session {$newUserWrapper->getIp()}; ".$e->getMessage(),
				[__CLASS__]
			);

			$newUserWrapper->send(['msg' => $lang->getPhrase('UnAuthSession'), 'refreshToken' => 1]);
			$newUserWrapper->close();
			return false;
		}


		if ($sessionInfo->getUserId() != 0) {
			$user = $this->handleKnownUser($sessionInfo, $clients, $logger, $newUserWrapper, $lang);
			$logger->info('Handled known user_id = '.$user->getId());
		} else {
			$user = UserDAO::create()
				->setChatId(1)
				->setDateRegister(date('Y-m-d H:i:s'))
				->setMessagesCount(0)
				->setRole(UserRoleEnum::USER);

			$user->save();

			$id = $user->getId();
			$guestName = $lang->getPhrase('Guest').$id;

			if (PropertiesDAO::create()->getByUserName($guestName)->getName()) {
				$guestName = $lang->getPhrase('Guest').' '.$id;
			}

			$properties = $user->getPropeties();
			$properties
				->setUserId($user->getId())
				->setName($guestName)
				->setSex(SexEnum::create(SexEnum::ANONYM))
				->setTim(TimEnum::create(TimEnum::ANY))
				->setOptions([PropertiesDAO::CENSOR => true]);

			try {
				$properties->save();
			} catch (\PDOException $e) {
				$logger->error("PDO Exception: ".print_r($e, 1), [__CLASS__]);
			}

			$sessionHandler->store($token, $user->getId());
			$logger->info("Created new user with id = $id for connectionId = {$newUserWrapper->getConnectionId()}", [__CLASS__]);
		}

		$newUserWrapper
			->setUserDAO($user)
			->setToken($token);

		$clients->attach($newUserWrapper);
	}

	/**
	 * @param $sessionInfo
	 * @param $clients
	 * @param $logger
	 * @param $newUserWrapper
	 * @return $this
	 */
	private function handleKnownUser($sessionInfo, UserCollection $clients, Logger $logger, User $newUserWrapper)
	{
		$user = UserDAO::create()->getById($sessionInfo['user_id']);
		$lang = $newUserWrapper->getLang();

		if ($oldClient = $clients->getClientById($user->getId())) {

			if ($timer = $oldClient->getDisconnectTimer()) {
				DI::get()->container()->get('eventloop')->cancelTimer($timer);
				$logger->info(
					"Deffered disconnection timer canceled: connection_id = {$newUserWrapper->getConnectionId(
					)} for user_id = {$sessionInfo['user_id']}",
					[__CLASS__]
				);

				if ($oldClient->getConnectionId()) {
					$oldClient
						->setAsyncDetach(false)
						->send(['disconnect' => 1]);
					$clients->detach($oldClient);

					$newUserWrapper->setLastMsgId(-1);
				}
			} elseif ($oldClient->getConnectionId()) {
				// If there is no timer set, then
				// 1) it's regular user visit
				// 2) an attempt to open another browser tab
				// 3) window reload

				$oldClient
					->setAsyncDetach(false)
					->send(['msg' => $lang->getPhrase('DuplicateConnection'), 'disconnect' => 1]);
				$clients->detach($oldClient);

				if ($oldClient->getIp() == $newUserWrapper->getIp()) {
					$newUserWrapper->setLastMsgId(-1);
				}

				$logger->info(
					"Probably tabs duplication detected: detaching = {$oldClient->getConnectionId()} for user_id = {$oldClient->getId()}}",
					[__CLASS__]
				);
			}

			if ($newUserWrapper->getLastMsgId()) {
				$logger->info(
					"Re-established connection for user_id = {$sessionInfo['user_id']}, lastMsgId = {$newUserWrapper->getLastMsgId()}",
					[__CLASS__]
				);
			}
		}
		return $user;
	}
}