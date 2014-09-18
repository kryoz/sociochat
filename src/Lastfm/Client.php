<?php

namespace Lastfm;

/**
 * The Last.fm client
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class Client
{
    const STATUS_SUCCESS = 'ok';
    const STATUS_ERROR   = 'error';

    private $apiKey;
    private $secret;
    private $session;
    private $transport;

    private $services;

    /**
     * Constructor
     *
     * @param  string    $apiKey    Your API key
     * @param  string    $secret    Your secret
     * @param  Session   $session   A Session instance
     * @param  Transport $transport A Transport instance
     */
    public function __construct($apiKey = null, $secret = null, Session $session = null, Transport $transport = null)
    {
        $this->setApiKey($apiKey);
        $this->setSecret($secret);
        $this->setSession($session);

        if (null === $transport) {
            $transport = new Transport\Curl();
        }

        $this->setTransport($transport);
    }

    /**
     * Defines the api key
     *
     * @param  string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Returns the api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Defines the secret
     *
     * @param  string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Return the secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Defines the session
     *
     * @param  Session $session
     */
    public function setSession(Session $session = null)
    {
        $this->session = $session;
    }

    /**
     * Returns the session
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Defines the underlying transport
     *
     * @param  Transport $transport
     */
    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Returns the underlying transport
     *
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Shortcut method to perform a GET request
     *
     * @param  string  $apiMethod
     * @param  array   $parameters
     * @param  boolean $addSignature
     * @param  boolean $addSession
     * @param  boolean $raw
     *
     * @return mixed
     */
    public function get($apiMethod, array $parameters = array(), $addSignature = false, $addSession = false, $raw = false)
    {
        return $this->request(Transport::HTTP_METHOD_GET, $apiMethod, $parameters, $addSignature, $addSession, $raw);
    }

    /**
     * Shortcut method to perform a POST request
     *
     * @param  string  $apiMethod
     * @param  array   $parameters
     * @param  boolean $addSignature
     * @param  boolean $addSession
     * @param  boolean $raw
     *
     * @return mixed
     */
    public function post($apiMethod, array $parameters = array(), $addSignature = false, $addSession = false, $raw = false)
    {
        return $this->request(Transport::HTTP_METHOD_POST, $apiMethod, $parameters, $addSignature, $addSession, $raw);
    }

    /**
     * Performs an API request and returns the result
     *
     * @param  string  $httpMethod   The HTTP method (one of the Transport::HTTP_METHOD_* constants)
     * @param  string  $apiMethod    The API method
     * @param  array   $parameters   An array of parameters
     * @param  boolean $addSignature Whether to add a method signature to the request
     * @param  boolean $addSession   Whether to add the session to the request
     * @param  boolean $raw          Whether to return the raw result
     *
     * @return mixed
     */
    public function request($httpMethod, $apiMethod, array $parameters = array(), $addSignature = false, $addSession = false, $raw = false)
    {
        if (null !== $this->apiKey) {
            $parameters['api_key'] = $this->apiKey;
        }

        if (!$raw) {
            $parameters['format'] = 'json';
        }

        if ($addSession) {
            if (null === $this->session) {
                throw new \LogicException('You must define the session prior to add it to a request.');
            }
            $parameters['sk'] = $this->session->getKey();
        }

        if ($addSignature) {
            $parameters['api_sig'] = $this->createMethodSignature(array_merge($parameters, array('method' => $apiMethod)));
        }

        $rawResult = $this->transport->request($httpMethod, $apiMethod, $parameters);

        if ($raw) {
            return $rawResult;
        }

        $result = json_decode($rawResult, true);

        if (!is_array($result)) {
            throw new Exception\InvalidResponse($rawResult);
        }

        if (isset($result['error'])) {
            if (isset($result['message'])) {
                $message = sprintf('Api error (%d): %s', $result['error'], $result['message']);
            } else {
                $message = sprintf('Api error (%d) with no message.', $result['error']);
            }

            throw new Exception\ErrorResponse($message, $result['error']);
        }

        if (1 === count($result)) {
            $result = reset($result);
        }

        // sometimes, when a collection is empty, you get a string
        if (is_string($result)) {
            $result = array();
        }

        return 1 === count($result) ? reset($result) : $result;
    }

    /**
     * Returns an album service instance
     *
     * @return \Lastfm\Service\Album
     */
    public function getAlbumService()
    {
        return $this->getService('album');
    }

    /**
     * Returns an artist service instance
     *
     * @return \Lastfm\Service\Artist
     */
    public function getArtistService()
    {
        return $this->getService('artist');
    }

    /**
     * Returns an auth service instance
     *
     * @return \Lastfm\Service\Auth
     */
    public function getAuthService()
    {
        return $this->getService('auth');
    }

    /**
     * Returns a chart service instance
     *
     * @return \Lastfm\Service\Chart
     */
    public function getChartService()
    {
        return $this->getService('chart');
    }

    /**
     * Returns an event service instance
     *
     * @return \Lastfm\Service\Event
     */
    public function getEventService()
    {
        return $this->getService('event');
    }

    /**
     * Returns a geo service instance
     *
     * @return \Lastfm\Service\Geo
     */
    public function getGeoService()
    {
        return $this->getService('geo');
    }

    /**
     * Returns a group service instance
     *
     * @return \Lastfm\Service\Group
     */
    public function getGroupService()
    {
        return $this->getService('group');
    }

    /**
     * Returns a library service instance
     *
     * @return \Lastfm\Service\Library
     */
    public function getLibraryService()
    {
        return $this->getService('library');
    }

    /**
     * Returns a playlist service instance
     *
     * @return \Lastfm\Service\Playlist
     */
    public function getPlaylistService()
    {
        return $this->getService('playlist');
    }

    /**
     * Returns a radio service instance
     *
     * @return \Lastfm\Service\Radio
     */
    public function getRadioService()
    {
        return $this->getService('radio');
    }

    /**
     * Returns a tag service instance
     *
     * @return \Lastfm\Service\Tag
     */
    public function getTagService()
    {
        return $this->getService('tag');
    }

    /**
     * Returns a tasteometer service instance
     *
     * @return \Lastfm\Service\Tasteometer
     */
    public function getTasteometerService()
    {
        return $this->getService('tasteometer');
    }

    /**
     * Returns a track service instance
     *
     * @return \Lastfm\Service\Track
     */
    public function getTrackService()
    {
        return $this->getService('track');
    }

    /**
     * Returns a user service instance
     *
     * @return \Lastfm\Service\User
     */
    public function getUserService()
    {
        return $this->getService('user');
    }

    /**
     * Returns a venue service instance
     *
     * @return \Lastfm\Service\Venue
     */
    public function getVenueService()
    {
        return $this->getService('venue');
    }

    /**
     * Returns an instance of the specified service
     *
     * @param  string $name
     *
     * @return \Lastfm\Service
     */
    protected function getService($name)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $this->createService($name);
        }

        return $this->services[$name];
    }

    /**
     * Creates an instance of the specified service
     *
     * @param  string $name
     *
     * @return \Lastfm\Service
     */
    protected function createService($name)
    {
        $className = sprintf('Lastfm\Service\%s', ucfirst($name));

        if (!class_exists($className)) {
            throw new \RuntimeException(sprintf(
                'Cannot create service \'%s\', class %s not found.',
                $name, $className
            ));
        }

        $r = new \ReflectionClass($className);

        return $r->newInstanceArgs(array($this));
    }

    /**
     * Creates the method signature for the given paramters
     *
     * @param  array $parameters
     *
     * @return string
     */
    protected function createMethodSignature(array $parameters)
    {
        if (null === $this->secret) {
            throw new \LogicException('You must configure the API secret prior to generate a method signature.');
        }

        ksort($parameters);

        $parametersString = '';
        foreach ($parameters as $name => $value) {
            $parametersString.= $name.$value;
        }

        return md5($parametersString.$this->secret);
    }
}
