<?php

namespace App\Api\Response\Mail;

use App\Api\Response\AbstractRootResponse;

/**
 * @OA\Schema()
 */
class MailItem extends AbstractRootResponse
{

    /**
     * @OA\Property(example=1)
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(example="Email subject")
     *
     * @var string
     */
    public $subject;

    /**
     * @OA\Property(example="test@test.com")
     *
     * @var string
     */
    public $sender;

    /**
     * @OA\Property(example="Hello friend! ...")
     *
     * @var string
     */
    public $text;

    /**
     * @OA\Property()
     *
     * @var boolean
     */
    public $read;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $date;
}