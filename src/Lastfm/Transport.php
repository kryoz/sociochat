<?php

namespace Lastfm;

/**
 * Interface for the transport classes
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
interface Transport
{
    const HTTP_METHOD_GET  = 'get';
    const HTTP_METHOD_POST = 'post';

    /**
     * Performs a request and returns the response
     *
     * @param  string $httpMethod The HTTP method (one of the HTTP_METHOD_* constants)
     * @param  string $apiMethod  The API method formated as Service.methodName
     * @param  array  $parameters An array of parameters
     * @param  array  $options    An array of options for this request only
     */
    function request($httpMethod, $apiMethod, array $parameters = array(), array $options = array());
}
