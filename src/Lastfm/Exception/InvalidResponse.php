<?php

namespace Lastfm\Exception;

use Lastfm\Exception;

/**
 * Exception to be thrown when an API response is not valid (e.g cannot be
 * unserialized)
 *
 * @package Last.fm
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 */
class InvalidResponse extends Exception
{
    private $response;

    /**
     * Constructor
     *
     * @param  string     $response The API response that caused the exception
     * @param  integer    $code
     * @param  \Exception $previous
     */
    public function __construct($response, $code = 0, \Exception $previous = null)
    {
        $this->response = $response;

        parent::__construct(
            sprintf('The API returned an invalid response: %s', $response),
            $code,
            $previous
        );
    }

    /**
     * Returns the response that caused the exception
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
