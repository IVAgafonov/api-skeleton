<?php

namespace App\Validator\Auth;

use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\ResponseInterface;
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
                    return new ClientErrorResponse([
                        'field' => 'email',
                        'message' => 'Invalid email'
                    ]);
                }
                if (!preg_match("/\w+@\w+\.\w+/i", $params['email'])) {
                    return new ClientErrorResponse([
                        'field' => 'email',
                        'message' => 'Invalid email format'
                    ]);
                }
                if (empty($params['password']) || !is_string($params['password'])) {
                    return new ClientErrorResponse([
                        'field' => 'password',
                        'message' => 'Invalid password'
                    ]);
                }
                if (mb_strlen($params['password']) < 6 || mb_strlen($params['password']) > 40) {
                    return new ClientErrorResponse([
                        'field' => 'password',
                        'message' => 'Password must contain 6 - 40 symbols'
                    ]);
                }
                if (!empty($params['permanent']) && !is_bool($params['permanent'])) {
                    return new ClientErrorResponse([
                        'field' => 'permanent',
                        'message' => 'Invalid value'
                    ]);
                }
                break;
        }
        return null;
    }
}