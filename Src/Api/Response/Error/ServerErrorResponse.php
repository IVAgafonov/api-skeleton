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
     * @param array $data
     * @param int $response_code
     */
    public function __construct(array $data, int $response_code = 500)
    {
        parent::__construct($data, $response_code);
    }
}