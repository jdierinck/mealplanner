<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {   
  //       $users = array(
  //       	0 => array("name"=>"admin", "password"=>"admin", "email"=>"admin@invalid.be"),
  //       	1 => array("name"=>"Joske Vermeulen", "password"=>"joske", "email"=>"joske@invalid.be"),
  //       	2 => array("name"=>"Mireille Meisje", "password"=>"mireille", "email"=>"mireille@invalid.be"),
  //       );
		
		// $encoder = $this->container->get('security.password_encoder');
		
		// foreach($users as $u){
		// 	$user = new User();
		// 	$user->setUsername($u["name"]);
		// 	$encoded = $encoder->encodePassword($user, $u["password"]);
		// 	$user->setPassword($encoded);
		// 	$user->setEmail($u["email"]);
			
		// 	$manager->persist($user);
		// }
        
  //       $manager->flush();
    }
}