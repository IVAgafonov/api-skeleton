<?php

namespace App\Api\Response;

interface ResponseRootInterface extends ResponseInterface
{
    public function getResponseCode();
    public function setResponseCode(int $response_code);
}
