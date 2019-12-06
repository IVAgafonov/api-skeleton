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
     * @param string $field
     * @param string $message
     * @param int $response_code
     */
    public function __construct(string $field, string $message, int $response_code = 400)
    {
        $this->field = $field;
        $this->message = $message;
        parent::__construct($response_code);
    }
}