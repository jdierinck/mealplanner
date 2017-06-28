<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AfdelingOrdered
 *
 * @ORM\Table(name="afdeling_ordered")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class AfdelingOrdered
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="afdelingenordered")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     */    
    private $user;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Afdeling", inversedBy="afdelingenordered")
     * @ORM\JoinColumn(name="afdeling_id", referencedColumnName="id")
     *
     */    
    private $afdeling;

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
     *
     * @return AfdelingOrdered
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return AfdelingOrdered
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
     * Set afdeling
     *
     * @param \AppBundle\Entity\Afdeling $afdeling
     *
     * @return AfdelingOrdered
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
}
