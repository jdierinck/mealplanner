<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Afdeling
 *
 * @ORM\Table(name="afdeling")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AfdelingRepository")
 */
class Afdeling
{

	public function __construct(){
		$this->ingredienten = new ArrayCollection();
		$this->ingrbl = new ArrayCollection();
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Ingredient", mappedBy="afdeling")
     * @ORM\OrderBy({"ingredient" = "ASC"})
     */
    private $ingredienten; 
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */        
    private $voedingswaren;  
    
    /**
     * @ORM\OneToMany(targetEntity="IngrBL", mappedBy="afdeling", cascade={"all"})
     * @ORM\OrderBy({"ingr_ingr" = "ASC"})     
     */    
    private $ingrbl;    

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
     * @return Afdeling
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
     * Add ingredienten
     *
     * @param \AppBundle\Entity\Ingredient $ingredienten
     * @return Afdeling
     */
    public function addIngredienten(\AppBundle\Entity\Ingredient $ingredienten)
    {
        $this->ingredienten[] = $ingredienten;

        return $this;
    }

    /**
     * Remove ingredienten
     *
     * @param \AppBundle\Entity\Ingredient $ingredienten
     */
    public function removeIngredienten(\AppBundle\Entity\Ingredient $ingredienten)
    {
        $this->ingredienten->removeElement($ingredienten);
    }

    /**
     * Get ingredienten
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIngredienten()
    {
        return $this->ingredienten;
    }

    /**
     * Set voedingswaren
     *
     * @param string $voedingswaren
     * @return Afdeling
     */
    public function setVoedingswaren($voedingswaren)
    {
        $this->voedingswaren = $voedingswaren;

        return $this;
    }

    /**
     * Get voedingswaren
     *
     * @return string 
     */
    public function getVoedingswaren()
    {
        return $this->voedingswaren;
    }

    /**
     * Add ingrbl
     *
     * @param \AppBundle\Entity\IngrBL $ingrbl
     *
     * @return Afdeling
     */
    public function addIngrbl(\AppBundle\Entity\IngrBL $ingrbl)
    {
        $this->ingrbl[] = $ingrbl;

        return $this;
    }

    /**
     * Remove ingrbl
     *
     * @param \AppBundle\Entity\IngrBL $ingrbl
     */
    public function removeIngrbl(\AppBundle\Entity\IngrBL $ingrbl)
    {
        $this->ingrbl->removeElement($ingrbl);
    }

    /**
     * Get ingrbl
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIngrbl()
    {
        return $this->ingrbl;
    }
}
