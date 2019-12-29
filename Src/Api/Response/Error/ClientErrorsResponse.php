<?php

namespace App\Api\Response\Error;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ClientErrorsResponse extends AbstractRootResponse
{

    /**
     * @var int
     */
    protected static $response_code = 400;

    /**
     * @OA\Property(example=1)
     *
     * @var int
     */
    public $count;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ClientErrorResponse"))
     *
     * @var ClientErrorResponse[]
     */
    public $items;

    /**
     * @OA\Property(example="Validation failed")
     *
     * @var string
     */
    public $message;
    /**
     * ServerErrorResponse constructor.
     * @param array $items
     * @param string $message
     */
    public function __construct(array $items, string $message = "Validation failed")
    {
        $this->items = $items;
        $this->message = $message;
        $this->count = count($items);
    }
}