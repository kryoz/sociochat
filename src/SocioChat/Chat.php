<?php
namespace SocioChat;

use Guzzle\Http\Message\RequestInterface;
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
		if (!$jsonRequest) {
			return;
		}

		if (!$user = UserCollection::get()->getClientByConnectionId($from->resourceId)) {
			Log::get()->fetch()->error("Got request from unopened or closed connectionId = {$from->resourceId}", [__FUNCTION__]);
			return;
		}

		if (!$request = json_decode($jsonRequest, 1)) {
			return $this->respondOnMalformedJSON($user);
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
	 */
	private function respondOnMalformedJSON(User $from)
	{
		$response = (new ErrorResponse())
			->setErrors(['request' => Lang::get()->getPhrase('MalformedJsonRequest')])
			->setChatId($from->getChatId());

		(new UserCollection())
			->attach($from)
			->setResponse($response)
			->notify();
	}
}
