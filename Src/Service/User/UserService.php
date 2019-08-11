<?php

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\System\DataProvider\Mysql\DataProviderInterface;

class UserService {

    /**
     * @var DataProviderInterface
     */
    protected $dp;

    public function __construct(DataProviderInterface $dp)
    {
        $this->dp = $dp;
    }

    public function createUser($data)
    {
        $this->dp->doQuery("INSERT INTO `app_users` (`email`, `name`, `password`) ".
            "VALUES (".$this->dp->quote($data['email']).", ".$this->dp->quote($data['name']).", ".$this->dp->quote($data['password']).")");
        $user_id = $this->dp->getLastInsertId();
        if ($user_id) {
            return $this->getUserById($user_id);
        }
        return null;
    }

    public function saveUser(User $user, array $fields = [])
    {
        $params = self::toDb($user->toArray());

        $insert = [];

        foreach ($params as $field => $param) {
            if (!empty($fields) && !in_array($field, $fields)) {
                continue;
            }
            if ($param === null) {
                continue;
            }
            $insert[] = "`".$field."` = ".$this->dp->quote($param);
        }

        if ($insert) {
            $this->dp->doQuery("UPDATE `app_users` ".
                "SET ".implode(", ", $insert)." WHERE id = ".$user->getId());
        }

    }

    public function getUserById(int $id)
    {
        $user = $this->dp->getArray("SELECT * FROM `app_users` WHERE id = ".$id);
        if ($user) {
            return new User(self::fromDb($user));
        }
        return null;
    }

    public function getUserByEmail(string $email)
    {
        $user = $this->dp->getArray("SELECT * FROM `app_users` WHERE email = ".$this->dp->quote($email));
        if ($user) {
            return new User(self::fromDb($user));
        }
        return null;
    }

    protected static function fromDb(array $user)
    {
        $user['groups'] = explode(",", $user['groups']);
        return $user;
    }

    protected static function toDb(array $user)
    {
        $user['groups'] = implode(",", $user['groups']);
        unset($user['id']);
        return $user;
    }
}