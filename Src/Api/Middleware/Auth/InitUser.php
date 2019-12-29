<?php

namespace App\Api\Middleware\Auth;

use App\Api\Controller\AbstractApiController;
use App\Api\Middleware\MiddlewareInterface;
use App\Api\Response\Auth\ErrorAuthResponse;
use App\Api\Response\ResponseInterface;
use DI\Container;
use App\Service\Auth\AuthService;
use App\Service\User\UserService;
use App\System\Config\Config;
use App\System\DataProvider\Mysql\DataProvider;

/**
 * Class UpdateToken
 * @package App\Api\Middleware\Auth
 */
class InitUser implements MiddlewareInterface
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
    public function __invoke(AbstractApiController &$controller, Container $container, array $headers, array $params, array $extra_params = [])
    {
        if (!empty($extra_params['allowed_groups'])) {
            //$dp = new DataProvider(Config::get('mysql.main'));

            //$auth_service = new AuthService($dp);
            //$user_service = new UserService($dp);
            /** @var AuthService $auth_service */
            $auth_service = $container->get(AuthService::class);
            /** @var UserService $user_service */
            $user_service = $container->get(UserService::class);

            $token = AuthService::getTokenFromHeaders($headers);

            if (!$token) {
                return ErrorAuthResponse::createFromArray([
                    'message' => 'Unauthorized'
                ]);
            }

            if (!$auth_service->updateTokenExpireDate($token)) {
                return ErrorAuthResponse::createFromArray([
                    'message' => 'Unauthorized [Invalid token]'
                ]);
            }

            $user_id = $auth_service->getUserIdByToken($token);

            if (!$user_id) {
                return ErrorAuthResponse::createFromArray([
                    'message' => 'Unauthorized [User not found]'
                ]);
            }

            $user = $user_service->getUserById($user_id);

            if (!$user) {
                return ErrorAuthResponse::createFromArray([
                    'message' => 'Unauthorized [Invalid user]'
                ]);
            }

            if (empty(array_intersect($user->getGroups()->getArray(), $extra_params['allowed_groups']))) {
                return ErrorAuthResponse::createFromArray([
                    'message' => 'Unauthorized [Group not allowed]'
                ]);
            }

            $controller->setUser($user);
        }

        return null;
    }
}