<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="company_cars")
 */
class CompanyCar
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $brand;

    public function __construct($brand = null) {
        $this->brand = $brand;
    }

    public function getId() {
        return $this->id;
    }

    public function getBrand() {
        return $this->brand;
    }
}