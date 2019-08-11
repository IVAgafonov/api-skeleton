<?php

namespace App\Entity\Auth;

use App\Entity\AbstractEntityInterface;

interface AuthInterface extends AbstractEntityInterface {
    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token);

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId(int $user_id);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     * @throws \Exception
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getCreateDate();

    /**
     * @param string $create_date
     * @return $this
     */
    public function setCreateDate(string $create_date);

    /**
     * @return string
     */
    public function getExpireDate();

    /**
     * @param string $expire_date
     * @return $this
     */
    public function setExpireDate(string $expire_date);
}