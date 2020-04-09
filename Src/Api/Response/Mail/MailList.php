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
    public $count_inbox;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $count_outbox;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $count_deleted;

    /**
     * @OA\Property()
     *
     * @var int
     */
    public $count_unread;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/MailItem"));
     *
     * @var MailItem[]
     */
    public $items;
}