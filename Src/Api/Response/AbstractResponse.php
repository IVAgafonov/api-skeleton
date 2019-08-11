<?php

namespace App\Api\Response;

class AbstractResponse implements ResponseInterface
{
    /**
     * AbstractResponse constructor.
     * @param array $values
     * @param int $response_code
     */
    public function __construct(array $values = [], $response_code = 200)
    {
        http_response_code($response_code);
        foreach ($values as $property => $value) {
            if (property_exists($this, $property)) {
                if (is_numeric($value)) {
                    $this->$property = (float)$value;
                } else {
                    $this->$property = $value;
                }

            }
        }
    }

    public static function getResponseType()
    {
        $response_type = explode("\\", static::class);
        return basename(array_pop($response_type));
    }
}
