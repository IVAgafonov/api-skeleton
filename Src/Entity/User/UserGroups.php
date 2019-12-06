<?php

namespace App\Entity\User;

use App\Entity\AbstractSet;

/**
 * @OA\Schema(schema="UserGroup",
 *   type="string",
 *   enum={"USER", "ADMIN"}
 * )
 */
/**
 * @OA\Schema(schema="UserGroups",
 *   type="array",
 *   @OA\Items(ref="#/components/schemas/UserGroup")
 * )
 */
class UserGroups extends AbstractSet {
    const USER = 'USER';
    const ADMIN = 'ADMIN';
}