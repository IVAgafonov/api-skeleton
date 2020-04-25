<?php

namespace App\Api\Response\Mail;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ContactItem extends AbstractRootResponse
{

    /**
     * @OA\Property(example=1)
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(example="test@test.com")
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(example="Name Surname")
     *
     * @var string
     */
    public $name;

    public static function createFromArray(array $array)
    {
        $array['id'] = (int) $array['id'];
        return parent::createFromArray($array);
    }
}