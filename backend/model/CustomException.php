<?php

namespace Model;

class CustomException extends \Exception
{
    public function __construct($message, $code = 500)
    {
        parent::__construct(message: $message, code: $code);

    }

}
