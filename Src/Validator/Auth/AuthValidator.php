<?php

namespace App\Validator\Auth;

use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\ResponseInterface;
use App\Entity\Token\TokenType;
use App\Validator\ValidatorInterface;

class AuthValidator implements ValidatorInterface {

    const AUTH_LOGIN = 'AUTH_LOGIN';

    /**
     * @param array $params
     * @param string $context
     * @return ResponseInterface|null
     */
    public static function validate(array $params, string $context)
    {
        switch ($context) {
            case self::AUTH_LOGIN:
                if (empty($params['email']) || !is_string($params['email']) || mb_strlen($params['email']) > 255) {
                    return new ClientErrorResponse('email', 'Invalid email');
                }
                if (!preg_match("/\w+@\w+\.\w+/i", $params['email'])) {
                    return new ClientErrorResponse('email', 'Invalid email format');
                }
                if (empty($params['password']) || !is_string($params['password'])) {
                    return new ClientErrorResponse('password', 'Invalid password');
                }
                if (mb_strlen($params['password']) < 6 || mb_strlen($params['password']) > 40) {
                    return new ClientErrorResponse('password', 'Password must contain 6 - 40 symbols');
                }
                if (!empty($params['token_type'])) {
                    try {
                        TokenType::validate($params['token_type']);
                    } catch (\Throwable $e) {
                        return new ClientErrorResponse('token_type', $e->getMessage());
                    }
                }
                break;
        }
        return null;
    }
}