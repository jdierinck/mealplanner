<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Dag
 *
 * @ORM\Table(name="dag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DagRepository")
 */
class Dag
{
	public function __construct() {
        $this->recepten = new ArrayCollection();
	}
	
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Recept", inversedBy="dagen")
     * @ORM\JoinTable(name="dagen_recepten")
     */
	private $recepten;

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="dagen")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     */
    private $menu; 

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
     * Add recepten
     *
     * @param \AppBundle\Entity\Recept $recepten
     *
     * @return Dag
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

    /**
     * Set menu
     *
     * @param \AppBundle\Entity\Menu $menu
     *
     * @return Dag
     */
    public function setMenu(\AppBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return \AppBundle\Entity\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }
}
