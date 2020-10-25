<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ingredient
 *
 * @ORM\Table(name="ingredient")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IngredientRepository")
 */
class Ingredient
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
     * @ORM\ManyToOne(targetEntity="Recept", inversedBy="ingredienten")
     * @ORM\JoinColumn(name="recept_id", referencedColumnName="id")
     */
    private $recept;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     * @Assert\Type(type="numeric")
     */
    private $hoeveelheid;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type(type="alpha")
     */
    private $eenheid;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $ingredient;
    
	/**
     * @ORM\ManyToOne(targetEntity="Afdeling", inversedBy="ingredienten")
     * @ORM\JoinColumn(name="afdeling_id", referencedColumnName="id")
     */    
    private $afdeling;

    /**
     * @ORM\Column(type="boolean")
     */
    private $section;

    public function isSection()
    {
        return $this->section;
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
     * Set hoeveelheid
     *
     * @param string $hoeveelheid
     * @return Ingredient
     */
    public function setHoeveelheid($hoeveelheid)
    {
        $this->hoeveelheid = $hoeveelheid;

        return $this;
    }

    /**
     * Get hoeveelheid
     *
     * @return string 
     */
    public function getHoeveelheid()
    {
        return $this->hoeveelheid;
    }

    /**
     * Set eenheid
     *
     * @param string $eenheid
     * @return Ingredient
     */
    public function setEenheid($eenheid)
    {
        $this->eenheid = $eenheid;

        return $this;
    }

    /**
     * Get eenheid
     *
     * @return string 
     */
    public function getEenheid()
    {
        return $this->eenheid;
    }

    /**
     * Set ingredient
     *
     * @param string $ingredient
     * @return Ingredient
     */
    public function setIngredient($ingredient)
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    /**
     * Get ingredient
     *
     * @return string 
     */
    public function getIngredient()
    {
        return $this->ingredient;
    }

    /**
     * Set recept
     *
     * @param \AppBundle\Entity\Recept $recept
     * @return Ingredient
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
     * Set afdeling
     *
     * @param \AppBundle\Entity\Afdeling $afdeling
     * @return Ingredient
     */
    public function setAfdeling(\AppBundle\Entity\Afdeling $afdeling = null)
    {
        $this->afdeling = $afdeling;

        return $this;
    }

    /**
     * Get afdeling
     *
     * @return \AppBundle\Entity\Afdeling 
     */
    public function getAfdeling()
    {
        return $this->afdeling;
    }

    /**
     * Set section
     *
     * @param boolean $section
     *
     * @return Ingredient
     */
    public function setSection($section = false)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section
     *
     * @return boolean
     */
    public function getSection()
    {
        return $this->section;
    }
}
