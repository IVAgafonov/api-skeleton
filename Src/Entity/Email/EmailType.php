<?php

namespace App\Entity\Email;

use App\Entity\AbstractEnum;

/**
 * @OA\Schema(schema="EmailType",
 *   type="string",
 *   enum={"SENT", "RECEIVED"}
 * )
 */
class EmailType extends AbstractEnum {
    const SENT = 'SENT';
    const RECEIVED = 'RECEIVED';
}