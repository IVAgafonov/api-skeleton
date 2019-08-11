<?php

namespace App\Entity\User;

use App\Entity\AbstractEntity;

class User extends AbstractEntity implements UserInterface {

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var string
     */
    protected $create_date;

    /**
     * @var string
     */
    protected $delete_date;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return $this
     * @throws \Exception
     */
    public function setGroups(array $groups)
    {
        $this->groups = UserGroups::validate($groups);
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
    public function getDeleteDate()
    {
        return $this->delete_date;
    }

    /**
     * @param string $delete_date
     * @return $this
     */
    public function setDeleteDate(string $delete_date)
    {
        $this->delete_date = $delete_date;
        return $this;
    }


}