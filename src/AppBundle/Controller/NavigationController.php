<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NavigationController extends Controller
{
    
    public function menuAction(Request $request)
    {
        return $this->render('navigation/navigation.html.twig');
    }
  
}
