<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Hoofdingredient
 *
 * @ORM\Table(name="hoofdingredient")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HoofdingredientRepository")
 */
class Hoofdingredient
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Recept", mappedBy="hoofdingredient")
     */
    private $recepten;

    public function __construct()
    {
        $this->recepten = new ArrayCollection();
    }    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Hoofdingredient
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add recepten
     *
     * @param \AppBundle\Entity\Recept $recepten
     * @return Hoofdingredient
     */
    public function addRecepten(\AppBundle\Entity\Recept $recepten)
    {
        $this->recepten[] = $recepten;

        return $this;
    }

    /**
     * Remove recepten
     *
     * @param \AppBundle\Entity\Recept $recepten
     */
    public function removeRecepten(\AppBundle\Entity\Recept $recepten)
    {
        $this->recepten->removeElement($recepten);
    }

    /**
     * Get recepten
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecepten()
    {
        return $this->recepten;
    }
}
