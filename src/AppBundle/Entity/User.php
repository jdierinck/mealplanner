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
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    const ACCOUNT_FREE = 'FREE';
    const ACCOUNT_PREMIUM = 'PREMIUM';
    
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

    /**
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    public function __construct()
    {
        $this->isActive = true;
        $this->recepten = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->roles = array();
        $this->afdelingenordered = new ArrayCollection();
        $this->afdelingen = new ArrayCollection();
        $this->token = $this->generateToken(20);
    }

    public function generateToken($length)
    {
        if (function_exists('random_bytes')) { // PHP 7
            return bin2hex(random_bytes($length));
        }
        // if (function_exists('mcrypt_create_iv')) {
        //     return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        // }
        if (function_exists('openssl_random_pseudo_bytes')) { // PHP 5
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    public function setToken()
    {
        $this->token = $this->generateToken(20);

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        return null; // No need to hash the password with a salt since we are using bcrypt
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        // return array('ROLE_USER');
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;
        return array_unique($roles);
    }

    /**
     * Adds a role to the user.
     *
     * @param string $role
     *
     * @return self
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the AuthorizationChecker, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $authorizationChecker->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Removes a role to the user.
     *
     * @param string $role
     *
     * @return self
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
        return $this;
    }

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles)
    {
        $this->roles = array();
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @ORM\OneToMany(targetEntity="Recept", mappedBy="user", cascade={"remove"})
     */
	private $recepten;

    public function hasRecepten()
    {
        return count($this->recepten) !== 0;
    }

    /**
     * @ORM\OneToOne(targetEntity="Mealplan", mappedBy="user", cascade={"remove"})
     */
    private $mealplan;

	/**
	 * @ORM\OneToMany(targetEntity="Menu", mappedBy="user", cascade={"remove"})
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
     * @ORM\OneToMany(targetEntity="AfdelingOrdered", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    protected $afdelingenordered;

    private $afdelingen;
    
    public function getAfdelingen()
    {
        $afdelingen = new ArrayCollection();
        
        foreach ($this->afdelingenordered as $ao) {
            $afdelingen[] = $ao->getAfdeling();
        }

        return $afdelingen;
    }   
    
    // Alternatively:
    // public function getAfdelingen()
    // {
    //     return array_map(
    //         function ($afdelingordered) {
    //             return $afdelingordered->getAfdeling();
    //         },
    //         $this->afdelingenordered->toArray()
    //     );
    // }

    public function setAfdelingen($afdelingen)
    {
        foreach ($afdelingen as $afdeling) {
                
                $ao = new AfdelingOrdered();

                $ao->setUser($this);
                $ao->setAfdeling($afdeling);
                
                $this->addAfdelingenordered($ao);
        }
    }

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $account;

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

    /**
     * Add afdelingenordered
     *
     * @param \AppBundle\Entity\AfdelingOrdered $afdelingenordered
     *
     * @return User
     */
    public function addAfdelingenordered(\AppBundle\Entity\AfdelingOrdered $afdelingordered)
    {
        if (!$this->afdelingenordered->contains($afdelingordered)) {
            $this->afdelingenordered->add($afdelingordered);
            $afdelingordered->setUser($this);
        }

        return $this;        
    }

    /**
     * Remove afdelingenordered
     *
     * @param \AppBundle\Entity\AfdelingOrdered $afdelingenordered
     */
    public function removeAfdelingenordered(\AppBundle\Entity\AfdelingOrdered $afdelingordered)
    {
        if ($this->afdelingenordered->contains($afdelingordered)) {
            $this->afdelingenordered->removeElement($afdelingordered);
            $afdelingordered->setUser(null);
        }

        return $this;        
    }

    /**
     * Get afdelingenordered
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAfdelingenordered()
    {
        return $this->afdelingenordered;
    }

    /**
     * Set account
     *
     * @param string $account
     *
     * @return User
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set mealplan
     *
     * @param \AppBundle\Entity\Mealplan $mealplan
     *
     * @return User
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
}
