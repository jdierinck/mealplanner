<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
// use Doctrine\Common\Collections\Criteria;

/**
 * Gerecht
 *
 * @ORM\Table(name="gerecht")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GerechtRepository")
 */
class Gerecht
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
     * @ORM\OneToMany(targetEntity="Recept", mappedBy="gerecht")
     */
    private $recepten;

    public function __construct()
    {
        $this->recepten = new ArrayCollection();
    }    

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * @return Gerecht
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
     * @return Gerecht
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

    // public function getReceptenByUser($user){
    //     $criteria = Criteria::create();
    //     $criteria->where(Criteria::expr()->eq('user', $user));
        
    //     return $this->recepten->matching($criteria);
    // }
}
