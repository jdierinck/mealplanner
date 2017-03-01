<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ReceptOrdered
 *
 * @ORM\Table(name="recept_ordered")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class ReceptOrdered
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
     *
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="receptenordered")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id", onDelete="SET NULL")
     *
     */    
    private $menu;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Recept", inversedBy="receptenordered")
     * @ORM\JoinColumn(name="recept_id", referencedColumnName="id")
     *
     */    
    private $recept;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(name="positie", type="integer")
     */
    private $positie;


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
     * Set positie
     *
     * @param integer $positie
     * @return ReceptOrdered
     */
    public function setPositie($positie)
    {
        $this->positie = $positie;

        return $this;
    }

    /**
     * Get positie
     *
     * @return integer 
     */
    public function getPositie()
    {
        return $this->positie;
    }

    /**
     * Set menu
     *
     * @param \AppBundle\Entity\Menu $menu
     * @return ReceptOrdered
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

    /**
     * Set recept
     *
     * @param \AppBundle\Entity\Recept $recept
     * @return ReceptOrdered
     */
    public function setRecept(\AppBundle\Entity\Recept $recept = null)
    {
        $this->recept = $recept;

        return $this;
    }

    /**
     * Get recept
     *
     * @return \AppBundle\Entity\Recept 
     */
    public function getRecept()
    {
        return $this->recept;
    }
}
