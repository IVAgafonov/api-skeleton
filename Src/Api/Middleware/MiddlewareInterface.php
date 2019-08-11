<?php

namespace App\Api\Middleware;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\ResponseInterface;

/**
 * Interface MiddlewareInterface
 * @package App\Api\Middleware
 */
interface MiddlewareInterface
{
    /**
     * @param AbstractApiController $controller
     * @param array $headers
     * @param array $params
     * @param array $extra_params
     * @return ResponseInterface|null
     * @throws \Exception
     */
    public function __invoke(AbstractApiController &$controller, array $headers, array $params, array $extra_params = []);
}