<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * YieldType
 *
 * @ORM\Table(name="yield_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YieldTypeRepository")
 */
class YieldType
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_singular", type="string", length=255)
     */
    private $unitSingular;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_plural", type="string", length=255)
     */
    private $unitPlural;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set unitSingular
     *
     * @param string $unitSingular
     *
     * @return YieldType
     */
    public function setUnitSingular($unitSingular)
    {
        $this->unitSingular = $unitSingular;

        return $this;
    }

    /**
     * Get unitSingular
     *
     * @return string
     */
    public function getUnitSingular()
    {
        return $this->unitSingular;
    }

    /**
     * Set unitPlural
     *
     * @param string $unitPlural
     *
     * @return YieldType
     */
    public function setUnitPlural($unitPlural)
    {
        $this->unitPlural = $unitPlural;

        return $this;
    }

    /**
     * Get unitPlural
     *
     * @return string
     */
    public function getUnitPlural()
    {
        return $this->unitPlural;
    }

    /**
     * Get unit
     *
     * @return string
     */
    public function getUnit()
    {
        return [
            'singular' => $this->unitSingular,
            'plural' => $this->unitPlural,
        ];
    }
}
