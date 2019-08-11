<?php

namespace App\Api\Response\Error;

use App\Api\Response\AbstractResponse;

/**
 * @OA\Schema()
 */
class ClientErrorResponse extends AbstractResponse
{

    /**
     * @OA\Property(example="field_name")
     *
     * @var string
     */
    public $field;

    /**
     * @OA\Property(example="Invalid field")
     *
     * @var string
     */
    public $message;

    /**
     * ServerErrorResponse constructor.
     * @param array $data
     * @param int $response_code
     */
    public function __construct(array $data, int $response_code = 400)
    {
        parent::__construct($data, $response_code);
    }
}