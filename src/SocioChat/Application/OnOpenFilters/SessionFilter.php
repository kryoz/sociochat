<?php
namespace SocioChat\Application\OnOpenFilters;

use Core\Utils\DbQueryHelper;
use Guzzle\Http\Message\Request;
use SocioChat\Application\Chat;
use SocioChat\DAO\ReferralDAO;
use SocioChat\DAO\TmpSessionDAO;
use SocioChat\DI;
use Monolog\Logger;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Enum\UserRoleEnum;
use SocioChat\Forms\Rules;
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
        /* @var $socketRequest Request */

        $langCode = $socketRequest->getCookie('lang') ?: 'ru';
        $lang = $container->get('lang')->setLangByCode($langCode);
        /* @var $lang Lang */
        $newUserWrapper
            ->setIp($socketRequest->getHeader('X-Real-IP'))
            ->setLastMsgId((int)$socketRequest->getCookie('lastMsgId'))
            ->setLanguage($lang);

        $imprint = $socketRequest->getCookie('token2');

        $sessionHandler = DI::get()->getSession();

        $logger->info(
            "New connection:
            IP = {$newUserWrapper->getIp()},
            token = {$socketRequest->getCookie('token')},
            token2 = {$imprint},
            lastMsgId = {$newUserWrapper->getLastMsgId()}",
            [__CLASS__]
        );

        try {
            if (!$token = $socketRequest->getCookie('token')) {
                throw new InvalidSessionException('No token');
            }

            /** @var SessionDAO $session */
            $session = $sessionHandler->read($token);
            if (!$session) {
                $tmpSession = TmpSessionDAO::create()->getBySessionId($token);
                if (!$tmpSession->getId()) {
                    throw new InvalidSessionException('Wrong token ' . $token);
                }
	            $tmpSession->dropById($tmpSession->getId());
                $session = SessionDAO::create()->setSessionId($token);
            }

        } catch (InvalidSessionException $e) {
            $logger->error(
                "Unauthorized session {$newUserWrapper->getIp()}; " . $e->getMessage(),
                [__CLASS__]
            );

            $newUserWrapper->send(['msg' => $lang->getPhrase('UnAuthSession'), 'refreshToken' => 1]);
            $newUserWrapper->close();
            return false;
        }

        if ($session->getUserId() != 0) {
            $user = $this->handleKnownUser($session, $clients, $logger, $newUserWrapper);
            $logger->info('Handled known user_id = ' . $user->getId());
        } else {
	        $user = $this->createNewUser($lang, $logger, $newUserWrapper, $socketRequest);
        }

        //update access time
        $sessionHandler->store($token, $user->getId());

        if ($imprint) {
            $logger->info('Searching similar imprint '.$imprint.' for user ' . $user->getId());
            $user->setImprint($imprint);
            $similarUser = UserDAO::create()->getByImprint($imprint);
            if (count($similarUser)) {
                /** @var UserDAO $similarUser */
                $similarUser = $similarUser[0];
                if ($similarUser->getId() && $similarUser->getId() != $user->getId()) {
                    $logger->info('Found banned user '.$similarUser->getId().', banning also '.$user->getId());
                    $user->setBanned(true);
                }
            }

            $user->save(false);
        }

        if ($user->isBanned()) {
            $logger->info('Dropping banned user '.$user->getId());
            $newUserWrapper->send(['msg' => 'Banned!', 'disconnect' => 1]);
            return false;
        }

        $newUserWrapper
            ->setUserDAO($user)
            ->setToken($token)
            ->setLoginTime(time());

        $clients->attach($newUserWrapper);
    }

	/**
	 * @param $sessionInfo
	 * @param UserCollection $clients
	 * @param Logger $logger
	 * @param User $newUserWrapper
	 * @return UserDAO
	 */
    private function handleKnownUser($sessionInfo, UserCollection $clients, Logger $logger, User $newUserWrapper)
    {
        $user = UserDAO::create()->getById($sessionInfo['user_id']);
        $lang = $newUserWrapper->getLang();

        if ($oldClient = $clients->getClientById($user->getId())) {

            if ($timer = $oldClient->getDisconnectTimer()) {
                DI::get()->container()->get('eventloop')->cancelTimer($timer);
                $logger->info(
                    "Deffered disconnection timer canceled: connection_id = {$newUserWrapper->getConnectionId()} for user_id = {$sessionInfo['user_id']}",
                    [__METHOD__]
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
                    [__METHOD__]
                );
            }

            if ($newUserWrapper->getLastMsgId()) {
                $logger->info(
                    "Re-established connection for user_id = {$sessionInfo['user_id']}, lastMsgId = {$newUserWrapper->getLastMsgId()}",
                    [__METHOD__]
                );
            }
        }
        return $user;
    }

	/**
	 * @param Lang $lang
	 * @param Logger $logger
	 * @param User $newUserWrapper
	 * @param Request $socketRequest
	 * @return UserDAO
	 */
	private function createNewUser(Lang $lang, Logger $logger, User $newUserWrapper, Request $socketRequest)
	{
		$user = UserDAO::create()
			->setChatId(1)
			->setDateRegister(DbQueryHelper::timestamp2date())
			->setRole(UserRoleEnum::USER)
            ->setBanned(false)
            ->setImprint(null)
        ;

		try {
			$user->save();
		} catch (\PDOException $e) {
			$logger->error("PDO Exception: " . $e->getMessage().': '.$e->getTraceAsString(), [__METHOD__]);
		}

		$id = $user->getId();
		$guestName = $lang->getPhrase('Guest') . $id;

		if (PropertiesDAO::create()->getByUserName($guestName)->getName()) {
			$guestName = $lang->getPhrase('Guest') . ' ' . $id;
		}

		$properties = $user->getPropeties();
		$properties
			->setUserId($user->getId())
			->setName($guestName)
			->setSex(SexEnum::create(SexEnum::ANONYM))
			->setTim(TimEnum::create(TimEnum::ANY))
			->setBirthday(Rules::LOWEST_YEAR)
			->setOptions([PropertiesDAO::CENSOR => true])
			->setOnlineCount(0)
			->setMusicCount(0)
			->setWordsCount(0)
			->setRudeCount(0)
			->setKarma(0)
			->setMessagesCount(0)
			->setSubscription(true);

		try {
			$properties->save();
		} catch (\PDOException $e) {
			$logger->error("PDO Exception: " . $e->getTraceAsString(), [__CLASS__]);
		}

		if ($refUserId = $socketRequest->getCookie('refUserId')) {
			$ref = ReferralDAO::create()->getByUserId($user->getId(), $refUserId);
			if (!$ref) {
				$ref = ReferralDAO::create()
					->setUserId($user->getId())
					->setRefUserId($refUserId)
					->setDateRegister(DbQueryHelper::timestamp2date());
				$ref->save();

				$logger->info('Found referral userId '.$refUserId.' for guest userId '.$user->getId());
			}
		}

		$logger->info(
			"Created new user with id = $id for connectionId = {$newUserWrapper->getConnectionId()}",
			[__CLASS__]
		);
		return $user;
	}
}
