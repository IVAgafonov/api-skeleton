<?php

namespace App\Service\Auth;

use App\System\DataProvider\Mysql\DataProviderInterface;

class AuthService {

    const TOKEN_TYPE_PERMANENT = 'PERMANENT';
    const TOKEN_TYPE_TEMPORARY = 'TEMPORARY';

    private static $token_live_time = 900;

    private static $salt = "haD&#Hqd83d8qd";

    /**
     * @var DataProviderInterface
     */
    protected $dp;

    public function __construct(DataProviderInterface $dp)
    {
        $this->dp = $dp;
    }

    public function authUser($user_id, $token_type = self::TOKEN_TYPE_TEMPORARY)
    {
        $expire_date = date("Y-m-d H:i:s", strtotime("+ ".self::$token_live_time." seconds"));

        $hash = md5(self::$salt.date("Y-m-d H:i:s").$user_id.rand(0, 999999));

        $this->dp->doQuery("INSERT INTO `app_users_auth_tokens` ".
            "(token, user_id, expire_date, type) VALUES ".
            "(".$this->dp->quote($hash).", ".
            $user_id.", ".
            $this->dp->quote($expire_date).", ".
            $this->dp->quote($token_type).")"
        );

        return [
            'token' => $hash,
            'token_type' => $token_type,
            'expire_date' => ($token_type === self::TOKEN_TYPE_PERMANENT ? null : $expire_date)
        ];
    }

    public function updateTokenExpireDate(string $token, $seconds = 0) {
        $expire_seconds = $seconds ? $seconds : self::$token_live_time;
        $expire_date = date("Y-m-d H:i:s", strtotime("+ $expire_seconds seconds"));
        $auth = $this->dp->getArray("SELECT * FROM `app_users_auth_tokens` ".
            "WHERE `token` = ".$this->dp->quote($token)." ".
            "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(self::TOKEN_TYPE_PERMANENT).")");

        if ($auth) {
            $this->dp->doQuery("UPDATE `app_users_auth_tokens` SET `expire_date` = ".$this->dp->quote($expire_date)." ".
                "WHERE `token` = ".$this->dp->quote($token));
            return true;
        }

        $this->dp->doQuery("DELETE FROM `app_users_auth_tokens` ".
                "WHERE `token` = ".$this->dp->quote($token));

        return false;
    }

    public function getUserIdByToken(string $token)
    {
        return $this->dp->getValue("SELECT user_id FROM `app_users_auth_tokens` ".
            "WHERE `token` = ".$this->dp->quote($token)." ".
            "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(self::TOKEN_TYPE_PERMANENT).")");
    }

    public function logoutUser(string $token, bool $all_devices = false)
    {
        if ($all_devices) {
            $user_id = $this->getUserIdByToken($token);
            if ($user_id) {
                $this->dp->doQuery("DELETE FROM `app_users_auth_tokens` ".
                    "WHERE `user_id` = ".$user_id);
            }
        } else {
            $this->dp->doQuery("DELETE FROM `app_users_auth_tokens` ".
                "WHERE `token` = ".$this->dp->quote($token)." ".
                "AND (`expire_date` > ".$this->dp->quote(date("Y-m-d H:i:s"))." OR `type` = ".$this->dp->quote(self::TOKEN_TYPE_PERMANENT).")");
        }
    }

    public static function getTokenFromHeaders($headers)
    {
        return trim(preg_replace("/\s*bearer\s*/i","", $headers['Authorization'] ?? ''));
    }
}