<?php

namespace App\Api\Response\Error;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ServerErrorResponse extends AbstractRootResponse
{

    /**
     * @var int
     */
    protected static $response_code = 500;

    /**
     * @OA\Property(example="Server made a boo boo")
     *
     * @var string 
     */
    public $message;

    /**
     * ServerErrorResponse constructor.
     * @param string $message
     * @param int $response_code
     */
    public function __construct(string $message, int $response_code = 500)
    {
        $this->message = $message;
        $this->setResponseCode($response_code);
    }
}