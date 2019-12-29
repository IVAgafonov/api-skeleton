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

    public function toDb()
    {
        $values = get_object_vars($this);
        foreach ($values as $field => $param) {
            if ($param === null ||
                is_array($param)) {
                unset($values[$field]);
                continue;
            }
            if ($param instanceof AbstractEnum) {
                $values[$field] = $param->getValue();
            }
            if ($param instanceof AbstractSet) {
                $values[$field] = $param->getValue();
            }
        }

        return $values;
    }

    public function toArray()
    {
        return $this->entityToArray($this);
    }

    protected function entityToArray(AbstractEntity $entity)
    {
        $values = get_object_vars($entity);

        foreach ($values as $field => $param) {
            if ($param instanceof AbstractEnum) {
                $values[$field] = $param->getValue();
            }
            if ($param instanceof AbstractSet) {
                $values[$field] = $param->getArray();
            }
            if ($param instanceof AbstractEntity) {
                $values[$field] = $this->entityToArray($param);
            }
            if (is_array($param)) {
                foreach ($param as $index => $p) {
                    $array_values = get_object_vars($p);
                    foreach ($array_values as $array_field => $array_param) {
                        if ($array_param instanceof AbstractEnum) {
                            $array_values[$array_field] = $array_param->getValue();
                        }
                        if ($array_param instanceof AbstractSet) {
                            $array_values[$array_field] = $array_param->getArray();
                        }
                        if ($array_param instanceof AbstractEntity) {
                            $array_values[$array_field] = $this->entityToArray($array_param);
                        }
                    }
                    $values[$field][$index] = $array_values;
                }
            }
        }

        return $values;
    }

    public function toJson()
    {

    }
}
