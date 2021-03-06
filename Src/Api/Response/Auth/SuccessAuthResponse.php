<?php

namespace App\Api\Response\Auth;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class SuccessAuthResponse extends AbstractRootResponse
{

    /**
     * @OA\Property(example="sdfkgsjgiofgdfgdlfgeorineofnerfe")
     *
     * @var string
     */
    public $token;

    /**
     * @OA\Property(ref="#/components/schemas/TokenType")
     *
     * @var string
     */
    public $token_type;

    /**
     * @OA\Property(example="2019-07-07 20:00:00")
     *
     * @var string
     */
    public $expire_date;
}