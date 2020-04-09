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
    public $message;

    /**
     * @OA\Property()
     *
     * @var boolean
     */
    public $is_opened;

    /**
     * @OA\Property()
     *
     * @var boolean
     */
    public $is_important;

    /**
     * @OA\Property()
     *
     * @var string
     */
    public $date;

    public static function createFromArray(array $array)
    {
        $array['id'] = (int) $array['id'];
        $array['is_opened'] = (bool) $array['is_opened'];
        $array['is_important'] = (bool) $array['is_important'];
        $array['date'] = $array['create_date'];
        return parent::createFromArray($array);
    }
}