<?php

namespace App\Api\Response;

use App\Entity\AbstractEntity;
use App\Entity\AbstractEnum;
use App\Entity\AbstractSet;

class AbstractRootResponse extends AbstractResponse implements ResponseRootInterface
{

    /**
     * @var int
     */
    protected static $response_code = 200;

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return static::$response_code;
    }

    /**
     * @param int $response_code
     */
    public function setResponseCode(int $response_code)
    {
        static::$response_code = $response_code;
    }
}
