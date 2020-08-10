<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use AppBundle\Entity\Mealplan;


class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        // Get the User entity.
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user->getMealplan()) {
            $mealplan = new Mealplan();
            $mealplan->setUser($user);
            $this->em->persist($mealplan);

            $this->em->flush();
        }
    }
}