<?php

namespace Lastfm;

/**
 * Base service class
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
abstract class Service
{
    private $client;
    private $methods = array();

    /**
     * Constructor
     *
     * @param  Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->configure();
    }

    public function __call($method, $arguments)
    {
        if (!$this->hasMethod($method)) {
            throw new \Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }

        $options    = $this->getMethodOptions($method);
        $apiMethod  = sprintf('%s.%s', $this->getName(), $method);
        $parameters = isset($arguments[0]) ? $arguments[0] : array();

        return $this->client->request($options['http_method'], $apiMethod, $parameters);
    }

    /**
     * Configures the service methods
     *
     * @see Service::addMethod()
     */
    abstract protected function configure();

    /**
     * Adds a method to the service
     *
     * @param  string  $name
     * @param  boolean $requiresAuthentication
     * @param  string  $httpMethod
     */
    protected function addMethod($name, $requiresAuthentication = false, $httpMethod = Transport::HTTP_METHOD_GET)
    {
        $this->methods[$name] = array(
            'requires_authentication'   => $requiresAuthentication,
            'http_method'               => $httpMethod
        );
    }

    /**
     * Indicates whether the specified method is defined
     *
     * @param  string $name
     *
     * @return boolean
     */
    protected function hasMethod($name)
    {
        return isset($this->methods[$name]);
    }

    /**
     * Returns the specified method's options
     *
     * @param  string $name
     *
     * @return array
     */
    protected function getMethodOptions($name)
    {
        return $this->methods[$name];
    }

    /**
     * Returns all the defined methods
     *
     * @return array
     */
    protected function getMethods()
    {
        return $this->methods;
    }

    /**
     * Returns the name of the service. By default, it is the short class name
     *
     * @return string
     */
    protected function getName()
    {
        preg_match('/\\\\(\w+?)$/', strtolower(get_class($this)), $matches);

        if (empty($matches)) {
            throw new \RuntimeException(sprintf(
                'Unable to compute service name for class \'%s\'. Please override the ->getName() method.',
                get_class($this)
            ));
        }

        return end($matches);
    }
}
