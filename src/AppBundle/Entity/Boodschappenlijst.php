<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\IngRBL;

/**
 * Boodschappenlijst
 *
 * @ORM\Table(name="boodschappenlijst")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BoodschappenlijstRepository")
 */
class Boodschappenlijst
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ingredienten = new ArrayCollection();
        $this->receptenblordered = new ArrayCollection();
		$this->recepten = new ArrayCollection();
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
    
//     /**
//      * @ORM\OneToMany(targetEntity="Ingredient", mappedBy="boodschappenlijst")
//      * @ORM\OrderBy({"ingredient" = "ASC"})
//      */
//     private $ingredienten;

    /**
     * @ORM\OneToMany(targetEntity="ReceptBLOrdered", mappedBy="boodschappenlijst", cascade={"all"})
     */
	private $receptenblordered;
	
	private $recepten;
	
    public function getRecepten()
    {
        $recepten = new ArrayCollection();
        
        foreach ($this->receptenblordered as $r) {
            $recepten[] = $r->getRecept();
        }

        return $recepten;
    }	
    
    public function setRecepten($recepten)
    {	
        foreach ($recepten as $r) {
            $ro = new ReceptBLOrdered();

            $ro->setBoodschappenlijst($this);
            $ro->setRecept($r);
            $ro->setServings(4);
            
            $this->addReceptenblordered($ro);
        }
    }

    
    /**
     * @ORM\OneToMany(targetEntity="IngrBL", mappedBy="boodschappenlijst", cascade={"all"})
     */    
    private $ingrbl;
    
	private $ingredienten;
	
    public function getIngredienten()
    {
        $ingredienten = new ArrayCollection();
        
        foreach ($this->ingrbl as $i) {
            $ingredienten[] = $i->getIngredient();
        }

        return $ingredienten;
    }	
    
    public function setIngredienten($ingredienten)
    {	
        foreach ($ingredienten as $i) {
            $ibl = new IngrBL();

            $ibl->setBoodschappenlijst($this);
            $ibl->setIngredient($i);
            $ibl->setAfdeling($i->getAfdeling());
            $ibl->setServings(4);
            $ibl->setIngrIngr($i->getIngredient());
            
            $this->addIngrbl($ibl);
        }
    }    
    
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="boodschappenlijst")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
	private $user;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
//     /**
//      * Add ingredienten
//      *
//      * @param \AppBundle\Entity\Ingredient $ingredienten
//      * @return Boodschappenlijst
//      */
//     public function addIngredienten(\AppBundle\Entity\Ingredient $ingredienten)
//     {
//         $this->ingredienten[] = $ingredienten;
// 
//         return $this;
//     }
// 
//     /**
//      * Remove ingredienten
//      *
//      * @param \AppBundle\Entity\Ingredient $ingredienten
//      */
//     public function removeIngredienten(\AppBundle\Entity\Ingredient $ingredienten)
//     {
//         $this->ingredienten->removeElement($ingredienten);
//     }
// 
//     /**
//      * Get ingredienten
//      *
//      * @return \Doctrine\Common\Collections\Collection 
//      */
//     public function getIngredienten()
//     {
//         return $this->ingredienten;
//     }

    /**
     * Add receptenblordered
     *
     * @param \AppBundle\Entity\ReceptBLOrdered $receptenblordered
     * @return Boodschappenlijst
     */
    public function addReceptenblordered(\AppBundle\Entity\ReceptBLOrdered $receptenblordered)
    {
        $this->receptenblordered[] = $receptenblordered;

        return $this;
    }

    /**
     * Remove receptenblordered
     *
     * @param \AppBundle\Entity\ReceptBLOrdered $receptenblordered
     */
    public function removeReceptenblordered(\AppBundle\Entity\ReceptBLOrdered $receptenblordered)
    {
        $this->receptenblordered->removeElement($receptenblordered);
    }

    /**
     * Get receptenblordered
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReceptenblordered()
    {
        return $this->receptenblordered;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Boodschappenlijst
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add ingrbl
     *
     * @param \AppBundle\Entity\IngrBL $ingrbl
     *
     * @return Boodschappenlijst
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
