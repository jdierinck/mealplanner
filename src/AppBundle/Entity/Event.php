<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct()
    {
        $this->uid = uniqid();
        $this->recepten = new ArrayCollection();
    }

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="timeslot", type="integer")
     */
    private $timeSlot;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255, unique=true)
     */
    private $uid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="Recept", inversedBy="events")
     * @ORM\JoinTable(name="events_recipes")
     */ 
    private $recepten;

    /**
     * @ORM\ManyToOne(targetEntity="Mealplan", inversedBy="events")
     * @ORM\JoinColumn(name="mealplan_id", referencedColumnName="id")
     */
    private $mealplan;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        $titles = [];
        foreach ($this->getRecepten() as $recept) {
            $titles[] = $recept->getTitel();
        }
        $this->description = implode(', ', $titles);

        return $this->description;
    }

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return Event
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Event
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Event
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add recepten
     *
     * @param \AppBundle\Entity\Recept $recepten
     *
     * @return Event
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

    public function hasRecepten()
    {
        return count($this->recepten) > 0;
    }

    /**
     * Set mealplan
     *
     * @param \AppBundle\Entity\Mealplan $mealplan
     *
     * @return Event
     */
    public function setMealplan(\AppBundle\Entity\Mealplan $mealplan = null)
    {
        $this->mealplan = $mealplan;

        return $this;
    }

    /**
     * Get mealplan
     *
     * @return \AppBundle\Entity\Mealplan
     */
    public function getMealplan()
    {
        return $this->mealplan;
    }

    /**
     * Set timeSlot
     *
     * @param integer $timeSlot
     *
     * @return Event
     */
    public function setTimeSlot($timeSlot)
    {
        $this->timeSlot = $timeSlot;

        return $this;
    }

    /**
     * Get timeSlot
     *
     * @return integer
     */
    public function getTimeSlot()
    {
        return $this->timeSlot;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Event
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
