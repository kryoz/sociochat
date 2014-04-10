<?php
namespace SocioChat;

use Guzzle\Http\Message\RequestInterface;
use Monolog\Logger;
use MyApp\Chain\ChainContainer;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\OnCloseFilters\DetachFilter;
use MyApp\OnMessageFilters\ControllerFilter;
use MyApp\OnMessageFilters\FloodFilter;
use MyApp\OnMessageFilters\SessionFilter;
use MyApp\OnOpenFilters\ResponseFilter;
use MyApp\Response\ErrorResponse;
use MyApp\Session\DBSessionHandler;
use MyApp\Session\MemorySessionHandler;
use MyApp\Session\SessionHandler;
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
