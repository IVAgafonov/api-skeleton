<?php

namespace App\Api\Response\Mail;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class MailList extends AbstractRootResponse
{

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $count;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/MailItem"));
     *
     * @var MailItem[]
     */
    public $items;
}