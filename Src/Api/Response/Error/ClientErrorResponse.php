<?php

namespace App\Api\Response\Error;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ClientErrorResponse extends AbstractRootResponse
{

    /**
     * @var int
     */
    protected static $response_code = 400;

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
     * ClientErrorResponse constructor.
     * @param string $field
     * @param string $message
     * @param int $response_code
     */
    public function __construct(string $field, string $message, int $response_code = 400)
    {
        $this->field = $field;
        $this->message = $message;
        $this->setResponseCode($response_code);
    }
}