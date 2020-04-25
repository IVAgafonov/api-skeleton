<?php

namespace App\Api\Response\Mail;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class ContactList extends AbstractRootResponse
{

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $count;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/ContactItem"));
     *
     * @var ContactItem[]
     */
    public $items;
}