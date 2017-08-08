<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\AccountNonExpiredController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use AppBundle\Controller\UserController;

class AccountListener
{
    private $token_storage;
    private $resolver;

    public function __construct(TokenStorageInterface $token_storage, ControllerResolverInterface $resolver)
    {
        $this->token_storage = $token_storage;
        $this->resolver = $resolver;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof AccountNonExpiredController) {

            $user = $this->token_storage->getToken()->getUser();
        
            if ($user->isFreeAccountExpired(2592000)) {
                // throw new AccessDeniedHttpException('Your free account has expired!');
                $request = $event->getRequest();
                $request->attributes->set('_controller', 'AppBundle:User:expired');
                $newController = $this->resolver->getController($request);
                $event->setController($newController);    
            }
        }
    }
}