<?php

namespace App\Api\Response\Error;

use App\Api\Response\AbstractResponse;

/**
 * @OA\Schema()
 */
class ServerErrorResponse extends AbstractResponse
{

    /**
     * @OA\Property(example="Server mafe a boo boo")
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
        parent::__construct($response_code);
    }
}