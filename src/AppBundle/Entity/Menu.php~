<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\User;

/**
 * Menu
 *
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MenuRepository")
 */
class Menu
{
	public function __construct() {
		$this->dagen = new ArrayCollection();
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
     * @ORM\Column(name="naam", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $naam;

    /**
     * @ORM\OneToMany(targetEntity="Dag", mappedBy="menu", cascade={"persist","remove"})
     */
	private $dagen;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="menus")
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

    /**
     * Set naam
     *
     * @param string $naam
     * @return Menu
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;

        return $this;
    }

    /**
     * Get naam
     *
     * @return string 
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Menu
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
     * Add dagen
     *
     * @param \AppBundle\Entity\Dag $dagen
     *
     * @return Menu
     */
    public function addDagen(\AppBundle\Entity\Dag $dagen)
    {
        // set Menu on each Dag instance
        $dagen->setMenu($this);

        $this->dagen[] = $dagen;

        return $this;
    }

    /**
     * Remove dagen
     *
     * @param \AppBundle\Entity\Dag $dagen
     */
    public function removeDagen(\AppBundle\Entity\Dag $dagen)
    {
        $this->dagen->removeElement($dagen);
    }

    /**
     * Get dagen
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDagen()
    {
        return $this->dagen;
    }
}
