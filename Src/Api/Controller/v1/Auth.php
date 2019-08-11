<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\Auth\ErrorAuthResponse;
use App\Api\Response\Auth\SuccessAuthResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ServerErrorResponse;
use App\Service\Auth\AuthService;
use App\Service\Crypt\CryptService;
use App\Service\User\UserService;
use App\System\Config\Config;
use App\System\DataProvider\Mysql\DataProvider;
use App\Validator\Auth\AuthValidator;
use App\Validator\User\UserValidator;

/**
 * Class Auth
 * @package App\Api\Controller\v1
 */
class Auth extends AbstractApiController {

    /**
     * @OA\Post(path="/api/v1/auth/login",
     *     tags={"Auth"},
     *     summary="Auth user",
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             properties={
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="password", type="string", format="password"),
     *                 @OA\Property(property="permanent", type="bool"),
     *             },
     *             example={"email": "test@site.ru", "password": "654321", "permanent": true}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Auth success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="SuccessAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/SuccessAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function login()
    {
        $dp = new DataProvider(Config::get('mysql.main'));
        $user_service = new UserService($dp);

        if ($response = AuthValidator::validate($this->params, AuthValidator::AUTH_LOGIN)) {
            return $response;
        }

        $token_type =
            (!empty($this->params['permanent']) ? AuthService::TOKEN_TYPE_PERMANENT : AuthService::TOKEN_TYPE_TEMPORARY);

        $user = $user_service->getUserByEmail($this->params['email']);

        if (!$user) {
            return new ClientErrorResponse([
                'field' => "email",
                'message' => "User doesn't exist"
            ]);
        }

        if ($user->getPassword() !== CryptService::hashPassword($this->params['password'])) {
            return new ClientErrorResponse([
                'field' => "password",
                'message' => "Invalid password"
            ]);
        }

        $auth_service = new AuthService($dp);
        $auth = $auth_service->authUser($user->getId(), $token_type);

        if (!$auth) {
            return new ErrorAuthResponse([
                'message' => "Unauthorized"
            ]);
        }

        return new SuccessAuthResponse($auth);
    }

    /**
     * @OA\Get(path="/api/v1/auth/logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Parameter(
     *         name="all_devices",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="bool")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="EmptyResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/EmptyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ErrorAuthResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ErrorAuthResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ServerErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ServerErrorResponse")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        $dp = new DataProvider(Config::get('mysql.main'));
        $auth_service = new AuthService($dp);

        $token = AuthService::getTokenFromHeaders($this->headers);

        $all_devices = (bool)($this->params['all_devices'] ?? false);
        if ($all_devices && mb_strtolower($this->params['all_devices']) == 'false') {
            $all_devices = false;
        }

        $auth_service->logoutUser($token, $all_devices);

        return new EmptyResponse();
    }
}