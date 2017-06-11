<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime 
     */
    private $datum;

	/**
	 * @ORM\Column(type="integer")
	 * @Assert\Type("integer")
	 */
	private $servings;
	
	/**
	 * @ORM\OneToMany(targetEntity="IngrBL", mappedBy="receptblordered", cascade={"persist"})
	 */
	private $ingrbl;

	public function setIngrBL($ingredienten){
		foreach($ingredienten as $i){
			$ibl = new IngrBL();
			
			$ibl->setBoodschappenlijst($this->getBoodschappenlijst());
            $ibl->setIngredient($i);
            $ibl->setAfdeling($i->getAfdeling());
            $ibl->setServings(4);
            $ibl->setIngrIngr($i->getIngredient());
			$ibl->setReceptblordered($this);
			
			$this->addIngrbl($ibl);
		}
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

    /**
     * Set servings
     *
     * @param integer $servings
     *
     * @return ReceptBLOrdered
     */
    public function setServings($servings)
    {
        $this->servings = $servings;

        return $this;
    }

    /**
     * Get servings
     *
     * @return integer
     */
    public function getServings()
    {
        return $this->servings;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ingrbl = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add ingrbl
     *
     * @param \AppBundle\Entity\IngrBL $ingrbl
     *
     * @return ReceptBLOrdered
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

    /**
     * Set datum
     *
     * @param \DateTime $datum
     *
     * @return ReceptBLOrdered
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;

        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime
     */
    public function getDatum()
    {
        return $this->datum;
    }
}
