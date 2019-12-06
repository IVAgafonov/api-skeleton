<?php

namespace App\Entity;

/**
 * Class AbstractSet
 * @package App\Entity
 */
class AbstractSet {
    /**
     * @var array
     */
    public $items = [];

    public function __construct(array $items = [])
    {
        $this->items = static::validate($items);
    }

    /**
     * @param array $items
     * @return array
     * @throws \ReflectionException
     */
    public static function validate(array $items) {
        $items_unique = array_unique(array_filter($items));

        if (count($items_unique) != count($items)) {
            throw new \Exception("Invalid set (duplicate elements): ".static::class);
        }

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
            throw new \Exception("Invalid items in set ".static::class.": ".implode(", ", $diff));
        }

        return $items;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getAllowedValues()
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return implode(",", $this->items);
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->items;
    }

    /**
     * @param string $value
     * @throws \ReflectionException
     */
    public function setValue(string $value)
    {
        $this->items = self::validate(explode(",", $value));
    }

    /**
     * @param string $element
     * @return bool
     */
    public function has(string $element)
    {
        return in_array($element, $this->items);
    }

    /**
     * @param string $element
     * @return $this
     * @throws \ReflectionException
     */
    public function add(string $element)
    {
        if (!in_array($element, static::getAllowedValues())) {
            throw new \Exception("Invalid element ".$element." put in set: ".static::class);
        }
        if (in_array($element, $this->items)) {
            throw new \Exception("Element ".$element." already in set: ".static::class);
        }
        $this->items[] = $element;
        return $this;
    }

    /**
     * @param string $element
     * @throws \Exception
     */
    public function remove(string $element)
    {
        $index = array_search($element, $this->items);
        if ($index) {
            unset($this->items[$index]);
        }
        throw new \Exception("Element ".$element." is not in set: ".static::class);
    }
}