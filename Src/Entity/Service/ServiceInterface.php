<?php

namespace App\Entity\Service;

use App\Entity\AbstractEntityInterface;

interface ServiceInterface extends AbstractEntityInterface {

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);
}