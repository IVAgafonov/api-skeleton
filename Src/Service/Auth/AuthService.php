<?php

namespace App\Service\Auth;

use App\Api\Response\Auth\SuccessAuthResponse;
use App\Entity\Token\TokenType;
use App\Entity\User\User;
use App\System\DataProvider\Mysql\DataProviderInterface;

/**
 * Class AuthService
 * @package App\Service\Auth
 */
class AuthService {

    /**
     * @var int
     */
    private static $token_live_time = 900;

    /**
     * @var string
     */
    private static $salt = "haD&#Hqd83d8qd";

    /**
     * @var DataProviderInterface
     */
    protected $dp;

    /**
     * AuthService constructor.
     * @param DataProviderInterface $dp
     */
    public function __construct(DataProviderInterface $dp)
    {
        $this->dp = $dp;
    }

    /**
     * @param User $user
     * @param TokenType $token_type
     * @return SuccessAuthResponse
     * @throws \Exception
     */
    public function authUser(User $user, TokenType $token_type)
    {
        $expire_date = date("Y-m-d H:i:s", strtotime("+ ".self::$token_live_time." seconds"));
        $hash = md5(self::$salt.date("Y-m-d H:i:s").$user->getId().rand(0, 999999));

        /** @var SuccessAuthResponse $successAuthResponse */
        $successAuthResponse = SuccessAuthResponse::createFromArray([
            'token' => $hash,
            'token_type' => $token_type->getValue(),
            'expire_date' => $expire_date
        ]);

        $this->dp->query("INSERT INTO `app_users_auth_tokens` ".
            "(token, user_id, expire_date, type) ".
            "VALUES ".
            "(".$this->dp->quote($successAuthResponse->token).", ".
            $user->getId().", ".
            $this->dp->quote($successAuthResponse->expire_date).", ".
            $this->dp->quote($token_type->getValue()).")"
        );

        return $successAuthResponse;
    }

    /**
     * @param string $token
     * @param int $seconds
     * @return bool
     */
    public function updateTokenExpireDate(string $token, $seconds = 0) {
        $expire_seconds = $seconds ? $seconds : self::$token_live_time;
        $expire_date = date("Y-m-d H:i:s", strtotime("+ $expire_seconds seconds"));

        $auth = $this->dp->getArray("SELECT * FROM `app_users_auth_tokens` ".
            "WHERE `token` = ".$this->dp->quote($token)." ".
            "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(TokenType::PERMANENT).")");

        if ($auth) {
            if ($auth)
            $this->dp->query("UPDATE `app_users_auth_tokens` SET `expire_date` = ".$this->dp->quote($expire_date)." ".
                "WHERE `token` = ".$this->dp->quote($token));
            return true;
        }

        $this->dp->query("DELETE FROM `app_users_auth_tokens` ".
                "WHERE `token` = ".$this->dp->quote($token));

        return false;
    }

    public function getUserIdByToken(string $token)
    {
        return $this->dp->getValue("SELECT user_id FROM `app_users_auth_tokens` ".
            "WHERE `token` = ".$this->dp->quote($token)." ".
            "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(TokenType::PERMANENT).")");
    }

    public function logoutUser(string $token, bool $all_devices = false)
    {
        if ($all_devices) {
            $user_id = $this->getUserIdByToken($token);
            if ($user_id) {
                $this->dp->query("DELETE FROM `app_users_auth_tokens` ".
                    "WHERE `user_id` = ".$user_id);
            }
        } else {
            $this->dp->query("DELETE FROM `app_users_auth_tokens` ".
                "WHERE `token` = ".$this->dp->quote($token)." ".
                "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(TokenType::PERMANENT).")");
        }
    }

    public static function getTokenFromHeaders($headers)
    {
        return trim(preg_replace("/\s*bearer\s*/i","", $headers['Authorization'] ?? ''));
    }
}