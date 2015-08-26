<?php

namespace SocioChat\Application\Chain;

use SocioChat\Clients\User;

class ChainContainer
{
    /**
     * @var ChainInterface[]
     */
    protected $handlers = [];
    /**
     * @var User
     */
    protected $from;
    protected $request;

    public function addHandler(ChainInterface $handler)
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function run()
    {
        foreach ($this->handlers as $handler) {
            if ($handler->handleRequest($this) === false) {
                break;
            }
        }
    }

    /**
     * @param User $from
     * @return $this
     */
    public function setFrom(User $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return User
     */
    public function getFrom()
    {
        return $this->from;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function &getRequest()
    {
        return $this->request;
    }
}
