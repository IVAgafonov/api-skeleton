<?php

namespace App\Validator;

interface ValidatorInterface {

    public static function validate(array $params, string $context);
}