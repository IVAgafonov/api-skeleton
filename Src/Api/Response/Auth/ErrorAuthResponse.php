<?php

namespace App\Api\Response\Auth;

use App\Api\Response\AbstractResponse;

/**
 * @OA\Schema()
 */
class ErrorAuthResponse extends AbstractResponse
{

    /**
     * @OA\Property(example="Unauthorized")
     *
     * @var string
     */
    public $message;

    /**
     * ServerErrorResponse constructor.
     * @param array $data
     * @param int $response_code
     */
    public function __construct(array $data, int $response_code = 401)
    {
        parent::__construct($data, $response_code);
    }
}