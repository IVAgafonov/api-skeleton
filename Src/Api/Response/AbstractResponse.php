<?php

namespace App\Api\Response;

class AbstractResponse implements ResponseInterface
{
    public static function getResponseType()
    {
        $responseType = explode("\\", static::class);
        return basename(array_pop($responseType));
    }
}
