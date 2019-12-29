<?php

namespace App\Api\Middleware;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\ResponseInterface;
use DI\Container;

/**
 * Interface MiddlewareInterface
 * @package App\Api\Middleware
 */
interface MiddlewareInterface
{
    /**
     * @param AbstractApiController $controller
     * @param Container $container
     * @param array $headers
     * @param array $params
     * @param array $extra_params
     * @return ResponseInterface|null
     * @throws \Exception
     */
    public function __invoke(AbstractApiController &$controller, Container $container, array $headers, array $params, array $extra_params = []);
}