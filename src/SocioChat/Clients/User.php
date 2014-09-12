<?php

namespace SocioChat\Clients;

use Ratchet\ConnectionInterface;
use React\EventLoop\Timer\TimerInterface;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserBlacklistDAO;
use SocioChat\DAO\UserDAO;
use Core\DI;
use SocioChat\Enum\UserRoleEnum;
use SocioChat\Message\Lang;
use SocioChat\Response\Response;

class User implements ConnectionInterface
{
	/**
	 * @var \Ratchet\ConnectionInterface
	 */
	private $connection;

	/**
	 * @var TimerInterface
	 */
	private $timer;
	private $asyncDetach = true;
	private $lastMsgId = 0;

	/**
	 * @var UserDAO
	 */
	private $userDAO;
	/**
	 * @var Lang
	 */
	private $language;
	private $ip;
	private $token;

	public function __construct(ConnectionInterface $client)
	{
		$this->connection = $client;
		ChannelsCollection::get()->createChannel(1);
	}

	public function send($data)
	{
		$this->connection->send(json_encode($data));
		return $this;
	}

	public function close()
	{
		$this->connection->close();
	}

	public function update(Response $response)
	{
		$response->setRecipient($this);
		$this->connection->send($response->toString());
	}

	public function getUserDAO()
	{
		return $this->userDAO;
	}

	public function setUserDAO(UserDAO $user)
	{
		$this->userDAO = $user;
		return $this;
	}

	public function getConnectionId()
	{
		return $this->connection->resourceId;
	}

	public function &getWSRequest()
	{
		return $this->connection->WebSocket->request;
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public function setConnection(ConnectionInterface $conn)
	{
		$this->connection = $conn;
	}

	/**
	 * @param string $chatId
	 * @return $this
	 */
	public function setChannelId($chatId)
	{
		$this->userDAO->setChatId($chatId);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getChannelId()
	{
		return $this->userDAO->getChatId();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->userDAO->getId();
	}

	/**
	 * @return PropertiesDAO
	 */
	public function getProperties()
	{
		return $this->userDAO->getPropeties();
	}

	/**
	 * @return UserBlacklistDAO
	 */
	public function getBlacklist()
	{
		return $this->userDAO->getBlacklist();
	}

	/**
	 * @param \React\EventLoop\Timer\TimerInterface $timer
	 */
	public function setDisconnectTimer(TimerInterface $timer)
	{
		$this->timer = $timer;
	}

	/**
	 * @return \React\EventLoop\Timer\TimerInterface
	 */
	public function getDisconnectTimer()
	{
		return $this->timer;
	}

	/**
	 * @param $async
	 * @return $this
	 */
	public function setAsyncDetach($async)
	{
		$this->asyncDetach = $async;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isAsyncDetach()
	{
		return $this->asyncDetach;
	}

	/**
	 * @param $lastMsgId
	 * @return $this
	 */
	public function setLastMsgId($lastMsgId)
	{
		$this->lastMsgId = $lastMsgId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLastMsgId()
	{
		return $this->lastMsgId;
	}

	public function isInPrivateChat()
	{
		return $this->getChannelId()[0] == '_';
	}

	/**
	 * @param Lang $language
	 * @return $this
	 */
	public function setLanguage(Lang $language)
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * @return Lang
	 */
	public function getLang()
	{
		return $this->language;
	}


	public function setIp($ip)
	{
		$this->ip = $ip;
		return $this;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function isRegistered()
	{
		return (bool) $this->getUserDAO()->getEmail();
	}

	/**
	 * @return mixed
	 */
	public function getMessagesCount()
	{
		return $this->getUserDAO()->getMessagesCount();
	}

	/**
	 * @internal param mixed $messagesCount
	 * @return $this
	 */
	public function incMessagesCount()
	{
		$this->getUserDAO()->setMessagesCount($this->getMessagesCount() + 1);
		return $this;
	}

	/**
	 * @return UserRoleEnum
	 */
	public function getRole()
	{
		return $this->getUserDAO()->getRole();
	}

	/**
	 * @param mixed $role
	 * @return $this
	 */
	public function setRole(UserRoleEnum $role)
	{
		$this->getUserDAO()->setRole($role->getId());
		return $this;
	}

	public function save($fullSave = true)
	{
		try {
			if ($fullSave) {
				$properties = $this->getProperties();
				$properties->save();

				$blacklist = $this->getBlacklist();
				$blacklist->save();
			}

			$this->userDAO->save();
		} catch (\PDOException $e) {
			DI::get()->getLogger()->warn("User onSave PDO Exception: ".print_r($e, 1), [__CLASS__]);
		}
	}

	/**
	 * @return mixed
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @param mixed $token
	 * @return $this
	 */
	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}
}