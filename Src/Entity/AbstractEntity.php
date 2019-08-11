<?php

namespace App\Entity;

class AbstractEntity {

    public function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            if ($value === null) {
                continue;
            }
            $method = str_replace("_", "", "set".ucwords($property, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function toArray()
    {
        $values = get_object_vars($this);
        return $values;
    }
}