<?php

namespace App\Entity;

/**
 * Class AbstractEnum
 * @package App\Entity
 */
class AbstractEnum {

    /**
     * @var string
     */
    private $value = '';

    /**
     * AbstractEnum constructor.
     * @param string $value
     * @throws \Exception
     */
    public function __construct($value = '')
    {
        static::validate($value);
        $this->value = $value;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getValue()
    {
        if (empty($this->value)) {
            throw new \Exception("Empty enum: ".static::class);
        }
        return $this->value;
    }

    /**
     * @param string $item
     * @return string
     * @throws \ReflectionException
     */
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

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getAllowedValues()
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}