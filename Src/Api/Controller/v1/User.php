<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\Auth\ErrorAuthResponse;
use App\Api\Response\Auth\SuccessAuthResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\EmptyResponse;
use App\Api\Response\Error\ServerErrorResponse;
use App\Api\Response\User\UserResponse;
use App\Service\Auth\AuthService;
use App\Service\Crypt\CryptService;
use App\Service\User\UserService;
use App\System\Config\Config;
use App\System\DataProvider\Mysql\DataProvider;
use App\Validator\User\UserValidator;

/**
 * Class User
 * @package App\Api\Controller\v1
 */
class User extends AbstractApiController {

    /**
     * @OA\Post(path="/api/v1/user/register",
     *     tags={"User"},
     *     summary="Register new user",
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             properties={
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="password", type="string", format="password"),
     *                 @OA\Property(property="name", type="string"),
     *             },
     *             example={"email": "test@site.ru", "password": "654321", "name": "Test User"}
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
     *         description="Client error",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="ClientErrorResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/ClientErrorResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Client auth error",
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
    public function register()
    {
        $dp = new DataProvider(Config::get('mysql.main'));
        $user_service = new UserService($dp);

        if ($response = UserValidator::validate($this->params, UserValidator::CREATE_USER)) {
            return $response;
        }

        if ($user_service->getUserByEmail($this->params['email'])) {
            return new ClientErrorResponse([
                'field' => 'email',
                'message' => 'User with this email already exists'
            ]);
        }

        $user = $user_service->createUser([
            'email' => $this->params['email'],
            'name' => $this->params['name'],
            'password' => CryptService::hashPassword($this->params['password'])
        ]);

        if (!$user) {
            return new ClientErrorResponse([
                'field' => "email",
                'message' => "Can't create new user. Please, try later"
            ]);
        }

        $auth_service = new AuthService($dp);
        $auth = $auth_service->authUser($user->getId());

        if (!$auth) {
            return new ErrorAuthResponse([
                'message' => "Unauthorized"
            ]);
        }

        return new SuccessAuthResponse($auth);
    }

    /**
     * @OA\Post(path="/api/v1/user/update",
     *     tags={"User"},
     *     summary="Update user data",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             properties={
     *                 @OA\Property(property="name", type="string"),
     *             },
     *             example={"name": "New Test User"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Auth success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="UserResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/UserResponse")
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
    public function update()
    {
        $dp = new DataProvider(Config::get('mysql.main'));
        $user_service = new UserService($dp);

        if ($response = UserValidator::validate($this->params, UserValidator::UPDATE_USER)) {
            return $response;
        }

        $this->getUser()->setName($this->params['name']);

        $user_service->saveUser($this->getUser(), ['name']);

        return new UserResponse($this->getUser()->toArray());
    }

    /**
     * @OA\Get(path="/api/v1/user",
     *     tags={"User"},
     *     summary="Get user info",
     *     security={{"TokenAuth":{"USER"}}},
     *     @OA\Response(
     *         response=200,
     *         description="Response success",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="timestamp", type="integer", example="1563707216"),
     *              @OA\Property(property="response_type", type="string", example="UserResponse"),
     *              @OA\Property(property="response", ref="#/components/schemas/UserResponse")
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
    public function get()
    {
        return new UserResponse($this->getUser()->toArray());
    }
}
