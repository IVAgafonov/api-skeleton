<?php

namespace App\Entity\Token;

use App\Entity\AbstractEnum;

/**
 * @OA\Schema(schema="TokenType",
 *   type="string",
 *   enum={"PERMANENT", "TEMPORARY"}
 * )
 */
class TokenType extends AbstractEnum {
    const TEMPORARY = 'TEMPORARY';
    const PERMANENT = 'PERMANENT';
}