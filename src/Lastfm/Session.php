<?php

namespace Lastfm;

/**
 * Represents a Last.fm Web Service Session
 *
 * For more informations, visit @link http://www.lastfm.fr/api/authentication
 *
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Session
{
    private $username;
    private $key;

    /**
     * Constructor
     *
     * @param  string $username
     * @param  string $key
     */
    public function __construct($username = null, $key = null)
    {
        $this->setUsername($username);
        $this->setKey($key);
    }

    /**
     * Defines the username
     *
     * @param  string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Defines the key
     *
     * @param  string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Returns the key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
