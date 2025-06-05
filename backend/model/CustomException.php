<?php

namespace Model;

class CustomException extends \Exception
{
    protected $httpStatusCode;

    public function __construct($message, $httpStatusCode = 500)
    {
        parent::__construct($message);
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}
