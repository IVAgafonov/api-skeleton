<?php

namespace App\Entity\Auth;

use App\Entity\AbstractEnum;

class AuthTypes extends AbstractEnum {
    const TEMPORARY = 'TEMPORARY';
    const PERMANENT = 'PERMANENT';
}