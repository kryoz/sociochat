<?php

namespace SocioChat\Message\Filters;

use SocioChat\Clients\User;

class Chain
{
    /**
     * @var ChainInterface[]
     */
    protected $handlers = [];

    protected $request;
    /**
     * @var User
     */
    protected $user;

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

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param User $from
     * @return $this
     */
    public function setUser(User $from)
    {
        $this->user = $from;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
