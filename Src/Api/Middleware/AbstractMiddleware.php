<?php

namespace App\Api\Middleware;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\ResponseInterface;

class AbstractMiddleware implements MiddlewareInterface
{
    /**
     * @param AbstractApiController $controller
     * @param array $headers
     * @param array $params
     * @param array $extra_params
     * @return ResponseInterface|null
     * @throws \Exception
     */
    public function __invoke(AbstractApiController &$controller, array $headers, array $params, array $extra_params = [])
    {
        return null;
    }
}