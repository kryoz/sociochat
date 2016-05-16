<?php
namespace SocioChat\Application;

use Core\TSingleton;
use Guzzle\Http\Message\RequestInterface;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Application\OnCloseFilters\DetachFilter;
use SocioChat\Application\OnCloseFilters\UserFetchFilter;
use SocioChat\Application\OnMessageFilters\ControllerFilter;
use SocioChat\Application\OnMessageFilters\FloodFilter;
use SocioChat\Application\OnMessageFilters\SessionFilter;
use SocioChat\DI;
use SocioChat\Application\OnOpenFilters\ResponseFilter;
use SocioChat\Response\ErrorResponse;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface
{
    use TSingleton;

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        /** @var RequestInterface $request */
        $request = $conn->WebSocket->request;
        $header = (string) $request->getHeader('Origin');
        $origin = parse_url($header, PHP_URL_HOST) ?: $header;
        $config = DI::get()->getConfig();

        if ($origin !== $config->domain->web) {
            $conn->send('Not allowed origin!');
            $conn->close();
            return;
        }

        (new ChainContainer())
            ->setFrom(new User($conn))
            ->addHandler(new OnOpenFilters\SessionFilter())
            ->addHandler(new ResponseFilter())
            //->addHandler(AdsFilter::get())
            ->run();
    }

    public function onClose(ConnectionInterface $conn)
    {
        (new ChainContainer())
            ->setFrom(new User($conn))
            ->addHandler(new UserFetchFilter())
            //->addHandler(new \SocioChat\OnCloseFilters\AdsFilter())
            ->addHandler(new DetachFilter())
            ->run();
    }

    public function onMessage(ConnectionInterface $from, $jsonRequest)
    {
        if (!$jsonRequest) {
            return;
        }

        if (!$user = DI::get()->getUsers()->getClientByConnectionId($from->resourceId)) {
            DI::get()->getLogger()->error(
                "Got request from unopened or closed connectionId = {$from->resourceId}",
                [__FUNCTION__]
            );
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
            //->addHandler(AdsFilter::get())
            ->addHandler(new ControllerFilter())
            ->run();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        DI::get()->getLogger()->error(
            "An error has occurred: {$e->getMessage()}:\n{$e->getTraceAsString()}",
            [__FUNCTION__]
        );
        $conn->close();
    }

    /**
     * @param User $from
     */
    private function respondOnMalformedJSON(User $from)
    {
        $response = (new ErrorResponse())
            ->setErrors(['request' => $from->getLang()->getPhrase('MalformedJsonRequest')])
            ->setChannelId($from->getChannelId());

        (new UserCollection())
            ->attach($from)
            ->setResponse($response)
            ->notify();
    }
}
