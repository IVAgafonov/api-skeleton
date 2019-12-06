<?php

namespace App\Api\Middleware\Auth;

use App\Api\Controller\AbstractApiController;
use App\Api\Middleware\MiddlewareInterface;
use App\Api\Response\Auth\ErrorAuthResponse;
use App\Api\Response\ResponseInterface;
use App\Entity\User\User;
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
     * @param array $headers
     * @param array $params
     * @param array $extra_params
     * @return ResponseInterface|null
     * @throws \Exception
     */
    public function __invoke(AbstractApiController &$controller, array $headers, array $params, array $extra_params = [])
    {
        if (!empty($extra_params['allowed_groups'])) {
            $dp = new DataProvider(Config::get('mysql.main'));

            $auth_service = new AuthService($dp);
            $user_service = new UserService($dp);

            $token = AuthService::getTokenFromHeaders($headers);

            if (!$token) {
                return new ErrorAuthResponse('Unauthorized');
            }

            if (!$auth_service->updateTokenExpireDate($token)) {
                return new ErrorAuthResponse('Unauthorized [Invalid token]');
            }

            $user_id = $auth_service->getUserIdByToken($token);

            if (!$user_id) {
                return new ErrorAuthResponse('Unauthorized [User not found]');
            }

            $user = $user_service->getUserById($user_id);

            if (empty(array_intersect($user->getGroups()->items, $extra_params['allowed_groups']))) {
                return new ErrorAuthResponse('Unauthorized [Group not allowed]');
            }

            $controller->setUser($user);
        }

        return null;
    }
}