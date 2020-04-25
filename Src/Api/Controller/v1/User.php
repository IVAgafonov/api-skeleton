<?php

namespace App\Api\Controller\v1;

use App\Api\Controller\AbstractApiController;
use App\Api\Response\Auth\ErrorAuthResponse;
use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\User\UserResponse;
use App\Entity\Token\TokenType;
use App\Service\Auth\AuthService;
use App\Service\Crypt\CryptService;
use App\Service\Mail\EmailService;
use App\Service\User\UserService;
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
        /** @var UserService $user_service */
        $user_service = $this->container->get(UserService::class);

        if ($response = UserValidator::validate($this->params, UserValidator::CREATE_USER)) {
            return $response;
        }

        if ($user_service->getUserByEmail($this->params['email'])) {
            return new ClientErrorResponse('email', 'User with this email already exists');
        }

        $user = $user_service->createUser(
            $this->params['email'],
            $this->params['name'],
            CryptService::hashPassword($this->params['password'])
        );

        if (!$user) {
            return new ClientErrorResponse("email", "Can't create new user. Please, try later");
        }

        /** @var EmailService $email_service */
        $email_service = $this->getContainer()->get(EmailService::class);
        $email_service->createEmail(
            $user_service->getUserByEmail('admin@agafonov.me')->getId(),
            $user->getId(),
            'Thanks for registration',
            "Hello! Your registration successfully completed! Please, delete this message after reading."
        );

        /** @var AuthService $auth_service */
        $auth_service = $this->container->get(AuthService::class);
        $auth = $auth_service->authUser($user, new TokenType(TokenType::TEMPORARY));

        if (!$auth) {
            return ErrorAuthResponse::createFromArray([
                'message' => "Unauthorized"
            ]);
        }

        return $auth;
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
     *         description="Auth error",
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
        /** @var UserService $user_service */
        $user_service = $this->container->get(UserService::class);
        if ($response = UserValidator::validate($this->params, UserValidator::UPDATE_USER)) {
            return $response;
        }

        $this->getUser()->setName($this->params['name']);

        $user_service->saveUser($this->getUser());

        return UserResponse::createFromArray($this->getUser()->toArray());
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
        return UserResponse::createFromArray($this->getUser()->toArray());
    }
}
