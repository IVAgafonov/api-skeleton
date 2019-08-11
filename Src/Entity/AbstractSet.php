<?php

namespace App\Entity;

class AbstractSet {

    public static function validate(array $items) {
        $items = array_unique(array_filter($items));

        array_map(function ($e) {
            if (!is_string($e) || is_numeric($e)) {
                throw new \Exception("Invalid set: ".static::class);
            }
        }, $items);

        $allowed_elements = (new \ReflectionClass(static::class))->getConstants();
        if (empty($allowed_elements)) {
            throw new \Exception("Invalid set: ".static::class);
        }

        $diff = array_diff($items, $allowed_elements);

        if (count($diff)) {
            throw new \Exception("Invalid items: ".implode(", ", $diff));
        }

        return $items;
    }
}