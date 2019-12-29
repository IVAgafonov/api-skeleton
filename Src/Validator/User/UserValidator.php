<?php

namespace App\Validator\User;

use App\Api\Response\Error\ClientErrorResponse;
use App\Api\Response\ResponseInterface;
use App\Validator\ValidatorInterface;

class UserValidator implements ValidatorInterface {

    const CREATE_USER = 'CREATE_USER';
    const UPDATE_USER = 'UPDATE_USER';

    /**
     * @param array $params
     * @param string $context
     * @return ResponseInterface|null
     */
    public static function validate(array $params, string $context)
    {
        switch ($context) {
            case self::CREATE_USER:
                if (empty($params['email']) || !is_string($params['email']) || mb_strlen($params['email']) > 255) {
                    return new ClientErrorResponse('email', 'Invalid email');
                }
                if (!preg_match("/\w+@\w+\.\w+/i", $params['email'])) {
                    return new ClientErrorResponse('email', 'Invalid email format');
                }
                if (empty($params['password']) || !is_string($params['password'])) {
                    return new ClientErrorResponse('password', 'Invalid password');
                }
                if (mb_strlen(trim($params['password'])) < 6 || mb_strlen(trim($params['password'])) > 40) {
                    return new ClientErrorResponse('password', 'Password must contain 6 - 40 symbols');
                }
                if (empty($params['name']) || !is_string($params['name'])) {
                    return new ClientErrorResponse('name', 'Invalid name');
                }
                if (mb_strlen($params['name']) < 1 || mb_strlen($params['name']) > 96) {
                    return new ClientErrorResponse('name', 'Name must contain 1 - 96 symbols');
                }
                break;
            case self::UPDATE_USER:
                if (!empty($params['name'])) {
                    if (mb_strlen($params['name']) < 1 || mb_strlen($params['name']) > 96) {
                        return new ClientErrorResponse('name', 'Name must contain 1 - 96 symbols');
                    }
                }
                break;
        }
        return null;
    }
}