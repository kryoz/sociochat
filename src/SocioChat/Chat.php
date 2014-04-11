<?php
namespace SocioChat;

use Guzzle\Http\Message\RequestInterface;
use Monolog\Logger;
use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\OnCloseFilters\DetachFilter;
use SocioChat\OnMessageFilters\ControllerFilter;
use SocioChat\OnMessageFilters\FloodFilter;
use SocioChat\OnMessageFilters\SessionFilter;
use SocioChat\OnOpenFilters\ResponseFilter;
use SocioChat\Response\ErrorResponse;
use SocioChat\Session\DBSessionHandler;
use SocioChat\Session\MemorySessionHandler;
use SocioChat\Session\SessionHandler;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface
{
	use TSingleton;

	/**
	 * @var SessionHandler
	 */
	private static $sessionEngine;

	public function __construct()
	{
		$config = ChatConfig::get()->getConfig();
		self::$sessionEngine = $config->session->driver == 'memory' ? MemorySessionHandler::get() : DBSessionHandler::get();
	}

	public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
	{
		Log::get()->fetch()->info("Opened new connectionId = {$conn->resourceId}", [__FUNCTION__]);

		(new ChainContainer())
			->setFrom(new User($conn))
			->addHandler(new OnOpenFilters\SessionFilter(self::$sessionEngine))
			->addHandler(new ResponseFilter())
			->run();
	}

	public function onClose(ConnectionInterface $conn)
	{
		(new ChainContainer())
			->setFrom(new User($conn))
			->addHandler(new DetachFilter())
			->run();
	}

	public function onMessage(ConnectionInterface $from, $jsonRequest)
	{
		if (!$user = UserCollection::get()->getClientByConnectionId($from->resourceId)) {
			Log::get()->fetch()->error("Got unopened or closed connectionId = {$from->resourceId}", [__FUNCTION__]);
			return;
		}

		if (!$request = json_decode($jsonRequest, 1)) {
			return $this->notifyOnMalformedJSON($user, $jsonRequest);
		}

		(new ChainContainer())
			->setFrom($user)
			->setRequest($request)
			->addHandler(new SessionFilter())
			->addHandler(FloodFilter::get())
			->addHandler(new ControllerFilter())
			->run();
	}

	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		Log::get()->fetch()->error("An error has occurred: {$e->getMessage()}", [__FUNCTION__]);
		$conn->close();
	}

	public static function getSessionEngine()
	{
		return self::$sessionEngine;
	}

	/**
	 * @param User $from
	 * @param $msg
	 */
	private function notifyOnMalformedJSON(User $from, $msg)
	{
		if (!$msg) {
			return;
		}

		$message = (new ErrorResponse())
			->setErrors(['Ай-ай-ай! Нехорошка! :D'])
			->setChatId($from->getChatId());

		(new UserCollection())
			->attach($from)
			->setResponse($message)
			->notify();
	}
}
