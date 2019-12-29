<?php

namespace App\Api\Response\Auth;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ErrorAuthResponse extends AbstractRootResponse
{

    /**
     * @var int
     */
    protected static $response_code = 401;

    /**
     * @OA\Property(example="Unauthorized")
     *
     * @var string
     */
    public $message;
}