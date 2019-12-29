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
     * @var UserGroups
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

    public function __construct(array $data = [])
    {
        if (!empty($data['groups'])) {
            $data['groups'] = new UserGroups(explode(",", $data['groups']));
        } else {
            $data['groups'] = (new UserGroups([]))->add(UserGroups::USER);
        }
        parent::__construct($data);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this|UserInterface
     * @throws \Exception
     */
    public function setId(int $id)
    {
        if ($this->getId()) {
            throw new \Exception("Changing id is forbidden");
        }
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
     * @return UserGroups
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param UserGroups $groups
     * @return $this
     * @throws \Exception
     */
    public function setGroups(UserGroups $groups)
    {
        $this->groups = $groups;
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