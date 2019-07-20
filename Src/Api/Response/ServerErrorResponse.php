<?php

namespace App\Api\Response;

/**
 * Class ServerErrorResponse
 * @package App\Api\Response
 */
class ServerErrorResponse extends AbstractResponse
{

    /**
     * @var string
     */
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}