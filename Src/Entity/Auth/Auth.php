<?php

namespace App\Entity\Auth;

use App\Entity\AbstractEntity;

class Auth extends AbstractEntity implements AuthInterface {

    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $create_date;

    /**
     * @var string
     */
    protected $expire_date;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     * @throws \Exception
     */
    public function setType(string $type)
    {
        $this->type = AuthTypes::validate($type);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param string $create_date
     * @return $this
     */
    public function setCreateDate(string $create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpireDate()
    {
        return $this->expire_date;
    }

    /**
     * @param string $expire_date
     * @return $this
     */
    public function setExpireDate(string $expire_date)
    {
        $this->expire_date = $expire_date;
        return $this;
    }
}