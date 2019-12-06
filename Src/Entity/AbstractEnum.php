<?php

namespace App\Entity;

class AbstractEnum {

    private $value = null;

    public function __construct($value = null)
    {
        if (empty($value) && !empty($this->de))
        static::validate($value);
        $this->value = $value;
    }

    public function getValue()
    {
        if (empty($this->value)) {
            throw new \Exception("Empty enum: ".static::class);
        }
        return $this->value;
    }

    public static function validate(string $item) {
        $allowed_elements = (new \ReflectionClass(static::class))->getConstants();
        if (empty($allowed_elements)) {
            throw new \Exception("Invalid enum: ".static::class);
        }

        if (!in_array($item, $allowed_elements)) {
            throw new \Exception("Invalid element $item in enum: ".static::class);
        }

        return $item;
    }

    public static function getAllowedValues()
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}