<?php

namespace App\Api\Response;

use App\Entity\AbstractEntity;
use App\Entity\AbstractEnum;
use App\Entity\AbstractSet;

class AbstractResponse implements ResponseInterface
{

    /**
     * Fill response from array.
     * @param array $values
     * @return self
     */
    public static function createFromArray(array $values)
    {
        $response = new static();
        foreach ($values as $property => $value) {
            if (property_exists($response, $property)) {
                $response->$property = $value;
            }
        }
        return $response;
    }

    /**
     * @param AbstractEntity $entity
     * @return static
     * @throws \Exception
     */
    public static function createFromEntity(AbstractEntity $entity)
    {
        $response = new static();
        $vars = get_class_vars(static::class);

        foreach ($vars as $property => $value) {
            $method = str_replace("_", "", "get".ucwords($property, '_'));
            if (method_exists($entity, $method)) {
                $value = $entity->$method();
                if ($value instanceof AbstractEnum) {
                    $value = $value->getValue();
                } elseif ($value instanceof AbstractSet) {
                    $value = $value->getArray();
                }
                $response->$property = $value;
            }
        }
        return $response;
    }

    public static function getResponseType()
    {
        $response_type = explode("\\", static::class);
        return basename(array_pop($response_type));
    }
}
