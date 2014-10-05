<?php

namespace SocioChat\Response;


class ErrorResponse extends Response
{
    protected $errors = null;

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}