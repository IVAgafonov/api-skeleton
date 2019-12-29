<?php

namespace App\Api\Response\User;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class UserResponse extends AbstractRootResponse
{

    /**
     * @OA\Property(example=1)
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(example="test@site.ru")
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(example="Test User")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(ref="#/components/schemas/UserGroups");
     *
     * @var string[]
     */
    public $groups;
}