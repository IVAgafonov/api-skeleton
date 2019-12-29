<?php

namespace App\Service\User;

use App\Entity\AbstractEnum;
use App\Entity\AbstractSet;
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

    public function createUser($email, $name, $password)
    {
        $this->dp->doQuery("INSERT INTO `app_users` (`email`, `name`, `password`) ".
            "VALUES (".$this->dp->quote($email).", ".$this->dp->quote($name).", ".$this->dp->quote($password).")");
        $user_id = $this->dp->getLastInsertId();
        if ($user_id) {
            return $this->getUserById($user_id);
        }
        return null;
    }

    public function saveUser(User $user)
    {
        $params = $user->toDb();

        if (empty($params)) {
            throw new \Exception("Empty params to save of object: ".User::class);
        }

        $insert_params = [];
        $insert_values = [];
        $duplicate_values = [];

        foreach ($params as $field => $param) {
            $insert_params[] = "`".$field."`";
            $insert_values[] = $this->dp->quote($param);
            $duplicate_values[] = "`".$field."` = ".$this->dp->quote($param);
        }

        $this->dp->doQuery("INSERT INTO `app_users` ".
            "(".implode(",", $insert_params).") VALUES (".implode(",", $insert_values).") ".
            "ON DUPLICATE KEY UPDATE ".implode(",", $duplicate_values));
        if ($this->dp->getLastInsertId()) {
            $user = $this->getUserById($this->dp->getLastInsertId());
        } else {
            $user = $this->getUserById($user->getId());
        }
        return $user;
    }

    public function getUserById(int $id)
    {
        $user = $this->dp->getArray("SELECT * FROM `app_users` WHERE id = ".$id);
        if ($user) {
            return new User($user);
        }
        return null;
    }

    public function getUserByEmail(string $email)
    {
        $user = $this->dp->getArray("SELECT * FROM `app_users` WHERE email = ".$this->dp->quote($email));
        if ($user) {
            return new User($user);
        }
        return null;
    }
}