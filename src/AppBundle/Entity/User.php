<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User
 *
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="E-mail is al in gebruik")
 * @UniqueEntity(fields="username", message="Gebruikersnaam is al in gebruik")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\NotBlank(groups={"Default"})
     * @Assert\Length(max=4096, groups={"Default"})
     */
    private $plainPassword;    

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;
    
	/**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        $this->recepten = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /**
     * @ORM\OneToMany(targetEntity="Recept", mappedBy="user")
     */
	private $recepten;
	
	/**
	 * @ORM\OneToOne(targetEntity="Boodschappenlijst", mappedBy="user")
	 */
	private $boodschappenlijst;
	
	/**
	 * @ORM\OneToMany(targetEntity="Menu", mappedBy="user")
	 */
	private $menus;

    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="user", cascade={"all"})
     */
    private $tags;
	
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
        ) = unserialize($serialized);
    }

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var \DateTime $registratiedatum
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @var \DateTime
     */   
    private $registratiedatum;

    /**
     * @var \DateTime $passwordRequestedAt
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */  
    private $passwordRequestedAt;

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add recepten
     *
     * @param \AppBundle\Entity\Recept $recepten
     * @return User
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

    /**
     * Set boodschappenlijst
     *
     * @param \AppBundle\Entity\Boodschappenlijst $boodschappenlijst
     * @return User
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
     * Add menus
     *
     * @param \AppBundle\Entity\Menu $menus
     * @return User
     */
    public function addMenu(\AppBundle\Entity\Menu $menus)
    {
        $this->menus[] = $menus;

        return $this;
    }

    /**
     * Remove menus
     *
     * @param \AppBundle\Entity\Menu $menus
     */
    public function removeMenu(\AppBundle\Entity\Menu $menus)
    {
        $this->menus->removeElement($menus);
    }

    /**
     * Get menus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMenus()
    {
        return $this->menus;
    }
    
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Add tag
     *
     * @param \AppBundle\Entity\Tag $tag
     *
     * @return User
     */
    public function addTag(\AppBundle\Entity\Tag $tag)
    {
        $tag->setUser($this);

        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AppBundle\Entity\Tag $tag
     */
    public function removeTag(\AppBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set confirmationToken
     *
     * @param string $confirmationToken
     *
     * @return User
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * Get confirmationToken
     *
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set registratiedatum
     *
     * @param \DateTime $registratiedatum
     *
     * @return User
     */
    public function setRegistratiedatum($registratiedatum)
    {
        $this->registratiedatum = $registratiedatum;

        return $this;
    }

    /**
     * Get registratiedatum
     *
     * @return \DateTime
     */
    public function getRegistratiedatum()
    {
        return $this->registratiedatum;
    }

    /**
     * Set passwordRequestedAt
     *
     * @param \DateTime $passwordRequestedAt
     *
     * @return User
     */
    public function setPasswordRequestedAt(\DateTime $passwordRequestedAt=null)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    /**
     * Get passwordRequestedAt
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }    
}
