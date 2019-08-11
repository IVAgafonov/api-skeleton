<?php

namespace App\Entity;

class AbstractEnum {

    public static function validate(string $item) {
        $allowed_elements = (new \ReflectionClass(static::class))->getConstants();
        if (empty($allowed_elements)) {
            throw new \Exception("Invalid enum: ".static::class);
        }

        if (!in_array($item, $allowed_elements)) {
            throw new \Exception("Invalid enum: ".static::class);
        }

        return $item;
    }
}