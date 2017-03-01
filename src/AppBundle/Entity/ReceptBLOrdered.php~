<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ReceptBLOrdered
 *
 * @ORM\Table(name="recept_bl_ordered")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class ReceptBLOrdered
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
     * @ORM\ManyToOne(targetEntity="Boodschappenlijst", inversedBy="receptenblordered")
     * @ORM\JoinColumn(name="bl_id", referencedColumnName="id", onDelete="SET NULL")
     *
     */    
    private $boodschappenlijst;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Recept", inversedBy="receptenblordered")
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
     * @return ReceptBLOrdered
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
     * Set boodschappenlijst
     *
     * @param \AppBundle\Entity\Boodschappenlijst $boodschappenlijst
     * @return ReceptBLOrdered
     */
    public function setBoodschappenlijst(\AppBundle\Entity\Boodschappenlijst $boodschappenlijst = null)
    {
        $this->boodschappenlijst = $boodschappenlijst;

        return $this;
    }

    /**
     * Get boodschappenlijst
     *
     * @return \AppBundle\Entity\Boodschappenlijst 
     */
    public function getBoodschappenlijst()
    {
        return $this->boodschappenlijst;
    }

    /**
     * Set recept
     *
     * @param \AppBundle\Entity\Recept $recept
     * @return ReceptBLOrdered
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
