<?php

namespace App\Entity\Service;

use App\Entity\AbstractEntity;

class Service extends AbstractEntity implements ServiceInterface {

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this|ServiceInterface
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
}